@extends('layouts.public')
@section('titre', 'Accès refusé')
@section('contenu')
    <div class="py-16">
        <h1 class="text-4xl">Accès refusé</h1>
        <p class="mt-3 text-muted">{{ $exception->getMessage() ?: 'Cet espace ne correspond pas à votre rôle.' }}</p>
        <a class="btn btn-primary mt-6" href="{{ route('accueil') }}">Retour à l’accueil</a>
    </div>
@endsection
