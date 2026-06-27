<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // Respondent area (/respondent prefix or respondent.* named routes)
        if ($request->is('respondent', 'respondent/*') || $request->routeIs('respondent.*')) {
            return route('applicant.login', ['login_as' => 'respondent']);
        }

        // Applicant area (/applicant or /apply prefixes, or applicant.* named routes)
        if ($request->is('applicant', 'applicant/*', 'apply/*') || $request->routeIs('applicant.*')) {
            return route('applicant.login');
        }

        // Default back-office login
        return route('login');
    }
}
