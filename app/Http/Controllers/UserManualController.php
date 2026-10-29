<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\HomeSetting;
use App\Support\Ui\PublicSiteChrome;
use Inertia\Inertia;
use Inertia\Response;

class UserManualController extends Controller
{
    public function __invoke(): Response
    {
        $settings = HomeSetting::getJson('user_manual.settings');
        $locale = app()->getLocale() === 'am' ? 'am' : 'en';

        abort_unless((bool) ($settings['is_active'] ?? false), 404);

        $content = (string) ($settings["content_{$locale}"] ?? '');
        if ($content !== '' && strip_tags($content) === $content) {
            $paragraphs = preg_split('/\R{2,}/u', $content) ?: [];
            $content = implode('', array_map(
                static fn (string $paragraph): string => '<p>'.nl2br(e(trim($paragraph))).'</p>',
                $paragraphs,
            ));
        }

        return Inertia::render('Court/Public/UserManual', [
            'title' => (string) ($settings["title_{$locale}"] ?? __('home.nav.user_manual')),
            // Editor HTML, purified on output as well as on save (a manual saved
            // before HTMLPurifier was used may still hold anything). The default
            // profile keeps the tables, headings and inline styles a manual needs
            // (HTMLPurifier writes the platform's line endings; the page gets "\n").
            'content' => $content !== '' ? (string) preg_replace('/\r\n?/', "\n", (string) clean($content, 'default')) : '',
            'publicSite' => PublicSiteChrome::props(),
        ]);
    }
}
