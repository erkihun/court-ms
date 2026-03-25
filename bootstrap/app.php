<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
            'admin.only' => \App\Http\Middleware\AdminOnly::class,
            'perm' => \App\Http\Middleware\RequirePermission::class,
            'use.guard' => \App\Http\Middleware\UseGuard::class,
        ]);

        $middleware->prependToGroup('web', \App\Http\Middleware\SetSessionCookieForGuard::class);
        $middleware->prependToGroup('web', \App\Http\Middleware\AdminSessionTimeout::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\ForceHttps::class);

        // If you ever need global middleware:
        // $middleware->append(\App\Http\Middleware\SomethingGlobal::class);
    })
    ->withExceptions(function ($exceptions) {
        $exceptions->render(function (PostTooLargeException $e, $request) {
            $limit = (string) (ini_get('post_max_size') ?: 'server limit');
            $message = "Upload is too large for the server limit ({$limit}). Reduce file size/quantity and try again.";

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 413);
            }

            return back()->with('error', $message);
        });

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

        $exceptions->render(function (\Throwable $e, $request) {
            // Preserve default handling for common non-500 flows.
            if ($e instanceof ValidationException || $e instanceof AuthenticationException) {
                return null;
            }

            if ($e instanceof HttpExceptionInterface && $e->getStatusCode() < 500) {
                return null;
            }

            Log::error('Unhandled exception', [
                'exception' => $e,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
            ]);

            $message = 'Something went wrong. Please try again later.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 500);
            }

            return response()->view('errors.generic', [
                'message' => $message,
            ], 500);
        });
    })->create();
