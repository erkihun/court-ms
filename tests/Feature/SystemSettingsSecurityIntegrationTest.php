<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

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
    Cache::put('system_settings', false, 3600);

    $user = User::factory()->create(['must_change_password' => false]);

    $this->actingAs($user)
        ->get('/profile')
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
        // The page (Court/Settings/System) draws the posture card from these metrics.
        ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
            ->component('Court/Settings/System')
            ->where('tab', 'security')
            ->where('securityMetrics.strong_password', true)
            ->where('securityMetrics.strict_lockout', true)
            ->has('securityMetrics.score'));

    expect(config('session.lifetime'))->toBe(45);
});

test('saved SMTP password is never rendered into the settings page', function () {
    SystemSetting::query()->create([
        'app_name' => 'Court MS',
        'mail_enabled' => true,
        'mail_host' => 'smtp.example.test',
        'mail_password' => 'smtp-secret-value',
    ]);

    $this->actingAs(systemSettingsAdministrator())
        ->get(route('settings.system.edit', ['tab' => 'notifications']))
        ->assertOk()
        ->assertDontSee('smtp-secret-value', false)
        ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page->where('secretsSet.mail_password', true));
});

test('Telegram and SMS secrets never reach the page; blank keeps them and the remove box clears them', function () {
    $settings = SystemSetting::query()->create([
        'app_name' => 'Court MS',
        'telegram_bot_token' => 'telegram-secret-value',
        'sms_api_key' => 'sms-key-value',
        'sms_api_secret' => 'sms-secret-value',
    ]);
    $admin = systemSettingsAdministrator();

    $this->actingAs($admin)
        ->get(route('settings.system.edit', ['tab' => 'notifications']))
        ->assertOk()
        ->assertDontSee('telegram-secret-value', false)
        ->assertDontSee('sms-key-value', false)
        ->assertDontSee('sms-secret-value', false)
        ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
            ->where('secretsSet.telegram_bot_token', true)
            ->where('secretsSet.sms_api_key', true)
            ->missing('settings.telegram_bot_token')
            ->missing('settings.sms_api_secret'));

    $this->actingAs($admin)->post(route('settings.system.update'), [
        'app_name' => 'Court MS',
        'telegram_bot_token' => '',
        'sms_api_key' => 'new-sms-key',
        'clear_secrets' => ['sms_api_secret'],
        'tab' => 'notifications',
    ])->assertRedirect(route('settings.system.edit', ['tab' => 'notifications']));

    $settings->refresh();
    expect($settings->telegram_bot_token)->toBe('telegram-secret-value')
        ->and($settings->sms_api_key)->toBe('new-sms-key')
        ->and($settings->sms_api_secret)->toBeNull();
});

test('SMTP secrets are not flashed back after validation errors', function () {
    SystemSetting::query()->create(['app_name' => 'Court MS']);

    $this->actingAs(systemSettingsAdministrator())
        ->from(route('settings.system.edit', ['tab' => 'notifications']))
        ->post(route('settings.system.update'), [
            'app_name' => '',
            'mail_password' => 'smtp-secret-value',
        ])
        ->assertSessionHasErrors('app_name');

    expect(session()->getOldInput('mail_password'))->toBeNull();
});

test('authorized administrator can send a synchronous SMTP diagnostic email', function () {
    Mail::fake();
    Queue::fake();

    SystemSetting::query()->create(['app_name' => 'Court MS']);

    $this->actingAs(systemSettingsAdministrator())
        ->post(route('settings.system.testSmtp'), ['recipient' => 'admin@example.test'])
        ->assertRedirect(route('settings.system.edit', ['tab' => 'notifications']))
        ->assertSessionHasNoErrors();

    Mail::assertOutgoingCount(1);
    Queue::assertNotPushed(SendQueuedNotifications::class);
});

test('SMTP diagnostic endpoint requires settings permission', function () {
    $user = User::factory()->create(['must_change_password' => false]);

    $this->actingAs($user)
        ->post(route('settings.system.testSmtp'), ['recipient' => 'admin@example.test'])
        ->assertRedirect(route('dashboard'));
});

test('closed registration setting blocks applicant self registration', function () {
    SystemSetting::query()->create([
        'app_name' => 'Court MS',
        'registration_open' => false,
    ]);

    $this->get('/applicant/register')->assertForbidden();
});
