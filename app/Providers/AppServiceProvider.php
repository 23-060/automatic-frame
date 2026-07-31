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
            $dbPath = '/tmp/database.sqlite';
            if (!file_exists($dbPath)) {
                touch($dbPath);
                config(['database.connections.sqlite.database' => $dbPath]);
                
                try {
                    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Vercel Migration Error: ' . $e->getMessage());
                }
            } else {
                config(['database.connections.sqlite.database' => $dbPath]);
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
