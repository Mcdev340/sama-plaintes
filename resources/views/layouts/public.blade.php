<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre', 'Sama Plaintes')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,650|source-sans-3:400,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="skip" href="#contenu">Aller au contenu</a>
    <header class="site-header">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-6 gap-y-3 px-5 py-3">
            <a href="{{ route('accueil') }}" class="brand">
                <span class="brand-mark" aria-hidden="true">SP</span>
                <span>
                    <span class="display block text-xl leading-none text-baobab-dark">Sama Plaintes</span>
                    <span class="text-xs text-muted">Doléances citoyennes</span>
                </span>
            </a>
            <nav class="pub-nav" aria-label="Navigation publique">
                @foreach ([
                    ['Accueil', route('accueil'), 'accueil'],
                    ['Fonctionnalités', route('fonctionnalites'), 'fonctionnalites'],
                    ['À propos', route('a-propos'), 'a-propos'],
                    ['Contact', route('contact'), 'contact'],
                ] as [$libelle, $url, $nom])
                    <a href="{{ $url }}" @if(request()->routeIs($nom)) aria-current="page" @endif>{{ $libelle }}</a>
                @endforeach
            </nav>
            <div class="ml-auto flex items-center gap-2">
                @auth
                    <a class="btn btn-primary" href="{{ route('dashboard') }}">Mon espace</a>
                @else
                    <a class="btn btn-ghost" href="{{ route('login') }}">Connexion</a>
                    <a class="btn btn-primary" href="{{ route('register') }}">Déposer une plainte</a>
                @endauth
            </div>
        </div>
    </header>
    <main id="contenu" class="mx-auto max-w-6xl px-5 py-10">
        @include('partials.flash')
        @yield('contenu')
    </main>
    <footer class="site-footer">
        <div class="mx-auto grid max-w-6xl gap-8 px-5 py-10 md:grid-cols-3">
            <div>
                <p class="display text-2xl text-white">Sama Plaintes</p>
                <p class="mt-2 max-w-xs text-sm leading-relaxed text-[#efe7d6]">Plateforme de suivi des plaintes citoyennes, de l’ISEP Amadou Trawaré de Diamniadio.</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-gold-soft">Pages</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ route('fonctionnalites') }}">Fonctionnalités</a></li>
                    <li><a href="{{ route('a-propos') }}">À propos</a></li>
                    <li><a href="{{ route('contact') }}">Contact</a></li>
                </ul>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-gold-soft">Écrire</p>
                <p class="mt-3 text-sm">contact@samaplaintes.sn</p>
                <p class="text-sm">Diamniadio, Sénégal</p>
            </div>
        </div>
        <p class="border-t border-white/10 px-5 py-4 text-center text-xs text-[#d9cbb4]">Projet de fin de formation 2024-2025 · Issa Bathily, Sékou Diedhiou, Mamadou Aliou Diallo, Moussa Kathieuness Correa</p>
    </footer>
</body>
</html>
