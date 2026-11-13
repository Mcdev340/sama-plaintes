<?php

namespace App\Enums;

enum Domaine: string
{
    case Education = 'education';
    case Sante = 'sante';
    case Infrastructures = 'infrastructures';
    case Securite = 'securite';
    case Administratif = 'administratif';
    case Eau = 'eau';
    case Electricite = 'electricite';
    case Assainissement = 'assainissement';
    case Voirie = 'voirie';

    public function label(): string
    {
        return match ($this) {
            self::Education => 'Éducation',
            self::Sante => 'Santé',
            self::Infrastructures => 'Infrastructures',
            self::Securite => 'Sécurité',
            self::Administratif => 'Services administratifs',
            self::Eau => 'Eau',
            self::Electricite => 'Électricité',
            self::Assainissement => 'Assainissement',
            self::Voirie => 'Voirie',
        };
    }
}
