@extends('layouts.public')
@section('titre', 'Connexion')
@section('contenu')
    <div class="mx-auto grid max-w-xl gap-6 py-8">
        <div>
            <h1 class="text-4xl">Connexion</h1>
            <p class="mt-2 text-muted">Accédez à votre espace citoyen, autorité ou superviseur.</p>
        </div>
        @if (app()->environment('local'))
            <div class="card p-4 text-sm">
                <p class="font-semibold">Comptes de démonstration — mot de passe <span class="font-mono">Sama2025</span></p>
                <ul class="mt-2 space-y-1 text-muted">
                    <li>Citoyen : issa.bathily@samaplaintes.sn</li>
                    <li>Autorité : mairie.diamniadio@samaplaintes.sn</li>
                    <li>Superviseur : serigne.diagne@samaplaintes.sn</li>
                </ul>
            </div>
        @endif
        <form class="card grid gap-4 p-6" method="post" action="{{ route('login') }}">
            @csrf
            <label class="field">E-mail
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
            </label>
            @error('email')<p class="field-error">{{ $message }}</p>@enderror
            <label class="field">Mot de passe
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1"> Se souvenir de moi</label>
            <button class="btn btn-primary" type="submit">Entrer</button>
            <p class="text-sm text-muted"><a class="underline" href="{{ route('password.request') }}">Mot de passe oublié</a> · <a class="underline" href="{{ route('register') }}">Créer un compte</a></p>
        </form>
    </div>
@endsection
