<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Mail\MessageContact;
use App\Models\Plainte;
use App\Support\MessagesValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'total' => Plainte::count(),
            'resolues' => Plainte::query()->whereIn('statut', ['resolue', 'fermee'])->count(),
            'ouvertes' => Plainte::query()->whereNotIn('statut', ['resolue', 'rejetee', 'fermee'])->count(),
            'zones' => Plainte::query()->distinct()->count('region'),
            'recentes' => Plainte::query()->latest('created_at')->limit(8)->get(),
        ]);
    }

    public function fonctionnalites(): View
    {
        return view('pages.fonctionnalites');
    }

    public function aPropos(): View
    {
        return view('pages.a-propos');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function envoyer(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'sujet' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
        ], MessagesValidation::messages(), MessagesValidation::attributes());

        Mail::to('contact@samaplaintes.sn')->send(new MessageContact(
            $donnees['nom'],
            $donnees['email'],
            $donnees['sujet'],
            $donnees['message'],
        ));

        return back()->with('succes', 'Votre message a bien été envoyé. Une réponse vous sera adressée par e-mail.');
    }

    public function tableau(Request $request)
    {
        $role = $request->user()->role;

        return redirect()->route(match ($role) {
            Role::Citoyen => 'citoyen.dashboard',
            Role::Autorite => 'autorite.dashboard',
            Role::Superviseur => 'superviseur.dashboard',
        });
    }
}
