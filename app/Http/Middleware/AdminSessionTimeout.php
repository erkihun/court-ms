<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSessionTimeout
{
    /**
     * Reduce session lifetime for admin routes only.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isAdminRoute = $request->is('admin/*') || $request->is('dashboard');
        $routeName = $request->route()?->getName();
        $isNamedAdmin = $routeName && (str_starts_with($routeName, 'admin.') || $routeName === 'dashboard');

        if ($isAdminRoute || $isNamedAdmin) {
            $minutes = SystemSetting::cached()?->session_lifetime ?? 30;
            config(['session.lifetime' => max(5, (int) $minutes)]);
        }

        return $next($request);
    }
}
