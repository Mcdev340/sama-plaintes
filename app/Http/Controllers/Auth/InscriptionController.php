<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\MessagesValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InscriptionController extends Controller
{
    public function create(): View
    {
        return view('auth.inscription');
    }

    public function store(Request $request): RedirectResponse
    {
        $messages = MessagesValidation::messages() + [
            'regex' => 'Le numéro de téléphone n’est pas valide.',
        ];

        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telephone' => ['required', 'string', 'regex:/^[0-9+\s]{8,20}$/'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'numero_identite' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], $messages, MessagesValidation::attributes());

        $code = (string) random_int(100000, 999999);

        $user = User::create([
            'name' => $donnees['prenom'].' '.$donnees['nom'],
            'nom' => $donnees['nom'],
            'prenom' => $donnees['prenom'],
            'email' => $donnees['email'],
            'telephone' => $donnees['telephone'],
            'adresse' => $donnees['adresse'] ?? null,
            'date_naissance' => $donnees['date_naissance'] ?? null,
            'numero_identite' => $donnees['numero_identite'] ?? null,
            'password' => $donnees['password'],
            'role' => Role::Citoyen,
            'langue' => 'fr',
            'actif' => false,
            'code_activation' => Hash::make($code),
            'code_expire_at' => now()->addMinutes(30),
        ]);

        $user->citoyen()->create([
            'nom' => $donnees['nom'],
            'prenom' => $donnees['prenom'],
            'adresse' => $donnees['adresse'] ?? null,
            'telephone' => $donnees['telephone'],
            'date_naissance' => $donnees['date_naissance'] ?? null,
            'numero_identite' => $donnees['numero_identite'] ?? null,
        ]);

        $request->session()->put('email_activation', $user->email);
        if (app()->environment('local')) {
            $request->session()->put('code_demo', $code);
        }

        return redirect()
            ->route('activation')
            ->with('succes', 'Compte créé. Saisissez le code d’activation pour l’ouvrir.');
    }
}
