<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\MfaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;

/*
 * The sign-in, forced password change, second factor and profile screens are
 * React pages from EUISIS; these tests pin the props court-ms gives them and
 * the requests they send.
 */

function accountAdmin(array $attributes = []): User
{
    $user = User::factory()->create(['status' => 'active', 'email_verified_at' => now(), 'password' => Hash::make('secret-password-1'), ...$attributes]);
    $user->roles()->syncWithoutDetaching([Role::query()->firstOrCreate(['name' => 'admin'])->id]);

    return $user;
}

test('the sign-in page is the EUISIS login with the reset link', function (): void {
    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Auth/Login')
            ->where('canResetPassword', true)
            ->where('registration_enabled', false));
});

test('a flagged user is sent to the forced password change and signs in again afterwards', function (): void {
    $user = accountAdmin(['must_change_password' => true]);

    $this->actingAs($user)->get(route('admin.dashboard'))->assertRedirect(route('password.force'));
    $this->actingAs($user)->get(route('password.force'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('Auth/ForcedPasswordChange'));

    $this->actingAs($user)
        ->post(route('password.forced.update'), ['current_password' => 'wrong', 'password' => 'New-password-123', 'password_confirmation' => 'New-password-123'])
        ->assertSessionHasErrors('current_password');

    $this->actingAs($user)
        ->post(route('password.forced.update'), ['current_password' => 'secret-password-1', 'password' => 'New-password-123', 'password_confirmation' => 'New-password-123'])
        ->assertRedirect(route('login'));

    expect($user->fresh()->must_change_password)->toBeFalse()
        ->and(Hash::check('New-password-123', $user->fresh()->password))->toBeTrue();
    $this->assertGuest();
});

test('opening the second-factor setup starts the enrolment and confirming it shows the recovery codes once', function (): void {
    $user = accountAdmin();

    $this->actingAs($user)->get(route('mfa.setup.show'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Auth/MfaSetup')
            ->where('secretKey', fn ($key) => is_string($key) && $key !== '')
            ->where('qrCodeUri', fn ($uri) => str_starts_with((string) $uri, 'data:image/svg+xml;base64,'))
            ->where('user.email', $user->email));

    $secret = (string) $user->fresh()->mfa_secret;
    expect($secret)->not->toBe('');

    $this->actingAs($user)
        ->post(route('mfa.setup.confirm'), ['code' => app(\PragmaRX\Google2FA\Google2FA::class)->getCurrentOtp($secret)])
        ->assertRedirect(route('mfa.setup.show'))
        ->assertSessionHas('mfa_recovery_codes');

    $this->actingAs($user)->get(route('mfa.setup.show'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Auth/MfaSetup')
            // (Inertia hands a closure a Collection for an array prop.)
            ->where('flash.mfa_recovery_codes_once', fn ($codes) => count($codes) === 8));

    // Confirmed and no codes to show: the Security tab takes over.
    $this->actingAs($user)->get(route('mfa.setup.show'))->assertRedirect(route('profile.edit').'#security');
});

test('the second-factor challenge accepts a recovery code in its own field', function (): void {
    $user = accountAdmin();
    $mfa = app(MfaService::class);
    $codes = $mfa->createRecoveryCodes();
    $user->forceFill(['mfa_secret' => $mfa->generateSecret(), 'mfa_recovery_codes' => $mfa->hashRecoveryCodes($codes), 'mfa_confirmed_at' => now()])->save();
    SystemSetting::query()->updateOrCreate([], ['mfa_enabled' => true]);

    $this->actingAs($user)->get(route('mfa.challenge.show'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('Auth/MfaChallenge')->where('user.email', $user->email));

    $this->actingAs($user)->post(route('mfa.challenge.store'), ['code' => '', 'recovery_code' => 'not-a-code'])
        ->assertSessionHasErrors('recovery_code');

    $this->actingAs($user)->post(route('mfa.challenge.store'), ['code' => '', 'recovery_code' => $codes[0]])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('mfa_verified_at');
});

test('the profile page carries the account, security and devices props and saves the account fields', function (): void {
    $user = accountAdmin(['phone' => '+251911000000']);

    $this->actingAs($user)->get(route('profile.edit'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Profile/Edit')
            ->where('profile.email', $user->email)
            ->where('profile.phone_number', '+251911000000')
            ->where('security.two_factor_enabled', false)
            ->has('devices.sessions')
            ->where('devices.supported', config('session.driver') === 'database'));

    $this->actingAs($user)
        ->patch(route('profile.update'), ['name' => 'Court Clerk', 'email' => $user->email, 'phone_number' => '+251922111222', 'gender' => 'female', 'national_id' => '1234567890123456'])
        ->assertRedirect(route('profile.edit'));

    $fresh = $user->fresh();
    expect($fresh->name)->toBe('Court Clerk')
        ->and($fresh->phone)->toBe('+251922111222')
        ->and($fresh->gender)->toBe('female')
        ->and($fresh->national_id)->toBe('1234567890123456');

    $this->actingAs($user)
        ->patch(route('profile.update'), ['name' => 'Court Clerk', 'email' => $user->email, 'phone_number' => 'abc', 'gender' => '', 'national_id' => ''])
        ->assertSessionHasErrors(['phone_number']);
});

test('the profile password form writes its errors to the default bag', function (): void {
    $user = accountAdmin();

    $this->actingAs($user)
        ->put(route('password.update'), ['current_password' => 'wrong', 'password' => 'New-password-123', 'password_confirmation' => 'New-password-123'])
        ->assertSessionHasErrors('current_password');
    $this->actingAs($user)
        ->put(route('password.update'), ['current_password' => 'secret-password-1', 'password' => 'New-password-123', 'password_confirmation' => 'New-password-123'])
        ->assertSessionHas('status', 'password-updated');
});

test('another device can be signed out from the Devices tab', function (): void {
    config(['session.driver' => 'database']);
    $user = accountAdmin();
    DB::table('sessions')->insert(['id' => 'other-device', 'user_id' => $user->id, 'ip_address' => '10.0.0.2', 'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1', 'payload' => base64_encode(serialize([])), 'last_activity' => now()->getTimestamp()]);

    $this->actingAs($user)->get(route('profile.edit'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('devices.sessions', fn ($sessions) => collect($sessions)->contains(fn ($row) => $row['id'] === 'other-device' && $row['device'] === 'phone' && $row['browser'] === 'Safari')));

    $this->actingAs($user)->delete(route('profile.sessions.destroy', 'other-device'))->assertRedirect();
    expect(DB::table('sessions')->where('id', 'other-device')->exists())->toBeFalse();

    $this->actingAs($user)->get(route('profile.sessions.index'))->assertRedirect(route('profile.edit').'#devices');
});
