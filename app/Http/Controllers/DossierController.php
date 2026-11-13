<?php

namespace App\Http\Controllers;

use App\Models\Plainte;
use App\Services\RoutageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DossierController extends Controller
{
    public function show(Request $request, Plainte $plainte, RoutageService $routage): View
    {
        $this->authorize('view', $plainte);

        $plainte->load([
            'citoyen',
            'autorite.user',
            'documents.auteur',
            'traitements.auteur',
            'traitements.autorite',
            'messages.expediteur',
            'assignations.autorite',
            'assignations.superviseur',
        ]);

        $plainte->messages()
            ->where('expediteur_id', '!=', $request->user()->id)
            ->where('lu', false)
            ->update(['lu' => true]);

        $candidats = $request->user()->role->value === 'superviseur'
            ? $routage->candidats($plainte->domaine, $plainte->region)
            : collect();

        return view('plaintes.show', [
            'plainte' => $plainte,
            'candidats' => $candidats,
        ]);
    }
}
