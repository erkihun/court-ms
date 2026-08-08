<?php

test('diagnostic flags mismatched port and scheme', function (int $port, ?string $scheme, bool $expectProblem) {
    config([
        'mail.default' => 'smtp',
        'mail.mailers.smtp.host' => '127.0.0.1',
        'mail.mailers.smtp.port' => $port,
        'mail.mailers.smtp.scheme' => $scheme,
        'mail.mailers.smtp.username' => 'a@b.com',
        'mail.mailers.smtp.password' => 'abcdefghijklmnop',
        'mail.from.address' => 'a@b.com',
    ]);

    $this->artisan('mail:diagnose --no-probe')
        ->assertExitCode($expectProblem ? 1 : 0);
})->with([
    'valid implicit TLS' => [465, 'smtps', false],
    'valid STARTTLS' => [587, 'smtp', false],
    'valid plaintext/STARTTLS on 25' => [25, 'smtp', false],
    '465 with smtp is wrong' => [465, 'smtp', true],
    '587 with smtps is wrong' => [587, 'smtps', true],
    // Production's actual setting: mail.ethionet.et:25 with scheme smtps.
    '25 with smtps is wrong' => [25, 'smtps', true],
]);

test('diagnostic flags empty credentials', function () {
    config([
        'mail.default' => 'smtp',
        'mail.mailers.smtp.host' => '127.0.0.1',
        'mail.mailers.smtp.port' => 465,
        'mail.mailers.smtp.scheme' => 'smtps',
        'mail.mailers.smtp.username' => '',
        'mail.mailers.smtp.password' => '',
        'mail.from.address' => '',
    ]);

    $this->artisan('mail:diagnose --no-probe')->assertExitCode(1);
});
