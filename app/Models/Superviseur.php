<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'matricule', 'departement', 'niveau_acces'])]
class Superviseur extends Model
{
    protected $table = 'superviseurs';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignations(): HasMany
    {
        return $this->hasMany(Assignation::class);
    }
}
