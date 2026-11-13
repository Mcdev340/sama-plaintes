<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['destinataire_id', 'plainte_id', 'message', 'type', 'lu', 'date_envoi'])]
class Alerte extends Model
{
    protected $table = 'notifications';

    protected function casts(): array
    {
        return [
            'lu' => 'boolean',
            'date_envoi' => 'datetime',
        ];
    }

    public function destinataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destinataire_id');
    }

    public function plainte(): BelongsTo
    {
        return $this->belongsTo(Plainte::class);
    }
}
