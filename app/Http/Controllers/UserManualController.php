<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\HomeSetting;
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

        return Inertia::render('Public/UserManual', [
            'title' => (string) (($settings["title_{$locale}"] ?? null) ?: __('home.nav.user_manual')),
            // Editor HTML, purified on output. The 'default' profile keeps the
            // tables, headings and inline styles a manual needs.
            'contentHtml' => clean($content, 'default'),
        ]);
    }
}
