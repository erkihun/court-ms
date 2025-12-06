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

        // Only log authenticated users and named routes
        if (auth()->check()) {
            $routeName = optional($request->route())->getName();
            if (!$routeName) {
                return $response;
            }

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
            } catch (\Throwable $e) {
                // swallow logging errors to avoid breaking the request
            }
        }

        return $response;
    }

    private function deriveModule(?string $route): ?string
    {
        if (!$route) {
            return null;
        }
        $parts = explode('.', $route);
        return $parts[0] ?? null;
    }

    private function safeContext(Request $request): array
    {
        // Avoid logging sensitive fields; only keep small subset
        $input = collect($request->except(['password', 'password_confirmation', '_token']))
            ->map(fn($v) => is_array($v) ? '[array]' : (string) $v)
            ->take(10)
            ->toArray();

        return [
            'path'   => $request->path(),
            'input'  => $input,
        ];
    }
}
