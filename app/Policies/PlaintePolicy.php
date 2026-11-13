<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Plainte;
use App\Models\User;

class PlaintePolicy
{
    public function view(User $user, Plainte $plainte): bool
    {
        if (! $user->actif) {
            return false;
        }

        return match ($user->role) {
            Role::Superviseur => $user->superviseur !== null,
            Role::Citoyen => $user->citoyen !== null && $plainte->citoyen_id === $user->citoyen->id,
            Role::Autorite => $user->autorite !== null && $plainte->autorite_id === $user->autorite->id,
        };
    }

    public function traiter(User $user, Plainte $plainte): bool
    {
        return $user->actif
            && $user->role === Role::Autorite
            && $user->autorite !== null
            && $user->autorite->actif
            && $plainte->autorite_id === $user->autorite->id;
    }

    public function assigner(User $user, Plainte $plainte): bool
    {
        return $user->actif
            && $user->role === Role::Superviseur
            && $user->superviseur !== null;
    }

    public function completer(User $user, Plainte $plainte): bool
    {
        return $user->actif
            && $user->role === Role::Citoyen
            && $user->citoyen !== null
            && $plainte->citoyen_id === $user->citoyen->id;
    }

    public function commenter(User $user, Plainte $plainte): bool
    {
        return $this->view($user, $plainte);
    }
}
