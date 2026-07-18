<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function systemSettingsAdministrator(): User
{
    $user = User::factory()->create(['must_change_password' => false]);
    $role = Role::query()->create(['name' => 'admin']);
    $user->roles()->attach($role);

    return $user;
}

test('a poisoned system_settings cache key self-heals instead of crashing', function () {
    SystemSetting::query()->create(['app_name' => 'Court MS', 'maintenance_mode' => false]);

    // Simulate the old bug: a scalar cached under the shared settings key.
    \Illuminate\Support\Facades\Cache::put('system_settings', false, 3600);

    $user = User::factory()->create(['must_change_password' => false]);

    $this->actingAs($user)
        ->get('/profile/sessions')
        ->assertOk();

    expect(SystemSetting::cached())->toBeInstanceOf(SystemSetting::class);
});

test('system security settings require backend permission', function () {
    SystemSetting::query()->create(['app_name' => 'Court MS']);
    $user = User::factory()->create(['must_change_password' => false]);

    $this->actingAs($user)
        ->get(route('settings.system.edit'))
        ->assertRedirect(route('dashboard'));
});

test('security tab displays live integrated protection status', function () {
    SystemSetting::query()->create([
        'app_name' => 'Court MS',
        'session_lifetime' => 45,
        'password_min_length' => 12,
        'password_require_uppercase' => true,
        'password_require_number' => true,
        'password_require_symbol' => true,
        'login_max_attempts' => 5,
        'lockout_minutes' => 15,
    ]);

    $this->actingAs(systemSettingsAdministrator())
        ->get(route('settings.system.edit', ['tab' => 'security']))
        ->assertOk()
        ->assertSee(__('settings.security_posture'))
        ->assertSee(__('settings.password_strength'));

    expect(config('session.lifetime'))->toBe(45);
});

test('closed registration setting blocks applicant self registration', function () {
    SystemSetting::query()->create([
        'app_name' => 'Court MS',
        'registration_open' => false,
    ]);

    $this->get('/applicant/register')->assertForbidden();
});
