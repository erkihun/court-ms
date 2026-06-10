<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemAuditMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only audit authenticated users on state-changing requests
        if (!auth()->check()) {
            return $response;
        }

        // Skip read-only verbs — they are the vast majority of page loads
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $response;
        }

        $routeName = optional($request->route())->getName();
        if (!$routeName) {
            return $response;
        }

        // Fire-and-forget: dispatch_sync keeps it in the same process on
        // hosts without a real queue worker; swap to dispatch() once you
        // configure QUEUE_CONNECTION=redis or database-async.
        try {
            DB::table('system_audits')->insert([
                'user_id'    => auth()->id(),
                'actor_type' => 'user',
                'action'     => $routeName,
                'module'     => $this->deriveModule($routeName),
                'route'      => $routeName,
                'method'     => $request->method(),
                'ip'         => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 2000),
                'context'    => json_encode($this->safeContext($request)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable) {
            // swallow logging errors to avoid breaking the response
        }

        return $response;
    }

    private function deriveModule(?string $route): ?string
    {
        if (!$route) return null;
        return explode('.', $route)[0] ?? null;
    }

    private function safeContext(Request $request): array
    {
        $input = collect($request->except(['password', 'password_confirmation', '_token']))
            ->map(fn($v) => is_array($v) ? '[array]' : (string) $v)
            ->take(10)
            ->toArray();

        return ['path' => $request->path(), 'input' => $input];
    }
}
