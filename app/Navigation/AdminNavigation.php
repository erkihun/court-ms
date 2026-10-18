<?php

declare(strict_types=1);

namespace App\Navigation;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

/**
 * The staff sidebar, defined once and filtered per user.
 *
 * An entry is shown when the user holds its permission AND every `perm:`
 * middleware on its route, so the sidebar can never offer a link that would
 * be refused. Labels are translation keys resolved for the current locale.
 *
 * @phpstan-type NavLink array{type: 'link', key: string, label: string, href: string, icon: string, active: bool}
 * @phpstan-type NavGroup array{type: 'group', key: string, label: string, icon: string, active: bool, children: list<NavLink>}
 * @phpstan-type NavSection array{key: string, label: ?string, items: list<NavLink|NavGroup>}
 */
final class AdminNavigation
{
    public function __construct(private readonly Router $router) {}

    /**
     * @return list<NavSection>
     */
    public function for(User $user, Request $request): array
    {
        $sections = [];

        foreach ($this->definition() as $section) {
            $items = [];
            foreach ($section['items'] as $entry) {
                $built = $entry['type'] === 'group'
                    ? $this->buildGroup($entry, $user, $request)
                    : $this->buildLink($entry, $user, $request);

                if ($built !== null) {
                    $items[] = $built;
                }
            }

            if ($items !== []) {
                $sections[] = [
                    'key' => $section['key'],
                    'label' => $section['label'] !== null ? (string) __($section['label']) : null,
                    'items' => $items,
                ];
            }
        }

        return $sections;
    }

    /**
     * @return list<array{key: string, label: ?string, items: list<array<string, mixed>>}>
     */
    private function definition(): array
    {
        return [
            ['key' => 'main', 'label' => null, 'items' => [
                self::link('dashboard', 'app.Dashboard', 'home', ['dashboard']),
            ]],
            ['key' => 'cases', 'label' => 'app.case_management', 'items' => [
                self::link('appeals.index', 'app.Appeals', 'shield-check', ['appeals.*'], 'appeals.view'),
                self::group('decisions', 'app.Decisions', 'document-text', [
                    self::link('decisions.index', 'app.Decisions', 'document-text', ['decisions.*'], 'decision.view'),
                    self::link('decision-templates.index', 'decision_templates.title', 'document-duplicate', ['decision-templates.*'], 'decision.templet.view'),
                ]),
                self::link('cases.index', 'app.Cases', 'briefcase', ['cases.*'], 'cases.view'),
                self::group('inspections', 'app.case_inspections', 'clipboard-document-check', [
                    self::link('case-inspection-requests.index', 'case_inspections.requests.index_title', 'document-text', ['case-inspection-requests.*'], 'inspection-requests.manage'),
                    self::link('case-inspection-findings.index', 'case_inspections.findings.index_title', 'clipboard', ['case-inspection-findings.*'], 'inspection-findings.manage'),
                ]),
                self::link('admin.hearings.index', 'app.Hearings', 'calendar-days', ['admin.hearings.*'], 'cases.view'),
                self::link('recordes.index', 'app.Records', 'bookmark', ['recordes.*'], 'cases.view'),
                self::link('reports.index', 'app.Reports', 'chart-bar', ['reports.*'], 'reports.view'),
                self::link('performance-evaluations.index', 'app.performance_evaluations', 'chart-bar-square', ['performance-evaluations.*'], 'performance-evaluations.view'),
                self::link('applicants.index', 'app.Applicant', 'user-group', ['applicants.*'], 'applicants.view'),
                self::link('case-types.index', 'app.case_types', 'tag', ['case-types.*'], 'cases.types'),
            ]],
            ['key' => 'communication', 'label' => 'app.communication', 'items' => [
                self::group('letters', 'app.Letters', 'envelope', [
                    self::link('letter-templates.index', 'app.letter_templates', 'document-duplicate', ['letter-templates.*'], 'templates.manage'),
                    self::link('letter-categories.index', 'app.letter_categories', 'rectangle-stack', ['letter-categories.*'], 'templates.manage'),
                    self::link('letters.index', 'app.Letters', 'envelope-open', ['letters.index', 'letters.show'], 'cases.edit'),
                    self::link('letters.compose', 'app.compose_letter', 'pencil-square', ['letters.compose'], 'cases.edit'),
                ]),
                self::link('admin.notifications.index', 'app.Notifications', 'bell', ['admin.notifications.*']),
                self::link('announcements.index', 'app.Announcements', 'megaphone', ['announcements.*'], 'announcements.manage'),
            ]],
            ['key' => 'people', 'label' => 'app.users_access', 'items' => [
                self::group('user-control', 'app.user_control', 'users', [
                    self::link('users.index', 'app.Users', 'user', ['users.*'], 'users.manage'),
                    self::link('permissions.index', 'app.Permissions', 'key', ['permissions.*'], 'permissions.manage'),
                    self::link('teams.index', 'app.Teams', 'squares-2x2', ['teams.*'], 'teams.manage'),
                    self::link('roles.index', 'app.Roles', 'check-badge', ['roles.*'], 'roles.manage'),
                ]),
            ]],
            ['key' => 'system', 'label' => 'app.system_section', 'items' => [
                self::group('settings', 'app.Settings', 'cog-6-tooth', [
                    self::link('settings.system.edit', 'app.System_Settings', 'adjustments-horizontal', ['settings.system.*'], 'settings.manage'),
                    self::link('settings.performance-evaluation.index', 'performance.settings.title', 'chart-bar-square', ['settings.performance-evaluation.*'], 'settings.manage'),
                    self::link('terms.index', 'app.Terms', 'document-text', ['terms.*'], 'settings.manage'),
                    self::link('about.index', 'app.About', 'information-circle', ['about.*'], 'about.manage'),
                    self::link('admin.landing.index', 'admin_landing.page_title', 'home', ['admin.landing.*'], 'settings.manage'),
                    self::link('admin.audit', 'app.System_Audit', 'eye', ['admin.audit'], 'audit.view'),
                ]),
            ]],
        ];
    }

    /**
     * @param  list<string>  $activeRoutes
     * @return array<string, mixed>
     */
    private static function link(string $route, string $label, string $icon, array $activeRoutes, ?string $permission = null): array
    {
        return ['type' => 'link', 'route' => $route, 'label' => $label, 'icon' => $icon, 'active' => $activeRoutes, 'permission' => $permission];
    }

    /**
     * @param  list<array<string, mixed>>  $children
     * @return array<string, mixed>
     */
    private static function group(string $key, string $label, string $icon, array $children): array
    {
        return ['type' => 'group', 'key' => $key, 'label' => $label, 'icon' => $icon, 'children' => $children];
    }

    /**
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>|null
     */
    private function buildLink(array $entry, User $user, Request $request): ?array
    {
        $route = $this->router->getRoutes()->getByName($entry['route']);
        if ($route === null) {
            return null;
        }

        if ($entry['permission'] !== null && ! $user->hasPermission($entry['permission'])) {
            return null;
        }

        foreach ($route->gatherMiddleware() as $middleware) {
            if (is_string($middleware) && str_starts_with($middleware, 'perm:')
                && ! $user->hasPermission(substr($middleware, 5))) {
                return null;
            }
        }

        return [
            'type' => 'link',
            'key' => $entry['route'],
            'label' => (string) __($entry['label']),
            'href' => route($entry['route']),
            'icon' => $entry['icon'],
            'active' => $request->routeIs(...$entry['active']),
        ];
    }

    /**
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>|null
     */
    private function buildGroup(array $entry, User $user, Request $request): ?array
    {
        $children = array_values(array_filter(array_map(
            fn (array $child): ?array => $this->buildLink($child, $user, $request),
            $entry['children'],
        )));

        if ($children === []) {
            return null;
        }

        return [
            'type' => 'group',
            'key' => $entry['key'],
            'label' => (string) __($entry['label']),
            'icon' => $entry['icon'],
            'active' => collect($children)->contains(fn (array $child): bool => $child['active']),
            'children' => $children,
        ];
    }
}
