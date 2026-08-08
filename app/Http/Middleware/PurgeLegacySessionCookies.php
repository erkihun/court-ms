<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Expire session cookies left behind by earlier cookie-naming generations.
 *
 * Production accumulated three generations of admin/applicant session cookie:
 *
 *   -session-admin          (empty Str::slug() of the Amharic APP_NAME)
 *   pscourt-session-admin   (an explicit SESSION_COOKIE that was later removed)
 *   app-session-admin       (the current 'app' fallback)
 *
 * Several were also set at two different Domain scopes (host-only and
 * .pscourt.gov.et), so the browser sends the same name twice. PHP keeps only
 * one of each duplicate, so the session backing an OTP — and the XSRF-TOKEN
 * that must match it — can resolve to different generations on consecutive
 * requests. The OTP written during "send code" is then unreadable during
 * "verify code".
 *
 * This deletes every stale name at every scope it could have been set on,
 * leaving only the cookie the app currently issues. Remove this middleware
 * once traffic no longer carries the legacy names (roughly one session
 * lifetime after deploy).
 */
class PurgeLegacySessionCookies
{
    /**
     * Cookie name stems that are no longer issued.
     *
     * The '-admin' / '-applicant' suffixes are applied on top of these.
     */
    private const LEGACY_BASES = [
        '-session',
        'pscourt-session',
    ];

    /**
     * Every retired cookie name, including the per-portal suffixes.
     *
     * @return list<string>
     */
    public static function legacyNames(): array
    {
        $names = [];

        foreach (self::LEGACY_BASES as $base) {
            $names[] = $base;
            $names[] = $base.'-admin';
            $names[] = $base.'-applicant';
        }

        return $names;
    }

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $current = (string) config('session.cookie');

        $secure = (bool) config('session.secure', false);

        foreach ($this->staleNames($request, $current) as $name) {
            foreach ($this->scopes($request) as $domain) {
                // Built directly rather than via Cookie::forget(): the CookieJar
                // substitutes its configured default domain for a null one,
                // which would lose the host-only scope. ResponseHeaderBag keys
                // cookies by name+path+domain, so each scope survives.
                $response->headers->setCookie(
                    new SymfonyCookie(
                        name: $name,
                        value: '',
                        expire: 1,
                        path: '/',
                        domain: $domain,
                        secure: $secure,
                        httpOnly: true,
                        raw: false,
                        sameSite: null,
                    )
                );
            }
        }

        return $response;
    }

    /**
     * Legacy cookie names actually present on this request.
     *
     * @return list<string>
     */
    private function staleNames(Request $request, string $current): array
    {
        $stale = [];

        foreach (self::legacyNames() as $name) {
            // Never delete the cookie this request is actually using.
            if ($name !== $current && $request->cookies->has($name)) {
                $stale[] = $name;
            }
        }

        return $stale;
    }

    /**
     * Every domain scope a legacy cookie could have been written at.
     *
     * A delete only removes a cookie when it matches the original scope, so
     * both the host-only and the configured shared domain must be covered.
     *
     * @return list<string|null>
     */
    private function scopes(Request $request): array
    {
        $scopes = [null];                   // host-only (no Domain attribute)

        $configured = (string) config('session.domain', '');

        if ($configured !== '') {
            $scopes[] = $configured;        // e.g. .pscourt.gov.et
        }

        // Guard against the shared domain having been unset at some point.
        $host = $request->getHost();

        if ($host !== '' && $host !== $configured) {
            $scopes[] = $host;
        }

        return $scopes;
    }
}
