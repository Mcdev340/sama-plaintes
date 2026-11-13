@extends('layouts.app')
@section('titre', 'Déposer une plainte')
@section('contenu')
    <h1 class="text-4xl">Déposer une plainte</h1>
    <p class="mt-2 max-w-2xl text-muted">Décrivez le problème, précisez le lieu, puis vérifiez le récapitulatif avant l’envoi.</p>
    <noscript><style>.is-hidden { display: block !important; }</style></noscript>
    <form class="mt-6" method="post" action="{{ route('citoyen.plaintes.store') }}" enctype="multipart/form-data" data-plainte-form>
        @csrf
        <section class="card grid gap-4 p-6 md:grid-cols-2" data-step="1">
            <label class="field md:col-span-2">Titre<input name="titre" value="{{ old('titre') }}" required maxlength="255"></label>
            <label class="field">Domaine
                <select name="domaine" required>
                    <option value="">Choisir</option>
                    @foreach (\App\Enums\Domaine::cases() as $domaine)
                        <option value="{{ $domaine->value }}" @selected(old('domaine') === $domaine->value)>{{ $domaine->label() }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field">Priorité
                <select name="priorite" required>
                    @foreach (\App\Enums\Priorite::cases() as $priorite)
                        <option value="{{ $priorite->value }}" @selected(old('priorite', 'moyenne') === $priorite->value)>{{ $priorite->label() }} — {{ $priorite->delai() }} jours</option>
                    @endforeach
                </select>
            </label>
            <label class="field">Région
                <select name="region" required>
                    <option value="">Choisir</option>
                    @foreach (\App\Support\Catalogue::REGIONS as $region)
                        <option value="{{ $region }}" @selected(old('region') === $region)>{{ $region }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field">Commune ou quartier
                <input name="commune" value="{{ old('commune') }}" required list="communes">
                <datalist id="communes">
                    @foreach (\App\Support\Catalogue::COMMUNES as $commune)
                        <option value="{{ $commune }}"></option>
                    @endforeach
                </datalist>
            </label>
            <label class="field md:col-span-2">Lieu précis<input name="localisation" value="{{ old('localisation') }}" required placeholder="Rue, marché, école, quartier"></label>
            <label class="field">Latitude<input name="latitude" value="{{ old('latitude') }}" inputmode="decimal"></label>
            <label class="field">Longitude<input name="longitude" value="{{ old('longitude') }}" inputmode="decimal"></label>
            <label class="field md:col-span-2">Description
                <textarea name="description" rows="5" required minlength="20">{{ old('description') }}</textarea>
            </label>
            <label class="field md:col-span-2">Pièces jointes (photos ou PDF, 4 fichiers max)
                <input type="file" name="pieces[]" accept=".jpg,.jpeg,.png,.webp,.pdf" multiple>
            </label>
            <div class="md:col-span-2"><button class="btn btn-gold" type="button" data-recap>Vérifier le récapitulatif</button></div>
        </section>
        <section class="card is-hidden grid gap-3 p-6" data-step="2">
            <h2 class="text-2xl">Récapitulatif</h2>
            <p><strong>Titre.</strong> <span data-resume="titre"></span></p>
            <p><strong>Domaine.</strong> <span data-resume="domaine"></span></p>
            <p><strong>Priorité.</strong> <span data-resume="priorite"></span></p>
            <p><strong>Lieu.</strong> <span data-resume="localisation"></span></p>
            <p><strong>Description.</strong> <span data-resume="description"></span></p>
            <p><strong>Pièces.</strong> <span data-resume="pieces"></span></p>
            <div class="flex flex-wrap gap-3">
                <button class="btn btn-ghost" type="button" data-retour>Corriger</button>
                <button class="btn btn-primary" type="submit">Envoyer la plainte</button>
            </div>
        </section>
    </form>
@endsection
