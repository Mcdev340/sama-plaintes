<?php

namespace App\Http\Controllers;

use App\Enums\StatutPlainte;
use App\Models\Plainte;
use App\Services\FiltrePlaintes;
use App\Services\PlainteService;
use App\Services\StatistiquesService;
use App\Support\MessagesValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AutoriteController extends Controller
{
    public function dashboard(Request $request, StatistiquesService $statistiques): View
    {
        $autorite = $request->user()->autorite;
        abort_unless($autorite, 403, 'Aucun service n’est rattaché à ce compte.');

        $base = Plainte::query()->where('autorite_id', $autorite->id);

        return view('autorite.dashboard', [
            'autorite' => $autorite,
            'stats' => $statistiques->synthese($base),
            'dossiers' => (clone $base)
                ->whereNotIn('statut', StatutPlainte::clos())
                ->orderBy('date_echeance')
                ->limit(8)
                ->get(),
        ]);
    }

    public function index(Request $request, FiltrePlaintes $filtres): View
    {
        $autorite = $request->user()->autorite;
        abort_unless($autorite, 403);

        $plaintes = $filtres
            ->appliquer(Plainte::query()->where('autorite_id', $autorite->id), $request)
            ->orderBy('date_echeance')
            ->paginate(10)
            ->withQueryString();

        return view('autorite.dossiers.index', compact('plaintes', 'autorite'));
    }

    public function prendre(Request $request, Plainte $plainte, PlainteService $plaintes): RedirectResponse
    {
        $this->authorize('traiter', $plainte);
        $plaintes->prendreEnCharge($request->user(), $plainte, null);

        return back()->with('succes', 'Dossier pris en charge.');
    }

    public function complement(Request $request, Plainte $plainte, PlainteService $plaintes): RedirectResponse
    {
        $this->authorize('traiter', $plainte);
        $donnees = $request->validate([
            'commentaire' => ['required', 'string', 'min:5', 'max:2000'],
        ], MessagesValidation::messages(), MessagesValidation::attributes());
        $plaintes->demanderComplement($request->user(), $plainte, $donnees['commentaire']);

        return back()->with('succes', 'Complément demandé au citoyen.');
    }

    public function resoudre(Request $request, Plainte $plainte, PlainteService $plaintes): RedirectResponse
    {
        $this->authorize('traiter', $plainte);
        $donnees = $request->validate([
            'commentaire' => ['required', 'string', 'min:5', 'max:2000'],
        ], MessagesValidation::messages(), MessagesValidation::attributes());
        $plaintes->resoudre($request->user(), $plainte, $donnees['commentaire']);

        return back()->with('succes', 'Plainte marquée résolue.');
    }

    public function rejeter(Request $request, Plainte $plainte, PlainteService $plaintes): RedirectResponse
    {
        $this->authorize('traiter', $plainte);
        $donnees = $request->validate([
            'commentaire' => ['required', 'string', 'min:5', 'max:2000'],
        ], MessagesValidation::messages(), MessagesValidation::attributes());
        $plaintes->rejeter($request->user(), $plainte, $donnees['commentaire']);

        return back()->with('succes', 'Plainte rejetée.');
    }
}
