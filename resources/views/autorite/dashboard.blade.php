@extends('layouts.app')
@section('titre', 'Service')
@section('contenu')
    <h1 class="text-4xl">{{ $autorite->nom }}</h1>
    <p class="text-muted">{{ $autorite->zone }} · {{ $autorite->region }}</p>
    <section class="mt-6 grid gap-4 md:grid-cols-4">
        <article class="card stat"><b>{{ $stats['ouvertes'] }}</b><span>dossiers ouverts</span></article>
        <article class="card stat"><b>{{ $stats['retard'] }}</b><span>en retard</span></article>
        <article class="card stat"><b>{{ $stats['taux_resolution'] }}%</b><span>taux de résolution</span></article>
        <article class="card stat"><b>{{ $stats['delai_moyen_heures'] ?? '—' }}</b><span>heures moyennes jusqu’à clôture</span></article>
    </section>
    <h2 class="mb-3 mt-8 text-2xl">Charge opérationnelle</h2>
    @include('partials.table', ['plaintes' => $dossiers])
@endsection
