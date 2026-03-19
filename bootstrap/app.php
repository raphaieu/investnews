<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();

        // Atrás de nginx/Cloudflare/etc.: usa X-Forwarded-Proto para URL/asset corretos
        $trusted = env('TRUSTED_PROXIES');
        if ($trusted !== null && $trusted !== '') {
            $at = $trusted === '*' ? '*' : array_map('trim', explode(',', $trusted));
            $middleware->trustProxies(at: $at);
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
