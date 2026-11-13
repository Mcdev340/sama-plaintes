@extends('layouts.public')
@section('titre', 'Sama Plaintes')
@section('contenu')
    <section class="grid items-center gap-10 lg:grid-cols-[1.12fr_0.88fr]">
        <div>
            <p class="eyebrow">République du Sénégal · Gouvernance de proximité</p>
            <h1 class="mt-3 text-5xl leading-[1.05] text-baobab-dark">La plainte du citoyen, suivie jusqu’à la réponse.</h1>
            <p class="mt-5 max-w-xl text-lg leading-relaxed text-muted">Sama Plaintes relie les usagers, les autorités locales et les superviseurs. Chaque doléance est orientée vers le service compétent, tracée, puis rappelée si le délai est dépassé.</p>
            <div class="mt-7 flex flex-wrap gap-3">
                <a class="btn btn-primary" href="{{ auth()->check() ? route('dashboard') : route('register') }}">Déposer une plainte</a>
                <a class="btn btn-ghost" href="{{ route('fonctionnalites') }}">Voir les fonctionnalités</a>
            </div>
        </div>
        <aside class="card ticket hero-panel p-6" aria-label="Exemple de suivi">
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs uppercase tracking-widest text-muted">Dossier SP-2026-00018</p>
                <span class="badge badge-en_traitement">En traitement</span>
            </div>
            <h2 class="mt-3 text-2xl">Nid de poule devant l’ISEP</h2>
            <p class="mt-1 text-sm text-muted">Voirie · Diamniadio, Dakar</p>
            <ol class="timeline mt-5 text-sm">
                <li><strong>Soumise</strong> — le citoyen joint une photo et le lieu.</li>
                <li><strong>Assignée</strong> — routage vers la mairie compétente.</li>
                <li><strong>En traitement</strong> — l’équipe de voirie est programmée.</li>
                <li><strong>Résolue</strong> — le citoyen évalue la réponse.</li>
            </ol>
        </aside>
    </section>

    <section class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Chiffres">
        <article class="card stat"><b>{{ $total }}</b><span>plaintes enregistrées</span></article>
        <article class="card stat"><b>{{ $ouvertes }}</b><span>encore ouvertes</span></article>
        <article class="card stat"><b>{{ $resolues }}</b><span>résolues ou closes</span></article>
        <article class="card stat"><b>{{ $zones }}</b><span>régions concernées</span></article>
    </section>

    <section class="mt-14" aria-labelledby="tableau-accueil">
        <div class="card overflow-hidden">
            <div class="table-head">
                <div>
                    <p class="eyebrow">Suivi public</p>
                    <h2 id="tableau-accueil" class="mt-1 text-3xl">Dernières doléances</h2>
                    <p class="mt-1 max-w-xl text-sm text-muted">Les dossiers récents, sans le nom du plaignant. Le détail complet reste réservé aux personnes concernées.</p>
                </div>
                <a class="btn btn-gold btn-small" href="{{ auth()->user()?->estCitoyen() ? route('citoyen.plaintes.create') : (auth()->check() ? route('dashboard') : route('register')) }}">Déposer la vôtre</a>
            </div>
            <div class="table-wrap">
                <table class="data">
                    <caption>Huit dernières plaintes déposées sur la plateforme.</caption>
                    <thead>
                        <tr>
                            <th scope="col">Référence</th>
                            <th scope="col">Objet</th>
                            <th scope="col">Lieu</th>
                            <th scope="col">Priorité</th>
                            <th scope="col">Statut</th>
                            <th scope="col">Déposée le</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentes as $plainte)
                            <tr>
                                <td>
                                    @auth
                                        <a class="text-baobab" href="{{ route('plaintes.show', $plainte) }}">{{ $plainte->reference }}</a>
                                    @else
                                        {{ $plainte->reference }}
                                    @endauth
                                </td>
                                <td>{{ $plainte->titre }}<div class="text-xs text-muted">{{ $plainte->domaine->label() }}</div></td>
                                <td>{{ $plainte->commune }}<div class="text-xs text-muted">{{ $plainte->region }}</div></td>
                                <td><span class="badge badge-{{ $plainte->priorite->value }}">{{ $plainte->priorite->label() }}</span></td>
                                <td>
                                    <span class="badge badge-{{ $plainte->statut->value }}">{{ $plainte->statut->label() }}</span>
                                    @if ($plainte->estEnRetard())
                                        <span class="badge badge-retard">Retard</span>
                                    @endif
                                </td>
                                <td>{{ $plainte->date_soumission?->timezone(config('app.timezone'))->format('d/m/Y') ?? $plainte->created_at?->timezone(config('app.timezone'))->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted">Aucune doléance pour le moment. Le tableau se remplira dès le premier dépôt.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="mt-14" aria-labelledby="parcours">
        <h2 id="parcours" class="text-3xl">Le parcours d’un dossier</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-4">
            @foreach ([
                'Décrire le problème, le lieu et une preuve.',
                'Le superviseur oriente le dossier vers le bon service.',
                'L’autorité traite, demande un complément ou rejette.',
                'Passé le délai, le dossier est escaladé.',
            ] as $index => $etape)
                <article class="card p-5">
                    <p class="display text-2xl text-gold">0{{ $index + 1 }}</p>
                    <p class="mt-2 leading-relaxed">{{ $etape }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="mt-14" aria-labelledby="domaines">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h2 id="domaines" class="text-3xl">Domaines couverts</h2>
            <a class="text-sm font-semibold text-baobab" href="{{ route('fonctionnalites') }}">Comment le routage choisit le service</a>
        </div>
        <ul class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (\App\Enums\Domaine::cases() as $domaine)
                <li class="card px-4 py-3 font-semibold">{{ $domaine->label() }}</li>
            @endforeach
        </ul>
    </section>
@endsection
