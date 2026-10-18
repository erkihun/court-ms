<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\SystemSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    Cache::forget('system_settings');
    SystemSetting::query()->create(['app_name' => 'Court MS', 'default_locale' => 'en']);
});

test('the settings page sends form values, options and metrics but no secrets', function (): void {
    $this->actingAs(inertiaStaff(['settings.manage']))
        ->get(route('settings.system.edit', ['tab' => 'nonsense']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Settings/System')
            ->where('tab', 'general')
            ->where('settings.app_name', 'Court MS')
            ->where('settings.session_lifetime', '120')
            ->where('settings.registration_open', true)
            ->where('storedSecrets.mail_password', false)
            ->has('timezones')
            ->has('locales', fn (Assert $locales) => $locales->each(fn (Assert $locale) => $locale->hasAll(['code', 'name'])))
            ->has('roles')
            ->has('databaseMetrics.backup_supported')
            ->has('systemInfo', 10)
            ->where('csrfToken', fn ($token) => is_string($token) && $token !== ''));
});

test('a multipart save stores booleans, numbers, uploads and MFA roles and returns to the tab', function (): void {
    Storage::fake('public');
    $judge = Role::query()->create(['name' => 'judge-mfa']);
    $clerk = Role::query()->create(['name' => 'clerk-mfa', 'mfa_required' => true]);

    $this->actingAs(inertiaStaff(['settings.manage']))
        ->post(route('settings.system.update'), [
            'tab' => 'security',
            'app_name' => 'Federal Court',
            'registration_open' => '0',
            'maintenance_mode' => '1',
            'session_lifetime' => '60',
            'mfa_roles' => [$judge->id],
            'logo' => UploadedFile::fake()->image('logo.png', 40, 40),
            'accent_palette' => 'teal',
        ])
        ->assertRedirect(route('settings.system.edit', ['tab' => 'security']))
        ->assertSessionHasNoErrors();

    $settings = SystemSetting::query()->sole();
    expect($settings)
        ->app_name->toBe('Federal Court')
        ->registration_open->toBeFalse()
        ->maintenance_mode->toBeTrue()
        ->accent_palette->toBe('teal')
        ->and((int) $settings->session_lifetime)->toBe(60)
        ->and($settings->logo_path)->not->toBeNull()
        ->and($judge->fresh()->mfa_required)->toBeTrue()
        ->and($clerk->fresh()->mfa_required)->toBeFalse();
    Storage::disk('public')->assertExists($settings->logo_path);
});

test('clearing caches returns to the requested tab', function (): void {
    $this->actingAs(inertiaStaff(['settings.manage']))
        ->post(route('settings.system.clearCache'), ['tab' => 'api'])
        ->assertRedirect(route('settings.system.edit', ['tab' => 'api']));
});
