<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Ensure users flagged with must_change_password are redirected to update it.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || !$user->must_change_password) {
            return $next($request);
        }

        $route = $request->route()?->getName();

        // Allow password/profile routes and logout so they can complete the change.
        $allowed = [
            'password.force',
            'profile.edit',
            'profile.update',
            'password.update',
            'logout',
        ];

        if (in_array($route, $allowed, true)) {
            return $next($request);
        }

        return redirect()->route('password.force')->with('error', 'Please change your password to continue.');
    }
}
