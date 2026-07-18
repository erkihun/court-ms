<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMfaIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $request->routeIs('mfa.setup.*', 'profile.mfa.*', 'mfa.challenge.*', 'logout')) {
            return $next($request);
        }

        $enabled = (bool) SystemSetting::cached()?->mfa_enabled;

        if (! $enabled || ! $user->requiresMfa()) {
            return $next($request);
        }

        if (! $user->hasConfirmedMfa()) {
            return redirect()->route('profile.mfa.show')
                ->with('warning', __('auth.mfa_enrollment_required'));
        }

        if (! $request->session()->has('mfa_verified_at')) {
            $request->session()->put('url.intended', $request->fullUrl());

            return redirect()->route('mfa.challenge.show');
        }

        return $next($request);
    }
}
