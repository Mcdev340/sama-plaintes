<?php

namespace App\Http\Controllers;

use App\Models\AutoriteLocale;
use App\Models\Plainte;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlainteController extends Controller
{
    public function show(Request $request, Plainte $plainte): View
    {
        $this->authorize('view', $plainte);

        $plainte->load([
            'citoyen.user',
            'autorite',
            'autoriteSuggeree',
            'pieces',
            'traitements',
            'messages.auteur',
            'assignations.autorite',
        ]);

        return view('plaintes.show', [
            'plainte' => $plainte,
            'autorites' => $request->user()->estSuperviseur()
                ? AutoriteLocale::query()->where('actif', true)->orderBy('nom')->get()
                : collect(),
        ]);
    }
}
