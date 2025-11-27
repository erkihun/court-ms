<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        // allow only supported locales
        $allowed = ['en', 'am', 'am_ET'];
        if (!in_array($locale, $allowed, true)) {
            $locale = config('app.fallback_locale', 'en');
        }

        // normalize am variants
        if (str_starts_with($locale, 'am')) {
            $locale = 'am';
        }

        // PERSIST using the SAME KEY your middleware reads
        session(['app_locale' => $locale]);

        // apply for this immediate response (optional)
        App::setLocale($locale);

        // go back to previous or provided URL
        $return = $request->query('return');
        if (!$return) $return = url()->previous() ?: route('root');

        // use GROUP key for the flash text
        return redirect()->to($return);
    }
}
