<?php

namespace App\Http\Controllers;

use App\Models\Plainte;
use App\Services\PlainteService;
use App\Support\MessagesValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request, Plainte $plainte, PlainteService $plaintes): RedirectResponse
    {
        $this->authorize('commenter', $plainte);

        $donnees = $request->validate([
            'corps' => ['required', 'string', 'min:2', 'max:2000'],
        ], MessagesValidation::messages(), MessagesValidation::attributes());

        $plaintes->publierMessage($request->user(), $plainte, $donnees['corps']);

        return back()->with('succes', 'Message envoyé.');
    }
}
