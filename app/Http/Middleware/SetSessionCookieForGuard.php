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
        $path = $request->path();
        $base = config('session.cookie_base', config('session.cookie'));

        if ($base) {
            if ($request->is('applicant/*')) {
                config(['session.cookie' => $base . '-applicant']);
            } elseif ($request->is('respondent/*')) {
                config(['session.cookie' => $base . '-respondent']);
            } else {
                config(['session.cookie' => $base]);
            }
        }

        return $next($request);
    }
}
