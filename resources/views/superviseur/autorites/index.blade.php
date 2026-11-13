@extends('layouts.app')
@section('titre', 'Autorités')
@section('contenu')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-4xl">Autorités locales</h1>
        <a class="btn btn-primary" href="{{ route('superviseur.autorites.create') }}">Nouvelle autorité</a>
    </div>
    <div class="table-wrap card">
        <table class="data">
            <thead><tr><th>Institution</th><th>Zone</th><th>Compétences</th><th>Contact</th><th>Dossiers</th></tr></thead>
            <tbody>
                @forelse ($autorites as $autorite)
                    <tr>
                        <td>{{ $autorite->nom }}<div class="text-xs text-muted">{{ $autorite->user?->nomComplet() }}</div></td>
                        <td>{{ $autorite->zone }}<div class="text-xs text-muted">{{ $autorite->region }}</div></td>
                        <td>{{ str_replace(',', ', ', $autorite->competences) }}</td>
                        <td>{{ $autorite->email_contact }}</td>
                        <td>{{ $autorite->plaintes_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">Aucune autorité.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
