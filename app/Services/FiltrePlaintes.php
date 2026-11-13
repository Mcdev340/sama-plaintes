<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FiltrePlaintes
{
    public function appliquer(Builder $requete, Request $request): Builder
    {
        $comme = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        return $requete
            ->when($request->filled('statut'), fn (Builder $q) => $q->where('statut', $request->string('statut')->value()))
            ->when($request->filled('domaine'), fn (Builder $q) => $q->where('domaine', $request->string('domaine')->value()))
            ->when($request->filled('priorite'), fn (Builder $q) => $q->where('priorite', $request->string('priorite')->value()))
            ->when($request->filled('region'), fn (Builder $q) => $q->where('region', $request->string('region')->value()))
            ->when($request->boolean('retard'), fn (Builder $q) => $q->enRetard())
            ->when($request->filled('q'), function (Builder $q) use ($request, $comme) {
                $brut = str_replace(['%', '_'], '', $request->string('q')->value());
                $terme = '%'.$brut.'%';
                $q->where(function (Builder $q) use ($terme, $comme) {
                    $q->where('titre', $comme, $terme)
                        ->orWhere('reference', $comme, $terme)
                        ->orWhere('commune', $comme, $terme)
                        ->orWhere('localisation', $comme, $terme);
                });
            });
    }
}
