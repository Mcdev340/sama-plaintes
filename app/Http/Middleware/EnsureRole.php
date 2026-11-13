<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $role = $user?->role?->value;

        if (! $user || ! $user->actif || ! in_array($role, $roles, true)) {
            abort(403, 'Cet espace est réservé à un autre profil.');
        }

        return $next($request);
    }
}
