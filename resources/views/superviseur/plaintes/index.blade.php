@extends('layouts.app')
@section('titre', 'Toutes les plaintes')
@section('contenu')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-4xl">Plaintes</h1>
        <a class="btn btn-ghost" href="{{ route('superviseur.plaintes.export', request()->query()) }}">Exporter</a>
    </div>
    @include('partials.filtres', ['action' => route('superviseur.plaintes.index')])
    @include('partials.table', ['plaintes' => $plaintes])
@endsection
