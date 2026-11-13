@extends('layouts.app')
@section('titre', 'Profil')
@section('contenu')
    <h1 class="text-4xl">Profil</h1>
    <p class="text-muted">{{ $user->role->label() }} · {{ $user->email }}</p>
    <form class="card mt-6 grid max-w-3xl gap-4 p-6 md:grid-cols-2" method="post" action="{{ route('profil.update') }}">
        @csrf
        @method('put')
        <label class="field">Prénom<input name="prenom" value="{{ old('prenom', $user->prenom) }}" required></label>
        <label class="field">Nom<input name="nom" value="{{ old('nom', $user->nom) }}" required></label>
        <label class="field">Téléphone<input name="telephone" value="{{ old('telephone', $user->telephone) }}" required></label>
        <label class="field">Date de naissance<input type="date" name="date_naissance" value="{{ old('date_naissance', $user->date_naissance?->format('Y-m-d')) }}"></label>
        <label class="field md:col-span-2">Adresse<textarea name="adresse" rows="2">{{ old('adresse', $user->adresse) }}</textarea></label>
        <label class="field md:col-span-2">Pièce d’identité<input name="numero_identite" value="{{ old('numero_identite', $user->numero_identite) }}"></label>
        <label class="field">Nouveau mot de passe<input type="password" name="password" autocomplete="new-password"></label>
        <label class="field">Confirmation<input type="password" name="password_confirmation"></label>
        <div class="md:col-span-2"><button class="btn btn-primary" type="submit">Enregistrer</button></div>
    </form>
@endsection
