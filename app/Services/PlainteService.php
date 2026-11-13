<?php

namespace App\Services;

use App\Enums\Priorite;
use App\Enums\Role;
use App\Enums\StatutPlainte;
use App\Exceptions\TransitionInterdite;
use App\Models\Assignation;
use App\Models\AutoriteLocale;
use App\Models\Message;
use App\Models\PieceJointe;
use App\Models\Plainte;
use App\Models\Traitement;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PlainteService
{
    public function __construct(private AlerteService $alertes) {}

    /**
     * @param  array<string, mixed>  $donnees
     * @param  array<int, UploadedFile>  $fichiers
     */
    public function deposer(User $citoyen, array $donnees, array $fichiers = []): Plainte
    {
        $profil = $citoyen->citoyen;
        if ($citoyen->role !== Role::Citoyen || $profil === null) {
            throw new TransitionInterdite('Seul un citoyen peut déposer une plainte.');
        }

        return DB::transaction(function () use ($citoyen, $profil, $donnees, $fichiers) {
            $priorite = Priorite::from($donnees['priorite']);
            $plainte = Plainte::create([
                'reference' => $this->reference(),
                'titre' => $donnees['titre'],
                'description' => $donnees['description'],
                'domaine' => $donnees['domaine'],
                'priorite' => $priorite,
                'statut' => StatutPlainte::Soumise,
                'localisation' => $donnees['localisation'],
                'region' => $donnees['region'],
                'commune' => $donnees['commune'],
                'latitude' => $this->nullable($donnees['latitude'] ?? null),
                'longitude' => $this->nullable($donnees['longitude'] ?? null),
                'citoyen_id' => $profil->id,
                'date_soumission' => now(),
                'date_echeance' => now()->addDays($priorite->delai()),
                'niveau_escalade' => 0,
            ]);

            $this->enregistrerPieces($plainte, $fichiers);
            $suggestion = $this->suggererAutorite($plainte);
            if ($suggestion) {
                $plainte->update(['autorite_suggeree_id' => $suggestion->id]);
            }

            $this->tracer($plainte, $citoyen, 'depot', 'Plainte déposée par le citoyen.', null);
            $this->alertes->envoyer(
                $citoyen,
                "Votre plainte {$plainte->reference} a été enregistrée. Vous pouvez en suivre le traitement.",
                'confirmation',
                $plainte,
            );

            foreach ($this->superviseursActifs() as $superviseur) {
                $this->alertes->envoyer(
                    $superviseur,
                    "Nouvelle plainte {$plainte->reference} à examiner ({$plainte->commune}, {$plainte->domaine->label()}).",
                    'info',
                    $plainte,
                );
            }

            return $plainte->fresh(['pieces', 'autoriteSuggeree', 'citoyen.user']);
        });
    }

    public function suggererAutorite(Plainte $plainte): ?AutoriteLocale
    {
        $meilleure = null;
        $scoreMax = 0;

        foreach (AutoriteLocale::query()->where('actif', true)->orderBy('id')->get() as $autorite) {
            $score = $this->scoreAutorite($autorite, $plainte);
            if ($score > $scoreMax) {
                $scoreMax = $score;
                $meilleure = $autorite;
            }
        }

        return $scoreMax > 0 ? $meilleure : null;
    }

    public function analyser(User $superviseur, Plainte $plainte, ?string $commentaire): Plainte
    {
        $this->exigerSuperviseur($superviseur);
        $this->exiger($plainte, [StatutPlainte::Soumise]);

        $plainte->update(['statut' => StatutPlainte::EnAnalyse]);
        $this->tracer($plainte, $superviseur, 'analyse', $commentaire ?: 'Dossier pris en analyse.', null);
        $this->alertes->envoyer(
            $plainte->citoyen->user,
            "Votre plainte {$plainte->reference} est en cours d’analyse.",
            'info',
            $plainte,
        );

        return $plainte->fresh();
    }

    public function assigner(
        User $superviseur,
        Plainte $plainte,
        AutoriteLocale $autorite,
        int $delai,
        ?string $commentaire,
        ?string $priorite = null,
    ): Plainte {
        $this->exigerSuperviseur($superviseur);
        $this->exiger($plainte, [
            StatutPlainte::Soumise,
            StatutPlainte::EnAnalyse,
            StatutPlainte::Escaladee,
            StatutPlainte::Assignee,
            StatutPlainte::EnTraitement,
            StatutPlainte::EnAttente,
        ]);

        if (! $autorite->actif) {
            throw new TransitionInterdite('Cette autorité n’est pas active.');
        }

        return DB::transaction(function () use ($superviseur, $plainte, $autorite, $delai, $commentaire, $priorite) {
            $ancienne = $plainte->autorite;
            $plainte->fill([
                'autorite_id' => $autorite->id,
                'statut' => StatutPlainte::Assignee,
                'date_echeance' => now()->addDays($delai),
            ]);
            if ($priorite) {
                $plainte->priorite = Priorite::from($priorite);
            }
            $plainte->save();

            Assignation::create([
                'plainte_id' => $plainte->id,
                'superviseur_id' => $superviseur->superviseur->id,
                'autorite_id' => $autorite->id,
                'date_assignation' => now(),
                'delai_estime' => $delai,
                'commentaire' => $commentaire,
            ]);

            $this->tracer(
                $plainte,
                $superviseur,
                'assignation',
                $commentaire ?: "Assignée à {$autorite->nom} ({$delai} jours).",
                $autorite->id,
            );

            $this->alertes->envoyer(
                $plainte->citoyen->user,
                "Votre plainte {$plainte->reference} a été orientée vers {$autorite->nom}.",
                'info',
                $plainte,
            );
            if ($autorite->user) {
                $this->alertes->envoyer(
                    $autorite->user,
                    "La plainte {$plainte->reference} vous a été assignée. Délai : {$delai} jour(s).",
                    'alerte',
                    $plainte,
                );
            }
            if ($ancienne && $ancienne->id !== $autorite->id && $ancienne->user) {
                $this->alertes->envoyer(
                    $ancienne->user,
                    "La plainte {$plainte->reference} a été réassignée à un autre service.",
                    'info',
                    $plainte,
                );
            }

            return $plainte->fresh();
        });
    }

    public function prendreEnCharge(User $agent, Plainte $plainte, ?string $commentaire): Plainte
    {
        $autorite = $this->exigerAssignee($agent, $plainte);
        $this->exiger($plainte, [StatutPlainte::Assignee, StatutPlainte::Escaladee]);

        $plainte->update(['statut' => StatutPlainte::EnTraitement]);
        $this->tracer($plainte, $agent, 'prise_en_charge', $commentaire ?: 'Prise en charge du dossier.', $autorite->id);
        $this->alertes->envoyer(
            $plainte->citoyen->user,
            "Votre plainte {$plainte->reference} est prise en charge par {$autorite->nom}.",
            'info',
            $plainte,
        );

        return $plainte->fresh();
    }

    public function demanderComplement(User $agent, Plainte $plainte, string $commentaire): Plainte
    {
        $autorite = $this->exigerAssignee($agent, $plainte);
        $this->exiger($plainte, [StatutPlainte::Assignee, StatutPlainte::EnTraitement, StatutPlainte::Escaladee]);

        $plainte->update(['statut' => StatutPlainte::EnAttente]);
        $this->tracer($plainte, $agent, 'demande_complement', $commentaire, $autorite->id);
        $this->alertes->envoyer(
            $plainte->citoyen->user,
            "Un complément est demandé pour la plainte {$plainte->reference} : {$commentaire}",
            'rappel',
            $plainte,
        );

        return $plainte->fresh();
    }

    /**
     * @param  array<int, UploadedFile>  $fichiers
     */
    public function completer(User $citoyen, Plainte $plainte, string $complement, array $fichiers = []): Plainte
    {
        $this->exigerProprietaire($citoyen, $plainte);
        $this->exiger($plainte, [StatutPlainte::EnAttente]);

        return DB::transaction(function () use ($citoyen, $plainte, $complement, $fichiers) {
            $plainte->update(['statut' => StatutPlainte::EnTraitement]);
            $this->enregistrerPieces($plainte, $fichiers);
            $this->tracer($plainte, $citoyen, 'complement', $complement, $plainte->autorite_id);
            if ($plainte->autorite?->user) {
                $this->alertes->envoyer(
                    $plainte->autorite->user,
                    "Le citoyen a complété la plainte {$plainte->reference}.",
                    'info',
                    $plainte,
                );
            }

            return $plainte->fresh();
        });
    }

    public function resoudre(User $agent, Plainte $plainte, string $commentaire): Plainte
    {
        $autorite = $this->exigerAssignee($agent, $plainte);
        $this->exiger($plainte, [
            StatutPlainte::Assignee,
            StatutPlainte::EnTraitement,
            StatutPlainte::EnAttente,
            StatutPlainte::Escaladee,
        ]);

        $plainte->update([
            'statut' => StatutPlainte::Resolue,
            'date_cloture' => now(),
            'motif_rejet' => null,
        ]);
        $this->tracer($plainte, $agent, 'resolution', $commentaire, $autorite->id);
        $this->alertes->envoyer(
            $plainte->citoyen->user,
            "Votre plainte {$plainte->reference} a été résolue. Vous pouvez évaluer le traitement.",
            'confirmation',
            $plainte,
        );

        return $plainte->fresh();
    }

    public function rejeter(User $agent, Plainte $plainte, string $motif): Plainte
    {
        $autorite = $this->exigerAssignee($agent, $plainte);
        $this->exiger($plainte, [
            StatutPlainte::Assignee,
            StatutPlainte::EnTraitement,
            StatutPlainte::EnAttente,
            StatutPlainte::Escaladee,
        ]);

        $plainte->update([
            'statut' => StatutPlainte::Rejetee,
            'date_cloture' => now(),
            'motif_rejet' => $motif,
        ]);
        $this->tracer($plainte, $agent, 'rejet', $motif, $autorite->id);
        $this->alertes->envoyer(
            $plainte->citoyen->user,
            "Votre plainte {$plainte->reference} a été rejetée. Motif : {$motif}",
            'alerte',
            $plainte,
        );

        return $plainte->fresh();
    }

    public function escalader(User $superviseur, Plainte $plainte, ?string $commentaire): Plainte
    {
        $this->exigerSuperviseur($superviseur);
        if (! $plainte->statut->estOuvert()) {
            throw new TransitionInterdite('Une plainte close ne peut plus être escaladée.');
        }
        if ($plainte->niveau_escalade >= 3) {
            throw new TransitionInterdite('Cette plainte est déjà au niveau d’escalade maximal.');
        }

        return $this->appliquerEscalade($plainte, $superviseur, $commentaire ?: 'Escalade décidée par le superviseur.');
    }

    public function escaladerLesRetards(): int
    {
        $compte = 0;
        $plaintes = Plainte::query()->enRetard()->where('niveau_escalade', '<', 3)->get();

        foreach ($plaintes as $plainte) {
            $deja = $plainte->traitements()
                ->where('action', 'escalade')
                ->whereDate('date_traitement', today())
                ->exists();
            if ($deja) {
                continue;
            }
            $this->appliquerEscalade($plainte, null, 'Délai dépassé : escalade automatique.');
            $compte++;
        }

        return $compte;
    }

    public function cloturer(User $superviseur, Plainte $plainte, ?string $commentaire): Plainte
    {
        $this->exigerSuperviseur($superviseur);
        $this->exiger($plainte, [StatutPlainte::Resolue, StatutPlainte::Rejetee]);

        $plainte->update([
            'statut' => StatutPlainte::Fermee,
            'date_cloture' => $plainte->date_cloture ?? now(),
        ]);
        $this->tracer($plainte, $superviseur, 'cloture', $commentaire ?: 'Dossier clos après vérification.', $plainte->autorite_id);
        $this->alertes->envoyer(
            $plainte->citoyen->user,
            "Votre plainte {$plainte->reference} est close.",
            'info',
            $plainte,
        );

        return $plainte->fresh();
    }

    public function evaluer(User $citoyen, Plainte $plainte, int $note, ?string $commentaire): Plainte
    {
        $this->exigerProprietaire($citoyen, $plainte);
        $this->exiger($plainte, [StatutPlainte::Resolue, StatutPlainte::Fermee]);
        if ($plainte->note_satisfaction !== null) {
            throw new TransitionInterdite('Cette plainte a déjà été évaluée.');
        }

        $plainte->update([
            'note_satisfaction' => $note,
            'commentaire_satisfaction' => $commentaire,
        ]);
        $this->tracer($plainte, $citoyen, 'evaluation', $commentaire ?: "Note : {$note}/5.", $plainte->autorite_id);
        if ($plainte->autorite?->user) {
            $this->alertes->envoyer(
                $plainte->autorite->user,
                "Le citoyen a évalué la plainte {$plainte->reference} : {$note}/5.",
                'info',
                $plainte,
            );
        }

        return $plainte->fresh();
    }

    public function publierMessage(User $auteur, Plainte $plainte, string $corps): Message
    {
        if (! $this->estParticipant($auteur, $plainte)) {
            throw new TransitionInterdite('Vous ne participez pas à cette plainte.');
        }
        if (! $plainte->statut->estOuvert()) {
            throw new TransitionInterdite('La messagerie est close pour cette plainte.');
        }

        $message = Message::create([
            'plainte_id' => $plainte->id,
            'user_id' => $auteur->id,
            'corps' => $corps,
        ]);

        $plainte->loadMissing(['citoyen.user', 'autorite.user']);
        $destinataires = collect();
        if ($plainte->citoyen?->user && $plainte->citoyen->user->id !== $auteur->id) {
            $destinataires->push($plainte->citoyen->user);
        }
        if ($plainte->autorite?->user && $plainte->autorite->user->id !== $auteur->id) {
            $destinataires->push($plainte->autorite->user);
        }
        if (! $auteur->estSuperviseur()) {
            foreach ($this->superviseursActifs() as $superviseur) {
                if ($superviseur->id !== $auteur->id) {
                    $destinataires->push($superviseur);
                }
            }
        }

        foreach ($destinataires->unique('id') as $destinataire) {
            $this->alertes->envoyer(
                $destinataire,
                "Nouveau message sur la plainte {$plainte->reference}.",
                'info',
                $plainte,
            );
        }

        return $message;
    }

    /**
     * @param  list<UploadedFile>  $fichiers
     */
    private function enregistrerPieces(Plainte $plainte, array $fichiers): void
    {
        foreach ($fichiers as $fichier) {
            if (! $fichier instanceof UploadedFile) {
                continue;
            }
            $chemin = $fichier->store('pieces/'.$plainte->id, 'local');
            PieceJointe::create([
                'plainte_id' => $plainte->id,
                'nom_fichier' => basename($fichier->getClientOriginalName()),
                'chemin' => $chemin,
                'type_fichier' => $fichier->getClientMimeType() ?: $fichier->extension(),
                'taille' => $fichier->getSize() ?: 0,
                'date_upload' => now(),
            ]);
        }
    }

    private function scoreAutorite(AutoriteLocale $autorite, Plainte $plainte): int
    {
        if (! in_array($plainte->domaine->value, $autorite->domaines(), true)) {
            return 0;
        }

        $score = 5;
        $zone = mb_strtolower($autorite->zone.' '.$autorite->region);
        $commune = mb_strtolower($plainte->commune);
        $region = mb_strtolower($plainte->region);

        if ($commune !== '' && str_contains($zone, $commune)) {
            $score += 3;
        }
        if ($region !== '' && str_contains($zone, $region)) {
            $score += 1;
        }

        return $score;
    }

    private function appliquerEscalade(Plainte $plainte, ?User $auteur, string $commentaire): Plainte
    {
        return DB::transaction(function () use ($plainte, $auteur, $commentaire) {
            $plainte->loadMissing(['citoyen.user', 'autorite.user']);
            $plainte->niveau_escalade = min(3, $plainte->niveau_escalade + 1);
            $plainte->statut = StatutPlainte::Escaladee;
            $plainte->save();

            Traitement::create([
                'plainte_id' => $plainte->id,
                'autorite_id' => $plainte->autorite_id,
                'user_id' => $auteur?->id,
                'responsable' => $auteur?->name ?? 'Système',
                'action' => 'escalade',
                'commentaire' => $commentaire.' (niveau '.$plainte->niveau_escalade.')',
                'date_traitement' => now(),
            ]);

            $texte = "La plainte {$plainte->reference} est escaladée au niveau {$plainte->niveau_escalade}.";
            if ($plainte->citoyen?->user) {
                $this->alertes->envoyer($plainte->citoyen->user, $texte, 'alerte', $plainte);
            }
            if ($plainte->autorite?->user) {
                $this->alertes->envoyer($plainte->autorite->user, $texte, 'alerte', $plainte);
            }
            foreach ($this->superviseursActifs() as $superviseur) {
                if ($superviseur->id !== $auteur?->id) {
                    $this->alertes->envoyer($superviseur, $texte, 'alerte', $plainte);
                }
            }

            return $plainte->fresh();
        });
    }

    private function reference(): string
    {
        $prefixe = 'SP-'.now()->year.'-';
        $dernier = Plainte::query()
            ->where('reference', 'like', $prefixe.'%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('reference');

        $sequence = 1;
        if (is_string($dernier) && preg_match('/(\d+)$/', $dernier, $correspondance)) {
            $sequence = ((int) $correspondance[1]) + 1;
        }

        return $prefixe.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }

    /**
     * @param  list<StatutPlainte>  $statuts
     */
    private function exiger(Plainte $plainte, array $statuts): void
    {
        if (! in_array($plainte->statut, $statuts, true)) {
            throw new TransitionInterdite(
                'Cette action n’est pas possible tant que la plainte est « '.$plainte->statut->label().' ».'
            );
        }
    }

    private function exigerSuperviseur(User $user): void
    {
        if ($user->role !== Role::Superviseur || $user->superviseur === null) {
            throw new TransitionInterdite('Cette action est réservée au superviseur.');
        }
    }

    private function exigerAssignee(User $user, Plainte $plainte): AutoriteLocale
    {
        $autorite = $user->autorite;
        if ($user->role !== Role::Autorite || $autorite === null || ! $autorite->actif) {
            throw new TransitionInterdite('Seul le service assigné peut traiter cette plainte.');
        }
        if ($plainte->autorite_id !== $autorite->id) {
            throw new TransitionInterdite('Cette plainte n’est pas assignée à votre service.');
        }

        return $autorite;
    }

    private function exigerProprietaire(User $user, Plainte $plainte): void
    {
        if ($user->role !== Role::Citoyen || $user->citoyen === null || $plainte->citoyen_id !== $user->citoyen->id) {
            throw new TransitionInterdite('Cette plainte ne vous appartient pas.');
        }
    }

    private function estParticipant(User $user, Plainte $plainte): bool
    {
        if ($user->role === Role::Superviseur && $user->superviseur !== null) {
            return true;
        }
        if ($user->role === Role::Citoyen && $user->citoyen && $plainte->citoyen_id === $user->citoyen->id) {
            return true;
        }

        return $user->role === Role::Autorite
            && $user->autorite
            && $plainte->autorite_id === $user->autorite->id;
    }

    private function tracer(Plainte $plainte, User $user, string $action, ?string $commentaire, ?int $autoriteId): void
    {
        Traitement::create([
            'plainte_id' => $plainte->id,
            'autorite_id' => $autoriteId,
            'user_id' => $user->id,
            'responsable' => $user->name,
            'action' => $action,
            'commentaire' => $commentaire,
            'date_traitement' => now(),
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    private function superviseursActifs()
    {
        return User::query()
            ->where('role', Role::Superviseur->value)
            ->where('actif', true)
            ->get();
    }

    private function nullable(mixed $valeur): mixed
    {
        if ($valeur === null || $valeur === '') {
            return null;
        }

        return $valeur;
    }
}
