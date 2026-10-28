<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use App\Models\User;
use App\Support\Ui\CourtNavigation;
use App\Support\Ui\PortalNavigation;
use App\Support\Ui\SharedSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $shared = app(SharedSettings::class);
        // share() runs before the route's SetLocale and guard middleware, so anything
        // that reads the language, the signed-in person or the database is a closure,
        // resolved at render time.
        $locale = fn (): string => app()->getLocale() === 'am' ? 'am' : 'en';
        // The applicant/respondent portals sign in on their own guard; staff on `web`.
        $portal = fn (): ?string => PortalNavigation::portalFor($request);
        $staff = function () use ($portal): ?User {
            $user = $portal() === null ? Auth::guard('web')->user() : null;

            return $user instanceof User ? $user : null;
        };

        return [
            ...parent::share($request),
            'auth' => fn (): array => $portal() !== null ? $this->portalAuth() : $this->staffAuth($staff()),
            'portal' => fn (): ?array => ($name = $portal()) !== null ? PortalNavigation::routes($name) : null,
            'cafeteriaProviderAuth' => null,
            'features' => [],
            'assistant' => ['enabled' => false],
            'publicAssistant' => ['enabled' => false],
            'communication' => null,
            'workspace' => function () use ($portal, $staff): ?array {
                if (($name = $portal()) !== null) {
                    return PortalNavigation::user() ? ['name' => 'core', 'home' => PortalNavigation::routes($name)['home'], 'nav' => PortalNavigation::sections($name)] : null;
                }
                $user = $staff();

                return $user ? ['name' => 'core', 'home' => 'admin.dashboard', 'nav' => app(CourtNavigation::class)->for($user)] : null;
            },
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
                // Some court-ms controllers flash `ok` for a success.
                'success' => $this->flashText(session('success') ?? session('ok')),
                'error' => $this->flashText(session('error')),
                'warning' => $this->flashText(session('warning')),
                'info' => $this->flashText(session('info')),
                'message' => session('flash.message'),
                'type' => session('flash.type'),
                'generated_token' => null,
                'temporary_password' => null,
                // Shown once, right after the second factor is confirmed (MfaController::confirm).
                'mfa_recovery_codes_once' => session('mfa_recovery_codes'),
            ],
            'password_policy' => fn (): array => $this->passwordPolicy(),
        ];
    }

    /** @return array<string, mixed> */
    private function staffAuth(?User $user): array
    {
        $roles = $user ? $user->roles()->orderByRaw("CASE WHEN LOWER(roles.name) = 'admin' THEN 0 ELSE 1 END")->orderBy('roles.name')->get() : collect();

        return [
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => (string) ($user->status ?? 'active'),
                'profile_photo_url' => $user->avatar_url,
                'initials' => $this->initials((string) $user->name),
            ] : null,
            'roles' => $roles->pluck('name')->values()->all(),
            // court-ms: a person with several roles works under one at a time (ResolveAdminRole, admin.roles.switch).
            'activeRoleId' => $user?->activeRole()?->id,
            'roleOptions' => $roles->count() > 1
                ? $roles->map(fn ($role): array => ['id' => (int) $role->id, 'label' => Str::headline((string) $role->name)])->values()->all()
                : [],
            'permissions' => CourtNavigation::permissionNames($user),
            'isSuperAdmin' => $user?->hasRole('admin') ?? false,
            'canViewDashboard' => $user !== null,
            'unrestrictedScope' => true,
        ];
    }

    /** An applicant (or the same person acting as respondent): no roles or staff permissions. @return array<string, mixed> */
    private function portalAuth(): array
    {
        $user = PortalNavigation::user();

        return [
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'status' => $user->is_active ? 'active' : 'inactive',
                'profile_photo_url' => null,
                'initials' => $this->initials((string) $user->full_name),
                'is_lawyer' => (bool) $user->is_lawyer,
            ] : null,
            'roles' => [],
            'activeRoleId' => null,
            'roleOptions' => [],
            'permissions' => [],
            'isSuperAdmin' => false,
            'canViewDashboard' => $user !== null,
            'unrestrictedScope' => false,
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
