<?php

use App\Http\Middleware\SetLocale;
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
        // Trust reverse proxies (ngrok / shared hosting) so Laravel sees the
        // original HTTPS scheme via X-Forwarded-* and generates https URLs.
        // This keeps Livewire/Filament requests on https (no mixed-content).
        $middleware->trustProxies(at: '*');

        // Run SetLocale on every web request so translations + RTL/LTR
        // are resolved before controllers/views render.
        $middleware->web(append: [
            SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
