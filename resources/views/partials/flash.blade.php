@if (session('succes'))
    <p class="flash flash-ok" role="status">{{ session('succes') }}</p>
@endif
@if (session('erreur'))
    <p class="flash flash-ko" role="alert">{{ session('erreur') }}</p>
@endif
@if (session('lien_reinitialisation'))
    <p class="flash flash-ok">Lien local de réinitialisation : <a class="underline" href="{{ session('lien_reinitialisation') }}">choisir un nouveau mot de passe</a></p>
@endif
@if ($errors->any() && ! $errors->has('email') && ! $errors->has('code'))
    <div class="flash flash-ko" role="alert">
        <p>Vérifiez les informations saisies.</p>
    </div>
@endif
