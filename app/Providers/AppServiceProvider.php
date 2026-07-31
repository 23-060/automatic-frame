<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (env('VERCEL')) {
            config(['database.connections.sqlite.database' => '/tmp/database.sqlite']);
        }
    }

    public function boot(): void
    {
        //
    }
}
