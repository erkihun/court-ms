<?php

declare(strict_types=1);

namespace App\Support\Ui;

use App\Models\Applicant;
use App\Support\EuisisParties;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/**
 * The applicant and respondent portals in the EUISIS shell: which portal a
 * request belongs to, who is signed in to it, its sidebar (the links of the
 * Blade portal layout, components/applicant-layout) and the routes the header
 * uses for home, profile and sign-out. A person files cases as an applicant
 * and answers them as a respondent with the same account (the applicant guard).
 */
final class PortalNavigation
{
    /** @var array<string, list<array{label_key: string, groups: list<array{key: string, label_key: string, icon: string, items: list<array{route: string, label_key: string, icon: string}>}>}>> */
    private const SECTIONS = [
        'applicant' => [[
            'label_key' => 'cm.app.court_portal',
            'groups' => [[
                'key' => 'applicantCases', 'label_key' => 'cm.app.my_cases', 'icon' => 'ClipboardListIcon',
                'items' => [
                    ['route' => 'applicant.cases.index', 'label_key' => 'cm.app.my_cases', 'icon' => 'ClipboardListIcon'],
                    ['route' => 'applicant.cases.create', 'label_key' => 'cm.app.new_case', 'icon' => 'FilePenIcon'],
                    ['route' => 'applicant.response-replies.index', 'label_key' => 'cm.respondent.applicant_response_replies', 'icon' => 'ClipboardTextIcon'],
                    ['route' => 'applicant.notifications.index', 'label_key' => 'cm.app.notifications', 'icon' => 'ClipboardClockIcon'],
                    ['route' => 'applicant.profile.edit', 'label_key' => 'cm.app.profile', 'icon' => 'UserCircleIcon'],
                ],
            ]],
        ]],
        'respondent' => [[
            'label_key' => 'cm.respondent.respondent_view',
            'groups' => [[
                'key' => 'respondentCases', 'label_key' => 'cm.respondent.my_cases', 'icon' => 'ClipboardListIcon',
                'items' => [
                    ['route' => 'respondent.case.search', 'label_key' => 'cm.respondent.find_case', 'icon' => 'FileSearchIcon'],
                    ['route' => 'respondent.cases.my', 'label_key' => 'cm.respondent.my_cases', 'icon' => 'ClipboardListIcon'],
                    ['route' => 'respondent.responses.index', 'label_key' => 'cm.respondent.my_responses', 'icon' => 'FilePenIcon'],
                    ['route' => 'respondent.profile.edit', 'label_key' => 'cm.respondent.profile', 'icon' => 'UserCircleIcon'],
                ],
            ]],
        ]],
    ];

    /**
     * The header's routes. `feed`, `read`, `readAll` and `viewAll` are the notification bell's
     * (PortalNotificationFeedController); the respondent portal lists its notifications on its dashboard.
     *
     * @var array<string, array{home: string, profile: string, logout: string, switch: ?string, feed: string, read: string, readAll: string, viewAll: string}>
     */
    private const ROUTES = [
        'applicant' => [
            'home' => 'applicant.dashboard', 'profile' => 'applicant.profile.edit', 'logout' => 'applicant.logout', 'switch' => 'respondent.dashboard',
            'feed' => 'applicant.notifications.feed', 'read' => 'applicant.notifications.read', 'readAll' => 'applicant.notifications.read-all', 'viewAll' => 'applicant.notifications.index',
        ],
        'respondent' => [
            'home' => 'respondent.dashboard', 'profile' => 'respondent.profile.edit', 'logout' => 'respondent.logout', 'switch' => 'respondent.switchToApplicant',
            'feed' => 'respondent.notifications.feed', 'read' => 'respondent.notifications.read', 'readAll' => 'respondent.notifications.read-all', 'viewAll' => 'respondent.dashboard',
        ],
    ];

    /** 'applicant', 'respondent' or null (the staff application). Read at render time, after the route's guard middleware. */
    public static function portalFor(Request $request): ?string
    {
        if ($request->is('respondent', 'respondent/*', 'applicant/respondent/*')) {
            return 'respondent';
        }
        if ($request->is('applicant', 'applicant/*')) {
            return 'applicant';
        }
        $driver = Auth::getDefaultDriver();

        return in_array($driver, ['applicant', 'respondent'], true) ? $driver : null;
    }

    public static function user(): ?Applicant
    {
        $user = Auth::guard('applicant')->user();

        return $user instanceof Applicant ? $user : null;
    }

    /** @return list<array<string, mixed>> */
    public static function sections(string $portal): array
    {
        return collect(self::SECTIONS[$portal] ?? [])
            ->map(fn (array $section): array => [
                ...$section,
                'groups' => collect($section['groups'])
                    // With EUISIS parties only, new cases are filed from the EUISIS employee portal.
                    ->map(fn (array $group): array => [...$group, 'items' => array_values(array_filter($group['items'], fn (array $item): bool => Route::has($item['route'])
                        && ! ($item['route'] === 'applicant.cases.create' && EuisisParties::only())))])
                    ->filter(fn (array $group): bool => $group['items'] !== [])
                    ->values()->all(),
            ])
            ->values()->all();
    }

    /** @return array{name: string, home: string, profile: string, logout: string, switch: ?string, feed: string, read: string, readAll: string, viewAll: string, filesInEuisis: bool} */
    public static function routes(string $portal): array
    {
        return ['name' => $portal, ...self::ROUTES[$portal], 'filesInEuisis' => EuisisParties::only()];
    }
}
