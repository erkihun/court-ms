<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        $allowed = config('app.locales', ['en', 'am']);
        $allowed = array_unique(array_merge($allowed, ['am_ET']));

        if (!in_array($locale, $allowed, true)) {
            $locale = config('app.fallback_locale', 'en');
        }

        if (str_starts_with($locale, 'am')) {
            $locale = 'am';
        }

        session(['app_locale' => $locale]);
        Cookie::queue('app_locale', $locale, 60 * 24 * 365);

        App::setLocale($locale);

        $return = $request->query('return');
        if (!$return) {
            $return = url()->previous() ?: route('root');
        }

        return redirect()->to($return);
    }
}
