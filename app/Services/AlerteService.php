<?php

namespace App\Services;

use App\Models\Alerte;
use App\Models\Plainte;
use App\Models\User;

class AlerteService
{
    public function envoyer(User $destinataire, string $message, string $type, ?Plainte $plainte = null): Alerte
    {
        return Alerte::create([
            'destinataire_id' => $destinataire->id,
            'plainte_id' => $plainte?->id,
            'message' => $message,
            'type' => $type,
            'lu' => false,
            'date_envoi' => now(),
        ]);
    }
}
