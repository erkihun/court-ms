<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ends a staff session whose account the EUISIS staff check suspended since it
 * signed in (docs/euisis-integration.md, "Staff accounts"): the next request
 * signs it out and goes to the sign-in page.
 */
class EndSuspendedStaffSessions
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('web');
        if ($request->hasSession() && $guard->user()?->euisis_suspended_at !== null) {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $request->expectsJson()
                ? response()->json(['message' => __('euisis_staff.sign_in_suspended')], 401)
                : redirect()->route('login')->with('status', __('euisis_staff.sign_in_suspended'));
        }

        return $next($request);
    }
}
