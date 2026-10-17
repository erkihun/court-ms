<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\AboutPage;
use App\Models\SystemSetting;

/**
 * Branding shown on the sign-in screens beyond the shared `app` props: the
 * login banner and the published "about" page.
 */
final class AuthScreen
{
    /**
     * The staff screen always shows the banner; the party screens honour the
     * "show banner on login" setting.
     */
    public static function bannerUrl(bool $respectLoginToggle = false): ?string
    {
        $settings = self::settings();
        if ($settings === null || blank($settings->banner_path)) {
            return null;
        }

        if ($respectLoginToggle && ! ($settings->show_banner_on_login ?? true)) {
            return null;
        }

        return asset('storage/'.$settings->banner_path);
    }

    /**
     * @return array{title: string, html: string}|null HTML is sanitised with the 'cases' purifier profile.
     */
    public static function aboutPage(): ?array
    {
        try {
            $page = AboutPage::query()
                ->where('is_published', true)
                ->orderByDesc('updated_at')
                ->first(['title', 'body']);
        } catch (\Throwable) {
            return null;
        }

        if ($page === null) {
            return null;
        }

        return [
            'title' => (string) $page->title,
            'html' => (string) clean($page->body ?? '', 'cases'),
        ];
    }

    private static function settings(): ?SystemSetting
    {
        try {
            return SystemSetting::cached();
        } catch (\Throwable) {
            return null;
        }
    }
}
