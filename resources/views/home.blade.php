@extends('layouts.public')
@section('titre', 'Sama Plaintes')
@section('contenu')
    <section class="grid items-center gap-10 lg:grid-cols-[1.15fr_0.85fr]" aria-labelledby="hero-titre">
        <div>
            <p class="eyebrow">République du Sénégal · Gouvernance de proximité</p>
            <h1 id="hero-titre" class="mt-3 text-5xl leading-[1.05] text-baobab-dark">Signalez un problème. Suivez-le jusqu’à la réponse.</h1>
            <p class="mt-5 max-w-xl text-lg leading-relaxed text-muted">Sama Plaintes permet aux citoyens de déposer une plainte et de suivre son traitement depuis une plateforme centralisée. La gestion des réclamations devient plus simple, transparente et traçable.</p>
            <div class="mt-7 flex flex-wrap gap-3">
                <a class="btn btn-primary" href="{{ auth()->user()?->estCitoyen() ? route('citoyen.plaintes.create') : (auth()->check() ? route('dashboard') : route('register')) }}">Déposer une plainte</a>
                <a class="btn btn-ghost" href="#suivi">Retrouver un dossier</a>
            </div>
        </div>
        <aside class="card ticket hero-panel p-6" aria-label="Promesse">
            <p class="eyebrow">Une plainte, un numéro</p>
            <h2 class="mt-3 text-2xl">Éclairage, voirie, assainissement</h2>
            <p class="mt-3 text-sm leading-relaxed text-muted">Prenez une photo, décrivez le lieu et envoyez le signalement. Les responsables reçoivent le dossier et en mettent l’état à jour.</p>
        </aside>
    </section>

    <section class="mt-16" aria-labelledby="marche">
        <p class="eyebrow">Le processus</p>
        <h2 id="marche" class="mt-2 text-3xl">Comment ça marche</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-4">
            @foreach ([
                'Décrivez le problème, joignez une photo et indiquez le lieu.',
                'La plateforme génère un numéro de suivi unique.',
                'Le service compétent reçoit le dossier et le traite.',
                'Vous suivez l’état jusqu’à la réponse, avec une notification à chaque étape.',
            ] as $index => $etape)
                <article class="card p-5">
                    <p class="display text-2xl text-baobab">0{{ $index + 1 }}</p>
                    <p class="mt-2 leading-relaxed">{{ $etape }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="mt-16" aria-labelledby="categories">
        <p class="eyebrow">Problèmes concernés</p>
        <h2 id="categories" class="mt-2 text-3xl">Catégories</h2>
        <p class="mt-2 max-w-2xl text-sm text-muted">Chaque chiffre compte les plaintes réellement enregistrées dans cette catégorie.</p>
        <ul class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($categories as $categorie)
                <li class="card flex items-center justify-between gap-3 px-4 py-3">
                    <span class="font-semibold">{{ $categorie['label'] }}</span>
                    <span class="badge badge-assignee">{{ $categorie['total'] }}</span>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="mt-16" id="suivi" aria-labelledby="suivi-titre">
        <p class="eyebrow">Retrouver une plainte</p>
        <h2 id="suivi-titre" class="mt-2 text-3xl">Suivi rapide</h2>
        <p class="mt-2 max-w-2xl text-sm text-muted">Saisissez le numéro reçu au dépôt. Le résultat indique l’état public du dossier, sans le nom du plaignant.</p>
        <form class="card mt-5 grid gap-4 p-6 sm:grid-cols-[1fr_auto] sm:items-end" method="get" action="{{ route('accueil') }}#suivi">
            <label class="field">Numéro de suivi
                <input name="reference" value="{{ $reference }}" placeholder="SP-2026-00001" maxlength="30" required autocomplete="off">
            </label>
            <button class="btn btn-primary" type="submit">Retrouver</button>
        </form>
        @if ($reference !== '' && $suivie)
            <article class="card mt-4 p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="font-semibold text-baobab-dark">{{ $suivie->reference }}</p>
                    <span class="badge badge-{{ $suivie->statut->value }}">{{ $suivie->statut->label() }}</span>
                </div>
                <h3 class="mt-3 text-2xl">{{ $suivie->titre }}</h3>
                <p class="mt-1 text-sm text-muted">{{ $suivie->domaine->label() }} · {{ $suivie->commune }}, {{ $suivie->region }}</p>
                <p class="mt-3 text-sm">Déposée le {{ $suivie->date_soumission?->timezone(config('app.timezone'))->format('d/m/Y') }}.</p>
                @can('view', $suivie)
                    <a class="btn btn-ghost mt-4" href="{{ route('plaintes.show', $suivie) }}">Ouvrir le dossier</a>
                @endcan
            </article>
        @elseif ($reference !== '')
            <p class="flash flash-ko mt-4" role="alert">Aucun dossier ne correspond à {{ $reference }}.</p>
        @endif
    </section>

    <section class="mt-16" aria-labelledby="stats">
        <p class="eyebrow">Activité réelle</p>
        <h2 id="stats" class="mt-2 text-3xl">Statistiques</h2>
        <p class="mt-2 max-w-2xl text-sm text-muted">Ces indicateurs sont calculés à partir des plaintes enregistrées dans la base, pas de chiffres affichés à la main.</p>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <article class="card stat"><b data-stat="total">{{ $total }}</b><span>plaintes déposées</span></article>
            <article class="card stat"><b data-stat="resolues">{{ $resolues }}</b><span>plaintes résolues</span></article>
            <article class="card stat"><b data-stat="encours">{{ $enCours }}</b><span>plaintes en cours</span></article>
            <article class="card stat"><b data-stat="semaine">{{ $semaine }}</b><span>signalements cette semaine</span></article>
        </div>
    </section>

    <section class="mt-16" aria-labelledby="engagement">
        <div class="card hero-panel grid gap-6 p-8 md:grid-cols-[1.4fr_auto] md:items-center">
            <div>
                <p class="eyebrow">Passez à l’action</p>
                <h2 id="engagement" class="mt-2 text-3xl">Engagement citoyen</h2>
                <p class="mt-3 max-w-2xl leading-relaxed text-muted">Un éclairage en panne, une route abîmée ou un caniveau bouché mérite un signalement. Votre plainte reste traçable, du dépôt jusqu’à la réponse du service.</p>
            </div>
            <a class="btn btn-primary" href="{{ auth()->user()?->estCitoyen() ? route('citoyen.plaintes.create') : (auth()->check() ? route('dashboard') : route('register')) }}">Signaler un problème</a>
        </div>
    </section>
@endsection
