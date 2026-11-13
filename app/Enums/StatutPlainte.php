<?php

namespace App\Enums;

enum StatutPlainte: string
{
    case Soumise = 'soumise';
    case EnAnalyse = 'en_analyse';
    case Assignee = 'assignee';
    case EnTraitement = 'en_traitement';
    case EnAttente = 'en_attente';
    case Resolue = 'resolue';
    case Rejetee = 'rejetee';
    case Fermee = 'fermee';
    case Escaladee = 'escaladee';

    public function label(): string
    {
        return match ($this) {
            self::Soumise => 'Soumise',
            self::EnAnalyse => 'En analyse',
            self::Assignee => 'Assignée',
            self::EnTraitement => 'En traitement',
            self::EnAttente => 'En attente',
            self::Resolue => 'Résolue',
            self::Rejetee => 'Rejetée',
            self::Fermee => 'Fermée',
            self::Escaladee => 'Escaladée',
        };
    }

    public function estOuvert(): bool
    {
        return ! in_array($this, [self::Resolue, self::Rejetee, self::Fermee], true);
    }

    /**
     * @return list<string>
     */
    public static function clos(): array
    {
        return [self::Resolue->value, self::Rejetee->value, self::Fermee->value];
    }
}
