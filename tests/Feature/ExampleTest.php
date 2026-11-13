<?php

namespace Tests\Feature;

use App\Enums\StatutPlainte;
use App\Mail\MessageContact;
use App\Models\Citoyen;
use App\Models\Plainte;
use App\Models\Traitement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Sama Plaintes');
        $response->assertSee('Comment ça marche');
        $response->assertSee('Suivi rapide');
        $response->assertSee('Engagement citoyen');
        $response->assertSee('data-stat="total">0', false);
    }

    public function test_les_statistiques_et_le_suivi_lisent_les_plaintes_enregistrees(): void
    {
        $user = User::factory()->create();
        $citoyen = Citoyen::query()->create([
            'user_id' => $user->id,
            'nom' => 'Ndiaye',
            'prenom' => 'Awa',
            'telephone' => '770000000',
        ]);

        $ouverte = $this->plainte($citoyen, 'SP-TEST-0001', StatutPlainte::Soumise, now());
        $resolue = $this->plainte($citoyen, 'SP-TEST-0002', StatutPlainte::Resolue, now()->subWeeks(3));
        Traitement::query()->create([
            'plainte_id' => $resolue->id,
            'user_id' => $user->id,
            'responsable' => 'Service test',
            'action' => 'resolution',
            'commentaire' => 'Réparé.',
            'date_traitement' => now()->subWeeks(3),
        ]);

        $this->get(route('accueil'))
            ->assertOk()
            ->assertSee('data-stat="total">2', false)
            ->assertSee('data-stat="resolues">1', false)
            ->assertSee('data-stat="encours">1', false)
            ->assertSee('data-stat="semaine">1', false)
            ->assertSee('Voirie');

        $this->get(route('accueil', ['reference' => $ouverte->reference]))
            ->assertOk()
            ->assertSee($ouverte->reference)
            ->assertSee('Fuite d’eau')
            ->assertDontSee('Awa Ndiaye');

        $this->get(route('accueil', ['reference' => 'SP-INCONNUE']))
            ->assertOk()
            ->assertSee('Aucun dossier ne correspond à SP-INCONNUE');
    }

    private function plainte(Citoyen $citoyen, string $reference, StatutPlainte $statut, $date): Plainte
    {
        return Plainte::query()->create([
            'reference' => $reference,
            'titre' => 'Fuite d’eau',
            'description' => 'Une fuite visible depuis plusieurs jours au coin de la rue.',
            'domaine' => 'voirie',
            'priorite' => 'moyenne',
            'statut' => $statut,
            'localisation' => 'Rue 10',
            'region' => 'Dakar',
            'commune' => 'Médina',
            'citoyen_id' => $citoyen->id,
            'date_soumission' => $date,
        ]);
    }

    public function test_les_pages_publiques_sont_accessibles(): void
    {
        $this->get(route('fonctionnalites'))
            ->assertOk()
            ->assertSee('Fonctionnalités')
            ->assertSee('Le parcours d’une plainte')
            ->assertSee('Trois espaces')
            ->assertSee('États du dossier')
            ->assertSee('Voirie');
        $this->get(route('a-propos'))->assertOk()->assertSee('À propos de Sama Plaintes')->assertSee('Notre mission')->assertSee('en cours de conception');
        $this->get(route('contact'))->assertOk()->assertSee('Nous sommes à votre écoute')->assertSee('Adresse officielle à définir')->assertDontSee('Django');
    }

    public function test_le_formulaire_de_contact_envoie_un_message(): void
    {
        Mail::fake();

        $this->post(route('contact.envoyer'), [
            'nom' => 'Awa Ndiaye',
            'email' => 'awa@example.sn',
            'sujet' => 'Demande d\'assistance',
            'message' => 'Je souhaite savoir comment enregistrer une autorité locale.',
        ])->assertRedirect()->assertSessionHas('succes');

        Mail::assertSent(MessageContact::class, function (MessageContact $mail): bool {
            return $mail->hasTo('contact@samaplaintes.sn')
                && $mail->sujetMessage === 'Demande d\'assistance';
        });
    }
}
