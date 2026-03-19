<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $locale = session('app_locale')
            ?? $request->cookie('app_locale')
            ?? config('app.locale');

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
}