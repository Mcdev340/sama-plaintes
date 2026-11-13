<?php

namespace App\Models;

use App\Enums\Domaine;
use App\Enums\Priorite;
use App\Enums\StatutPlainte;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'reference',
    'titre',
    'description',
    'domaine',
    'priorite',
    'statut',
    'localisation',
    'region',
    'commune',
    'latitude',
    'longitude',
    'citoyen_id',
    'autorite_id',
    'autorite_suggeree_id',
    'date_soumission',
    'date_echeance',
    'date_cloture',
    'motif_rejet',
    'niveau_escalade',
    'note_satisfaction',
    'commentaire_satisfaction',
])]
class Plainte extends Model
{
    protected $table = 'plaintes';

    protected function casts(): array
    {
        return [
            'domaine' => Domaine::class,
            'priorite' => Priorite::class,
            'statut' => StatutPlainte::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'date_soumission' => 'datetime',
            'date_echeance' => 'datetime',
            'date_cloture' => 'datetime',
            'niveau_escalade' => 'integer',
            'note_satisfaction' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function citoyen(): BelongsTo
    {
        return $this->belongsTo(Citoyen::class);
    }

    public function autorite(): BelongsTo
    {
        return $this->belongsTo(AutoriteLocale::class, 'autorite_id');
    }

    public function autoriteSuggeree(): BelongsTo
    {
        return $this->belongsTo(AutoriteLocale::class, 'autorite_suggeree_id');
    }

    public function pieces(): HasMany
    {
        return $this->hasMany(PieceJointe::class);
    }

    public function assignations(): HasMany
    {
        return $this->hasMany(Assignation::class);
    }

    public function traitements(): HasMany
    {
        return $this->hasMany(Traitement::class)->orderBy('date_traitement');
    }

    public function alertes(): HasMany
    {
        return $this->hasMany(Alerte::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function scopeEnRetard(Builder $query): Builder
    {
        return $query
            ->whereNotNull('date_echeance')
            ->where('date_echeance', '<', now())
            ->whereNotIn('statut', StatutPlainte::clos());
    }

    public function estEnRetard(): bool
    {
        return $this->date_echeance !== null
            && $this->date_echeance->isPast()
            && $this->statut->estOuvert();
    }

    public function accepte(string $action): bool
    {
        return match ($action) {
            'analyser' => $this->statut === StatutPlainte::Soumise,
            'assigner' => in_array($this->statut, [
                StatutPlainte::Soumise,
                StatutPlainte::EnAnalyse,
                StatutPlainte::Escaladee,
                StatutPlainte::Assignee,
                StatutPlainte::EnTraitement,
                StatutPlainte::EnAttente,
            ], true),
            'prendre' => in_array($this->statut, [StatutPlainte::Assignee, StatutPlainte::Escaladee], true),
            'complement_autorite' => in_array($this->statut, [
                StatutPlainte::Assignee,
                StatutPlainte::EnTraitement,
                StatutPlainte::Escaladee,
            ], true),
            'resoudre', 'rejeter' => in_array($this->statut, [
                StatutPlainte::Assignee,
                StatutPlainte::EnTraitement,
                StatutPlainte::EnAttente,
                StatutPlainte::Escaladee,
            ], true),
            'completer' => $this->statut === StatutPlainte::EnAttente,
            'escalader' => $this->statut->estOuvert() && $this->niveau_escalade < 3,
            'cloturer' => in_array($this->statut, [StatutPlainte::Resolue, StatutPlainte::Rejetee], true),
            'evaluer' => in_array($this->statut, [StatutPlainte::Resolue, StatutPlainte::Fermee], true)
                && $this->note_satisfaction === null,
            'ecrire' => $this->statut->estOuvert(),
            default => false,
        };
    }
}
