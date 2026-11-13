<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'plainte_id',
    'autorite_id',
    'user_id',
    'responsable',
    'action',
    'commentaire',
    'date_traitement',
])]
class Traitement extends Model
{
    protected $table = 'traitements';

    protected function casts(): array
    {
        return [
            'date_traitement' => 'datetime',
        ];
    }

    public function plainte(): BelongsTo
    {
        return $this->belongsTo(Plainte::class);
    }

    public function autorite(): BelongsTo
    {
        return $this->belongsTo(AutoriteLocale::class, 'autorite_id');
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function libelle(): string
    {
        return match ($this->action) {
            'depot' => 'Dépôt',
            'analyse' => 'Analyse',
            'assignation' => 'Assignation',
            'prise_en_charge' => 'Prise en charge',
            'demande_complement' => 'Demande de complément',
            'complement' => 'Complément du citoyen',
            'resolution' => 'Résolution',
            'rejet' => 'Rejet',
            'escalade' => 'Escalade',
            'cloture' => 'Clôture',
            'evaluation' => 'Évaluation',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }
}
