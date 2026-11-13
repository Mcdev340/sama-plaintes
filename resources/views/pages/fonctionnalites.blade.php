@extends('layouts.public')
@section('titre', 'Fonctionnalités — Sama Plaintes')
@section('contenu')
    <p class="eyebrow">Ce que la plateforme fait</p>
    <h1 class="mt-2 text-5xl text-baobab-dark">Fonctionnalités</h1>
    <p class="mt-4 max-w-2xl text-lg text-muted">Trois espaces, un même dossier. Le citoyen dépose, le superviseur oriente, l’autorité locale traite. Chaque étape laisse une trace.</p>

    <section class="mt-10 grid gap-4 md:grid-cols-3" aria-label="Rôles">
        <article class="card p-6">
            <p class="eyebrow">Citoyen</p>
            <h2 class="mt-2 text-2xl">Déposer et suivre</h2>
            <ul class="mt-4 space-y-2 text-sm leading-relaxed">
                <li>Inscription, activation du compte et réinitialisation du mot de passe.</li>
                <li>Dépôt avec récapitulatif, lieu, priorité et pièces jointes.</li>
                <li>Complément demandé par le service, puis note de satisfaction.</li>
            </ul>
        </article>
        <article class="card p-6">
            <p class="eyebrow">Autorité locale</p>
            <h2 class="mt-2 text-2xl">Traiter le dossier</h2>
            <ul class="mt-4 space-y-2 text-sm leading-relaxed">
                <li>File des dossiers assignés au service.</li>
                <li>Prise en charge, demande de complément, résolution ou rejet motivé.</li>
                <li>Messages internes avec le citoyen et le superviseur.</li>
            </ul>
        </article>
        <article class="card p-6">
            <p class="eyebrow">Superviseur</p>
            <h2 class="mt-2 text-2xl">Orienter et relancer</h2>
            <ul class="mt-4 space-y-2 text-sm leading-relaxed">
                <li>Suggestion d’autorité selon le domaine, la commune et la région.</li>
                <li>Assignation, escalade manuelle et clôture après vérification.</li>
                <li>Tableau de bord, gestion des autorités et export CSV anonymisé.</li>
            </ul>
        </article>
    </section>

    <section class="mt-12" aria-labelledby="mecanismes">
        <h2 id="mecanismes" class="text-3xl">Mécanismes de suivi</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            @foreach ([
                ['Routage', 'Le service proposé gagne des points s’il couvre le domaine, la commune, puis la région. Le superviseur confirme ou choisit une autre autorité.'],
                ['Délais', 'Une priorité basse laisse 15 jours, une priorité moyenne 7 jours, une priorité haute 3 jours. Le dépassement est visible sur le dossier.'],
                ['Escalade', 'Chaque matin, les dossiers ouverts dont l’échéance est passée montent d’un niveau, jusqu’à trois. Le superviseur peut aussi escalader à la main.'],
                ['Notifications', 'Dépôt, analyse, assignation, complément, résolution, rejet, escalade et clôture préviennent les personnes concernées.'],
            ] as [$titre, $texte])
                <article class="card p-6">
                    <h3 class="text-2xl">{{ $titre }}</h3>
                    <p class="mt-2 text-muted">{{ $texte }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <p class="mt-10">
        <a class="btn btn-primary" href="{{ auth()->check() ? route('dashboard') : route('register') }}">Commencer</a>
    </p>
@endsection
