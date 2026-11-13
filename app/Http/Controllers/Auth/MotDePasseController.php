<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\MessagesValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class MotDePasseController extends Controller
{
    public function demande(): View
    {
        return view('auth.mot-de-passe');
    }

    public function envoyer(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'email' => ['required', 'email'],
        ], MessagesValidation::messages(), MessagesValidation::attributes());

        $user = User::query()->where('email', $donnees['email'])->first();
        $retour = back()->with('succes', 'Si un compte correspond à cette adresse, un lien de réinitialisation a été préparé.');

        if ($user) {
            $token = Password::broker()->createToken($user);
            $lien = route('password.reset', ['token' => $token, 'email' => $user->email]);
            if (app()->environment('local')) {
                $retour = $retour->with('lien_reinitialisation', $lien);
            }
        }

        return $retour;
    }

    public function reinitialiser(Request $request, string $token): View
    {
        return view('auth.reinitialiser', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function enregistrer(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ], MessagesValidation::messages(), MessagesValidation::attributes());

        $statut = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])->save();
            },
        );

        if ($statut !== Password::PASSWORD_RESET) {
            return back()->with('erreur', 'Ce lien est invalide ou a expiré.');
        }

        return redirect()->route('login')->with('succes', 'Mot de passe mis à jour. Vous pouvez vous connecter.');
    }
}
