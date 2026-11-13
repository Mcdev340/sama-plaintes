<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'plainte_id',
    'superviseur_id',
    'autorite_id',
    'date_assignation',
    'delai_estime',
    'commentaire',
])]
class Assignation extends Model
{
    protected $table = 'assignations';

    protected function casts(): array
    {
        return [
            'date_assignation' => 'datetime',
        ];
    }

    public function plainte(): BelongsTo
    {
        return $this->belongsTo(Plainte::class);
    }

    public function superviseur(): BelongsTo
    {
        return $this->belongsTo(Superviseur::class);
    }

    public function autorite(): BelongsTo
    {
        return $this->belongsTo(AutoriteLocale::class, 'autorite_id');
    }
}
