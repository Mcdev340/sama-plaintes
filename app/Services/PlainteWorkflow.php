<?php

namespace App\Services;

use App\Enums\Domaine;
use App\Enums\Role;
use App\Enums\StatutPlainte;
use App\Enums\TypeNotification;
use App\Exceptions\TransitionInterdite;
use App\Models\AutoriteLocale;
use App\Models\Plainte;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Machine à états des plaintes.
 *
 * soumise -> assignee, s'il n'existe qu'une autorité compétente
 * soumise -> en_analyse -> assignee
 * assignee -> en_traitement -> en_attente -> en_traitement
 * en_traitement -> resolue -> fermee
 * en_traitement -> rejetee -> fermee, ou retour en analyse
 * assignee, en_traitement, en_attente -> escaladee -> assignee
 */
class PlainteWorkflow
{
    public function __construct(
        private NotificationService $notifications,
        private RoutageService $routage,
    ) {}

    /**
     * @param  array<string, mixed>  $donnees
     * @param  array<int, UploadedFile|null>  $pieces
     */
    public function deposer(User $citoyen, array $donnees, array $pieces = []): Plainte
    {
        return DB::transaction(function () use ($citoyen, $donnees, $pieces): Plainte {
            $plainte = $citoyen->plaintes()->create([
                'titre' => $donnees['titre'],
                'description' => $donnees['description'],
                'domaine' => $donnees['domaine'],
                'priorite' => $donnees['priorite'],
                'statut' => StatutPlainte::Soumise,
                'region' => $donnees['region'],
                'localisation' => $donnees['localisation'],
                'latitude' => $donnees['latitude'] ?? null,
                'longitude' => $donnees['longitude'] ?? null,
                'date_soumission' => now(),
            ]);

            $this->joindre($plainte, $citoyen, $pieces);
            $this->journal($plainte, $citoyen, 'depot', 'Dépôt de la plainte par le citoyen.', null, StatutPlainte::Soumise);

            $candidats = $this->routage->candidats($plainte->domaine, $plainte->region);

            if ($candidats->count() === 1) {
                $this->assignerInterne(
                    acteur: null,
                    plainte: $plainte,
                    autorite: $candidats->first(),
                    delai: $plainte->priorite->delaiJours(),
                    commentaire: 'Routage automatique : une seule autorité est compétente pour ce domaine et cette zone.',
                    auto: true,
                );
            } else {
                $texte = $candidats->isEmpty()
                    ? "Plainte {$plainte->reference} en attente de routage : aucune autorité ne couvre {$plainte->domaine->label()} à {$plainte->region}."
                    : "Plainte {$plainte->reference} à analyser : plusieurs autorités peuvent la prendre en charge à {$plainte->region}.";

                $this->notifications->auxSuperviseurs($plainte, $texte, TypeNotification::Alerte);
            }

            $this->notifications->envoyer(
                $citoyen,
                "Votre plainte {$plainte->reference} a bien été enregistrée.",
                TypeNotification::Confirmation,
                $plainte,
            );

            return $plainte->refresh();
        });
    }

    public function analyser(User $superviseur, Plainte $plainte): void
    {
        $this->exiger($plainte, [StatutPlainte::Soumise]);

        DB::transaction(function () use ($superviseur, $plainte): void {
            $this->changer($plainte, $superviseur, StatutPlainte::EnAnalyse, 'mise_en_analyse', 'Le superviseur prend la plainte en analyse.');
        });
    }

    public function assigner(User $superviseur, Plainte $plainte, AutoriteLocale $autorite, int $delai, ?string $commentaire): void
    {
        $this->exiger($plainte, [
            StatutPlainte::Soumise,
            StatutPlainte::EnAnalyse,
            StatutPlainte::Escaladee,
            StatutPlainte::Rejetee,
        ]);
        $this->verifierCompetence($plainte, $autorite);

        DB::transaction(function () use ($superviseur, $plainte, $autorite, $delai, $commentaire): void {
            $this->assignerInterne($superviseur, $plainte, $autorite, $delai, $commentaire, false);
        });
    }

    public function prendreEnCharge(User $agent, Plainte $plainte): void
    {
        $this->exigerAutorite($agent, $plainte);
        $this->exiger($plainte, [StatutPlainte::Assignee, StatutPlainte::Escaladee]);

        DB::transaction(function () use ($agent, $plainte): void {
            $this->changer(
                $plainte,
                $agent,
                StatutPlainte::EnTraitement,
                'prise_en_charge',
                'Prise en charge par '.$agent->autorite->institution.'.',
                $agent->autorite,
            );
            $this->notifications->envoyer(
                $plainte->citoyen,
                "Votre plainte {$plainte->reference} est en cours de traitement.",
                TypeNotification::Info,
                $plainte,
            );
        });
    }

    public function demanderComplement(User $agent, Plainte $plainte, string $commentaire): void
    {
        $this->exigerAutorite($agent, $plainte);
        $this->exiger($plainte, [StatutPlainte::EnTraitement, StatutPlainte::Escaladee]);

        DB::transaction(function () use ($agent, $plainte, $commentaire): void {
            $this->changer($plainte, $agent, StatutPlainte::EnAttente, 'demande_complement', $commentaire, $agent->autorite);
            $this->notifications->envoyer(
                $plainte->citoyen,
                "Un complément est demandé pour {$plainte->reference}.",
                TypeNotification::Alerte,
                $plainte,
            );
        });
    }

    /**
     * @param  array<int, UploadedFile|null>  $pieces
     */
    public function completer(User $citoyen, Plainte $plainte, string $commentaire, array $pieces = []): void
    {
        $this->exigerCitoyen($citoyen, $plainte);
        $this->exiger($plainte, [StatutPlainte::EnAttente]);

        DB::transaction(function () use ($citoyen, $plainte, $commentaire, $pieces): void {
            $this->joindre($plainte, $citoyen, $pieces);
            $this->changer($plainte, $citoyen, StatutPlainte::EnTraitement, 'complement_citoyen', $commentaire);
            $agent = $plainte->autorite?->user;
            if ($agent) {
                $this->notifications->envoyer(
                    $agent,
                    "Le citoyen a complété la plainte {$plainte->reference}.",
                    TypeNotification::Info,
                    $plainte,
                );
            }
        });
    }

    public function resoudre(User $agent, Plainte $plainte, string $commentaire): void
    {
        $this->exigerAutorite($agent, $plainte);
        $this->exiger($plainte, [StatutPlainte::EnTraitement, StatutPlainte::Escaladee]);

        DB::transaction(function () use ($agent, $plainte, $commentaire): void {
            $this->changer($plainte, $agent, StatutPlainte::Resolue, 'resolution', $commentaire, $agent->autorite);
            $this->notifications->envoyer(
                $plainte->citoyen,
                "Votre plainte {$plainte->reference} est marquée résolue. Vous pouvez évaluer la réponse.",
                TypeNotification::Confirmation,
                $plainte,
            );
            $this->notifications->auxSuperviseurs(
                $plainte,
                "La plainte {$plainte->reference} est résolue et attend une validation.",
                TypeNotification::Info,
            );
        });
    }

    public function rejeter(User $agent, Plainte $plainte, string $motif): void
    {
        $this->exigerAutorite($agent, $plainte);
        $this->exiger($plainte, [StatutPlainte::Assignee, StatutPlainte::EnTraitement, StatutPlainte::Escaladee]);

        DB::transaction(function () use ($agent, $plainte, $motif): void {
            $this->changer($plainte, $agent, StatutPlainte::Rejetee, 'rejet', $motif, $agent->autorite);
            $this->notifications->envoyer(
                $plainte->citoyen,
                "Votre plainte {$plainte->reference} a été rejetée. Le motif est disponible dans le dossier.",
                TypeNotification::Alerte,
                $plainte,
            );
            $this->notifications->auxSuperviseurs(
                $plainte,
                "La plainte {$plainte->reference} a été rejetée par {$agent->autorite->institution}.",
                TypeNotification::Alerte,
            );
        });
    }

    public function escalader(?User $acteur, Plainte $plainte, string $motif): void
    {
        $this->exiger($plainte, [
            StatutPlainte::Assignee,
            StatutPlainte::EnTraitement,
            StatutPlainte::EnAttente,
        ]);

        DB::transaction(function () use ($acteur, $plainte, $motif): void {
            $this->changer($plainte, $acteur, StatutPlainte::Escaladee, 'escalade', $motif, $plainte->autorite);
            $this->notifications->envoyer(
                $plainte->citoyen,
                "Le délai de {$plainte->reference} est dépassé. Le dossier a été escaladé.",
                TypeNotification::Alerte,
                $plainte,
            );
            if ($plainte->autorite?->user) {
                $this->notifications->envoyer(
                    $plainte->autorite->user,
                    "La plainte {$plainte->reference} a été escaladée : {$motif}",
                    TypeNotification::Alerte,
                    $plainte,
                );
            }
            $this->notifications->auxSuperviseurs($plainte, "Escalade de {$plainte->reference} : {$motif}", TypeNotification::Alerte);
        });
    }

    public function clore(User $superviseur, Plainte $plainte, ?string $commentaire): void
    {
        $this->exiger($plainte, [StatutPlainte::Resolue, StatutPlainte::Rejetee]);

        DB::transaction(function () use ($superviseur, $plainte, $commentaire): void {
            $this->changer(
                $plainte,
                $superviseur,
                StatutPlainte::Fermee,
                'cloture',
                $commentaire ?: 'Dossier clos par le superviseur.',
            );
            $this->notifications->envoyer(
                $plainte->citoyen,
                "Le dossier {$plainte->reference} est clos.",
                TypeNotification::Info,
                $plainte,
            );
        });
    }

    public function renvoyer(User $superviseur, Plainte $plainte, string $commentaire): void
    {
        $this->exiger($plainte, [StatutPlainte::Rejetee, StatutPlainte::Escaladee]);

        DB::transaction(function () use ($superviseur, $plainte, $commentaire): void {
            $avant = $plainte->statut;
            $plainte->autorite_id = null;
            $plainte->date_echeance = null;
            $plainte->statut = StatutPlainte::EnAnalyse;
            $plainte->save();
            $this->journal($plainte, $superviseur, 'renvoi', $commentaire, $avant, StatutPlainte::EnAnalyse);
            $this->notifications->auxSuperviseurs(
                $plainte,
                "La plainte {$plainte->reference} est renvoyée en analyse.",
                TypeNotification::Info,
            );
        });
    }

    public function evaluer(User $citoyen, Plainte $plainte, int $note, ?string $commentaire): void
    {
        $this->exigerCitoyen($citoyen, $plainte);
        $this->exiger($plainte, [StatutPlainte::Resolue, StatutPlainte::Fermee]);

        if ($plainte->satisfaction !== null) {
            throw new TransitionInterdite('Cette plainte a déjà été évaluée.');
        }

        DB::transaction(function () use ($citoyen, $plainte, $note, $commentaire): void {
            $plainte->satisfaction = $note;
            $plainte->commentaire_satisfaction = $commentaire;
            $plainte->save();
            $this->journal(
                $plainte,
                $citoyen,
                'evaluation',
                'Satisfaction : '.$note.'/5'.($commentaire ? ' — '.$commentaire : ''),
                $plainte->statut,
                $plainte->statut,
            );
        });
    }

    public function commenter(User $acteur, Plainte $plainte, string $commentaire, string $action = 'commentaire'): void
    {
        DB::transaction(function () use ($acteur, $plainte, $commentaire, $action): void {
            $this->journal($plainte, $acteur, $action, $commentaire, $plainte->statut, $plainte->statut, $acteur->autorite);
        });

        if ($action === 'mediation') {
            $this->notifications->envoyer(
                $plainte->citoyen,
                "Un superviseur a ajouté une note de médiation sur {$plainte->reference}.",
                TypeNotification::Info,
                $plainte,
            );
            if ($plainte->autorite?->user) {
                $this->notifications->envoyer(
                    $plainte->autorite->user,
                    "Médiation enregistrée sur {$plainte->reference}.",
                    TypeNotification::Info,
                    $plainte,
                );
            }
        }
    }

    /**
     * @param  array<int, UploadedFile|null>  $fichiers
     */
    public function joindre(Plainte $plainte, User $user, array $fichiers): void
    {
        foreach ($fichiers as $fichier) {
            if (! $fichier instanceof UploadedFile) {
                continue;
            }

            $chemin = $fichier->store('pieces/'.$plainte->id, 'local');
            $plainte->documents()->create([
                'user_id' => $user->id,
                'nom_fichier' => $fichier->getClientOriginalName(),
                'chemin' => $chemin,
                'type_fichier' => $fichier->getClientMimeType() ?: 'application/octet-stream',
                'taille' => $fichier->getSize() ?: 0,
            ]);
        }
    }

    private function assignerInterne(?User $acteur, Plainte $plainte, AutoriteLocale $autorite, int $delai, ?string $commentaire, bool $auto): void
    {
        $avant = $plainte->statut;
        $plainte->autorite_id = $autorite->id;
        $plainte->date_echeance = now()->addDays($delai);
        $plainte->statut = StatutPlainte::Assignee;
        $plainte->save();

        $plainte->assignations()->create([
            'superviseur_id' => $acteur?->role === Role::Superviseur ? $acteur->id : null,
            'autorite_id' => $autorite->id,
            'date_assignation' => now(),
            'delai_estime' => $delai,
            'commentaire' => $commentaire,
        ]);

        $this->journal(
            $plainte,
            $acteur,
            $auto ? 'routage_auto' : 'assignation',
            $commentaire ?: 'Plainte assignée à '.$autorite->institution.'.',
            $avant,
            StatutPlainte::Assignee,
            $autorite,
        );

        $this->notifications->envoyer(
            $plainte->citoyen,
            "Votre plainte {$plainte->reference} a été orientée vers {$autorite->institution}.",
            TypeNotification::Info,
            $plainte,
        );

        if ($autorite->user) {
            $this->notifications->envoyer(
                $autorite->user,
                "La plainte {$plainte->reference} vous a été assignée. Délai : {$delai} jour(s).",
                TypeNotification::Alerte,
                $plainte,
            );
        }

        $this->notifications->auxSuperviseurs(
            $plainte,
            ($auto ? 'Routage automatique' : 'Assignation')." de {$plainte->reference} vers {$autorite->institution}.",
            TypeNotification::Info,
        );
    }

    private function verifierCompetence(Plainte $plainte, AutoriteLocale $autorite): void
    {
        $domaine = $plainte->domaine instanceof Domaine ? $plainte->domaine->value : (string) $plainte->domaine;

        if ($autorite->zone !== $plainte->region || ! $autorite->couvre($domaine)) {
            throw new TransitionInterdite('Cette autorité n’est pas compétente pour le domaine ou la zone de la plainte.');
        }
    }

    /**
     * @param  array<int, StatutPlainte>  $autorises
     */
    private function exiger(Plainte $plainte, array $autorises): void
    {
        if (! in_array($plainte->statut, $autorises, true)) {
            throw new TransitionInterdite('Action impossible : la plainte est « '.$plainte->statut->label().' ».');
        }
    }

    private function exigerAutorite(User $agent, Plainte $plainte): void
    {
        if ($agent->role !== Role::Autorite || ! $agent->autorite || $plainte->autorite_id !== $agent->autorite->id) {
            throw new TransitionInterdite('Cette plainte n’est pas assignée à votre autorité.');
        }
    }

    private function exigerCitoyen(User $citoyen, Plainte $plainte): void
    {
        if ($plainte->citoyen_id !== $citoyen->id) {
            throw new TransitionInterdite('Cette plainte ne vous appartient pas.');
        }
    }

    private function changer(
        Plainte $plainte,
        ?User $acteur,
        StatutPlainte $vers,
        string $action,
        string $commentaire,
        ?AutoriteLocale $autorite = null,
    ): void {
        $avant = $plainte->statut;
        $plainte->statut = $vers;
        $plainte->save();
        $this->journal($plainte, $acteur, $action, $commentaire, $avant, $vers, $autorite);
    }

    private function journal(
        Plainte $plainte,
        ?User $acteur,
        string $action,
        string $commentaire,
        ?StatutPlainte $avant,
        StatutPlainte $apres,
        ?AutoriteLocale $autorite = null,
    ): void {
        $plainte->traitements()->create([
            'user_id' => $acteur?->id,
            'autorite_id' => $autorite?->id ?? $plainte->autorite_id,
            'action' => $action,
            'commentaire' => $commentaire,
            'statut_avant' => $avant,
            'statut_apres' => $apres,
        ]);
    }
}
