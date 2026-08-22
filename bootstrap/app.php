<?php

use App\Http\Middleware\ActAsRespondent;
use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\AdminOnly;
use App\Http\Middleware\AdminSessionTimeout;
use App\Http\Middleware\AssignRequestId;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CaptureClientHints;
use App\Http\Middleware\EnforceSystemAvailability;
use App\Http\Middleware\EnsureMfaIsVerified;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\ForcePasswordChange;
use App\Http\Middleware\PurgeLegacySessionCookies;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RequirePermission;
use App\Http\Middleware\RequireRole;
use App\Http\Middleware\ResolveAdminRole;
use App\Http\Middleware\SetSessionCookieForGuard;
use App\Http\Middleware\SystemAuditMiddleware;
use App\Http\Middleware\UseGuard;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__.'/../routes/channels.php',
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // The production site may sit behind Apache/Nginx or a load balancer.
        // Trust the configured proxy chain so ForceHttps can see the browser's
        // original HTTPS scheme instead of redirecting HTTPS requests forever.
        $middleware->trustProxies(
            at: env('TRUSTED_PROXIES', '*'),
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_PREFIX
                | Request::HEADER_X_FORWARDED_AWS_ELB,
        );

        $middleware->prepend(AssignRequestId::class);
        $middleware->append(AddSecurityHeaders::class);

        // Add/keep your other aliases here too
        $middleware->alias([
            'auth' => Authenticate::class,
            'guest' => RedirectIfAuthenticated::class,
            'force.password.change' => ForcePasswordChange::class,
            'admin.only' => AdminOnly::class,
            'perm' => RequirePermission::class,
            'role' => RequireRole::class,
            'active.admin.role' => ResolveAdminRole::class,
            'audit' => SystemAuditMiddleware::class,
            'act.respondent' => ActAsRespondent::class,
            'use.guard' => UseGuard::class,
            'mfa' => EnsureMfaIsVerified::class,
        ]);

        $middleware->prependToGroup('web', SetSessionCookieForGuard::class);

        // Retires session cookies from earlier naming generations. Temporary —
        // remove (with the encryption exemption below) once live traffic no
        // longer carries the legacy names.
        $middleware->appendToGroup('web', PurgeLegacySessionCookies::class);

        // The delete cookies carry an empty value on purpose. Without this,
        // EncryptCookies rewrites them into fresh encrypted payloads and the
        // browser keeps the stale cookie instead of dropping it.
        $middleware->encryptCookies(
            except: PurgeLegacySessionCookies::legacyNames(),
        );
        $middleware->prependToGroup('web', AdminSessionTimeout::class);
        $middleware->appendToGroup('web', EnforceSystemAvailability::class);
        $middleware->appendToGroup('web', ForceHttps::class);
        $middleware->appendToGroup('web', CaptureClientHints::class);
        $middleware->appendToGroup('web', EnsureMfaIsVerified::class);
        $middleware->appendToGroup('web', SystemAuditMiddleware::class);
        $middleware->appendToGroup('api', SystemAuditMiddleware::class);

        // If you ever need global middleware:
        // $middleware->append(\App\Http\Middleware\SomethingGlobal::class);
    })
    ->withExceptions(function ($exceptions) {
        $exceptions->dontFlash([
            'mail_password',
            'telegram_bot_token',
            'sms_api_key',
            'sms_api_secret',
        ]);

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

        $exceptions->render(function (Throwable $e, $request) {
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
