<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__.'/../routes/channels.php',
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Add/keep your other aliases here too
        $middleware->alias([
            'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,
            'perm' => \App\Http\Middleware\RequirePermission::class,
        ]);

        // If you ever need global middleware:
        // $middleware->append(\App\Http\Middleware\SomethingGlobal::class);
    })
    ->withExceptions(function ($exceptions) {
        //
    })->create();
