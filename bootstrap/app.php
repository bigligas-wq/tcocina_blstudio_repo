<?php

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Application;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'kitchen' => \App\Http\Middleware\KitchenMiddleware::class,
            'site.offline' => \App\Http\Middleware\SiteOfflineMiddleware::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'can.do' => \App\Http\Middleware\CheckPermission::class,
        ]);
        // Global web middleware to enforce site offline for non-admins
        $middleware->append(\App\Http\Middleware\SiteOfflineMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);
    })
    ->create();
