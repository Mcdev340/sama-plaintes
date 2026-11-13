<?php

namespace App\Http\Controllers;

use App\Models\Alerte;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlerteController extends Controller
{
    public function index(Request $request): View
    {
        $alertes = $request->user()
            ->alertes()
            ->with('plainte')
            ->latest('date_envoi')
            ->paginate(12);

        return view('notifications.index', compact('alertes'));
    }

    public function lire(Request $request, Alerte $alerte): RedirectResponse
    {
        abort_unless($alerte->destinataire_id === $request->user()->id, 403);
        $alerte->update(['lu' => true]);

        if ($alerte->plainte) {
            return redirect()->route('plaintes.show', $alerte->plainte);
        }

        return back();
    }

    public function toutLire(Request $request): RedirectResponse
    {
        $request->user()->alertes()->where('lu', false)->update(['lu' => true]);

        return back()->with('succes', 'Toutes les notifications sont marquées comme lues.');
    }
}
