@extends('layouts.public')
@section('titre', 'À propos — Sama Plaintes')
@section('contenu')
    <p class="eyebrow">Le projet</p>
    <h1 class="mt-2 max-w-3xl text-5xl leading-tight text-baobab-dark">À propos de Sama Plaintes</h1>
    <p class="mt-5 max-w-2xl text-lg leading-relaxed text-muted">Sama Plaintes est une plateforme de gouvernance de proximité. Elle donne au citoyen un canal unique pour signaler un problème local, et aux services un dossier traçable jusqu’à la réponse.</p>

    <section class="mt-10 grid gap-4 md:grid-cols-[1.2fr_0.8fr]">
        <article class="card p-6">
            <h2 class="text-2xl">Pourquoi cette plateforme</h2>
            <p class="mt-3 leading-relaxed">Les doléances circulent souvent par téléphone, courrier ou réseau social, sans délai ni responsable clairement identifié. Sama Plaintes rassemble le dépôt, l’orientation par domaine et par lieu, le traitement, la relance en cas de retard et l’avis du citoyen sur la réponse obtenue.</p>
            <p class="mt-3 leading-relaxed">Le nom « Sama » signifie « ma » en wolof : la plainte reste celle de la personne qui l’a déposée, visible à chaque étape.</p>
        </article>
        <article class="card bg-baobab-dark p-6 text-[#efe7d6]">
            <p class="eyebrow text-gold-soft">Cadre</p>
            <h2 class="mt-2 text-2xl text-white">ISEP Diamniadio</h2>
            <p class="mt-3 text-sm leading-relaxed">Projet de fin de formation 2024-2025, Institut supérieur d’enseignement professionnel Amadou Trawaré de Diamniadio. Encadrement : Serigne Diagne.</p>
        </article>
    </section>

    <section class="mt-12" aria-labelledby="equipe">
        <h2 id="equipe" class="text-3xl">L’équipe</h2>
        <ul class="mt-5 grid gap-4 sm:grid-cols-2">
            @foreach ([
                ['Issa Bathily', 'Conception et parcours citoyen'],
                ['Sékou Diedhiou', 'Traitement par les autorités'],
                ['Mamadou Aliou Diallo', 'Supervision et statistiques'],
                ['Moussa Kathieuness Correa', 'Suivi, notifications et relances'],
            ] as [$nom, $apport])
                <li class="card p-5">
                    <p class="display text-2xl text-baobab-dark">{{ $nom }}</p>
                    <p class="mt-1 text-sm text-muted">{{ $apport }}</p>
                </li>
            @endforeach
        </ul>
    </section>
@endsection
