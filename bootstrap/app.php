<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    // ... (Bagian atas file biarkan saja)
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Ini adalah tempat baru untuk mendaftarkan alias
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class, // <-- TAMBAHKAN BARIS INI
        ]);

        // Mungkin ada middleware lain di sini, biarkan saja
        // $middleware->validateCsrfTokens(except: [...]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ...
    })->create();
