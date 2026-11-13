<?php

namespace App\Enums;

enum Role: string
{
    case Citoyen = 'citoyen';
    case Autorite = 'autorite';
    case Superviseur = 'superviseur';

    public function label(): string
    {
        return match ($this) {
            self::Citoyen => 'Citoyen',
            self::Autorite => 'Autorité locale',
            self::Superviseur => 'Superviseur',
        };
    }
}
