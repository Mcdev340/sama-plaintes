@extends('layouts.app')
@section('titre', 'Nouvelle autorité')
@section('contenu')
    <h1 class="text-4xl">Nouvelle autorité</h1>
    <form class="card mt-6 grid gap-4 p-6 md:grid-cols-2" method="post" action="{{ route('superviseur.autorites.store') }}">
        @csrf
        <label class="field">Prénom de l’agent<input name="prenom" value="{{ old('prenom') }}" required></label>
        <label class="field">Nom de l’agent<input name="nom_agent" value="{{ old('nom_agent') }}" required></label>
        <label class="field">E-mail de connexion<input type="email" name="email" value="{{ old('email') }}" required></label>
        <label class="field">Mot de passe initial<input type="text" name="password" required minlength="8"></label>
        <label class="field">Téléphone<input name="telephone" value="{{ old('telephone') }}" required></label>
        <label class="field">E-mail du service<input type="email" name="email_contact" value="{{ old('email_contact') }}" required></label>
        <label class="field md:col-span-2">Institution<input name="institution" value="{{ old('institution') }}" required placeholder="Mairie de…"></label>
        <label class="field">Zone<input name="zone" value="{{ old('zone') }}" required placeholder="Commune ou quartier"></label>
        <label class="field">Région
            <select name="region" required>
                @foreach (\App\Support\Catalogue::REGIONS as $region)
                    <option value="{{ $region }}" @selected(old('region') === $region)>{{ $region }}</option>
                @endforeach
            </select>
        </label>
        <fieldset class="md:col-span-2">
            <legend class="mb-2 text-sm font-semibold">Compétences</legend>
            <div class="flex flex-wrap gap-3">
                @foreach (\App\Enums\Domaine::cases() as $domaine)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="competences[]" value="{{ $domaine->value }}" @checked(in_array($domaine->value, old('competences', []), true))>
                        {{ $domaine->label() }}
                    </label>
                @endforeach
            </div>
        </fieldset>
        <div class="md:col-span-2"><button class="btn btn-primary" type="submit">Créer le compte</button></div>
    </form>
@endsection
