<?php

declare(strict_types=1);

use App\Http\Middleware\PurgeLegacySessionCookies;
use Illuminate\Http\Request;

/**
 * Production browsers carry three generations of session cookie
 * ("-session-admin", "pscourt-session-admin", "app-session-admin"), some set at
 * two Domain scopes. The duplicates make the resolved session — and the
 * XSRF-TOKEN paired with it — nondeterministic, which breaks OTP verification.
 */
function purgeWithCookies(array $cookies, string $currentCookie = 'app-session-admin')
{
    config(['session.cookie' => $currentCookie]);

    $request = Request::create('/forgot-password', 'POST');

    foreach ($cookies as $name => $value) {
        $request->cookies->set($name, $value);
    }

    $response = (new PurgeLegacySessionCookies)->handle($request, fn () => response('ok'));

    // Symfony\Cookie exposes no public $name, so key the lookup on getName().
    return collect($response->headers->getCookies());
}

/** All emitted cookies carrying the given name (one per scope). */
function emittedNamed(iterable $cookies, string $name)
{
    return collect($cookies)->filter(fn ($c) => $c->getName() === $name)->values();
}

test('legacy session cookies present on the request are expired', function () {
    $sent = purgeWithCookies([
        '-session' => 'a',
        '-session-admin' => 'b',
        '-session-applicant' => 'c',
        'pscourt-session-admin' => 'd',
        'app-session-admin' => 'keep',
    ]);

    // Only assert on names that are actually retired: SESSION_COOKIE decides
    // whether "pscourt-session*" is legacy or the live cookie.
    $retired = array_intersect(
        ['-session', '-session-admin', '-session-applicant', 'pscourt-session-admin'],
        PurgeLegacySessionCookies::legacyNames(),
    );

    expect($retired)->not->toBeEmpty();

    foreach ($retired as $stale) {
        $forgotten = emittedNamed($sent, $stale)->first();

        expect($forgotten)->not->toBeNull("expected {$stale} to be expired")
            // Cookie::forget() clears the value and back-dates the expiry.
            ->and($forgotten->getValue())->toBeEmpty()
            ->and($forgotten->getExpiresTime())->toBeLessThan(time());
    }
});

test('the cookie currently in use is never expired', function () {
    $sent = purgeWithCookies([
        '-session-admin' => 'stale',
        'app-session-admin' => 'live',
    ]);

    expect(emittedNamed($sent, 'app-session-admin'))->toBeEmpty()
        ->and(emittedNamed($sent, '-session-admin'))->not->toBeEmpty();
});

test('a legacy name is expired at every scope it could have been set on', function () {
    config(['session.domain' => '.pscourt.gov.et']);

    $domains = emittedNamed(purgeWithCookies(['-session-admin' => 'stale']), '-session-admin')
        ->map(fn ($c) => $c->getDomain())
        ->all();

    // Host-only plus the shared parent domain, or the browser keeps sending it.
    expect($domains)->toContain(null)
        ->and($domains)->toContain('.pscourt.gov.et');
});

test('nothing is emitted when no legacy cookies are present', function () {
    expect(purgeWithCookies(['app-session-admin' => 'live']))->toBeEmpty();
});

test('the configured session cookie is never exempted from encryption', function () {
    // Exempting a live cookie would put the raw session id on the wire.
    // Production hit exactly this when SESSION_COOKIE=pscourt-session was set
    // while "pscourt-session" was still listed as a retired name.
    $active = trim((string) env('SESSION_COOKIE', ''), '-');

    if ($active === '') {
        // Nothing configured: every legacy base is safe to exempt.
        expect(PurgeLegacySessionCookies::legacyNames())->toContain('pscourt-session');

        return;
    }

    $legacy = PurgeLegacySessionCookies::legacyNames();

    foreach ([$active, $active.'-admin', $active.'-applicant'] as $live) {
        expect($legacy)->not->toContain($live);
    }
})->skip(
    fn () => trim((string) env('SESSION_COOKIE', ''), '-') === '',
    'requires SESSION_COOKIE to be configured',
);

test('a legacy base that becomes the active cookie drops out of the exempt list', function () {
    // Simulates SESSION_COOKIE=pscourt-session, which is production's setting.
    putenv('SESSION_COOKIE=pscourt-session');
    $_ENV['SESSION_COOKIE'] = 'pscourt-session';

    try {
        $legacy = PurgeLegacySessionCookies::legacyNames();

        expect($legacy)->not->toContain('pscourt-session')
            ->not->toContain('pscourt-session-admin')
            ->not->toContain('pscourt-session-applicant')
            // The genuinely dead names are still purged.
            ->toContain('-session-admin');
    } finally {
        putenv('SESSION_COOKIE');
        unset($_ENV['SESSION_COOKIE']);
    }
});

test('deletes survive the encryption layer in the real web stack', function () {
    $response = $this->withUnencryptedCookies([
        '-session-admin' => 'stale',
        'pscourt-session-admin' => 'stale2',
    ])->get('/forgot-password');

    $cookies = $response->baseResponse->headers->getCookies();

    $retired = array_intersect(
        ['-session-admin', 'pscourt-session-admin'],
        PurgeLegacySessionCookies::legacyNames(),
    );

    foreach ($retired as $stale) {
        $deletes = emittedNamed($cookies, $stale);

        expect($deletes)->not->toBeEmpty("expected {$stale} to be deleted");

        foreach ($deletes as $delete) {
            // EncryptCookies must not re-encrypt these: a non-empty value would
            // hand the browser a fresh cookie instead of removing the old one.
            expect($delete->getValue())->toBe('')
                ->and($delete->getExpiresTime())->toBeLessThan(time());
        }
    }

    // The live session and CSRF cookies must still be encrypted.
    foreach ($cookies as $cookie) {
        if ($cookie->getName() === 'XSRF-TOKEN') {
            expect($cookie->getValue())->not->toBe('');
        }
    }
});
