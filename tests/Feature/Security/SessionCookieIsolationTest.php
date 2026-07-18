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

test('admin login explains that an applicant session is already active', function () {
    $base = (string) config('session.cookie_base');
    $applicantLogin = $this->get(route('applicant.login'));
    $applicantCookie = $applicantLogin->getCookie($base.'-applicant');

    expect($applicantCookie)->not->toBeNull();

    $this->withCookie($applicantCookie->getName(), $applicantCookie->getValue())
        ->get(route('login'))
        ->assertOk()
        ->assertSee(__('auth.applicant_session_active_title'))
        ->assertSee(__('auth.applicant_session_active_message'));
});

test('applicant login explains that an admin session is already active', function () {
    $base = (string) config('session.cookie_base');
    $adminLogin = $this->get(route('login'));
    $adminCookie = $adminLogin->getCookie($base.'-admin');

    expect($adminCookie)->not->toBeNull();

    $this->withCookie($adminCookie->getName(), $adminCookie->getValue())
        ->get(route('applicant.login'))
        ->assertOk()
        ->assertSee(__('auth.admin_session_active_title'))
        ->assertSee(__('auth.admin_session_active_message'));
});
