@extends('layouts.public')
@section('titre', 'Activation')
@section('contenu')
    <div class="mx-auto max-w-lg py-8">
        <h1 class="text-4xl">Activer le compte</h1>
        <p class="mt-2 text-muted">Saisissez le code à 6 chiffres envoyé après l’inscription.</p>
        @if ($codeDemo)
            <p class="flash flash-ok mt-4">Code de démonstration : <strong>{{ $codeDemo }}</strong></p>
        @endif
        <form class="card mt-6 grid gap-4 p-6" method="post" action="{{ route('activation') }}">
            @csrf
            <label class="field">E-mail<input type="email" name="email" value="{{ old('email', $email) }}" required></label>
            <label class="field">Code<input name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required></label>
            @error('code')<p class="field-error">{{ $message }}</p>@enderror
            <button class="btn btn-primary" type="submit">Activer</button>
        </form>
    </div>
@endsection
