<?php

test('diagnostic flags mismatched port and scheme', function (int $port, ?string $scheme, bool $expectProblem) {
    config([
        'mail.default' => 'smtp',
        'mail.mailers.smtp.host' => 'smtp.gmail.com',
        'mail.mailers.smtp.port' => $port,
        'mail.mailers.smtp.scheme' => $scheme,
        'mail.mailers.smtp.username' => 'a@b.com',
        'mail.mailers.smtp.password' => 'abcdefghijklmnop',
        'mail.from.address' => 'a@b.com',
    ]);

    $this->artisan('mail:diagnose')
        ->assertExitCode($expectProblem ? 1 : 0);
})->with([
    'valid implicit TLS' => [465, 'smtps', false],
    'valid STARTTLS' => [587, 'smtp', false],
    '465 with smtp is wrong' => [465, 'smtp', true],
    '587 with smtps is wrong' => [587, 'smtps', true],
]);

test('diagnostic flags empty credentials', function () {
    config([
        'mail.default' => 'smtp',
        'mail.mailers.smtp.host' => 'smtp.gmail.com',
        'mail.mailers.smtp.port' => 465,
        'mail.mailers.smtp.scheme' => 'smtps',
        'mail.mailers.smtp.username' => '',
        'mail.mailers.smtp.password' => '',
        'mail.from.address' => '',
    ]);

    $this->artisan('mail:diagnose')->assertExitCode(1);
});
