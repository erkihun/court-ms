<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSessionTimeout
{
    /**
     * Reduce session lifetime for admin routes only.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isAdminRoute = $request->is('admin/*') || $request->is('dashboard');
        $routeName = $request->route()?->getName();
        $isNamedAdmin = $routeName && (str_starts_with($routeName, 'admin.') || $routeName === 'dashboard');

        if ($isAdminRoute || $isNamedAdmin) {
            config(['session.lifetime' => 1]);
        }

        return $next($request);
    }
}
