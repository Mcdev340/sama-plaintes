<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\MessagesValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ActivationController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.activation', [
            'email' => old('email', $request->session()->get('email_activation')),
            'codeDemo' => app()->environment('local') ? $request->session()->get('code_demo') : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ], MessagesValidation::messages() + [
            'digits' => 'Le code comporte 6 chiffres.',
        ], MessagesValidation::attributes());

        $user = User::query()->where('email', $donnees['email'])->first();

        if (
            ! $user
            || ! $user->code_activation
            || $user->code_expire_at === null
            || $user->code_expire_at->isPast()
            || ! Hash::check($donnees['code'], $user->code_activation)
        ) {
            throw ValidationException::withMessages([
                'code' => 'Code incorrect ou expiré.',
            ]);
        }

        $user->update([
            'actif' => true,
            'email_verified_at' => now(),
            'code_activation' => null,
            'code_expire_at' => null,
        ]);

        $request->session()->forget(['code_demo', 'email_activation']);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('succes', 'Votre compte est activé.');
    }
}
