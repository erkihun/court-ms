<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifiedApplicant
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user('applicant');

        if ($user && ! $user->hasVerifiedEmail()) {
            return redirect()->route('applicant.verification.notice')
                ->with('error', 'Please verify your email to continue.');
        }

        return $next($request);
    }
}
