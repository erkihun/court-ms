<?php

declare(strict_types=1);

use App\Http\Middleware\SetSessionCookieForGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Regression coverage for the production OTP failure.
 *
 * The OTP code is held in the session between the "send code" request and the
 * "verify code" request. If the session cookie name is invalid, the verify
 * request starts a brand new session, the stored hash is gone, and the user is
 * bounced back with "session expired" no matter which code they type.
 *
 * Production APP_NAME is Amharic, and Str::slug() reduces it to an empty
 * string — the old config derived the cookie name "-session" from it.
 */
test('a fully non-ascii app name slugs to nothing', function () {
    // This is the upstream condition that triggered the bug.
    expect(Str::slug('የ አአ አስተዳደር ፍርድ-ቤት'))->toBe('');
});

test('guard cookie names stay valid even when the base is empty or dashed', function (string $base) {
    config(['session.cookie_base' => $base]);

    $middleware = new SetSessionCookieForGuard;

    $paths = [
        '/password-otp',            // admin OTP verify
        '/applicant/verify-otp',    // applicant registration OTP
        '/applicant/password-otp',  // applicant reset OTP
        '/',
    ];

    foreach ($paths as $path) {
        $middleware->handle(Request::create($path, 'GET'), fn () => response('ok'));

        $cookie = (string) config('session.cookie');

        expect($cookie)->not->toBe('')
            ->not->toStartWith('-')
            // RFC 6265 cookie-name token characters only.
            ->toMatch('/^[A-Za-z0-9!#$%&\'*+\-.^_`|~]+$/');
    }
})->with([
    'empty base' => '',
    'dash-only base' => '-',
    'slug-stripped base' => '-session',
    'normal base' => 'pscourt-session',
]);

test('each portal still gets its own distinct cookie', function () {
    config(['session.cookie_base' => 'pscourt-session']);

    $middleware = new SetSessionCookieForGuard;

    $resolve = function (string $path) use ($middleware): string {
        $middleware->handle(Request::create($path, 'GET'), fn () => response('ok'));

        return (string) config('session.cookie');
    };

    // The OTP send and verify steps must land on the same cookie, or the
    // pending code is unreachable on the second request.
    expect($resolve('/forgot-password'))->toBe($resolve('/password-otp'))
        ->and($resolve('/applicant/forgot-password'))->toBe($resolve('/applicant/password-otp'));

    expect($resolve('/password-otp'))->toBe('pscourt-session-admin')
        ->and($resolve('/applicant/verify-otp'))->toBe('pscourt-session-applicant');
});
