<?php

namespace App\Services;

use App\Enums\TypeNotification;
use App\Models\Alerte;
use App\Models\Plainte;
use App\Models\Superviseur;
use App\Models\User;

class NotificationService
{
    public function envoyer(User $destinataire, string $message, TypeNotification $type, ?Plainte $plainte = null): Alerte
    {
        return $destinataire->alertes()->create([
            'plainte_id' => $plainte?->id,
            'type' => $type,
            'message' => $message,
            'lu' => false,
            'date_envoi' => now(),
        ]);
    }

    public function auxSuperviseurs(Plainte $plainte, string $message, TypeNotification $type): void
    {
        Superviseur::query()
            ->where(function ($query) use ($plainte): void {
                $query->whereNull('perimetre')->orWhere('perimetre', $plainte->region);
            })
            ->with('user')
            ->get()
            ->each(function (Superviseur $superviseur) use ($message, $type, $plainte): void {
                if ($superviseur->user) {
                    $this->envoyer($superviseur->user, $message, $type, $plainte);
                }
            });
    }
}
