<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

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

        $middleware->appendToGroup('web', \App\Http\Middleware\ForceHttps::class);

        // If you ever need global middleware:
        // $middleware->append(\App\Http\Middleware\SomethingGlobal::class);
    })
    ->withExceptions(function ($exceptions) {
        $exceptions->render(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Session expired.',
                ], 419);
            }

            foreach (['web', 'applicant', 'respondent'] as $guard) {
                if (auth($guard)->check()) {
                    auth($guard)->logout();
                }
            }

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->is('applicant/*')) {
                return redirect()->route('applicant.login')
                    ->with('error', 'Session expired. Please sign in again.');
            }

            if ($request->is('respondent/*')) {
                return redirect()->route('respondent.login')
                    ->with('error', 'Session expired. Please sign in again.');
            }

            return redirect()->route('login')
                ->with('error', 'Session expired. Please sign in again.');
        });
    })->create();
