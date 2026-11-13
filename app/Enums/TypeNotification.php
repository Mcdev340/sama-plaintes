<?php

namespace App\Enums;

enum TypeNotification: string
{
    case Info = 'info';
    case Alerte = 'alerte';
    case Rappel = 'rappel';
    case Confirmation = 'confirmation';

    public function label(): string
    {
        return match ($this) {
            self::Info => __('Information'),
            self::Alerte => __('Alerte'),
            self::Rappel => __('Rappel'),
            self::Confirmation => __('Confirmation'),
        };
    }
}
