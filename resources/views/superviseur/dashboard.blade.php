@extends('layouts.app')
@section('titre', 'Supervision')
@section('contenu')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-4xl">Supervision</h1>
            <p class="text-muted">File d’attente, retards et répartition des doléances.</p>
        </div>
        <a class="btn btn-ghost" href="{{ route('superviseur.plaintes.export', request()->query()) }}">Exporter CSV</a>
    </div>
    <section class="grid gap-4 md:grid-cols-4">
        <article class="card stat"><b>{{ $stats['total'] }}</b><span>plaintes</span></article>
        <article class="card stat"><b>{{ $stats['a_assigner'] }}</b><span>à orienter</span></article>
        <article class="card stat"><b>{{ $stats['retard'] }}</b><span>en retard</span></article>
        <article class="card stat"><b>{{ $stats['taux_resolution'] }}%</b><span>résolues ou closes</span></article>
    </section>
    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <section class="card p-5">
            <h2 class="text-2xl">Par domaine</h2>
            <ul class="mt-4 space-y-3">
                @forelse ($stats['par_domaine'] as $ligne)
                    <li>
                        <div class="mb-1 flex justify-between text-sm"><span>{{ $ligne['label'] }}</span><span>{{ $ligne['total'] }}</span></div>
                        <div class="bar"><i style="width: {{ $ligne['largeur'] }}%"></i></div>
                    </li>
                @empty
                    <li class="text-muted">Aucune donnée.</li>
                @endforelse
            </ul>
        </section>
        <section class="card p-5">
            <h2 class="text-2xl">Par région</h2>
            <ul class="mt-4 space-y-3">
                @forelse ($stats['par_region'] as $ligne)
                    <li>
                        <div class="mb-1 flex justify-between text-sm"><span>{{ $ligne['label'] }}</span><span>{{ $ligne['total'] }}</span></div>
                        <div class="bar"><i style="width: {{ $ligne['largeur'] }}%"></i></div>
                    </li>
                @empty
                    <li class="text-muted">Aucune donnée.</li>
                @endforelse
            </ul>
        </section>
    </div>
    <h2 class="mb-3 mt-8 text-2xl">À orienter</h2>
    @include('partials.table', ['plaintes' => $file])
    <h2 class="mb-3 mt-8 text-2xl">Activité récente</h2>
    <ol class="card timeline p-5">
        @forelse ($activite as $etape)
            <li>
                <p class="text-sm text-muted">{{ $etape->date_traitement?->isoFormat('D MMM YYYY, HH:mm') }} · {{ $etape->responsable }}</p>
                <p><a class="font-semibold text-baobab" href="{{ route('plaintes.show', $etape->plainte) }}">{{ $etape->plainte->reference }}</a> — {{ $etape->libelle() }}</p>
                <p class="text-sm">{{ $etape->commentaire }}</p>
            </li>
        @empty
            <li>Aucune action pour le moment.</li>
        @endforelse
    </ol>
@endsection
