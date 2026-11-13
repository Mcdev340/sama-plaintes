@extends('layouts.public')
@section('titre', 'Contact — Sama Plaintes')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
@endpush

@section('contenu')
    <section aria-labelledby="contact-titre">
        <h1 id="contact-titre" class="max-w-3xl text-5xl leading-tight text-baobab-dark">Nous sommes à votre écoute.</h1>
        <p class="mt-5 max-w-2xl text-lg leading-relaxed text-muted">Une question, une suggestion ou besoin d’assistance ? Notre équipe est là pour vous accompagner dans l’utilisation de Sama Plaintes.</p>
    </section>

    <section class="mt-12" aria-labelledby="formulaire">
        <h2 id="formulaire" class="text-3xl">Envoyez-nous un message</h2>
        <p class="mt-2 text-sm text-muted">Tous les champs marqués d’un astérisque sont obligatoires.</p>
        <form id="formulaire-contact" class="card mt-5 grid gap-4 p-6" method="post" action="{{ route('contact.envoyer') }}" data-contact-form>
            @csrf
            <label class="field">Nom complet *
                <input name="nom" value="{{ old('nom') }}" required maxlength="120" autocomplete="name">
                @error('nom')<span class="field-error">{{ $message }}</span>@enderror
            </label>
            <label class="field">Adresse e-mail *
                <input name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email">
                @error('email')<span class="field-error">{{ $message }}</span>@enderror
            </label>
            <label class="field">Objet du message *
                <select name="sujet" required>
                    @foreach ($sujets as $sujet)
                        <option value="{{ $sujet }}" @selected(old('sujet', 'Demande d\'assistance') === $sujet)>{{ $sujet }}</option>
                    @endforeach
                </select>
                @error('sujet')<span class="field-error">{{ $message }}</span>@enderror
            </label>
            <label class="field">Votre message *
                <textarea name="message" rows="6" required minlength="20" maxlength="2000">{{ old('message') }}</textarea>
                @error('message')<span class="field-error">{{ $message }}</span>@enderror
            </label>
            <fieldset class="grid gap-3">
                <legend class="text-sm font-semibold">Localisation (facultative)</legend>
                <p class="text-sm text-muted">Vous pouvez partager votre position pour aider notre équipe à mieux comprendre votre demande.</p>
                <label class="flex items-start gap-2 text-sm">
                    <input class="mt-1" type="checkbox" name="joindre_localisation" value="1" data-joindre @checked(old('joindre_localisation'))>
                    Je souhaite joindre ma localisation.
                </label>
                <input type="hidden" name="latitude" value="{{ old('latitude') }}" data-latitude @disabled(! old('joindre_localisation'))>
                <input type="hidden" name="longitude" value="{{ old('longitude') }}" data-longitude @disabled(! old('joindre_localisation'))>
                @error('latitude')<span class="field-error">{{ $message }}</span>@enderror
                @error('longitude')<span class="field-error">{{ $message }}</span>@enderror
            </fieldset>
            <div>
                <button class="btn btn-primary" type="submit">Envoyer mon message</button>
            </div>
        </form>
    </section>

    <section class="mt-12" aria-labelledby="carte-titre">
        <h2 id="carte-titre" class="text-3xl">Localisation sur une carte</h2>
        <p class="mt-2 max-w-2xl text-sm text-muted">La position n’est demandée que si vous le choisissez. Vous pouvez aussi déplacer le marqueur pour indiquer un lieu.</p>
        <div class="card mt-5 overflow-hidden p-4">
            <div id="carte-contact" class="carte-contact"></div>
            <div class="mt-4 flex flex-wrap items-end gap-3">
                <button class="btn btn-ghost" type="button" data-gps>Utiliser ma position</button>
                <p class="text-sm text-muted" data-coords>Aucune position sélectionnée.</p>
                <p class="field-error" data-gps-erreur hidden>La position n’a pas été partagée. Placez le marqueur sur la carte.</p>
            </div>
            <label class="field mt-4">Adresse ou quartier
                <input name="lieu" form="formulaire-contact" value="{{ old('lieu') }}" maxlength="160" placeholder="Quartier, rue ou repère" data-lieu>
            </label>
        </div>
    </section>

    <section class="mt-12" aria-labelledby="infos">
        <h2 id="infos" class="text-3xl">Informations de contact</h2>
        <dl class="mt-5 grid gap-4 md:grid-cols-3">
            <div class="card p-5">
                <dt class="font-semibold">Adresse e-mail</dt>
                <dd class="mt-2 text-sm text-muted">Adresse officielle à définir</dd>
            </div>
            <div class="card p-5">
                <dt class="font-semibold">Téléphone</dt>
                <dd class="mt-2 text-sm text-muted">Numéro de support à définir</dd>
            </div>
            <div class="card p-5">
                <dt class="font-semibold">Assistance</dt>
                <dd class="mt-2 text-sm text-muted">Horaires de disponibilité à définir</dd>
            </div>
        </dl>
    </section>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const carte = document.getElementById('carte-contact');
            if (!carte || typeof L === 'undefined') {
                return;
            }

            const formulaire = document.querySelector('[data-contact-form]');
            const joindre = formulaire.querySelector('[data-joindre]');
            const latitude = formulaire.querySelector('[data-latitude]');
            const longitude = formulaire.querySelector('[data-longitude]');
            const coords = document.querySelector('[data-coords]');
            const erreur = document.querySelector('[data-gps-erreur]');
            const depart = [14.72, -17.18];
            const carteOsm = L.map(carte, { scrollWheelZoom: false }).setView(depart, 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap',
            }).addTo(carteOsm);

            const marqueur = L.marker(depart, { draggable: true }).addTo(carteOsm);
            let positionChoisie = latitude.value !== '' && longitude.value !== '';

            const ecrire = (lat, lng) => {
                latitude.value = Number(lat).toFixed(6);
                longitude.value = Number(lng).toFixed(6);
                coords.textContent = latitude.value + ', ' + longitude.value;
                positionChoisie = true;
                erreur.hidden = true;
            };

            const activerChamps = () => {
                const actif = joindre.checked && positionChoisie;
                latitude.disabled = !actif;
                longitude.disabled = !actif;
                if (joindre.checked && !positionChoisie) {
                    latitude.disabled = true;
                    longitude.disabled = true;
                }
            };

            if (positionChoisie) {
                marqueur.setLatLng([Number(latitude.value), Number(longitude.value)]);
                carteOsm.setView(marqueur.getLatLng(), 15);
                coords.textContent = latitude.value + ', ' + longitude.value;
            }

            marqueur.on('dragend', () => {
                const point = marqueur.getLatLng();
                ecrire(point.lat, point.lng);
                activerChamps();
            });
            carteOsm.on('click', (evenement) => {
                marqueur.setLatLng(evenement.latlng);
                ecrire(evenement.latlng.lat, evenement.latlng.lng);
                activerChamps();
            });
            joindre.addEventListener('change', activerChamps);

            document.querySelector('[data-gps]').addEventListener('click', () => {
                erreur.hidden = true;
                if (!navigator.geolocation) {
                    erreur.hidden = false;
                    return;
                }
                navigator.geolocation.getCurrentPosition((position) => {
                    const point = [position.coords.latitude, position.coords.longitude];
                    marqueur.setLatLng(point);
                    carteOsm.setView(point, 16);
                    ecrire(point[0], point[1]);
                    joindre.checked = true;
                    activerChamps();
                }, () => {
                    erreur.hidden = false;
                });
            });

            formulaire.addEventListener('submit', (evenement) => {
                if (joindre.checked && !positionChoisie) {
                    evenement.preventDefault();
                    erreur.hidden = false;
                }
            });

            activerChamps();
        });
    </script>
@endpush
