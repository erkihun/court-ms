<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle($request, \Closure $next, string $perm)
    {
        $user = $request->user();
        abort_if(!$user, 403);
        if (!$user->hasPermission($perm)) {
            abort(403, 'Insufficient permissions.');
        }
        return $next($request);
    }
}
