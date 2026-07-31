<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\VercelDatabase::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e) {
            if (env('VERCEL')) {
                header('Content-Type: text/plain');
                echo "PRIMARY EXCEPTION:\n";
                echo "Class: " . get_class($e) . "\n";
                echo "Message: " . $e->getMessage() . "\n";
                echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
                echo "Trace:\n" . $e->getTraceAsString() . "\n";
                exit(1);
            }
        });
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();

if (env('VERCEL')) {
    $app->singleton(
        Illuminate\Contracts\Http\Kernel::class,
        Illuminate\Foundation\Http\Kernel::class
    );
    $app->singleton(
        Illuminate\Contracts\Console\Kernel::class,
        Illuminate\Foundation\Console\Kernel::class
    );
}

return $app;
