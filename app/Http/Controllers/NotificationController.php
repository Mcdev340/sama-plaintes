<?php

namespace App\Http\Controllers;

use App\Models\Alerte;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $alertes = $request->user()->alertes()->with('plainte')->latest('date_envoi')->paginate(15);

        return view('notifications.index', ['alertes' => $alertes]);
    }

    public function read(Request $request, Alerte $alerte)
    {
        abort_unless($alerte->user_id === $request->user()->id, 403);
        $alerte->update(['lu' => true]);

        if ($alerte->plainte) {
            return redirect()->route('dossiers.show', $alerte->plainte);
        }

        return back();
    }

    public function readAll(Request $request)
    {
        $request->user()->alertes()->where('lu', false)->update(['lu' => true]);

        return back()->with('succes', 'Toutes les notifications sont marquées comme lues.');
    }
}
