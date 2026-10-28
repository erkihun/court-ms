<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\EuisisParties;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Flow 6: with EUISIS_PORTAL_IN_EUISIS on, employees file, follow and answer
 * their cases in the EUISIS employee portal, so every applicant and respondent
 * portal page here (sign-in, registration, password reset, email verification,
 * the cases and replies themselves) shows where to go instead, and nothing is
 * submitted to it. A session someone still holds is ended. Staff pages and
 * the integration API are untouched.
 */
final class CourtPortalInEuisis
{
    private const PATHS = ['applicant', 'applicant/*', 'respondent', 'respondent/*', 'apply/*'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! EuisisParties::portalClosed() || ! $request->is(...self::PATHS)) {
            return $next($request);
        }

        foreach (['applicant', 'respondent'] as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }

        $portalUrl = EuisisParties::portalUrl();
        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return response()->json(['message' => 'Court cases are managed in the EUISIS employee portal.', 'error_code' => 'portal_in_euisis', 'portal_url' => $portalUrl], 410);
        }

        return Inertia::render('Court/PortalMoved', ['portalUrl' => $portalUrl])
            ->toResponse($request)
            ->setStatusCode($request->isMethod('GET') || $request->isMethod('HEAD') ? 200 : 410);
    }
}
