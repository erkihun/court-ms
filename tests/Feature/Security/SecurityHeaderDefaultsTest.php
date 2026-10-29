<?php

declare(strict_types=1);

/*
| Apache (public/.htaccess) sends nosniff / X-Frame-Options / Referrer-Policy, so
| the application copies stay opt-in. These tests pin down the headers the
| application owns regardless of that switch.
*/

beforeEach(function (): void {
    config()->set('compliance.security.app_headers_enabled', false);
});

test('permissions policy is sent by default', function (): void {
    $this->getJson('/api/health')
        ->assertOk()
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
});

test('baseline headers stay with the web server unless the app copies are enabled', function (): void {
    $response = $this->getJson('/api/health')->assertOk();

    expect($response->headers->has('X-Frame-Options'))->toBeFalse()
        ->and($response->headers->has('X-Content-Type-Options'))->toBeFalse();
});

test('hsts can be enabled without enabling the baseline app headers', function (): void {
    config()->set('compliance.security.hsts_enabled', true);

    $response = $this->getJson('https://localhost/api/health')
        ->assertOk()
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

    expect($response->headers->has('X-Frame-Options'))->toBeFalse();
});

test('hsts is never sent over plain http', function (): void {
    config()->set('compliance.security.hsts_enabled', true);

    $response = $this->getJson('http://localhost/api/health')->assertOk();

    expect($response->headers->has('Strict-Transport-Security'))->toBeFalse();
});

test('a content security policy can be enabled without enabling the baseline app headers', function (): void {
    config()->set('compliance.security.content_security_policy', "default-src 'self'");

    $this->getJson('/api/health')
        ->assertOk()
        ->assertHeader('Content-Security-Policy-Report-Only', "default-src 'self'");
});
