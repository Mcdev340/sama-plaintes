@extends('layouts.public')
@section('titre', 'À propos — Sama Plaintes')
@section('contenu')
    <section aria-labelledby="apropos-titre">
        <p class="eyebrow">Découvrir Sama Plaintes</p>
        <h1 id="apropos-titre" class="mt-2 max-w-3xl text-5xl leading-tight text-baobab-dark">À propos de Sama Plaintes</h1>
        <p class="mt-5 max-w-2xl text-lg leading-relaxed">Donner à chaque citoyen la possibilité de faire entendre sa voix et de contribuer à l’amélioration de son environnement.</p>
        <div class="mt-5 max-w-3xl space-y-4 text-muted">
            <p>Sama Plaintes est une plateforme numérique conçue pour faciliter le signalement des problèmes du quotidien, simplifier le dépôt des réclamations et améliorer le suivi de leur traitement.</p>
            <p>Grâce à un espace centralisé, les citoyens peuvent transmettre leurs préoccupations et suivre l’évolution de leurs dossiers en toute transparence.</p>
            <p>Cette solution est en cours de conception. Elle n’est pas présentée comme un service déjà déployé auprès des collectivités.</p>
        </div>
    </section>

    <section class="mt-16" aria-labelledby="histoire">
        <p class="eyebrow">Notre histoire</p>
        <h2 id="histoire" class="mt-2 text-3xl">Pourquoi Sama Plaintes ?</h2>
        <div class="mt-4 max-w-3xl space-y-4 leading-relaxed text-muted">
            <p>De nombreux problèmes rencontrés dans les quartiers restent parfois difficiles à signaler et à suivre : routes endommagées, éclairage public défectueux, problèmes d’assainissement ou gestion des déchets.</p>
            <p>Sama Plaintes propose une solution numérique pour faciliter la communication entre les citoyens et les services responsables.</p>
        </div>
    </section>

    <section class="mt-16" aria-labelledby="mission">
        <h2 id="mission" class="text-3xl">Notre mission, notre vision</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <article class="card p-6">
                <h3 class="text-2xl">Notre mission</h3>
                <p class="mt-3 leading-relaxed text-muted">Faciliter le signalement des problèmes et améliorer le suivi des réclamations citoyennes.</p>
            </article>
            <article class="card p-6">
                <h3 class="text-2xl">Notre vision</h3>
                <p class="mt-3 leading-relaxed text-muted">Construire un environnement où chaque préoccupation citoyenne peut être entendue et suivie.</p>
            </article>
        </div>
    </section>

    <section class="mt-16" aria-labelledby="valeurs">
        <h2 id="valeurs" class="text-3xl">Nos valeurs</h2>
        <ul class="mt-5 grid gap-4 sm:grid-cols-2">
            @foreach ([
                ['Transparence', 'Permettre aux citoyens de suivre les différentes étapes de leurs plaintes.'],
                ['Engagement citoyen', 'Encourager la participation de chacun à l’amélioration de son cadre de vie.'],
                ['Confidentialité', 'Protéger les informations personnelles et les données transmises.'],
                ['Innovation', 'Utiliser le numérique pour simplifier les démarches et améliorer les services.'],
            ] as [$titre, $texte])
                <li class="card p-6">
                    <h3 class="text-2xl">{{ $titre }}</h3>
                    <p class="mt-2 leading-relaxed text-muted">{{ $texte }}</p>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="mt-16" aria-labelledby="fonctionnement">
        <p class="eyebrow">Le parcours prévu</p>
        <h2 id="fonctionnement" class="mt-2 text-3xl">Comment fonctionne Sama Plaintes ?</h2>
        <ol class="mt-5 grid gap-4 md:grid-cols-2">
            @foreach ([
                ['Signalement', 'Le citoyen décrit le problème et transmet les informations nécessaires.'],
                ['Enregistrement', 'La plateforme crée un dossier avec un numéro de suivi.'],
                ['Traitement', 'Le service compétent examine et prend en charge la plainte.'],
                ['Suivi et résolution', 'Le citoyen est informé de l’évolution du dossier.'],
            ] as $index => [$titre, $texte])
                <li class="card p-6">
                    <p class="display text-2xl text-baobab">0{{ $index + 1 }}</p>
                    <h3 class="mt-2 text-2xl">{{ $titre }}</h3>
                    <p class="mt-2 leading-relaxed text-muted">{{ $texte }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    <section class="mt-16" aria-labelledby="engagement">
        <h2 id="engagement" class="text-3xl">Notre engagement envers les citoyens</h2>
        <div class="mt-4 max-w-3xl space-y-4 leading-relaxed text-muted">
            <p>Un problème signalé ne doit pas rester sans suivi.</p>
            <p>Sama Plaintes ambitionne de faciliter les échanges entre les citoyens et les responsables chargés de traiter les problèmes signalés.</p>
            <p>Nous souhaitons contribuer à une gestion plus organisée, plus accessible et plus transparente des réclamations.</p>
        </div>
    </section>

    <section class="mt-16" aria-labelledby="appel">
        <div class="card hero-panel p-8">
            <h2 id="appel" class="text-3xl">Votre quartier mérite votre engagement.</h2>
            <p class="mt-3 max-w-2xl leading-relaxed text-muted">Participez à l’amélioration de votre environnement en signalant les problèmes que vous rencontrez.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a class="btn btn-primary" href="{{ auth()->user()?->estCitoyen() ? route('citoyen.plaintes.create') : (auth()->check() ? route('dashboard') : route('register')) }}">Déposer une plainte</a>
                @guest
                    <a class="btn btn-ghost" href="{{ route('register') }}">Créer un compte</a>
                @endguest
            </div>
        </div>
    </section>
@endsection