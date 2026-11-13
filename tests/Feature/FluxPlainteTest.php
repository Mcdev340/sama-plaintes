<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\StatutPlainte;
use App\Models\AutoriteLocale;
use App\Models\Citoyen;
use App\Models\Plainte;
use App\Models\Superviseur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FluxPlainteTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_circuit_complet_oriente_traite_et_clot_une_plainte(): void
    {
        [$citoyen, $autre, $agent, $superviseur] = $this->acteurs();

        $this->actingAs($citoyen)->post(route('citoyen.plaintes.store'), [
            'titre' => 'Nid de poule dangereux',
            'description' => 'Un nid de poule large bloque la rue devant l’école depuis une semaine.',
            'domaine' => 'voirie',
            'priorite' => 'haute',
            'region' => 'Dakar',
            'commune' => 'Médina',
            'localisation' => 'Rue 6, devant l’école',
        ])->assertRedirect();

        $plainte = Plainte::query()->first();
        $this->assertSame(StatutPlainte::Soumise, $plainte->statut);
        $this->assertNotNull($plainte->autorite_suggeree_id);

        $this->actingAs($autre)->get(route('plaintes.show', $plainte))->assertForbidden();

        $this->actingAs($superviseur)->post(route('superviseur.plaintes.assigner', $plainte), [
            'autorite_id' => $agent->autorite->id,
            'delai' => 5,
            'commentaire' => 'Service de voirie compétent.',
        ])->assertRedirect();

        $plainte->refresh();
        $this->assertSame(StatutPlainte::Assignee, $plainte->statut);

        $this->actingAs($agent)
            ->post(route('autorite.dossiers.prendre', $plainte))
            ->assertRedirect();
        $this->actingAs($agent)->post(route('autorite.dossiers.resoudre', $plainte), [
            'commentaire' => 'La voirie a rebouché le nid de poule ce matin.',
        ])->assertRedirect();

        $this->actingAs($citoyen)->post(route('citoyen.plaintes.evaluer', $plainte), [
            'note' => 5,
            'commentaire' => 'Travail rapide, merci.',
        ])->assertRedirect();

        $this->actingAs($superviseur)->post(route('superviseur.plaintes.clore', $plainte), [
            'commentaire' => 'Résolution vérifiée.',
        ])->assertRedirect();

        $plainte->refresh();
        $this->assertSame(StatutPlainte::Fermee, $plainte->statut);
        $this->assertSame(5, $plainte->note_satisfaction);
    }

    public function test_une_plainte_en_retard_est_escaladee(): void
    {
        [$citoyen, , $agent, $superviseur] = $this->acteurs();
        $this->actingAs($citoyen)->post(route('citoyen.plaintes.store'), [
            'titre' => 'Fuite permanente',
            'description' => 'L’eau coule sur la chaussée depuis plus de dix jours sans intervention.',
            'domaine' => 'eau',
            'priorite' => 'haute',
            'region' => 'Dakar',
            'commune' => 'Médina',
            'localisation' => 'Rue 10',
        ]);
        $plainte = Plainte::query()->first();
        $this->actingAs($superviseur)->post(route('superviseur.plaintes.assigner', $plainte), [
            'autorite_id' => $agent->autorite->id,
            'delai' => 2,
        ]);
        $plainte->update(['date_echeance' => now()->subHour(), 'statut' => StatutPlainte::EnTraitement]);

        $this->artisan('plaintes:escalader')->assertSuccessful();

        $this->assertSame(StatutPlainte::Escaladee, $plainte->refresh()->statut);
    }

    /**
     * @return array{0: User, 1: User, 2: User, 3: User}
     */
    private function acteurs(): array
    {
        $citoyen = User::factory()->create(['role' => Role::Citoyen, 'actif' => true]);
        Citoyen::create(['user_id' => $citoyen->id, 'nom' => $citoyen->nom, 'prenom' => $citoyen->prenom]);
        $autre = User::factory()->create(['role' => Role::Citoyen, 'actif' => true]);
        Citoyen::create(['user_id' => $autre->id, 'nom' => $autre->nom, 'prenom' => $autre->prenom]);

        $agent = User::factory()->create(['role' => Role::Autorite, 'actif' => true]);
        AutoriteLocale::create([
            'user_id' => $agent->id,
            'nom' => 'Mairie de Dakar',
            'zone' => 'Médina',
            'region' => 'Dakar',
            'competences' => 'voirie,eau',
            'telephone_contact' => '338000000',
            'email_contact' => 'mairie@test.sn',
            'actif' => true,
        ]);

        $superviseur = User::factory()->create(['role' => Role::Superviseur, 'actif' => true]);
        Superviseur::create([
            'user_id' => $superviseur->id,
            'matricule' => 'SUP-TEST',
            'departement' => 'National',
            'niveau_acces' => 3,
        ]);

        return [$citoyen, $autre, $agent->fresh('autorite'), $superviseur->fresh('superviseur')];
    }
}
