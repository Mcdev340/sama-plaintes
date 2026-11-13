<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\AutoriteLocale;
use App\Models\Citoyen;
use App\Models\Superviseur;
use App\Models\User;
use App\Services\PlainteService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $plaintes = app(PlainteService::class);

        $issa = $this->citoyen('Issa', 'Bathily', 'issa.bathily@samaplaintes.sn', 'Diamniadio');
        $sekou = $this->citoyen('Sékou', 'Diedhiou', 'sekou.diedhiou@samaplaintes.sn', 'Médina, Dakar');
        $mamadou = $this->citoyen('Mamadou Aliou', 'Diallo', 'mamadou.diallo@samaplaintes.sn', 'Pikine');
        $moussa = $this->citoyen('Moussa', 'Correa', 'moussa.correa@samaplaintes.sn', 'Thiès');

        $serigne = $this->superviseur('Serigne', 'Diagne', 'serigne.diagne@samaplaintes.sn', 'SUP-2025-001', 'Supervision nationale', 3);

        $diamniadio = $this->autorite('Ndèye', 'Faye', 'mairie.diamniadio@samaplaintes.sn', 'Mairie de Diamniadio', 'Diamniadio', 'Dakar', 'voirie,electricite,eau,infrastructures');
        $dakar = $this->autorite('Bineta', 'Sow', 'mairie.dakar@samaplaintes.sn', 'Mairie de Dakar', 'Dakar Plateau', 'Dakar', 'voirie,infrastructures,eau,assainissement');
        $assainissement = $this->autorite('Ibrahima', 'Sarr', 'assainissement.dakar@samaplaintes.sn', 'Office d’assainissement de Dakar', 'Dakar', 'Dakar', 'assainissement');
        $sante = $this->autorite('Fatou', 'Diop', 'sante.dakar@samaplaintes.sn', 'District sanitaire de Dakar', 'Dakar', 'Dakar', 'sante');
        $prefecture = $this->autorite('Ousmane', 'Ba', 'prefecture.dakar@samaplaintes.sn', 'Préfecture de Dakar', 'Dakar', 'Dakar', 'securite,administratif');
        $thies = $this->autorite('Abdou', 'Kane', 'mairie.thies@samaplaintes.sn', 'Mairie de Thiès', 'Thiès', 'Thiès', 'voirie,eau,electricite,infrastructures');
        $saintLouis = $this->autorite('Mariama', 'Sy', 'mairie.saintlouis@samaplaintes.sn', 'Mairie de Saint-Louis', 'Saint-Louis', 'Saint-Louis', 'voirie,eau,assainissement');

        $nid = $plaintes->deposer($issa, $this->dossier('Nid de poule devant l’ISEP', 'Un nid de poule large bloque l’accès des étudiants depuis plus d’une semaine.', 'voirie', 'haute', 'Dakar', 'Diamniadio', 'Devant l’ISEP Amadou Trawaré'));
        $nid = $plaintes->assigner($serigne, $nid, $diamniadio, 5, 'Routage vers la mairie de Diamniadio.');
        $plaintes->prendreEnCharge($diamniadio->user, $nid, 'Équipe de voirie programmée.');

        $eau = $plaintes->deposer($sekou, $this->dossier('Coupure d’eau à la Médina', 'Le quartier est sans eau potable depuis trois jours, les bornes-fontaines sont à sec.', 'eau', 'haute', 'Dakar', 'Médina', 'Médina, rue 6'));
        $plaintes->assigner($serigne, $eau, $dakar, 3, 'Compétence eau de la ville de Dakar.');

        $medicament = $plaintes->deposer($mamadou, $this->dossier('Rupture de médicaments', 'Le poste de santé n’a plus de traitements pour le paludisme depuis plusieurs jours.', 'sante', 'haute', 'Dakar', 'Pikine', 'Poste de santé de Pikine'));
        $medicament = $plaintes->assigner($serigne, $medicament, $sante, 3, null);
        $medicament = $plaintes->prendreEnCharge($sante->user, $medicament, null);
        $plaintes->demanderComplement($sante->user, $medicament, 'Merci de préciser le nom du poste et la date du dernier approvisionnement.');

        $lampe = $plaintes->deposer($moussa, $this->dossier('Lampadaires en panne', 'La rue principale reste noire le soir, ce qui rend le trajet dangereux.', 'electricite', 'moyenne', 'Dakar', 'Diamniadio', 'Route de Diamniadio'));
        $lampe = $plaintes->assigner($serigne, $lampe, $diamniadio, 7, null);
        $lampe = $plaintes->prendreEnCharge($diamniadio->user, $lampe, null);
        $lampe = $plaintes->resoudre($diamniadio->user, $lampe, 'Les lampadaires ont été remplacés et vérifiés hier soir.');
        $plaintes->evaluer($moussa, $lampe, 5, 'Travail rapide, la rue est de nouveau éclairée.');

        $plaintes->deposer($issa, $this->dossier('École sans tables', 'Les élèves de CM2 travaillent à même le sol depuis la rentrée, il manque une vingtaine de tables.', 'education', 'moyenne', 'Ziguinchor', 'Ziguinchor', 'École élémentaire de Boucotte'));

        $caniveau = $plaintes->deposer($sekou, $this->dossier('Caniveau bouché', 'Les eaux usées débordent sur la chaussée après chaque pluie et stagnent devant les maisons.', 'assainissement', 'haute', 'Dakar', 'Grand Dakar', 'Avenue Cheikh Anta Diop'));
        $plaintes->analyser($serigne, $caniveau, 'Deux services sont compétents, arbitrage nécessaire.');

        $extrait = $plaintes->deposer($mamadou, $this->dossier('Extrait de naissance en retard', 'Le dossier déposé il y a six semaines n’a toujours pas été délivré au guichet.', 'administratif', 'basse', 'Dakar', 'Dakar Plateau', 'Centre d’état civil'));
        $extrait = $plaintes->assigner($serigne, $extrait, $prefecture, 15, null);
        $plaintes->rejeter($prefecture->user, $extrait, 'Le dossier est incomplet : l’acte de mariage des parents est absent.');

        $route = $plaintes->deposer($moussa, $this->dossier('Route coupée après les pluies', 'La piste qui relie le quartier au marché est impraticable pour les charrettes et les voitures.', 'voirie', 'haute', 'Thiès', 'Thiès', 'Sortie vers le marché central'));
        $route = $plaintes->assigner($serigne, $route, $thies, 5, null);
        $route = $plaintes->prendreEnCharge($thies->user, $route, null);
        $route = $plaintes->resoudre($thies->user, $route, 'La chaussée a été rechargée et rouverte à la circulation.');
        $plaintes->evaluer($moussa, $route, 4, 'La route est praticable, quelques nids restent sur le bord.');
        $plaintes->cloturer($serigne, $route, 'Résolution vérifiée sur place.');

        $retard = $plaintes->deposer($issa, $this->dossier('Fuite d’eau sur la corniche', 'Une fuite importante inonde le trottoir depuis dix jours sans intervention.', 'eau', 'haute', 'Saint-Louis', 'Saint-Louis', 'Corniche de Sor'));
        $retard = $plaintes->assigner($serigne, $retard, $saintLouis, 3, null);
        $retard = $plaintes->prendreEnCharge($saintLouis->user, $retard, null);
        $retard->update(['date_echeance' => now()->subDay()]);

        $plaintes->publierMessage($diamniadio->user, $nid->fresh(), 'Une équipe passe demain matin. Merci de laisser l’accès dégagé.');
    }

    private function dossier(string $titre, string $description, string $domaine, string $priorite, string $region, string $commune, string $lieu): array
    {
        return compact('titre', 'description', 'domaine', 'priorite', 'region', 'commune') + ['localisation' => $lieu];
    }

    private function citoyen(string $prenom, string $nom, string $email, string $adresse): User
    {
        $user = $this->compte($prenom, $nom, $email, Role::Citoyen, $adresse);
        Citoyen::create([
            'user_id' => $user->id,
            'prenom' => $prenom,
            'nom' => $nom,
            'adresse' => $adresse,
            'telephone' => $user->telephone,
        ]);

        return $user;
    }

    private function superviseur(string $prenom, string $nom, string $email, string $matricule, string $departement, int $niveau): User
    {
        $user = $this->compte($prenom, $nom, $email, Role::Superviseur, 'Dakar');
        Superviseur::create([
            'user_id' => $user->id,
            'matricule' => $matricule,
            'departement' => $departement,
            'niveau_acces' => $niveau,
        ]);

        return $user;
    }

    private function autorite(string $prenom, string $nom, string $email, string $institution, string $zone, string $region, string $competences): AutoriteLocale
    {
        $user = $this->compte($prenom, $nom, $email, Role::Autorite, $zone);

        return AutoriteLocale::create([
            'user_id' => $user->id,
            'nom' => $institution,
            'zone' => $zone,
            'region' => $region,
            'competences' => $competences,
            'telephone_contact' => $user->telephone,
            'email_contact' => $email,
            'actif' => true,
        ]);
    }

    private function compte(string $prenom, string $nom, string $email, Role $role, string $adresse): User
    {
        return User::create([
            'name' => $prenom.' '.$nom,
            'prenom' => $prenom,
            'nom' => $nom,
            'email' => $email,
            'telephone' => '77'.random_int(1000000, 9999999),
            'adresse' => $adresse,
            'password' => 'Sama2025',
            'role' => $role,
            'langue' => 'fr',
            'actif' => true,
            'email_verified_at' => now(),
        ]);
    }
}
