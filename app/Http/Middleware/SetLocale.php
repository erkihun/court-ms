<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $locale = session('app_locale', config('app.locale'));

        // Normalize variants like "am_ET" → "am"
        if (is_string($locale) && str_starts_with($locale, 'am')) {
            $locale = 'am';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
