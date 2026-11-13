<?php

namespace App\Services;

use App\Enums\StatutPlainte;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StatistiquesService
{
    /**
     * @return array<string, mixed>
     */
    public function synthese(Builder $requete): array
    {
        $total = (clone $requete)->count();
        $resolues = (clone $requete)->whereIn('statut', [
            StatutPlainte::Resolue->value,
            StatutPlainte::Fermee->value,
        ])->count();
        $rejetees = (clone $requete)->where('statut', StatutPlainte::Rejetee->value)->count();
        $retard = (clone $requete)->enRetard()->count();
        $ouvertes = (clone $requete)->whereNotIn('statut', StatutPlainte::clos())->count();
        $aAssigner = (clone $requete)->whereNull('autorite_id')->whereNotIn('statut', StatutPlainte::clos())->count();

        $durees = (clone $requete)->whereNotNull('date_cloture')->get(['date_soumission', 'date_cloture']);
        $heures = $durees->avg(fn ($plainte) => abs($plainte->date_soumission->diffInHours($plainte->date_cloture)));

        return [
            'total' => $total,
            'ouvertes' => $ouvertes,
            'resolues' => $resolues,
            'rejetees' => $rejetees,
            'retard' => $retard,
            'a_assigner' => $aAssigner,
            'taux_resolution' => $total > 0 ? (int) round(($resolues / $total) * 100) : 0,
            'delai_moyen_heures' => $heures !== null ? (int) round($heures) : null,
            'par_statut' => $this->barres(
                (clone $requete)->selectRaw('statut, count(*) as total')->groupBy('statut')->pluck('total', 'statut'),
                StatutPlainte::class,
            ),
            'par_domaine' => $this->barres(
                (clone $requete)->selectRaw('domaine, count(*) as total')->groupBy('domaine')->pluck('total', 'domaine'),
                \App\Enums\Domaine::class,
            ),
            'par_region' => $this->barresLibres(
                (clone $requete)->selectRaw('region, count(*) as total')->groupBy('region')->pluck('total', 'region'),
            ),
        ];
    }

    /**
     * @param  class-string  $enumClass
     * @return list<array{label: string, total: int, largeur: int}>
     */
    public function barres(Collection $compteur, string $enumClass): array
    {
        $max = max(1, (int) $compteur->max());

        return $compteur->map(function ($total, $cle) use ($enumClass, $max) {
            $cas = $enumClass::tryFrom((string) $cle);

            return [
                'label' => $cas?->label() ?? (string) $cle,
                'total' => (int) $total,
                'largeur' => (int) round(((int) $total / $max) * 100),
            ];
        })->values()->all();
    }

    /**
     * @return list<array{label: string, total: int, largeur: int}>
     */
    public function barresLibres(Collection $compteur): array
    {
        $max = max(1, (int) $compteur->max());

        return $compteur->map(fn ($total, $cle) => [
            'label' => (string) $cle,
            'total' => (int) $total,
            'largeur' => (int) round(((int) $total / $max) * 100),
        ])->values()->all();
    }
}
