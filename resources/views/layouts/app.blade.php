<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre', 'Espace') — Sama Plaintes</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,650|source-sans-3:400,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="skip" href="#contenu">Aller au contenu</a>
    <div class="min-h-screen lg:grid lg:grid-cols-[17rem_minmax(0,1fr)]">
        <aside class="bg-baobab-dark text-white lg:min-h-screen">
            <div class="flex items-center justify-between px-5 py-5">
                <a href="{{ route('dashboard') }}" class="display text-2xl">Sama Plaintes</a>
            </div>
            <div class="px-3 pb-6">
                @include('partials.nav')
            </div>
        </aside>
        <div>
            <header class="flex items-center justify-between gap-4 border-b border-line px-5 py-4 lg:px-8">
                <p class="text-sm text-muted">{{ auth()->user()->role->label() }}</p>
                <div class="flex items-center gap-3">
                    <a class="btn btn-ghost btn-small" href="{{ route('notifications.index') }}">
                        Notifications
                        @if(($nonLues ?? 0) > 0)
                            <span class="badge badge-escaladee">{{ $nonLues }}</span>
                        @endif
                    </a>
                    <span class="hidden text-sm font-semibold sm:inline">{{ auth()->user()->nomComplet() }}</span>
                </div>
            </header>
            <main id="contenu" class="px-5 py-6 lg:px-8">
                @include('partials.flash')
                @yield('contenu')
            </main>
        </div>
    </div>
</body>
</html>
