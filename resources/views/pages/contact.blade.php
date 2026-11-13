@extends('layouts.public')
@section('titre', 'Contact — Sama Plaintes')
@section('contenu')
    <p class="eyebrow">Écrire à l’équipe</p>
    <h1 class="mt-2 text-5xl text-baobab-dark">Contact</h1>
    <p class="mt-4 max-w-2xl text-lg text-muted">Une question sur un dossier, un service à enregistrer ou une remarque sur la plateforme : laissez un message.</p>

    <div class="mt-10 grid gap-6 lg:grid-cols-[0.85fr_1.15fr]">
        <aside class="card p-6">
            <h2 class="text-2xl">Coordonnées</h2>
            <dl class="mt-4 space-y-4 text-sm">
                <div>
                    <dt class="font-semibold">E-mail</dt>
                    <dd class="text-muted">contact@samaplaintes.sn</dd>
                </div>
                <div>
                    <dt class="font-semibold">Adresse</dt>
                    <dd class="text-muted">ISEP Amadou Trawaré<br>Diamniadio, Sénégal</dd>
                </div>
                <div>
                    <dt class="font-semibold">Pour un dossier en cours</dt>
                    <dd class="text-muted">Connectez-vous et écrivez dans les échanges du dossier. Ce formulaire ne remplace pas le suivi d’une plainte.</dd>
                </div>
            </dl>
        </aside>
        <form class="card grid gap-4 p-6" method="post" action="{{ route('contact.envoyer') }}">
            @csrf
            <label class="field">Nom
                <input name="nom" value="{{ old('nom') }}" required maxlength="120" autocomplete="name">
                @error('nom')<span class="field-error">{{ $message }}</span>@enderror
            </label>
            <label class="field">E-mail
                <input name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email">
                @error('email')<span class="field-error">{{ $message }}</span>@enderror
            </label>
            <label class="field">Sujet
                <input name="sujet" value="{{ old('sujet') }}" required maxlength="160">
                @error('sujet')<span class="field-error">{{ $message }}</span>@enderror
            </label>
            <label class="field">Message
                <textarea name="message" rows="6" required minlength="20" maxlength="2000">{{ old('message') }}</textarea>
                @error('message')<span class="field-error">{{ $message }}</span>@enderror
            </label>
            <div>
                <button class="btn btn-primary" type="submit">Envoyer le message</button>
            </div>
        </form>
    </div>
@endsection
