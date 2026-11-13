@extends('layouts.app')
@section('titre', 'Mes plaintes')
@section('contenu')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-4xl">Mes plaintes</h1>
        <a class="btn btn-primary" href="{{ route('citoyen.plaintes.create') }}">Déposer</a>
    </div>
    @include('partials.filtres', ['action' => route('citoyen.plaintes.index')])
    @include('partials.table', ['plaintes' => $plaintes])
@endsection
