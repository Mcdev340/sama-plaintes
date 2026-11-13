@extends('layouts.app')
@section('titre', $plainte->reference)
@section('contenu')
    @php
        $retour = match (auth()->user()->role) {
            \App\Enums\Role::Autorite => route('autorite.dossiers.index'),
            \App\Enums\Role::Superviseur => route('superviseur.plaintes.index'),
            default => route('citoyen.plaintes.index'),
        };
    @endphp
    <a class="text-sm text-baobab" href="{{ $retour }}">Retour à la liste</a>
    <div class="mt-3 flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="text-sm text-muted">{{ $plainte->reference }} · {{ $plainte->date_soumission?->isoFormat('D MMMM YYYY') }}</p>
            <h1 class="text-4xl">{{ $plainte->titre }}</h1>
        </div>
        <div class="flex flex-wrap gap-2">
            <span class="badge badge-{{ $plainte->statut->value }}">{{ $plainte->statut->label() }}</span>
            <span class="badge badge-{{ $plainte->priorite->value }}">{{ $plainte->priorite->label() }}</span>
            @if ($plainte->estEnRetard())<span class="badge badge-retard">Retard</span>@endif
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="space-y-6">
            <article class="card p-6">
                <p>{{ $plainte->description }}</p>
                <dl class="mt-4 grid gap-3 text-sm md:grid-cols-2">
                    <div><dt class="text-muted">Domaine</dt><dd>{{ $plainte->domaine->label() }}</dd></div>
                    <div><dt class="text-muted">Lieu</dt><dd>{{ $plainte->localisation }}, {{ $plainte->commune }} ({{ $plainte->region }})</dd></div>
                    <div><dt class="text-muted">Citoyen</dt><dd>{{ $plainte->citoyen?->nomComplet() }}</dd></div>
                    <div><dt class="text-muted">Autorité</dt><dd>{{ $plainte->autorite?->nom ?? 'Pas encore assignée' }}</dd></div>
                    <div><dt class="text-muted">Échéance</dt><dd>{{ $plainte->date_echeance?->format('d/m/Y') ?? '—' }}</dd></div>
                    @if ($plainte->note_satisfaction)
                        <div><dt class="text-muted">Satisfaction</dt><dd>{{ $plainte->note_satisfaction }}/5</dd></div>
                    @endif
                </dl>
                @if ($plainte->latitude && $plainte->longitude)
                    <p class="mt-3 text-sm"><a class="underline" href="https://www.openstreetmap.org/?mlat={{ $plainte->latitude }}&mlon={{ $plainte->longitude }}#map=16/{{ $plainte->latitude }}/{{ $plainte->longitude }}">Voir sur la carte</a></p>
                @endif
            </article>

            @if ($plainte->pieces->isNotEmpty())
                <section class="card p-6">
                    <h2 class="text-2xl">Pièces jointes</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($plainte->pieces as $piece)
                            <li><a class="text-baobab underline" href="{{ route('pieces.download', [$plainte, $piece]) }}">{{ $piece->nom_fichier }}</a> · {{ $piece->tailleLisible() }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="card p-6">
                <h2 class="text-2xl">Historique</h2>
                <ol class="timeline mt-4">
                    @foreach ($plainte->traitements as $etape)
                        <li>
                            <p class="text-sm text-muted">{{ $etape->date_traitement?->isoFormat('D MMM YYYY, HH:mm') }} · {{ $etape->responsable }}</p>
                            <p class="font-semibold">{{ $etape->libelle() }}</p>
                            @if ($etape->commentaire)<p class="text-sm">{{ $etape->commentaire }}</p>@endif
                        </li>
                    @endforeach
                </ol>
            </section>

            <section class="card p-6">
                <h2 class="text-2xl">Échanges</h2>
                <ul class="mt-4 space-y-3">
                    @forelse ($plainte->messages as $message)
                        <li class="rounded-xl bg-canopy p-3">
                            <p class="text-sm text-muted">{{ $message->auteur?->nomComplet() }} · {{ $message->created_at?->isoFormat('D MMM, HH:mm') }}</p>
                            <p>{{ $message->corps }}</p>
                        </li>
                    @empty
                        <li class="text-sm text-muted">Aucun message.</li>
                    @endforelse
                </ul>
                @can('commenter', $plainte)
                    @if ($plainte->accepte('ecrire'))
                        <form class="mt-4 grid gap-3" method="post" action="{{ route('messages.store', $plainte) }}">
                            @csrf
                            <label class="field">Message<textarea name="corps" rows="3" required minlength="2"></textarea></label>
                            <button class="btn btn-primary w-fit" type="submit">Envoyer</button>
                        </form>
                    @endif
                @endcan
            </section>
        </div>

        <aside class="space-y-4">
            @can('traiter', $plainte)
                @if ($plainte->accepte('prendre'))
                    <form method="post" action="{{ route('autorite.dossiers.prendre', $plainte) }}">@csrf<button class="btn btn-primary w-full" type="submit">Prendre en charge</button></form>
                @endif
                @if ($plainte->accepte('complement_autorite'))
                    <form class="card grid gap-3 p-4" method="post" action="{{ route('autorite.dossiers.complement', $plainte) }}">
                        @csrf
                        <label class="field">Demander un complément<textarea name="commentaire" rows="3" required></textarea></label>
                        <button class="btn btn-ghost" type="submit">Envoyer la demande</button>
                    </form>
                @endif
                @if ($plainte->accepte('resoudre'))
                    <form class="card grid gap-3 p-4" method="post" action="{{ route('autorite.dossiers.resoudre', $plainte) }}">
                        @csrf
                        <label class="field">Résolution<textarea name="commentaire" rows="3" required></textarea></label>
                        <button class="btn btn-primary" type="submit">Marquer résolue</button>
                    </form>
                    <form class="card grid gap-3 p-4" method="post" action="{{ route('autorite.dossiers.rejeter', $plainte) }}">
                        @csrf
                        <label class="field">Motif de rejet<textarea name="commentaire" rows="3" required></textarea></label>
                        <button class="btn btn-danger" type="submit">Rejeter</button>
                    </form>
                @endif
            @endcan

            @can('completer', $plainte)
                @if ($plainte->accepte('completer'))
                    <form class="card grid gap-3 p-4" method="post" action="{{ route('citoyen.plaintes.completer', $plainte) }}" enctype="multipart/form-data">
                        @csrf
                        <label class="field">Complément demandé<textarea name="complement" rows="3" required></textarea></label>
                        <label class="field">Pièce<input type="file" name="pieces[]"></label>
                        <button class="btn btn-primary" type="submit">Transmettre</button>
                    </form>
                @endif
                @if ($plainte->accepte('evaluer'))
                    <form class="card grid gap-3 p-4" method="post" action="{{ route('citoyen.plaintes.evaluer', $plainte) }}">
                        @csrf
                        <label class="field">Votre note
                            <select name="note" required>
                                @for ($note = 5; $note >= 1; $note--)
                                    <option value="{{ $note }}">{{ $note }}/5</option>
                                @endfor
                            </select>
                        </label>
                        <label class="field">Commentaire<textarea name="commentaire" rows="2"></textarea></label>
                        <button class="btn btn-gold" type="submit">Évaluer</button>
                    </form>
                @endif
            @endcan

            @can('assigner', $plainte)
                @if ($plainte->accepte('analyser'))
                    <form method="post" action="{{ route('superviseur.plaintes.analyser', $plainte) }}">@csrf<button class="btn btn-ghost w-full" type="submit">Prendre en analyse</button></form>
                @endif
                @if ($plainte->accepte('assigner'))
                    <form class="card grid gap-3 p-4" method="post" action="{{ route('superviseur.plaintes.assigner', $plainte) }}">
                        @csrf
                        <p class="text-sm text-muted">Suggestion : {{ $plainte->autoriteSuggeree?->nom ?? 'aucune autorité correspondante' }}</p>
                        <label class="field">Autorité
                            <select name="autorite_id" required>
                                @foreach ($autorites as $autorite)
                                    <option value="{{ $autorite->id }}" @selected($plainte->autorite_suggeree_id === $autorite->id)>{{ $autorite->nom }} — {{ $autorite->zone }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="field">Délai en jours<input type="number" name="delai" min="1" max="60" value="{{ $plainte->priorite->delai() }}" required></label>
                        <label class="field">Commentaire<textarea name="commentaire" rows="2"></textarea></label>
                        <button class="btn btn-primary" type="submit">Assigner</button>
                    </form>
                @endif
                @if ($plainte->accepte('escalader'))
                    <form class="card grid gap-3 p-4" method="post" action="{{ route('superviseur.plaintes.escalader', $plainte) }}">
                        @csrf
                        <label class="field">Motif d’escalade<textarea name="commentaire" rows="2"></textarea></label>
                        <button class="btn btn-danger" type="submit">Escalader</button>
                    </form>
                @endif
                @if ($plainte->accepte('cloturer'))
                    <form class="card grid gap-3 p-4" method="post" action="{{ route('superviseur.plaintes.clore', $plainte) }}">
                        @csrf
                        <label class="field">Validation<textarea name="commentaire" rows="2"></textarea></label>
                        <button class="btn btn-primary" type="submit">Clôturer</button>
                    </form>
                @endif
            @endcan
        </aside>
    </div>
@endsection
