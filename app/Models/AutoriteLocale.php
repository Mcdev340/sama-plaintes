<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'nom',
    'zone',
    'region',
    'competences',
    'telephone_contact',
    'email_contact',
    'actif',
])]
class AutoriteLocale extends Model
{
    protected $table = 'autorites_locales';

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plaintes(): HasMany
    {
        return $this->hasMany(Plainte::class, 'autorite_id');
    }

    /**
     * @return list<string>
     */
    public function domaines(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', mb_strtolower($this->competences)))));
    }
}
