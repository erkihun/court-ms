<?php

declare(strict_types=1);

namespace App\Queue\Middleware;

use Carbon\CarbonImmutable;
use Closure;

final readonly class SkipExpiredOtp
{
    public function __construct(private CarbonImmutable $expiresAt) {}

    public function handle(mixed $job, Closure $next): mixed
    {
        if (CarbonImmutable::now()->greaterThanOrEqualTo($this->expiresAt)) {
            return false;
        }

        return $next($job);
    }
}
