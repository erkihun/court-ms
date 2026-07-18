<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\CreateDatabaseBackupAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSystemSettingRequest;
use App\Models\Role;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemSettingController extends Controller
{
    public function edit(): View
    {
        $settings = SystemSetting::current();

        $timezones = \DateTimeZone::listIdentifiers();
        $locales = config('app.locales', ['en', 'am']);
        $localeNames = config('app.locale_names', ['en' => 'English', 'am' => 'Amharic']);
        $databaseMetrics = $this->databaseMetrics();
        $roles = Role::query()->orderBy('name')->get(['id', 'name', 'description', 'mfa_required']);

        $securityMetrics = $this->securityMetrics($settings);

        return view('admin.settings.system', compact('settings', 'timezones', 'locales', 'localeNames', 'databaseMetrics', 'securityMetrics', 'roles'));
    }

    public function update(UpdateSystemSettingRequest $request): RedirectResponse
    {
        $data = $request->validate([
            // Identity
            'app_name' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:50'],
            'welcome_message' => ['nullable', 'string', 'max:500'],
            'about' => ['nullable', 'string'],
            'footer_text' => ['nullable', 'string', 'max:500'],
            'address' => ['nullable', 'string', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],

            // Branding files
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'banner' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:3072'],
            'favicon' => ['nullable', 'file', 'mimes:png,ico', 'max:512'],
            'seal' => ['nullable', 'image', 'mimes:png', 'max:1024'],

            // Localization
            'default_locale' => ['nullable', 'string', 'max:10'],
            'show_language_switcher' => ['nullable', 'boolean'],
            'timezone' => ['nullable', 'string', 'timezone'],
            'date_format' => ['nullable', 'string', 'max:30'],
            'time_format' => ['nullable', 'string', 'max:10'],
            'use_ethiopian_calendar' => ['nullable', 'boolean'],

            // Security
            'maintenance_mode' => ['nullable', 'boolean'],
            'registration_open' => ['nullable', 'boolean'],
            'force_https' => ['nullable', 'boolean'],
            'mfa_enabled' => ['nullable', 'boolean'],
            'audit_logging_enabled' => ['nullable', 'boolean'],
            'audit_retention_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'mfa_roles' => ['nullable', 'array'],
            'mfa_roles.*' => ['integer', 'exists:roles,id'],
            'session_lifetime' => ['nullable', 'integer', 'min:5', 'max:43200'],
            'session_warning_minutes' => ['nullable', 'integer', 'min:1', 'max:120'],
            'session_extend_on_activity' => ['nullable', 'boolean'],
            'password_min_length' => ['nullable', 'integer', 'min:6', 'max:128'],
            'password_require_uppercase' => ['nullable', 'boolean'],
            'password_require_number' => ['nullable', 'boolean'],
            'password_require_symbol' => ['nullable', 'boolean'],
            'login_max_attempts' => ['nullable', 'integer', 'min:1', 'max:100'],
            'lockout_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],

            // Appearance
            'accent_palette' => ['nullable', 'string', 'in:blue,teal,violet,emerald,rose'],
            'default_theme' => ['nullable', 'string', 'in:light,dark,system'],
            'show_banner_on_login' => ['nullable', 'boolean'],
            'custom_css' => ['nullable', 'string', 'max:10000'],

            // API
            'api_enabled' => ['nullable', 'boolean'],
            'api_rate_limit' => ['nullable', 'integer', 'min:1', 'max:10000'],

            // Email (SMTP)
            'mail_enabled' => ['nullable', 'boolean'],
            'mail_mailer' => ['nullable', 'string', 'in:smtp,sendmail,log'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:500'],
            'mail_encryption' => ['nullable', 'string', 'in:tls,ssl,starttls,none'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],

            // Telegram
            'telegram_enabled' => ['nullable', 'boolean'],
            'telegram_bot_token' => ['nullable', 'string', 'max:500'],
            'telegram_default_chat_id' => ['nullable', 'string', 'max:100'],

            // SMS
            'sms_enabled' => ['nullable', 'boolean'],
            'sms_provider' => ['nullable', 'string', 'max:100'],
            'sms_api_key' => ['nullable', 'string', 'max:500'],
            'sms_api_secret' => ['nullable', 'string', 'max:500'],
            'sms_sender_id' => ['nullable', 'string', 'max:20'],
            'sms_base_url' => ['nullable', 'url', 'max:255'],
        ]);

        $settings = SystemSetting::first() ?? new SystemSetting;

        // Scalar fields
        $scalar = [
            'app_name', 'short_name', 'welcome_message', 'about', 'footer_text',
            'address', 'website_url', 'contact_email', 'contact_phone',
            'default_locale', 'timezone', 'date_format', 'time_format',
            'session_lifetime', 'password_min_length',
            'session_warning_minutes', 'audit_retention_days',
            'login_max_attempts', 'lockout_minutes',
            'accent_palette', 'default_theme', 'custom_css', 'api_rate_limit',
            'mail_mailer', 'mail_host', 'mail_port', 'mail_username', 'mail_password',
            'mail_encryption', 'mail_from_address', 'mail_from_name',
            'telegram_bot_token', 'telegram_default_chat_id',
            'sms_provider', 'sms_api_key', 'sms_api_secret', 'sms_sender_id', 'sms_base_url',
        ];
        foreach ($scalar as $field) {
            $settings->$field = $data[$field] ?? null;
        }

        // Boolean fields (unchecked checkboxes are absent from request)
        $booleans = [
            'maintenance_mode', 'registration_open', 'force_https',
            'mfa_enabled',
            'audit_logging_enabled', 'session_extend_on_activity',
            'use_ethiopian_calendar', 'show_language_switcher',
            'password_require_uppercase', 'password_require_number', 'password_require_symbol',
            'show_banner_on_login', 'api_enabled',
            'mail_enabled', 'telegram_enabled', 'sms_enabled',
        ];
        foreach ($booleans as $field) {
            $settings->$field = $request->boolean($field);
        }

        // File uploads
        $uploads = [
            'logo' => ['field' => 'logo_path',    'dir' => 'logos'],
            'banner' => ['field' => 'banner_path',  'dir' => 'banners'],
            'favicon' => ['field' => 'favicon_path', 'dir' => 'favicons'],
            'seal' => ['field' => 'seal_path',    'dir' => 'seals'],
        ];
        foreach ($uploads as $input => $meta) {
            if ($request->hasFile($input)) {
                $settings->{$meta['field']} = $request->file($input)->store($meta['dir'], 'public');
            }
        }

        $settings->save();

        $mfaRoleIds = collect($data['mfa_roles'] ?? [])->map(fn ($id): int => (int) $id);
        Role::query()->update(['mfa_required' => false]);
        Role::query()->whereKey($mfaRoleIds)->update(['mfa_required' => true]);

        // Bust the cached settings so all views pick up the change immediately
        Cache::forget('system_settings');

        return redirect()
            ->route('settings.system.edit', ['tab' => $this->activeTab($request)])
            ->with('ok', [
                'key' => 'messages.success.updated',
                'replace' => ['resource' => __('messages.resources.system_settings')],
            ]);
    }

    public function clearCache(Request $request): RedirectResponse
    {
        Cache::forget('system_settings');
        try {
            Artisan::call('cache:clear');
        } catch (\Throwable) {
        }
        try {
            Artisan::call('view:clear');
        } catch (\Throwable) {
        }

        return redirect()
            ->route('settings.system.edit', ['tab' => $this->activeTab($request)])
            ->with('ok', ['key' => 'Cache cleared successfully.']);
    }

    private function activeTab(Request $request): string
    {
        $tab = (string) $request->input('tab', 'general');
        $valid = ['general', 'branding', 'localization', 'security', 'appearance', 'notifications', 'data', 'api'];

        return in_array($tab, $valid, true) ? $tab : 'general';
    }

    public function downloadDatabaseBackup(Request $request, CreateDatabaseBackupAction $backup): StreamedResponse
    {
        abort_unless($request->user()?->hasPermission('settings.manage'), 403);
        abort_unless($backup->supportsCurrentConnection(), 422, __('settings.backup_unsupported'));

        return response()->streamDownload(
            function () use ($backup): void {
                $stream = fopen('php://output', 'wb');

                if ($stream === false) {
                    return;
                }

                $backup->writeTo($stream);
            },
            $backup->filename(),
            ['Content-Type' => $backup->contentType()]
        );
    }

    /**
     * @return array<string, string|int|bool|null>
     */
    private function databaseMetrics(): array
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $database = (string) $connection->getDatabaseName();

        return [
            'driver' => $driver,
            'database' => $database,
            'connection' => config('database.default'),
            'table_count' => $this->tableCount($driver, $database),
            'size' => $this->databaseSize($driver, $database),
            'migration_batch' => $this->migrationBatch(),
            'backup_supported' => in_array($driver, ['mysql', 'sqlite'], true),
        ];
    }

    private function tableCount(string $driver, string $database): int
    {
        try {
            if ($driver === 'mysql') {
                return (int) DB::selectOne(
                    'select count(*) as aggregate from information_schema.tables where table_schema = ? and table_type = ?',
                    [$database, 'BASE TABLE']
                )->aggregate;
            }

            return count(Schema::getTables());
        } catch (\Throwable) {
            return 0;
        }
    }

    private function databaseSize(string $driver, string $database): ?string
    {
        try {
            $bytes = match ($driver) {
                'mysql' => (int) DB::selectOne(
                    'select coalesce(sum(data_length + index_length), 0) as bytes from information_schema.tables where table_schema = ?',
                    [$database]
                )->bytes,
                'sqlite' => $this->sqliteDatabaseSize(),
                default => null,
            };

            return $bytes === null ? null : $this->humanBytes($bytes);
        } catch (\Throwable) {
            return null;
        }
    }

    private function sqliteDatabaseSize(): ?int
    {
        $databasePath = (string) config('database.connections.sqlite.database');
        $realPath = realpath($databasePath);

        if ($databasePath === ':memory:' || $realPath === false || ! is_file($realPath)) {
            return null;
        }

        return filesize($realPath) ?: null;
    }

    private function migrationBatch(): ?int
    {
        try {
            return Schema::hasTable('migrations')
                ? (int) DB::table('migrations')->max('batch')
                : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function humanBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = (float) $bytes;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, $unit === 0 ? 0 : 2).' '.$units[$unit];
    }

    /** @return array<string, bool|int|string> */
    private function securityMetrics(SystemSetting $settings): array
    {
        $passwordStrength = min(50, max(8, (int) ($settings->password_min_length ?? 8)) * 3)
            + (($settings->password_require_uppercase ?? true) ? 15 : 0)
            + (($settings->password_require_number ?? true) ? 15 : 0)
            + (($settings->password_require_symbol ?? false) ? 20 : 0);
        $httpsEnforced = app()->environment('production') || (bool) ($settings->force_https ?? false);
        $controls = [
            'https_enforced' => $httpsEnforced,
            'debug_disabled' => ! (bool) config('app.debug'),
            'secure_cookies' => (bool) config('session.secure'),
            'strong_password' => $passwordStrength >= 80,
            'strict_lockout' => (int) ($settings->login_max_attempts ?? 5) <= 5
                && (int) ($settings->lockout_minutes ?? 15) >= 15,
            'mfa_configured' => (bool) ($settings->mfa_enabled ?? false)
                && Role::query()->where('mfa_required', true)->exists(),
        ];

        return [
            ...$controls,
            'score' => (int) round(count(array_filter($controls)) / count($controls) * 100),
            'environment' => app()->environment(),
            'session_driver' => (string) config('session.driver'),
        ];
    }
}
