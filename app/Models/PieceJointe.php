<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['plainte_id', 'nom_fichier', 'chemin', 'type_fichier', 'taille', 'date_upload'])]
class PieceJointe extends Model
{
    protected $table = 'documents';

    protected function casts(): array
    {
        return [
            'date_upload' => 'datetime',
            'taille' => 'integer',
        ];
    }

    public function plainte(): BelongsTo
    {
        return $this->belongsTo(Plainte::class);
    }

    public function tailleLisible(): string
    {
        $octets = $this->taille;
        if ($octets < 1024) {
            return $octets.' o';
        }

        return number_format($octets / 1024, 0, ',', ' ').' Ko';
    }
}
