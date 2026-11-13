@extends('layouts.public')
@section('titre', 'Page introuvable')
@section('contenu')
    <div class="py-16">
        <h1 class="text-4xl">Page introuvable</h1>
        <p class="mt-3 text-muted">Ce lien ne correspond à aucune page de Sama Plaintes.</p>
        <a class="btn btn-primary mt-6" href="{{ route('accueil') }}">Retour à l’accueil</a>
    </div>
@endsection
