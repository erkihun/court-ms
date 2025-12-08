<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ActAsRespondent
{
    public function handle(Request $request, Closure $next)
    {
        // Flag the session/UI to show respondent navigation while keeping applicant guard/session.
        $request->session()->put('acting_as_respondent', true);
        return $next($request);
    }
}
