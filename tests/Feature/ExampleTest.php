<?php

namespace Tests\Feature;

use App\Mail\MessageContact;
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
        $response->assertSee('Dernières doléances');
    }

    public function test_les_pages_publiques_sont_accessibles(): void
    {
        $this->get(route('fonctionnalites'))->assertOk()->assertSee('Fonctionnalités');
        $this->get(route('a-propos'))->assertOk()->assertSee('À propos de Sama Plaintes');
        $this->get(route('contact'))->assertOk()->assertSee('Coordonnées');
    }

    public function test_le_formulaire_de_contact_envoie_un_message(): void
    {
        Mail::fake();

        $this->post(route('contact.envoyer'), [
            'nom' => 'Awa Ndiaye',
            'email' => 'awa@example.sn',
            'sujet' => 'Question sur un service',
            'message' => 'Je souhaite savoir comment enregistrer une autorité locale.',
        ])->assertRedirect()->assertSessionHas('succes');

        Mail::assertSent(MessageContact::class, function (MessageContact $mail): bool {
            return $mail->hasTo('contact@samaplaintes.sn')
                && $mail->sujetMessage === 'Question sur un service';
        });
    }
}
