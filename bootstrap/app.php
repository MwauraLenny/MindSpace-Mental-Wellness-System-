<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'not_admin' => \App\Http\Middleware\NotAdminMiddleware::class,
            'notifications.sync' => \App\Http\Middleware\SyncNotificationsMiddleware::class,
            'suspended' => \App\Http\Middleware\EnsureUserIsNotSuspended::class,
            'sessions.track' => \App\Http\Middleware\TrackUserSessionActivity::class,
            'security.headers' => \App\Http\Middleware\SecurityHeadersMiddleware::class,
            'metrics.requests' => \App\Http\Middleware\RequestMetricsMiddleware::class,
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\RequestMetricsMiddleware::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\SecurityHeadersMiddleware::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\SyncNotificationsMiddleware::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureUserIsNotSuspended::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\TrackUserSessionActivity::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
