<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Redirect to HTTPS and the canonical host in production.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('production')) {
            return $next($request);
        }

        $appUrl = config('app.url');
        $canonicalHost = $appUrl ? parse_url($appUrl, PHP_URL_HOST) : null;

        $needsHttps = ! $request->isSecure();
        $needsHost = $canonicalHost && $request->getHost() !== $canonicalHost;

        if ($needsHttps || $needsHost) {
            $targetHost = $canonicalHost ?: $request->getHost();
            $target = 'https://'.$targetHost.$request->getRequestUri();

            return redirect()->to($target, 301);
        }

        return $next($request);
    }
}
