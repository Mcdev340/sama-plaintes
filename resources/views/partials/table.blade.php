<div class="table-wrap card">
    <table class="data">
        <thead>
            <tr>
                <th scope="col">Référence</th>
                <th scope="col">Objet</th>
                <th scope="col">Lieu</th>
                <th scope="col">Priorité</th>
                <th scope="col">Statut</th>
                <th scope="col">Échéance</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($plaintes as $plainte)
                <tr>
                    <td><a class="font-semibold text-baobab" href="{{ route('plaintes.show', $plainte) }}">{{ $plainte->reference }}</a></td>
                    <td>{{ $plainte->titre }}<div class="text-xs text-muted">{{ $plainte->domaine->label() }}</div></td>
                    <td>{{ $plainte->commune }}<div class="text-xs text-muted">{{ $plainte->region }}</div></td>
                    <td><span class="badge badge-{{ $plainte->priorite->value }}">{{ $plainte->priorite->label() }}</span></td>
                    <td>
                        <span class="badge badge-{{ $plainte->statut->value }}">{{ $plainte->statut->label() }}</span>
                        @if ($plainte->estEnRetard())
                            <span class="badge badge-retard">Retard</span>
                        @endif
                    </td>
                    <td>{{ $plainte->date_echeance?->format('d/m/Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-muted">Aucune plainte pour ces critères.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if (method_exists($plaintes, 'links'))
    <div class="mt-4">{{ $plaintes->links() }}</div>
@endif
