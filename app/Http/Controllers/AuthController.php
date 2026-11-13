<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $identifiants = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $cle = 'login:'.$request->ip().'|'.mb_strtolower($identifiants['email']);

        if (RateLimiter::tooManyAttempts($cle, 5)) {
            return back()->with('erreur', 'Trop de tentatives. Réessayez dans une minute.')->withInput();
        }

        if (! Auth::attempt($identifiants, $request->boolean('remember'))) {
            RateLimiter::hit($cle, 60);

            return back()->with('erreur', 'Identifiants incorrects.')->withInput();
        }

        $user = $request->user();

        if (! $user->actif) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $url = $this->lienActivation($user);

            return redirect()->route('login')
                ->with('erreur', 'Activez votre compte avant de vous connecter.')
                ->with('activation_url', app()->environment('local') ? $url : null);
        }

        RateLimiter::clear($cle);
        $request->session()->regenerate();

        if (! $request->session()->has('locale')) {
            $request->session()->put('locale', $user->langue ?: 'fr');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $donnees = $request->validate([
            'prenom' => ['required', 'string', 'max:100'],
            'nom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'telephone' => ['required', 'string', 'max:30'],
            'adresse' => ['required', 'string', 'max:500'],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'numero_identite' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'prenom' => $donnees['prenom'],
            'nom' => $donnees['nom'],
            'email' => $donnees['email'],
            'telephone' => $donnees['telephone'],
            'adresse' => $donnees['adresse'],
            'date_naissance' => $donnees['date_naissance'] ?? null,
            'numero_identite' => $donnees['numero_identite'] ?? null,
            'password' => $donnees['password'],
            'role' => Role::Citoyen,
            'langue' => 'fr',
            'actif' => false,
        ]);

        $url = $this->lienActivation($user);

        return redirect()->route('login')
            ->with('succes', 'Compte créé. Activez-le avec le lien reçu par e-mail.')
            ->with('activation_url', app()->environment('local') ? $url : null);
    }

    public function activate(User $user)
    {
        $user->forceFill([
            'actif' => true,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        return redirect()->route('login')->with('succes', 'Compte activé. Vous pouvez vous connecter.');
    }

    public function showForgot(): View
    {
        return view('auth.forgot');
    }

    public function sendReset(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::query()->where('email', $request->string('email'))->first();
        $message = 'Si un compte actif correspond à cet e-mail, un lien de réinitialisation a été envoyé.';

        if ($user && $user->actif) {
            $token = Password::broker()->createToken($user);
            $url = route('password.reset', ['token' => $token, 'email' => $user->email]);
            Mail::raw('Réinitialisez votre mot de passe Sama Plaintes : '.$url, function ($mail) use ($user): void {
                $mail->to($user->email)->subject('Sama Plaintes — réinitialisation du mot de passe');
            });

            return back()->with('succes', $message)->with('reset_url', app()->environment('local') ? $url : null);
        }

        return back()->with('succes', $message);
    }

    public function showReset(Request $request, string $token): View
    {
        return view('auth.reset', [
            'token' => $token,
            'email' => $request->string('email'),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $statut = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill(['password' => $password])->save();
            }
        );

        if ($statut !== Password::PASSWORD_RESET) {
            return back()->with('erreur', 'Ce lien de réinitialisation est invalide ou expiré.');
        }

        return redirect()->route('login')->with('succes', 'Mot de passe mis à jour. Vous pouvez vous connecter.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function lienActivation(User $user): string
    {
        $url = URL::temporarySignedRoute('activation', now()->addDay(), ['user' => $user->id]);

        Mail::raw('Activez votre compte Sama Plaintes : '.$url, function ($mail) use ($user): void {
            $mail->to($user->email)->subject('Activez votre compte Sama Plaintes');
        });

        return $url;
    }
}
