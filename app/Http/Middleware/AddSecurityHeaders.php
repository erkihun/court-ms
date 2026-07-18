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

        if (! config('compliance.security.app_headers_enabled', false)) {
            return $response;
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

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
