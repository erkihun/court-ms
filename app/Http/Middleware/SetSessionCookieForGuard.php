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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $base = config('session.cookie_base', config('session.cookie'));

        if ($base) {
            $adminCookie = $base . '-admin';
            $applicantCookie = $base . '-applicant';

            // The respondent portal uses the applicant guard, so both portal
            // modes intentionally share the applicant session cookie.
            if ($this->isAdminPath($request)) {
                config(['session.cookie' => $adminCookie]);
            } elseif ($request->is('applicant', 'applicant/*', 'respondent', 'respondent/*')) {
                config(['session.cookie' => $base . '-applicant']);
            } else {
                config(['session.cookie' => $base]);
            }
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
            'admin',
            'admin/*',
        );
    }
}
