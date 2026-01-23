<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        // ❌ REMOVE this line from the global stack:
        // \App\Http\Middleware\SetLocale::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\ForceHttps::class,
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

            // ✅ Keep SetLocale here (AFTER StartSession):
            \App\Http\Middleware\SetLocale::class,
        ],

        'api' => [
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];



    /**
     * The application's route middleware / aliases.
     */
    protected $middlewareAliases = [
        'auth'               => \App\Http\Middleware\Authenticate::class,
        'auth.basic'         => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers'      => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can'                => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'              => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm'   => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed'             => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle'           => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified'           => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,

        // Custom
        'perm'               => \App\Http\Middleware\RequirePermission::class,
        'role'               => \App\Http\Middleware\RequireRole::class,
        'audit'              => \App\Http\Middleware\SystemAuditMiddleware::class,
        'act.respondent'     => \App\Http\Middleware\ActAsRespondent::class,
    ];

    /**
     * Backwards-compatibility: some stacks still read $routeMiddleware.
     */
    protected $routeMiddleware = [
        'auth'               => \App\Http\Middleware\Authenticate::class,
        'auth.basic'         => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers'      => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can'                => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'              => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm'   => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed'             => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle'           => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified'           => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,

        // Custom
        'perm'               => \App\Http\Middleware\RequirePermission::class,
        'role'               => \App\Http\Middleware\RequireRole::class,
        'audit'              => \App\Http\Middleware\SystemAuditMiddleware::class,
        'act.respondent'     => \App\Http\Middleware\ActAsRespondent::class,
    ];

    /**
     * Ensure audit runs after authentication.
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\Authenticate::class,
        \App\Http\Middleware\SystemAuditMiddleware::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ];
}
