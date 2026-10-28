<?php

declare(strict_types=1);

namespace App\Support\Ui;

use App\Models\HomeSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * What the React public layout (resources/js/court/public/PublicLayout.tsx)
 * shows around a page: the landing manager's footer settings and the user
 * manual link. Read through the same cache keys, with the same values, as
 * the home page (PublicHomeController), so the landing manager's flush
 * refreshes both. The Blade home page's rules hold on every public page: the
 * footer description is English-only text, so Amharic falls back to the
 * translation, and the manual is linked once it is switched on and has
 * content, under the title saved for the request's language.
 */
final class PublicSiteChrome
{
    private const FOOTER_FIELDS = [
        'description', 'contact_phone', 'contact_email', 'contact_address',
        'social_facebook', 'social_twitter', 'social_linkedin', 'social_youtube', 'social_telegram', 'social_instagram',
    ];

    /** @return array{footer: array<string, string|null>|null, userManualEnabled: bool, userManualTitle: string|null} */
    public static function props(): array
    {
        $footer = Cache::remember('home_footer', 3600,
            fn () => Schema::hasTable('home_settings') ? HomeSetting::getJson('footer.settings') : null
        );
        $manual = Cache::remember('home_user_manual', 3600,
            fn () => Schema::hasTable('home_settings') ? HomeSetting::getJson('user_manual.settings') : []
        );
        $manual = is_array($manual) ? $manual : [];
        $locale = app()->getLocale() === 'am' ? 'am' : 'en';

        $footer = is_array($footer) && $footer !== [] ? array_combine(
            self::FOOTER_FIELDS,
            array_map(fn (string $field): ?string => match (true) {
                ! isset($footer[$field]) || $footer[$field] === '' || ! is_scalar($footer[$field]) => null,
                // Social links land in an href: http(s) only, whatever was stored (EditorLink).
                str_starts_with($field, 'social_') => EditorLink::safe((string) $footer[$field], allowRelative: false),
                default => (string) $footer[$field],
            }, self::FOOTER_FIELDS),
        ) : null;
        if ($footer !== null && $locale === 'am') {
            $footer['description'] = null;
        }
        $title = trim((string) ($manual["title_{$locale}"] ?? ''));

        return [
            'footer' => $footer,
            'userManualEnabled' => (bool) ($manual['is_active'] ?? false)
                && (! empty($manual['content_en']) || ! empty($manual['content_am'])),
            'userManualTitle' => $title !== '' ? $title : null,
        ];
    }
}
