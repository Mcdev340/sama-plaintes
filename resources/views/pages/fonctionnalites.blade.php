@extends('layouts.public')
@section('titre', 'Fonctionnalités — Sama Plaintes')
@section('contenu')
    <section class="grid items-center gap-10 lg:grid-cols-[1.15fr_0.85fr]" aria-labelledby="fonctions-titre">
        <div>
            <p class="eyebrow">Ce que la plateforme fait</p>
            <h1 id="fonctions-titre" class="mt-3 max-w-3xl text-5xl leading-[1.05] text-baobab-dark">Tout le parcours d’une plainte, au même endroit.</h1>
            <p class="mt-5 max-w-xl text-lg leading-relaxed text-muted">Le citoyen décrit le problème, joint une preuve et indique le lieu. Le dossier reçoit un numéro, passe au service compétent, puis reste visible jusqu’à la réponse.</p>
            <div class="mt-7 flex flex-wrap gap-3">
                <a class="btn btn-primary" href="{{ auth()->user()?->estCitoyen() ? route('citoyen.plaintes.create') : (auth()->check() ? route('dashboard') : route('register')) }}">Déposer une plainte</a>
                <a class="btn btn-ghost" href="#parcours">Voir le parcours</a>
            </div>
            <nav class="mt-6 flex flex-wrap gap-x-4 gap-y-2 text-sm font-semibold text-baobab-dark" aria-label="Sections de la page">
                <a href="#parcours">Parcours</a>
                <a href="#capacites">Capacités</a>
                <a href="#domaines">Domaines</a>
                <a href="#etats">États</a>
                <a href="#espaces">Espaces</a>
                <a href="#suivi">Suivi</a>
            </nav>
        </div>
        <aside class="card ticket hero-panel p-6" aria-label="Exemple de dossier">
            <p class="eyebrow">Exemple de dossier</p>
            <h2 class="mt-3 text-2xl">Un numéro, puis chaque étape</h2>
            <p class="mt-3 text-sm text-muted">Voirie · commune indiquée au dépôt</p>
            <ol class="mt-5 space-y-3 text-sm">
                @foreach (['Soumise', 'Assignée', 'En traitement', 'Résolue'] as $etape)
                    <li class="flex items-center gap-3">
                        <span class="badge badge-assignee">{{ $etape }}</span>
                    </li>
                @endforeach
            </ol>
            <p class="mt-4 text-sm text-muted">Le nom du plaignant n’apparaît pas dans le suivi public.</p>
        </aside>
    </section>

    <section class="mt-16" id="parcours" aria-labelledby="parcours-titre">
        <p class="eyebrow">De la description à la réponse</p>
        <h2 id="parcours-titre" class="mt-2 text-3xl">Le parcours d’une plainte</h2>
        <ol class="mt-5 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['Décrire', 'Le citoyen rédige la plainte, choisit le domaine et la priorité, précise le lieu et joint une photo ou un document. Un récapitulatif s’affiche avant l’envoi.'],
                ['Enregistrer', 'La plateforme crée le dossier et remet une référence unique. Le suivi public montre l’état, sans le nom du plaignant.'],
                ['Orienter', 'Une autorité est suggérée selon le domaine, la commune et la région. Le superviseur confirme l’assignation ou en choisit une autre.'],
                ['Répondre', 'Le service prend en charge le dossier, peut demander un complément, puis le résout ou le rejette avec un motif. Le citoyen note sa satisfaction.'],
            ] as $index => [$titre, $texte])
                <li class="card p-5">
                    <p class="display text-2xl text-baobab">0{{ $index + 1 }}</p>
                    <h3 class="mt-2 text-xl">{{ $titre }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">{{ $texte }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    <section class="mt-16" id="capacites" aria-labelledby="capacites-titre">
        <p class="eyebrow">Pour le citoyen</p>
        <h2 id="capacites-titre" class="mt-2 text-3xl">Ce que vous pouvez faire</h2>
        <ul class="mt-5 grid gap-4 sm:grid-cols-2">
            @foreach ([
                ['Dépôt', 'Rédiger une plainte ou une réclamation, puis vérifier le récapitulatif avant l’envoi.'],
                ['Preuves', 'Joindre des photos ou des documents au dossier.'],
                ['Localisation', 'Indiquer la région, la commune et le lieu du problème.'],
                ['Catégories', 'Classer la plainte parmi les domaines prévus, de la voirie à la santé.'],
                ['Numéro de suivi', 'Recevoir une référence unique dès l’enregistrement, puis la retrouver depuis l’accueil.'],
                ['État du dossier', 'Voir si la plainte est soumise, assignée, en traitement, résolue, rejetée ou escaladée.'],
                ['Notifications', 'Être prévenu quand le dossier avance, demande un complément ou se termine.'],
                ['Compte', 'S’inscrire, activer le compte, se connecter et réinitialiser le mot de passe.'],
            ] as [$titre, $texte])
                <li class="card p-5">
                    <h3 class="text-xl">{{ $titre }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">{{ $texte }}</p>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="mt-16" id="domaines" aria-labelledby="domaines-titre">
        <p class="eyebrow">Classement</p>
        <h2 id="domaines-titre" class="mt-2 text-3xl">Domaines de plainte</h2>
        <p class="mt-2 max-w-2xl text-sm text-muted">Le domaine sert à proposer le service compétent. La liste est celle utilisée à l’enregistrement.</p>
        <ul class="mt-5 flex flex-wrap gap-2">
            @foreach ($domaines as $domaine)
                <li class="badge badge-assignee">{{ $domaine->label() }}</li>
            @endforeach
        </ul>
    </section>

    <section class="mt-16" id="etats" aria-labelledby="etats-titre">
        <p class="eyebrow">Visibles sur le dossier</p>
        <h2 id="etats-titre" class="mt-2 text-3xl">États du dossier</h2>
        <p class="mt-2 max-w-2xl text-sm text-muted">Chaque plainte porte un seul état à la fois. Résolue, Rejetée et Fermée closent le dossier. Escaladée le laisse ouvert.</p>
        <ul class="mt-5 flex flex-wrap gap-2">
            @foreach ($statuts as $statut)
                <li class="badge badge-{{ $statut->value }}">{{ $statut->label() }}</li>
            @endforeach
        </ul>
    </section>

    <section class="mt-16" id="espaces" aria-labelledby="espaces-titre">
        <p class="eyebrow">Selon le rôle</p>
        <h2 id="espaces-titre" class="mt-2 text-3xl">Trois espaces</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-3">
            <article class="card p-6">
                <p class="eyebrow">Citoyen</p>
                <h3 class="mt-2 text-2xl">Déposer et suivre</h3>
                <ul class="mt-4 space-y-2 text-sm leading-relaxed text-muted">
                    <li>Inscription, activation du compte et réinitialisation du mot de passe.</li>
                    <li>Dépôt avec récapitulatif, lieu, priorité et pièces jointes.</li>
                    <li>Réponse à un complément, puis note de satisfaction.</li>
                </ul>
            </article>
            <article class="card p-6">
                <p class="eyebrow">Autorité locale</p>
                <h3 class="mt-2 text-2xl">Traiter le dossier</h3>
                <ul class="mt-4 space-y-2 text-sm leading-relaxed text-muted">
                    <li>File des dossiers assignés au service.</li>
                    <li>Prise en charge, demande de complément, résolution ou rejet motivé.</li>
                    <li>Messages internes avec le citoyen et le superviseur.</li>
                </ul>
            </article>
            <article class="card p-6">
                <p class="eyebrow">Superviseur</p>
                <h3 class="mt-2 text-2xl">Orienter et relancer</h3>
                <ul class="mt-4 space-y-2 text-sm leading-relaxed text-muted">
                    <li>Suggestion d’autorité selon le domaine, la commune et la région.</li>
                    <li>Assignation, escalade manuelle et clôture après vérification.</li>
                    <li>Tableau de bord, gestion des autorités et export CSV anonymisé.</li>
                </ul>
            </article>
        </div>
    </section>

    <section class="mt-16" id="suivi" aria-labelledby="suivi-mecanismes">
        <p class="eyebrow">Ce qui fait avancer le dossier</p>
        <h2 id="suivi-mecanismes" class="mt-2 text-3xl">Mécanismes de suivi</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <article class="card p-6">
                <h3 class="text-2xl">Routage</h3>
                <p class="mt-2 leading-relaxed text-muted">Le service proposé est celui qui couvre le domaine, puis la commune, puis la région. Le dépôt ne l’assigne pas tout seul : le superviseur confirme ou choisit une autre autorité.</p>
            </article>
            <article class="card p-6">
                <h3 class="text-2xl">Délais</h3>
                <p class="mt-2 leading-relaxed text-muted">Le délai dépend de la priorité choisie au dépôt. Le dépassement reste visible sur le dossier.</p>
                <ul class="mt-4 flex flex-wrap gap-2">
                    @foreach ($priorites as $priorite)
                        <li class="badge badge-{{ $priorite->value }}">{{ $priorite->label() }} · {{ $priorite->delai() }} jours</li>
                    @endforeach
                </ul>
            </article>
            <article class="card p-6">
                <h3 class="text-2xl">Escalade</h3>
                <p class="mt-2 leading-relaxed text-muted">Chaque matin, les dossiers ouverts dont l’échéance est passée montent d’un niveau, jusqu’à trois. Le superviseur peut aussi escalader à la main.</p>
            </article>
            <article class="card p-6">
                <h3 class="text-2xl">Notifications</h3>
                <p class="mt-2 leading-relaxed text-muted">Dépôt, analyse, assignation, complément, résolution, rejet, escalade et clôture préviennent les personnes concernées.</p>
            </article>
        </div>
    </section>

    <section class="mt-16" aria-labelledby="appel-fonctions">
        <div class="card hero-panel p-8">
            <h2 id="appel-fonctions" class="text-3xl">Un signalement clair, un dossier suivi.</h2>
            <p class="mt-3 max-w-2xl leading-relaxed text-muted">Décrivez le problème de votre quartier. Le numéro de suivi permet de revoir l’état du dossier à chaque étape.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a class="btn btn-primary" href="{{ auth()->user()?->estCitoyen() ? route('citoyen.plaintes.create') : (auth()->check() ? route('dashboard') : route('register')) }}">Déposer une plainte</a>
                @guest
                    <a class="btn btn-ghost" href="{{ route('register') }}">Créer un compte</a>
                @endguest
            </div>
        </div>
    </section>
@endsection
