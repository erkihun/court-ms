<?php

namespace App\Providers;

use App\Listeners\RecordAuthEvent;
use App\Listeners\RecordModelMutation;
use App\Models\SystemSetting;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RateLimiter::for('api-ip-hourly', function (Request $request) {
            return Limit::perHour(100)->by($request->ip() ?: 'unknown-ip');
        });

        // ── Auth event audit trail ──────────────────────────────────────────
        Event::listen(Login::class, [RecordAuthEvent::class, 'handleLogin']);
        Event::listen(Logout::class, [RecordAuthEvent::class, 'handleLogout']);
        Event::listen(Failed::class, [RecordAuthEvent::class, 'handleFailed']);
        Event::listen(Lockout::class, [RecordAuthEvent::class, 'handleLockout']);
        Event::listen(PasswordReset::class, [RecordAuthEvent::class, 'handlePasswordReset']);

        foreach (['created', 'updated', 'deleted', 'restored'] as $modelEvent) {
            Event::listen("eloquent.{$modelEvent}: *", function (string $eventName, array $payload): void {
                app(RecordModelMutation::class)->handle($eventName, $payload);
            });
        }

        // ── Password policy ─────────────────────────────────────────────────
        // Enforces the admin-configured rules (system settings) for every
        // guard that validates with Password::defaults(). Minimum length is
        // floored at 8 regardless of the stored setting (security baseline).
        Password::defaults(function () {
            $min = 8;
            $upper = true;
            $number = true;
            $symbol = false;

            try {
                if (Schema::hasTable('system_settings')) {
                    $s = SystemSetting::cached();
                    if ($s) {
                        $min = max(8, (int) ($s->password_min_length ?? 8));
                        $upper = (bool) ($s->password_require_uppercase ?? true);
                        $number = (bool) ($s->password_require_number ?? true);
                        $symbol = (bool) ($s->password_require_symbol ?? false);
                    }
                }
            } catch (\Throwable) {
                // Settings unavailable (e.g. during migrations) — keep the baseline.
            }

            $rule = Password::min($min);
            if ($upper) {
                $rule->mixedCase();
            }
            if ($number) {
                $rule->numbers();
            }
            if ($symbol) {
                $rule->symbols();
            }

            return $rule;
        });

        try {
            $purifierCachePath = config('purifier.cachePath');
            if ($purifierCachePath) {
                File::ensureDirectoryExists($purifierCachePath, 0755, true);
                File::ensureDirectoryExists($purifierCachePath.DIRECTORY_SEPARATOR.'CSS', 0755, true);
                File::ensureDirectoryExists($purifierCachePath.DIRECTORY_SEPARATOR.'HTML', 0755, true);
                File::ensureDirectoryExists($purifierCachePath.DIRECTORY_SEPARATOR.'URI', 0755, true);
            }
        } catch (\Throwable) {
            // Don't block the app if the directory can't be created
        }
    }
}
