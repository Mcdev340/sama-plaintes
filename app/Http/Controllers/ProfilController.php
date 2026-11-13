<?php

namespace App\Http\Controllers;

use App\Support\MessagesValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profil.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'telephone' => ['required', 'string', 'regex:/^[0-9+\s]{8,20}$/'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'numero_identite' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ], MessagesValidation::messages() + [
            'regex' => 'Le numéro de téléphone n’est pas valide.',
        ], MessagesValidation::attributes());

        $user->fill([
            'nom' => $donnees['nom'],
            'prenom' => $donnees['prenom'],
            'name' => $donnees['prenom'].' '.$donnees['nom'],
            'telephone' => $donnees['telephone'],
            'adresse' => $donnees['adresse'] ?? null,
            'date_naissance' => $donnees['date_naissance'] ?? null,
            'numero_identite' => $donnees['numero_identite'] ?? null,
        ]);

        if (! empty($donnees['password'])) {
            $user->password = $donnees['password'];
        }
        $user->save();

        if ($user->citoyen) {
            $user->citoyen->update([
                'nom' => $donnees['nom'],
                'prenom' => $donnees['prenom'],
                'telephone' => $donnees['telephone'],
                'adresse' => $donnees['adresse'] ?? null,
                'date_naissance' => $donnees['date_naissance'] ?? null,
                'numero_identite' => $donnees['numero_identite'] ?? null,
            ]);
        }

        if ($user->autorite) {
            $user->autorite->update([
                'telephone_contact' => $donnees['telephone'],
            ]);
        }

        return back()->with('succes', 'Profil mis à jour.');
    }
}
