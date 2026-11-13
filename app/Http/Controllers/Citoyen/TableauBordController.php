<?php

namespace App\Http\Controllers\Citoyen;

use App\Http\Controllers\Controller;
use App\Models\Plainte;
use App\Services\StatistiquesService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TableauBordController extends Controller
{
    public function index(Request $request, StatistiquesService $statistiques): View
    {
        $citoyen = $request->user()->citoyen;
        abort_unless($citoyen, 403);

        $base = Plainte::query()->where('citoyen_id', $citoyen->id);
        $stats = $statistiques->synthese($base);
        $recentes = (clone $base)->with('autorite')->latest('date_soumission')->limit(6)->get();

        return view('citoyen.dashboard', [
            'stats' => $stats,
            'recentes' => $recentes,
            'cartes' => [
                ['label' => 'Plaintes déposées', 'valeur' => $stats['total']],
                ['label' => 'En cours', 'valeur' => $stats['ouvertes']],
                ['label' => 'Résolues', 'valeur' => $stats['resolues']],
                ['label' => 'En retard', 'valeur' => $stats['retard']],
            ],
        ]);
    }
}
