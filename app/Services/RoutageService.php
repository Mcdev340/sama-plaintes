<?php

namespace App\Services;

use App\Enums\Domaine;
use App\Models\AutoriteLocale;
use Illuminate\Database\Eloquent\Collection;

class RoutageService
{
    /**
     * Autorités dont la zone et les compétences couvrent la plainte.
     *
     * @return Collection<int, AutoriteLocale>
     */
    public function candidats(Domaine $domaine, string $region): Collection
    {
        return AutoriteLocale::query()
            ->where('zone', $region)
            ->whereJsonContains('competences', $domaine->value)
            ->with('user')
            ->orderBy('institution')
            ->get();
    }
}
