@php
    $role = auth()->user()->role;
    $liens = match ($role) {
        \App\Enums\Role::Autorite => [
            ['Tableau de bord', route('autorite.dashboard'), 'autorite.dashboard'],
            ['Dossiers', route('autorite.dossiers.index'), 'autorite.dossiers.*'],
        ],
        \App\Enums\Role::Superviseur => [
            ['Tableau de bord', route('superviseur.dashboard'), 'superviseur.dashboard'],
            ['Plaintes', route('superviseur.plaintes.index'), 'superviseur.plaintes.*'],
            ['Autorités', route('superviseur.autorites.index'), 'superviseur.autorites.*'],
        ],
        default => [
            ['Tableau de bord', route('citoyen.dashboard'), 'citoyen.dashboard'],
            ['Mes plaintes', route('citoyen.plaintes.index'), 'citoyen.plaintes.index'],
            ['Déposer', route('citoyen.plaintes.create'), 'citoyen.plaintes.create'],
        ],
    };
@endphp
<nav class="flex flex-col gap-1" aria-label="Espace de travail">
    @foreach ($liens as [$libelle, $url, $motif])
        <a class="nav-link" href="{{ $url }}" @if(request()->routeIs($motif)) aria-current="page" @endif>{{ $libelle }}</a>
    @endforeach
    <a class="nav-link" href="{{ route('profil.edit') }}" @if(request()->routeIs('profil.*')) aria-current="page" @endif>Profil</a>
    <form method="post" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button class="nav-link w-full text-left" type="submit">Déconnexion</button>
    </form>
</nav>
