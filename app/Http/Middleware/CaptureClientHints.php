<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CaptureClientHints
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasSession() && $request->hasHeader('Sec-CH-UA-Platform')) {
            $hints = array_filter([
                'platform' => trim((string) $request->header('Sec-CH-UA-Platform'), '" '),
                'platform_version' => trim((string) $request->header('Sec-CH-UA-Platform-Version'), '" '),
                'brands' => (string) ($request->header('Sec-CH-UA-Full-Version-List') ?: $request->header('Sec-CH-UA')),
            ]);

            if ($request->session()->get('client_hints') !== $hints) {
                $request->session()->put('client_hints', $hints);
            }
        }

        $response = $next($request);

        // Ask Chromium browsers to send accurate OS/browser versions on subsequent
        // requests; Critical-CH makes the first navigation retry with them included.
        $response->headers->set('Accept-CH', 'Sec-CH-UA, Sec-CH-UA-Platform, Sec-CH-UA-Platform-Version, Sec-CH-UA-Full-Version-List');
        $response->headers->set('Critical-CH', 'Sec-CH-UA-Platform-Version, Sec-CH-UA-Full-Version-List');

        return $response;
    }
}
