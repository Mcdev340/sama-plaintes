<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request, string $locale)
    {
        abort_unless(in_array($locale, ['fr', 'wo'], true), 404);
        $request->session()->put('locale', $locale);

        if ($request->user()) {
            $request->user()->update(['langue' => $locale]);
        }

        return back();
    }
}
