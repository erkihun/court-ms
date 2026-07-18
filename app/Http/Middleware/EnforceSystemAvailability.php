<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceSystemAvailability
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $settings = SystemSetting::cached();

        if ($settings?->maintenance_mode
            && ! $request->user()
            && ! $request->is('admin/*', 'login', 'logout', 'forgot-password', 'password-*')) {
            abort(503, __('settings.maintenance_public_message'));
        }

        if ($settings && ! $settings->registration_open
            && $request->is('applicant/register')) {
            abort(403, __('settings.registration_closed_message'));
        }

        return $next($request);
    }
}
