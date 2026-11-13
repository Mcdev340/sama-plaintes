@extends('layouts.app')
@section('titre', 'Espace citoyen')
@section('contenu')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-4xl">Bonjour {{ auth()->user()->prenom }}</h1>
            <p class="text-muted">Suivez vos doléances et leur traitement.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('citoyen.plaintes.create') }}">Nouvelle plainte</a>
    </div>
    <section class="grid gap-4 md:grid-cols-4">
        @foreach ($cartes as $carte)
            <article class="card stat"><b>{{ $carte['valeur'] }}</b><span>{{ $carte['label'] }}</span></article>
        @endforeach
    </section>
    <h2 class="mb-3 mt-8 text-2xl">Dernières plaintes</h2>
    @include('partials.table', ['plaintes' => $recentes])
@endsection
