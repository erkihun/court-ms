<?php

use App\Http\Middleware\SetSessionCookieForGuard;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
        ['/mfa-challenge', $base.'-admin'],
        ['/users', $base.'-admin'],
        ['/users/1', $base.'-admin'],
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

test('admin login does not flash a cross-portal session notice', function () {
    $base = (string) config('session.cookie_base');
    $applicantLogin = $this->get(route('applicant.login'));
    $applicantCookie = $applicantLogin->getCookie($base.'-applicant');
    $user = User::factory()->create([
        'password' => Hash::make('password'),
        'status' => 'active',
    ]);

    expect($applicantCookie)->not->toBeNull();

    Auth::shouldUse('web');

    $this->withCookie($applicantCookie->getName(), $applicantCookie->getValue())
        ->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])
        ->assertRedirect(route('dashboard', absolute: false))
        ->assertSessionMissing('info');
});

test('applicant login does not flash a cross-portal session notice', function () {
    $base = (string) config('session.cookie_base');
    $adminLogin = $this->get(route('login'));
    $adminCookie = $adminLogin->getCookie($base.'-admin');
    $applicant = Applicant::create([
        'first_name' => 'Abel',
        'middle_name' => 'K',
        'last_name' => 'Teka',
        'gender' => 'male',
        'position' => 'Manager',
        'organization_name' => 'ACME',
        'phone' => '0911000200',
        'email' => 'session-notice@example.com',
        'address' => 'Addis Ababa',
        'national_id_number' => '1234567890123456',
        'password' => Hash::make('password'),
        'is_active' => true,
        'is_lawyer' => false,
    ]);

    expect($adminCookie)->not->toBeNull();

    $this->withCookie($adminCookie->getName(), $adminCookie->getValue())
        ->post(route('applicant.login.submit'), [
            'email' => $applicant->email,
            'password' => 'password',
        ])
        ->assertRedirect(route('applicant.dashboard'))
        ->assertSessionMissing('info');
});
