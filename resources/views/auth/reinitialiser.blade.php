@extends('layouts.public')
@section('titre', 'Nouveau mot de passe')
@section('contenu')
    <div class="mx-auto max-w-lg py-8">
        <h1 class="text-4xl">Nouveau mot de passe</h1>
        <form class="card mt-6 grid gap-4 p-6" method="post" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <label class="field">E-mail<input type="email" name="email" value="{{ old('email', $email) }}" required></label>
            <label class="field">Nouveau mot de passe<input type="password" name="password" required></label>
            <label class="field">Confirmation<input type="password" name="password_confirmation" required></label>
            <button class="btn btn-primary" type="submit">Enregistrer</button>
        </form>
    </div>
@endsection
