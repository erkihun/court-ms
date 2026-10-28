<?php

declare(strict_types=1);

namespace App\Support\Ui;

use App\Models\HomeSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * What the React public layout (resources/js/court/public/PublicLayout.tsx)
 * shows around a page: the landing manager's footer settings and whether the
 * user manual is linked. Read through the same cache keys, with the same
 * values, as the home page (routes/web.php `landing.home`), so the landing
 * manager's flush refreshes both.
 */
final class PublicSiteChrome
{
    private const FOOTER_FIELDS = [
        'description', 'contact_phone', 'contact_email', 'contact_address',
        'social_facebook', 'social_twitter', 'social_linkedin', 'social_youtube', 'social_telegram', 'social_instagram',
    ];

    /** @return array{footer: array<string, string|null>|null, userManualEnabled: bool} */
    public static function props(): array
    {
        $footer = Cache::remember('home_footer', 3600,
            fn () => Schema::hasTable('home_settings') ? HomeSetting::getJson('footer.settings') : null
        );
        $manual = Cache::remember('home_user_manual', 3600,
            fn () => Schema::hasTable('home_settings') ? HomeSetting::getJson('user_manual.settings') : []
        );

        return [
            'footer' => $footer ? array_combine(
                self::FOOTER_FIELDS,
                array_map(fn (string $field): ?string => isset($footer[$field]) && $footer[$field] !== '' ? (string) $footer[$field] : null, self::FOOTER_FIELDS),
            ) : null,
            'userManualEnabled' => (bool) ($manual['is_active'] ?? false),
        ];
    }
}
