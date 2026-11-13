<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        View::composer('layouts.app', function ($view): void {
            $user = auth()->user();
            $view->with('nonLues', $user ? $user->alertes()->where('lu', false)->count() : 0);
        });
    }
}
