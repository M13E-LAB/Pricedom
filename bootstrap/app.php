<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Désactiver CSRF pour la route d'analyse de tickets (pour dev local)
        $middleware->validateCsrfTokens(except: [
            'contribute/scan-ticket',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
