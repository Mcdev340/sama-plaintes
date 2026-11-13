<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectionController extends Controller
{
    public function tableau(Request $request): RedirectResponse
    {
        $route = match ($request->user()->role) {
            Role::Autorite => 'autorite.dashboard',
            Role::Superviseur => 'superviseur.dashboard',
            default => 'citoyen.dashboard',
        };

        return redirect()->route($route);
    }
}
