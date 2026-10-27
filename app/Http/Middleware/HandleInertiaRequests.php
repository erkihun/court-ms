<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use App\Models\User;
use App\Support\Ui\CourtNavigation;
use App\Support\Ui\SharedSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Middleware;

/**
 * The props every court-ms React page gets, in the shape of EUISIS
 * HandleInertiaRequests (as the EUISIS Core platform does) so the screens
 * copied from EUISIS read them unchanged. court-ms is always in its own
 * workspace (`workspace`); EUISIS-only features (assistant, communication,
 * the employee portal, ID cards) are switched off. Blade pages ignore all of
 * this until they move.
 */
class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'inertia';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $user = $user instanceof User ? $user : null;
        $shared = app(SharedSettings::class);
        // share() runs before the route's SetLocale, so anything that reads the
        // language (or touches the database) is a closure, resolved at render time.
        $locale = fn (): string => app()->getLocale() === 'am' ? 'am' : 'en';

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => (string) ($user->status ?? 'active'),
                    'profile_photo_url' => $user->avatar_url,
                    'initials' => $this->initials((string) $user->name),
                ] : null,
                'roles' => fn (): array => $user ? $user->roles()->pluck('name')->all() : [],
                // court-ms: a person with several roles works under one at a time (ResolveAdminRole, admin.roles.switch).
                'activeRoleId' => fn (): ?int => $user?->activeRole()?->id,
                'roleOptions' => function () use ($user): array {
                    $roles = $user ? $user->roles()->orderByRaw("CASE WHEN LOWER(roles.name) = 'admin' THEN 0 ELSE 1 END")->orderBy('roles.name')->get() : collect();

                    return $roles->count() > 1
                        ? $roles->map(fn ($role): array => ['id' => (int) $role->id, 'label' => Str::headline((string) $role->name)])->values()->all()
                        : [];
                },
                'permissions' => fn (): array => CourtNavigation::permissionNames($user),
                'isSuperAdmin' => fn (): bool => $user?->hasRole('admin') ?? false,
                'canViewDashboard' => $user !== null,
                'unrestrictedScope' => true,
            ],
            'cafeteriaProviderAuth' => null,
            'features' => [],
            'assistant' => ['enabled' => false],
            'publicAssistant' => ['enabled' => false],
            'communication' => null,
            'workspace' => fn (): ?array => $user ? ['name' => 'core', 'nav' => app(CourtNavigation::class)->for($user)] : null,
            'locale' => $locale,
            'richText' => null,
            'textLocale' => fn (): string => app()->getLocale(),
            'calendar' => fn (): array => ['system' => $locale() === 'am' && $this->ethiopianCalendar() ? 'ethiopian' : 'gregorian', 'mode' => 'locale_based'],
            'settings' => fn (): array => $shared->all(),
            'branding' => fn (): array => $shared->branding(),
            'idCardTemplate' => null,
            'idCardTemplates' => null,
            'registration_enabled' => false,
            'announcement_count' => 0,
            'is_employee_user' => false,
            'has_employee_record' => false,
            // court-ms times admin sessions out on the server (AdminSessionTimeout); the EUISIS idle dialog needs routes it does not have yet.
            'session_policy' => null,
            'flash' => [
                'success' => $this->flashText(session('success')),
                'error' => $this->flashText(session('error')),
                'warning' => $this->flashText(session('warning')),
                'info' => $this->flashText(session('info')),
                'message' => session('flash.message'),
                'type' => session('flash.type'),
                'generated_token' => null,
                'temporary_password' => null,
            ],
            'password_policy' => fn (): array => $this->passwordPolicy(),
        ];
    }

    /** court-ms flashes either text or {key, replace} for a translation. */
    private function flashText(mixed $value): ?string
    {
        if (is_array($value) && isset($value['key'])) {
            return (string) __((string) $value['key'], (array) ($value['replace'] ?? []));
        }

        return is_string($value) ? __($value) : null;
    }

    private function ethiopianCalendar(): bool
    {
        try {
            return (bool) (SystemSetting::current()->use_ethiopian_calendar ?? true);
        } catch (\Throwable) {
            return true;
        }
    }

    /** @return array<string, int|bool> */
    private function passwordPolicy(): array
    {
        $settings = rescue(fn () => SystemSetting::current(), null, false);

        return [
            'min_length' => (int) ($settings->password_min_length ?? 8),
            'max_length' => 128,
            'history_count' => 0,
            'requires_uppercase' => (bool) ($settings->password_require_uppercase ?? false),
            'requires_number' => (bool) ($settings->password_require_number ?? false),
            'requires_symbol' => (bool) ($settings->password_require_symbol ?? false),
            'blocks_personal_information' => false,
            'blocks_common_passwords' => false,
            'checks_breaches' => false,
        ];
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];

        return mb_strtoupper(implode('', array_map(fn (string $part): string => mb_substr($part, 0, 1), array_slice($parts, 0, 2))));
    }
}
