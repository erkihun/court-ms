<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Applicant;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use App\Navigation\AdminNavigation;
use App\Support\EthiopianDate;
use App\Support\FlashMessages;
use App\Support\Translations;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shares the data every page needs (app branding, the signed-in actor, flash
 * toasts, navigation) and keeps navigation working while pages move from
 * Blade to React.
 */
class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function __construct(private readonly AdminNavigation $adminNavigation) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = parent::handle($request, $next);

        // Respondent mode is a navigation flag on the applicant session. Opening
        // an applicant page leaves it, as the Blade layout did when it rendered.
        if ($this->rendersPage($request, $response) && $request->is('applicant', 'applicant/*') && $request->hasSession()) {
            $request->session()->forget('acting_as_respondent');
        }

        // An Inertia visit that reaches a page not yet converted to React (or a
        // download) gets a regular HTML/file response. Tell the client to load
        // it as a full page instead of showing it in Inertia's error modal.
        if ($request->header('X-Inertia')
            && $request->isMethod('GET')
            && ! $response->headers->has('X-Inertia')
            && ! $response instanceof RedirectResponse
            && $response->isSuccessful()) {
            return Inertia::location($request->fullUrl());
        }

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'app' => fn (): array => $this->app(),
            'auth' => fn (): array => $this->auth($request),
            'navigation' => fn (): ?array => ($user = $request->user('web')) instanceof User && ! $this->isPartyArea($request)
                ? $this->adminNavigation->for($user, $request)
                : null,
            'toasts' => fn (): array => $request->hasSession() ? FlashMessages::from($request->session()) : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function app(): array
    {
        $settings = $this->settings();
        $name = $settings?->app_name ?: config('app.name', 'CMS');
        $locale = app()->getLocale();
        $now = now();

        return [
            'name' => $name,
            'shortName' => $settings?->short_name ?: $name,
            'logoUrl' => $settings?->logo_path ? asset('storage/'.$settings->logo_path) : null,
            'footerText' => $settings?->footer_text ?: __('app.all_rights_reserved'),
            'locale' => $locale,
            'translationsUrl' => route('i18n.messages', ['locale' => $locale, 'v' => Translations::version($locale)]),
            'showLanguageSwitcher' => (bool) ($settings?->show_language_switcher ?? true),
            'session' => [
                'lifetimeMinutes' => (int) ($settings?->session_lifetime ?? config('session.lifetime', 120)),
                'warningMinutes' => (int) ($settings?->session_warning_minutes ?? 5),
                'extendOnActivity' => (bool) ($settings?->session_extend_on_activity ?? true),
            ],
            'today' => [
                'long' => $now->translatedFormat('l').', '.EthiopianDate::format($now),
                'short' => EthiopianDate::formatDate($now),
                'time' => EthiopianDate::formatTime($now),
            ],
        ];
    }

    /**
     * @return array{user: array<string, mixed>|null, applicant: array<string, mixed>|null}
     */
    private function auth(Request $request): array
    {
        $staff = $request->user('web');
        $applicant = $request->user('applicant');

        return [
            'user' => $staff instanceof User ? $this->staff($staff) : null,
            'applicant' => $applicant instanceof Applicant ? [
                'id' => $applicant->id,
                'fullName' => $applicant->full_name,
                'firstName' => $applicant->first_name,
                'email' => $applicant->email,
                'isLawyer' => (bool) $applicant->is_lawyer,
                'actingAsRespondent' => $this->isRespondentArea($request),
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function staff(User $user): array
    {
        $user->loadMissing('roles');
        $active = $user->activeRole();
        $role = fn (Role $role): array => ['id' => $role->id, 'name' => $role->name, 'label' => Str::headline($role->name)];

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatarUrl' => $user->avatar_url,
            'activeRole' => $active instanceof Role ? $role($active) : null,
            'roles' => $user->roles->sortBy('name')->map($role)->values()->all(),
            'permissions' => $user->effectivePermissionNames(),
        ];
    }

    /** The respondent pages: /respondent/* and the respondent.* routes. */
    private function isRespondentArea(Request $request): bool
    {
        return $request->is('respondent', 'respondent/*') || $request->routeIs('respondent.*');
    }

    /** A successful page view (Inertia or HTML), as opposed to a redirect, download or JSON call. */
    private function rendersPage(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET') || ! $response->isSuccessful()) {
            return false;
        }

        return $response->headers->has('X-Inertia')
            || str_starts_with((string) $response->headers->get('Content-Type'), 'text/html');
    }

    private function isPartyArea(Request $request): bool
    {
        return $request->is('applicant', 'applicant/*', 'respondent', 'respondent/*');
    }

    private function settings(): ?SystemSetting
    {
        try {
            return SystemSetting::cached();
        } catch (\Throwable) {
            return null;
        }
    }
}
