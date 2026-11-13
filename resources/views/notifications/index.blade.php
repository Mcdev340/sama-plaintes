@extends('layouts.app')
@section('titre', 'Notifications')
@section('contenu')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-4xl">Notifications</h1>
        <form method="post" action="{{ route('notifications.read-all') }}">@csrf<button class="btn btn-ghost" type="submit">Tout marquer lu</button></form>
    </div>
    <ul class="space-y-3">
        @forelse ($alertes as $alerte)
            <li class="card p-4 {{ $alerte->lu ? 'opacity-70' : '' }}">
                <p>{{ $alerte->message }}</p>
                <p class="mt-1 text-sm text-muted">{{ $alerte->date_envoi?->isoFormat('D MMM YYYY, HH:mm') }}</p>
                @unless ($alerte->lu)
                    <form class="mt-2" method="post" action="{{ route('notifications.read', $alerte) }}">
                        @csrf
                        <button class="btn btn-small btn-primary" type="submit">{{ $alerte->plainte ? 'Ouvrir le dossier' : 'Marquer lu' }}</button>
                    </form>
                @endunless
            </li>
        @empty
            <li class="text-muted">Aucune notification.</li>
        @endforelse
    </ul>
    <div class="mt-4">{{ $alertes->links() }}</div>
@endsection
