<form method="get" action="{{ $action }}" class="card mb-4 grid gap-3 p-4 md:grid-cols-5">
    <label class="field md:col-span-2">Recherche
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Référence, titre, lieu">
    </label>
    <label class="field">Statut
        <select name="statut">
            <option value="">Tous</option>
            @foreach (\App\Enums\StatutPlainte::cases() as $statut)
                <option value="{{ $statut->value }}" @selected(request('statut') === $statut->value)>{{ $statut->label() }}</option>
            @endforeach
        </select>
    </label>
    <label class="field">Domaine
        <select name="domaine">
            <option value="">Tous</option>
            @foreach (\App\Enums\Domaine::cases() as $domaine)
                <option value="{{ $domaine->value }}" @selected(request('domaine') === $domaine->value)>{{ $domaine->label() }}</option>
            @endforeach
        </select>
    </label>
    <div class="flex items-end gap-2">
        <button class="btn btn-primary" type="submit">Filtrer</button>
        <a class="btn btn-ghost" href="{{ $action }}">Effacer</a>
    </div>
</form>
