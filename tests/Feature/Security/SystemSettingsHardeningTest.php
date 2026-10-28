<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

/*
 * System settings secrets (SMTP password, Telegram bot token, SMS key and
 * secret) are write-only: no page, shared prop or flashed old input ever
 * carries them back; a blank field keeps the stored value and only the
 * remove box clears it.
 */

const SETTINGS_HARDENING_SECRETS = [
    'mail_password' => 'smtp-secret-7f3a',
    'telegram_bot_token' => '123456:telegram-secret-9b1c',
    'sms_api_key' => 'sms-key-4d2e',
    'sms_api_secret' => 'sms-secret-8a6f',
];

function settingsHardeningAdmin(): User
{
    $user = User::factory()->create(['status' => 'active', 'email_verified_at' => now(), 'must_change_password' => false]);
    $user->roles()->attach(Role::query()->firstOrCreate(['name' => 'admin']));

    return $user;
}

test('stored secrets never reach the settings page or any shared page props', function (): void {
    SystemSetting::query()->create(['app_name' => 'Court MS', ...SETTINGS_HARDENING_SECRETS]);
    $admin = settingsHardeningAdmin();

    foreach (['general', 'notifications', 'security'] as $tab) {
        $response = $this->actingAs($admin)->get(route('settings.system.edit', ['tab' => $tab]))->assertOk();
        foreach (SETTINGS_HARDENING_SECRETS as $secret) {
            $response->assertDontSee($secret, false);
        }
    }

    $this->actingAs($admin)->get(route('settings.system.edit', ['tab' => 'notifications']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('secretsSet', ['mail_password' => true, 'telegram_bot_token' => true, 'sms_api_key' => true, 'sms_api_secret' => true])
            ->missing('settings.mail_password')
            ->missing('settings.telegram_bot_token')
            ->missing('settings.sms_api_key')
            ->missing('settings.sms_api_secret'));

    // The settings every page shares (SharedSettings) carry none of them either.
    foreach ([route('admin.dashboard'), route('landing.home')] as $url) {
        $response = $this->actingAs($admin)->get($url)->assertOk();
        foreach (SETTINGS_HARDENING_SECRETS as $secret) {
            $response->assertDontSee($secret, false);
        }
    }
});

test('secrets are not flashed back after a validation error, and blank keeps them', function (): void {
    $settings = SystemSetting::query()->create(['app_name' => 'Court MS', ...SETTINGS_HARDENING_SECRETS]);
    $admin = settingsHardeningAdmin();

    $this->actingAs($admin)->from(route('settings.system.edit', ['tab' => 'notifications']))
        ->post(route('settings.system.update'), [
            'app_name' => '',
            'telegram_bot_token' => 'typed-telegram-token',
            'sms_api_key' => 'typed-sms-key',
            'sms_api_secret' => 'typed-sms-secret',
            'mail_password' => 'typed-smtp-password',
        ])
        ->assertSessionHasErrors('app_name');

    foreach (['telegram_bot_token', 'sms_api_key', 'sms_api_secret', 'mail_password'] as $field) {
        expect(session()->getOldInput($field))->toBeNull();
    }

    $this->actingAs($admin)->post(route('settings.system.update'), [
        'app_name' => 'Court MS', 'telegram_bot_token' => '', 'sms_api_key' => '', 'sms_api_secret' => '', 'mail_password' => '',
        'clear_secrets' => ['sms_api_secret'], 'tab' => 'notifications',
    ])->assertSessionHasNoErrors();

    $settings->refresh();
    expect($settings->telegram_bot_token)->toBe(SETTINGS_HARDENING_SECRETS['telegram_bot_token'])
        ->and($settings->sms_api_key)->toBe(SETTINGS_HARDENING_SECRETS['sms_api_key'])
        ->and($settings->mail_password)->toBe(SETTINGS_HARDENING_SECRETS['mail_password'])
        ->and($settings->sms_api_secret)->toBeNull();
});
