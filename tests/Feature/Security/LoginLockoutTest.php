<?php

declare(strict_types=1);

use App\Models\Applicant;
use App\Models\User;
use App\Support\LoginThrottle;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/*
| The client IP is resolved from X-Forwarded-For (proxies are trusted), so a
| caller can present a new "IP" on every request. These tests rotate that header
| to prove the per-account bucket still locks the account.
*/

const LOCKOUT_PASSWORD = 'CorrectHorse123!';

function lockoutApplicant(): Applicant
{
    $suffix = Str::lower(Str::random(8));

    $applicant = Applicant::create([
        'first_name' => 'Lockout',
        'middle_name' => 'Test',
        'last_name' => 'Applicant',
        'gender' => 'male',
        'position' => 'Officer',
        'organization_name' => 'ACME',
        'phone' => '09'.random_int(10000000, 99999999),
        'email' => "lockout-{$suffix}@example.com",
        'address' => 'Addis Ababa',
        'national_id_number' => (string) random_int(1000000000000000, 9999999999999999),
        'password' => Hash::make(LOCKOUT_PASSWORD),
        'is_active' => true,
        'is_lawyer' => false,
    ]);
    $applicant->markEmailAsVerified();

    return $applicant;
}

function rotatingIp(int $i): array
{
    return ['X-Forwarded-For' => '198.51.100.'.(($i % 250) + 1)];
}

function accountAttemptLimit(): int
{
    return 5 * LoginThrottle::ACCOUNT_MULTIPLIER;
}

test('staff lockout cannot be bypassed by rotating the forwarded client ip', function (): void {
    $user = User::factory()->create(['password' => Hash::make(LOCKOUT_PASSWORD)]);

    for ($i = 0; $i < accountAttemptLimit(); $i++) {
        $this->withHeaders(rotatingIp($i))
            ->post('/login', ['email' => $user->email, 'password' => 'wrong-'.$i]);
    }

    // Even the correct password, from a never-seen "IP", is refused while locked.
    $this->withHeaders(['X-Forwarded-For' => '203.0.113.77'])
        ->post('/login', ['email' => $user->email, 'password' => LOCKOUT_PASSWORD])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    expect(session('errors')->get('email')[0])->not->toBe(trans('auth.failed'));
});

test('staff lockout from one client still trips at the configured attempt limit', function (): void {
    $user = User::factory()->create(['password' => Hash::make(LOCKOUT_PASSWORD)]);

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-'.$i]);
    }

    $this->post('/login', ['email' => $user->email, 'password' => LOCKOUT_PASSWORD])
        ->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('locking one staff account does not lock a different account', function (): void {
    $victim = User::factory()->create(['password' => Hash::make(LOCKOUT_PASSWORD)]);
    $bystander = User::factory()->create(['password' => Hash::make(LOCKOUT_PASSWORD)]);

    for ($i = 0; $i < accountAttemptLimit(); $i++) {
        $this->withHeaders(rotatingIp($i))
            ->post('/login', ['email' => $victim->email, 'password' => 'wrong-'.$i]);
    }

    $this->post('/login', ['email' => $bystander->email, 'password' => LOCKOUT_PASSWORD])
        ->assertSessionHasNoErrors();
    $this->assertAuthenticatedAs($bystander);
});

test('applicant lockout cannot be bypassed by rotating the forwarded client ip', function (): void {
    $applicant = lockoutApplicant();

    for ($i = 0; $i < accountAttemptLimit(); $i++) {
        $this->withHeaders(rotatingIp($i))
            ->post(route('applicant.login.submit'), ['email' => $applicant->email, 'password' => 'wrong-'.$i]);
    }

    $this->withHeaders(['X-Forwarded-For' => '203.0.113.78'])
        ->post(route('applicant.login.submit'), ['email' => $applicant->email, 'password' => LOCKOUT_PASSWORD])
        ->assertSessionHasErrors('email');

    $this->assertGuest('applicant');
});

test('api token login cannot be bypassed by rotating the forwarded client ip', function (): void {
    $applicant = lockoutApplicant();

    // The route-level throttle is per IP; rotating IPs is exactly what it cannot stop.
    for ($i = 0; $i < accountAttemptLimit(); $i++) {
        $this->withHeaders(rotatingIp($i))
            ->postJson('/api/v1/auth/login', [
                'guard' => 'applicant',
                'email' => $applicant->email,
                'password' => 'wrong-'.$i,
            ]);
    }

    $this->withHeaders(['X-Forwarded-For' => '203.0.113.79'])
        ->postJson('/api/v1/auth/login', [
            'guard' => 'applicant',
            'email' => $applicant->email,
            'password' => LOCKOUT_PASSWORD,
        ])
        ->assertStatus(429)
        ->assertJsonMissingPath('token');
});

test('respondent sign-in never creates an account for an unknown email', function (): void {
    $email = 'never-registered-'.Str::lower(Str::random(8)).'@example.com';

    $this->post(route('applicant.login.submit'), [
        'email' => $email,
        'password' => 'Whatever123!',
        'login_as' => 'respondent',
    ])->assertSessionHasErrors('email');

    $this->assertGuest('applicant');
    expect(Applicant::query()->where('email', $email)->exists())->toBeFalse();
});

test('respondent sign-in answers an unknown email exactly like a wrong password', function (): void {
    $applicant = lockoutApplicant();

    $unknown = $this->post(route('applicant.login.submit'), [
        'email' => 'nobody-'.Str::lower(Str::random(8)).'@example.com',
        'password' => 'Whatever123!',
        'login_as' => 'respondent',
    ]);
    $unknownError = session('errors')->get('email')[0];

    $this->flushSession();

    $wrongPassword = $this->post(route('applicant.login.submit'), [
        'email' => $applicant->email,
        'password' => 'not-the-password',
        'login_as' => 'respondent',
    ]);
    $wrongPasswordError = session('errors')->get('email')[0];

    expect($unknown->status())->toBe($wrongPassword->status())
        ->and($unknownError)->toBe($wrongPasswordError);
});

test('an existing account can still sign in as a respondent', function (): void {
    $applicant = lockoutApplicant();

    $this->post(route('applicant.login.submit'), [
        'email' => $applicant->email,
        'password' => LOCKOUT_PASSWORD,
        'login_as' => 'respondent',
    ])->assertRedirect(route('respondent.dashboard'));

    $this->assertAuthenticatedAs($applicant, 'applicant');
    expect(session('acting_as_respondent'))->toBeTrue();
});
