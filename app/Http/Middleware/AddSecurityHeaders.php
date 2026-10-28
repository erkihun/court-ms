<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddSecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // public/.htaccess already sends these three on Apache. The application
        // copies are for deployments without that layer (e.g. Nginx only), and
        // stay opt-in so Apache deployments do not receive duplicate headers.
        if (config('compliance.security.app_headers_enabled', false)) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        }

        // No web-server layer sends this, and the application uses none of these
        // browser features, so it is always safe to deny them.
        if (! $response->headers->has('Permissions-Policy')) {
            $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        }

        // HSTS and CSP have their own switches, independent of the baseline set
        // above, so they can be enabled without duplicating Apache's headers.
        if ($request->isSecure() && config('compliance.security.hsts_enabled', false)) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age='.(int) config('compliance.security.hsts_max_age', 31_536_000).'; includeSubDomains'
            );
        }

        $contentSecurityPolicy = trim((string) config('compliance.security.content_security_policy', ''));
        if ($contentSecurityPolicy !== '') {
            $header = config('compliance.security.csp_enforce', false)
                ? 'Content-Security-Policy'
                : 'Content-Security-Policy-Report-Only';
            $response->headers->set($header, $contentSecurityPolicy);
        }

        return $response;
    }
}
