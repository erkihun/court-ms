<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        // Admin-configured default locale (system settings) takes precedence over
        // the static config default. A user's explicit choice (session/cookie)
        // still overrides the admin default.
        $locale = session('app_locale')
            ?? $request->cookie('app_locale')
            ?? $this->defaultLocale();

        $allowed = config('app.locales', ['en', 'am']);
        if (!in_array($locale, $allowed, true) && !str_starts_with((string) $locale, 'am')) {
            $locale = config('app.fallback_locale', 'en');
        }

        if (is_string($locale) && str_starts_with($locale, 'am')) {
            $locale = 'am';
        }

        App::setLocale($locale);

        return $next($request);
    }

    /** The default locale configured by an admin, falling back to config. */
    private function defaultLocale(): string
    {
        try {
            if (Schema::hasTable('system_settings')) {
                $settings = Cache::remember(
                    'system_settings',
                    3600,
                    fn() => SystemSetting::query()->first()
                );

                $default = $settings?->default_locale;
                if (is_string($default) && $default !== '') {
                    return $default;
                }
            }
        } catch (\Throwable) {
            // Fall through to config default.
        }

        return config('app.locale', 'en');
    }
}
