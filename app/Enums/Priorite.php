<?php

namespace App\Enums;

enum Priorite: string
{
    case Basse = 'basse';
    case Moyenne = 'moyenne';
    case Haute = 'haute';

    public function label(): string
    {
        return match ($this) {
            self::Basse => 'Basse',
            self::Moyenne => 'Moyenne',
            self::Haute => 'Haute',
        };
    }

    public function delai(): int
    {
        return match ($this) {
            self::Haute => 3,
            self::Moyenne => 7,
            self::Basse => 15,
        };
    }
}
