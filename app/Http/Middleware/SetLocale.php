<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) $request->session()->get('locale', config('app.locale'));

        if (! in_array($locale, ['fr', 'wo'], true)) {
            $locale = 'fr';
        }

        App::setLocale($locale);
        Carbon::setLocale('fr');

        return $next($request);
    }
}
