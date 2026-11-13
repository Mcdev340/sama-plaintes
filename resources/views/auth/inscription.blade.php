@extends('layouts.public')
@section('titre', 'Inscription')
@section('contenu')
    <div class="mx-auto max-w-2xl py-8">
        <h1 class="text-4xl">Créer un compte citoyen</h1>
        <p class="mt-2 text-muted">Après l’inscription, un code à 6 chiffres active le compte.</p>
        <form class="card mt-6 grid gap-4 p-6 md:grid-cols-2" method="post" action="{{ route('register') }}">
            @csrf
            <label class="field">Prénom<input name="prenom" value="{{ old('prenom') }}" required></label>
            <label class="field">Nom<input name="nom" value="{{ old('nom') }}" required></label>
            <label class="field md:col-span-2">E-mail<input type="email" name="email" value="{{ old('email') }}" required></label>
            <label class="field">Téléphone<input name="telephone" value="{{ old('telephone') }}" required placeholder="77 000 00 00"></label>
            <label class="field">Date de naissance<input type="date" name="date_naissance" value="{{ old('date_naissance') }}"></label>
            <label class="field md:col-span-2">Adresse<textarea name="adresse" rows="2">{{ old('adresse') }}</textarea></label>
            <label class="field md:col-span-2">Numéro de pièce d’identité<input name="numero_identite" value="{{ old('numero_identite') }}"></label>
            <label class="field">Mot de passe<input type="password" name="password" required autocomplete="new-password"></label>
            <label class="field">Confirmation<input type="password" name="password_confirmation" required></label>
            <div class="md:col-span-2"><button class="btn btn-primary" type="submit">Créer le compte</button></div>
        </form>
    </div>
@endsection
