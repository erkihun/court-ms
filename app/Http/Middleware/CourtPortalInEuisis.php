<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\EuisisParties;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Flow 6: with EUISIS_PORTAL_IN_EUISIS on, employees file, follow and answer
 * their cases in the EUISIS employee portal, so the applicant and respondent
 * portal is gone from the web: every applicant, respondent and apply URL
 * (sign-in, registration, password reset, the cases and replies) is not
 * found, and a session someone still holds is ended. The route names stay
 * registered for the portal's own controllers, which the integration API
 * runs for the employee (EuisisCaseWorkspaceController). Staff pages and the
 * integration API are untouched.
 */
final class CourtPortalInEuisis
{
    private const PATHS = ['applicant', 'applicant/*', 'respondent', 'respondent/*', 'apply', 'apply/*'];

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

        abort(404);
    }
}
