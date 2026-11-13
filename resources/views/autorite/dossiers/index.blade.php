@extends('layouts.app')
@section('titre', 'Dossiers')
@section('contenu')
    <h1 class="text-4xl">Dossiers de {{ $autorite->nom }}</h1>
    <div class="mt-4">
        @include('partials.filtres', ['action' => route('autorite.dossiers.index')])
        @include('partials.table', ['plaintes' => $plaintes])
    </div>
@endsection
