<?php

declare(strict_types=1);

use App\Notifications\ApplicantEmailOtp;
use App\Notifications\PasswordResetOtp;
use App\Queue\Middleware\SkipExpiredOtp;
use Carbon\CarbonImmutable;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('allows fresh OTP notifications to be processed', function (string $notificationClass): void {
    CarbonImmutable::setTestNow('2026-08-08 12:00:00');

    $notification = new $notificationClass('123456');
    $middleware = $notification->middleware(new stdClass, 'mail');

    $processed = false;
    $result = $middleware[0]->handle(new stdClass, function () use (&$processed): bool {
        $processed = true;

        return true;
    });

    expect($middleware[0])->toBeInstanceOf(SkipExpiredOtp::class)
        ->and($result)->toBeTrue()
        ->and($processed)->toBeTrue();
})->with([
    ApplicantEmailOtp::class,
    PasswordResetOtp::class,
]);

it('discards queued OTP notifications after ten minutes', function (string $notificationClass): void {
    CarbonImmutable::setTestNow('2026-08-08 12:00:00');
    $notification = new $notificationClass('123456');
    $middleware = unserialize(serialize($notification->middleware(new stdClass, 'mail')));

    CarbonImmutable::setTestNow('2026-08-08 12:10:00');

    $processed = false;
    $result = $middleware[0]->handle(new stdClass, function () use (&$processed): bool {
        $processed = true;

        return true;
    });

    expect($middleware[0])->toBeInstanceOf(SkipExpiredOtp::class)
        ->and($result)->toBeFalse()
        ->and($processed)->toBeFalse();
})->with([
    ApplicantEmailOtp::class,
    PasswordResetOtp::class,
]);
