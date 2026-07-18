<?php

use App\Http\Middleware\SetSessionCookieForGuard;
use Illuminate\Http\Request;

test('admin and applicant paths select separate session cookies', function () {
    $base = (string) config('session.cookie_base');
    $middleware = new SetSessionCookieForGuard();

    $cases = [
        ['/applicant/login', $base.'-applicant'],
        ['/applicant/dashboard', $base.'-applicant'],
        ['/respondent/profile', $base.'-applicant'],
        ['/login', $base.'-admin'],
        ['/dashboard', $base.'-admin'],
        ['/admin/settings/system', $base.'-admin'],
        ['/profile', $base.'-admin'],
    ];

    foreach ($cases as [$path, $expectedCookie]) {
        $middleware->handle(
            Request::create($path, 'GET'),
            fn () => response('ok')
        );

        expect(config('session.cookie'))->toBe($expectedCookie);
    }

    config(['session.cookie' => $base]);
});
