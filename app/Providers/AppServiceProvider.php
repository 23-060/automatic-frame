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
            
            // Ensure views directory exists
            $viewsPath = '/tmp/views';
            if (!file_exists($viewsPath)) {
                @mkdir($viewsPath, 0777, true);
            }
        }
    }

    public function boot(): void
    {
        if (env('VERCEL') || $this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
