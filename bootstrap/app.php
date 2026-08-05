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
        // Shared hosting terminates TLS at a proxy, so trust X-Forwarded-* to
        // detect the original https scheme and generate https URLs. Without
        // this, Livewire and Filament request assets over http on an https
        // page and the browser blocks them as mixed content.
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
