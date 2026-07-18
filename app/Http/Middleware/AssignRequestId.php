<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AssignRequestId
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $this->validIncomingId($request->header('X-Request-ID'))
            ?? (string) Str::uuid7();

        $request->attributes->set('request_id', $requestId);
        Log::withContext(['request_id' => $requestId]);

        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }

    private function validIncomingId(?string $value): ?string
    {
        if ($value === null || preg_match('/^[A-Za-z0-9._:-]{8,128}$/', $value) !== 1) {
            return null;
        }

        return $value;
    }
}
