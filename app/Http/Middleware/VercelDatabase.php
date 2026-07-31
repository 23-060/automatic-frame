<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VercelDatabase
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (env('VERCEL')) {
            $dbPath = '/tmp/database.sqlite';
            if (!file_exists($dbPath) || filesize($dbPath) === 0) {
                $templatePath = database_path('database.sqlite');
                if (file_exists($templatePath)) {
                    copy($templatePath, $dbPath);
                } else {
                    touch($dbPath);
                }
            }
        }

        return $next($request);
    }
}
