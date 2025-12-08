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

        // Applicant area (/apply prefix or applicant.* named routes)
        if ($request->is('apply/*') || $request->routeIs('applicant.*')) {
            return route('applicant.login');
        }

        // Respondent area (/respondent prefix or respondent.* named routes)
        if ($request->is('respondent/*') || $request->routeIs('respondent.*')) {
            return route('applicant.login', ['login_as' => 'respondent']);
        }

        // Default back-office login
        return route('login');
    }
}
