<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'nom',
    'prenom',
    'telephone',
    'adresse',
    'date_naissance',
    'numero_identite',
    'role',
    'langue',
    'actif',
    'code_activation',
    'code_expire_at',
])]
#[Hidden(['password', 'remember_token', 'code_activation'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_naissance' => 'date',
            'code_expire_at' => 'datetime',
            'role' => Role::class,
            'actif' => 'boolean',
        ];
    }

    public function citoyen(): HasOne
    {
        return $this->hasOne(Citoyen::class);
    }

    public function autorite(): HasOne
    {
        return $this->hasOne(AutoriteLocale::class);
    }

    public function superviseur(): HasOne
    {
        return $this->hasOne(Superviseur::class);
    }

    public function alertes(): HasMany
    {
        return $this->hasMany(Alerte::class, 'destinataire_id');
    }

    public function nomComplet(): string
    {
        $complet = trim($this->prenom.' '.$this->nom);

        return $complet !== '' ? $complet : (string) $this->name;
    }

    public function estCitoyen(): bool
    {
        return $this->role === Role::Citoyen;
    }

    public function estAutorite(): bool
    {
        return $this->role === Role::Autorite;
    }

    public function estSuperviseur(): bool
    {
        return $this->role === Role::Superviseur;
    }
}
