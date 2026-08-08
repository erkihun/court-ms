<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSessionCookieForGuard
{
    /**
     * Set a distinct session cookie name per guard context to avoid collisions.
     *
     * This runs before StartSession so the correct cookie is read/written.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $base = (string) config('session.cookie_base', config('session.cookie'));

        // Never build a cookie name off an empty or dash-leading base: browsers
        // and proxies may drop it, which silently destroys the session that
        // holds pending OTP codes between the send and verify requests.
        $base = trim($base, '-');

        if ($base === '') {
            $base = 'app-session';
        }

        // The respondent portal uses the applicant guard, so both portal
        // modes intentionally share the applicant session cookie.
        if ($this->isAdminPath($request)) {
            config(['session.cookie' => $base.'-admin']);
        } elseif ($request->is('applicant', 'applicant/*', 'respondent', 'respondent/*')) {
            config(['session.cookie' => $base.'-applicant']);
        } else {
            config(['session.cookie' => $base]);
        }

        return $next($request);
    }

    private function isAdminPath(Request $request): bool
    {
        return $request->is(
            'login',
            'logout',
            'register',
            'forgot-password',
            'password-otp',
            'password-otp/*',
            'new-password',
            'new-password/*',
            'verify-email',
            'verify-email/*',
            'confirm-password',
            'force-password',
            'password',
            'email/verification-notification',
            'dashboard',
            'profile',
            'mfa/*',
            'mfa-challenge',
            'mfa-challenge/*',
            'users',
            'users/*',
            'admin',
            'admin/*',
        );
    }
}
