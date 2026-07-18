<?php

use App\Models\SystemSetting;
use App\Models\Applicant;
use App\Models\User;
use App\Services\MfaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

test('a user with active MFA is challenged after logging in again', function () {
    $user = User::factory()->create();
    $user->forceFill([
        'mfa_secret' => app(MfaService::class)->generateSecret(),
        'mfa_confirmed_at' => now(),
    ])->save();

    $settings = SystemSetting::query()->first() ?? SystemSetting::create(['mfa_enabled' => true]);
    $settings->forceFill(['mfa_enabled' => true])->save();
    Cache::forget('system_settings');

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/login');

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->get('/dashboard')
        ->assertRedirect(route('mfa.challenge.show'));
});

test('applicant dashboard is not processed by admin MFA middleware', function () {
    $applicant = Applicant::create([
        'first_name' => 'Abel',
        'middle_name' => 'K',
        'last_name' => 'Teka',
        'gender' => 'male',
        'position' => 'Manager',
        'organization_name' => 'ACME',
        'phone' => '0911000000',
        'email' => 'abel-dashboard@example.com',
        'address' => 'Addis Ababa',
        'national_id_number' => '1234567890123456',
        'password' => Hash::make('password'),
        'is_active' => true,
        'is_lawyer' => false,
    ]);
    $applicant->markEmailAsVerified();

    $this->actingAs($applicant, 'applicant')
        ->get(route('applicant.dashboard'))
        ->assertOk();
});
