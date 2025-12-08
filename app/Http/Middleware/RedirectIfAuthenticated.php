<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // If already signed in as applicant, honor login_as flag for respondent dashboard
                if ($guard === 'applicant' || $guard === null) {
                    if ($request->query('login_as') === 'respondent') {
                        $request->session()->put('acting_as_respondent', true);
                        return redirect()->route('respondent.dashboard');
                    }
                    $request->session()->forget('acting_as_respondent');
                    return redirect()->route('applicant.dashboard');
                }

                // Fallback: default dashboard for other guards
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
