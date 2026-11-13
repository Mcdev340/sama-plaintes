@extends('layouts.public')
@section('titre', 'Mot de passe oublié')
@section('contenu')
    <div class="mx-auto max-w-lg py-8">
        <h1 class="text-4xl">Mot de passe oublié</h1>
        <form class="card mt-6 grid gap-4 p-6" method="post" action="{{ route('password.email') }}">
            @csrf
            <label class="field">E-mail<input type="email" name="email" value="{{ old('email') }}" required></label>
            <button class="btn btn-primary" type="submit">Recevoir le lien</button>
        </form>
    </div>
@endsection
