@props(['title' => 'Dashboard'])

<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}" x-data="themeSystem()" x-init="init()">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
    $systemSettings = $systemSettings ?? null;
    if (!$systemSettings) {
        try {
            $systemSettings = \App\Models\SystemSetting::query()->first();
        } catch (\Throwable $e) {
            $systemSettings = null;
        }
    }

    use Illuminate\Support\Arr;

    $wrappedTitle = Arr::wrap($title);
    $titleCandidate = $wrappedTitle[0] ?? null;

    // Localized title with safe fallbacks (accepts keys or plain strings)
    if (is_string($titleCandidate) && \Illuminate\Support\Facades\Lang::has($titleCandidate)) {
        $t = __($titleCandidate);
    } elseif (is_string($titleCandidate) && \Illuminate\Support\Facades\Lang::has('app.' . $titleCandidate)) {
        $t = __('app.' . $titleCandidate);
    } else {
        $t = $titleCandidate;
    }

    if (!is_string($t)) {
        $wrappedTitleFallback = Arr::wrap($t);
        $firstFallback = $wrappedTitleFallback[0] ?? '';
        $t = is_scalar($firstFallback) ? (string) $firstFallback : '';
    }
    @endphp
    <title>{{ $t }} | {{ $systemSettings->app_name ?? config('app.name','CMS') }}</title>
    <script>
        (() => {
            const theme = localStorage.getItem('theme') || 'system';
            const dark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.dataset.theme = theme;
        })();
    </script>
    <style>
        [x-cloak] {
            display: none !important
        }

        /* NEW: Icons are light blue/white in the dark sidebar for contrast */
        .sidebar-icon {
            color: #dbeafe !important;
            /* Blue-100 for dark sidebar */
        }

        /* NEW: Focus ring now uses Primary Brand Blue for authority and professionalism */
        .focus-ring {
            outline: 2px solid transparent;
            outline-offset: 2px;
        }

        .focus-ring:focus-visible {
            outline: 2px solid #2563eb;
            /* Blue-600 */
            outline-offset: 2px;
        }
    </style>
    @vite(['resources/css/app.css','resources/js/app.js'])
    @livewireStyles
    @if(app()->getLocale() === 'am')
    <link rel="stylesheet" href="{{ asset('vendor/modern-ethiopian-calendar/css/datepicker.css') }}">
    @endif
    @stack('styles')
</head>

{{-- Tribunal systems use clear, high-contrast typography --}}

<body x-data="layoutState()" x-init="init()" class="ui-shell admin-subtle-grid min-h-screen flex font-sans font-ui text-[var(--text)]">

    {{-- Sidebar (mobile slide-in + desktop collapsible) --}}
    @php
    use Illuminate\Support\Facades\Route;

    // Guard route usage to avoid "Route [...] not defined" exceptions
    $hasDashboard = Route::has('dashboard');
    $hasAppeals = Route::has('appeals.index');

    $hasCases = Route::has('cases.index');
    $hasCaseInspections = Route::has('case-inspections.index');
    $hasCaseInspectionRequests = Route::has('case-inspection-requests.index');
    $hasCaseInspectionFindings = Route::has('case-inspection-findings.index');
    $canManageInspectionRequests = auth()->user()?->hasPermission('inspection-requests.manage') ?? false;
    $canManageInspectionFindings = auth()->user()?->hasPermission('inspection-findings.manage') ?? false;
    $hasApplicants = Route::has('applicants.index');
    $hasRecordes = Route::has('recordes.index');
    $hasCaseTypes = Route::has('case-types.index');
    $hasHearings = Route::has('admin.hearings.index');
    $hasDecisions = Route::has('decisions.index');
    $hasUsers = Route::has('users.index');
    $hasPermissions = Route::has('permissions.index');
    $hasRoles = Route::has('roles.index');
    $hasTeams = Route::has('teams.index');
    $hasNotifIndex = Route::has('admin.notifications.index');
    $hasNotifMarkAll = Route::has('admin.notifications.markAll');
    $hasNotifMarkOne = Route::has('admin.notifications.markOne');
    $hasProfileEdit = Route::has('profile.edit');
    $hasLogout = Route::has('logout');
    $hasLangSwitch = Route::has('language.switch');
    $hasSystemSettings = Route::has('settings.system.edit');
    $hasTerms = Route::has('terms.index');
    $hasAbout = Route::has('about.index');
    $hasLetterTemplates = Route::has('letter-templates.index');
    $hasLetterCategories = Route::has('letter-categories.index');
    $hasLetterComposer = Route::has('letters.compose');
    $hasLetters = Route::has('letters.index');
    $hasAudit = Route::has('admin.audit');
    $hasReports = Route::has('reports.index');
    $hasAnnouncements = Route::has('announcements.index');
    $isCaseTypographyRoute = request()->routeIs('cases.*')
        || request()->routeIs('recordes.*')
        || request()->routeIs('respondent-responses.*');
    $canManageTemplates = $hasLetterTemplates && auth()->user()?->hasPermission('templates.manage');
    $canViewLetters = $hasLetters && auth()->user()?->hasPermission('cases.edit');
    $canComposeLetters = $hasLetterComposer && auth()->user()?->hasPermission('cases.edit');
    $canManageUsers = $hasUsers && auth()->user()?->hasPermission('users.manage');
    $canManagePermissions = $hasPermissions && auth()->user()?->hasPermission('permissions.manage');
    $canManageRoles = $hasRoles && auth()->user()?->hasPermission('roles.manage');
    $canManageTeams = $hasTeams && auth()->user()?->hasPermission('teams.manage');
    $letterTemplatesActive = request()->routeIs('letter-templates.*');
    $letterCategoriesActive = request()->routeIs('letter-categories.*');
    $lettersActive = request()->routeIs('letters.index') || request()->routeIs('letters.show');
    $composeActive = request()->routeIs('letters.compose');
    $letterMenuOpen = $letterTemplatesActive || $letterCategoriesActive || $lettersActive || $composeActive;
    $applicantsActive = request()->routeIs('applicants.*');
    $inspectionRequestsActive = request()->routeIs('case-inspection-requests.*');
    $inspectionFindingsActive = request()->routeIs('case-inspection-findings.*');
    $inspectionMenuOpen = $inspectionRequestsActive || $inspectionFindingsActive;
    $usersActive = request()->routeIs('users.*');
    $permissionsActive = request()->routeIs('permissions.*');
    $rolesActive = request()->routeIs('roles.*');
    $teamsActive = request()->routeIs('teams.*');
    $userControlOpen = $usersActive || $permissionsActive || $rolesActive || $teamsActive;
    $settingsMenuOpen = request()->routeIs('settings.system.*')
        || request()->routeIs('terms.*')
        || request()->routeIs('about.*')
        || request()->routeIs('admin.audit');
    $canViewReports = $hasReports && auth()->user()?->hasPermission('reports.view');
    @endphp

    <aside
        {{-- UPDATED: Deep Blue/Navy Sidebar BG (Primary Brand Color for Authority) --}}
        class="fixed top-0 left-0 z-40 h-screen
               transform transition-transform-base
               -translate-x-full md:translate-x-0
               flex flex-col font-sidebar bg-[linear-gradient(180deg,#0f172a_0%,#102c4d_54%,#12375f_100%)] border-r border-slate-800/80 shadow-xl shadow-slate-950/20
               w-72 transition-width-slow"
        :class="{
            'translate-x-0': sidebar,
            'md:w-20': compact,
            'md:w-64': !compact
        }"
        aria-label="{{ __('app.Sidebar') }}">

        {{-- Brand / collapse toggle row --}}
        {{-- UPDATED: Border uses darker blue --}}
        <div class="relative flex items-center justify-center gap-2 border-b border-white/10 bg-white/5 px-4 py-5">
            <div class="flex items-center justify-center w-full">
                {{-- Text color is white/light blue --}}
                <a href="{{ $hasDashboard ? route('dashboard') : url('/') }}" aria-label="{{ __('app.Dashboard') }}" class="focus-ring rounded flex flex-col items-center text-center">
                    @if(!empty($systemSettings?->logo_path))
                    <img
                        src="{{ asset('storage/'.$systemSettings->logo_path) }}"
                        alt="{{ $systemSettings->app_name ?? config('app.name','CMS') }}"
                        class="h-10 w-auto object-contain">
                    @endif
                    {{-- Full name (shown when NOT compact) --}}
                    <span class="text-white text-xl font-extrabold truncate origin-left"
                        x-show="!compact"
                        x-transition:enter="motion-enter"
                        x-transition:enter-start="motion-slide-inline-start"
                        x-transition:enter-end="motion-slide-inline-end"
                        x-transition:leave="motion-leave"
                        x-transition:leave-start="motion-slide-inline-end"
                        x-transition:leave-end="motion-slide-inline-start">
                        {{ $systemSettings->app_name ?? config('app.name','CMS') }}
                    </span>

                    {{-- Short name (shown when compact) --}}
                    <span class="text-white text-xl font-extrabold truncate origin-left"
                        x-show="compact"
                        x-transition:enter="motion-enter"
                        x-transition:enter-start="motion-slide-inline-start"
                        x-transition:enter-end="motion-slide-inline-end"
                        x-transition:leave="motion-leave"
                        x-transition:leave-start="motion-slide-inline-end"
                        x-transition:leave-end="motion-slide-inline-start">
                        {{ $systemSettings->short_name ?? 'CMS' }}
                    </span>
                </a>
            </div>

            {{-- Close on mobile --}}
            {{-- UPDATED: Hover uses darker blue --}}
            <button type="button"
                class="md:hidden absolute right-3 top-1/2 -translate-y-1/2 inline-flex items-center justify-center w-9 h-9 rounded-md text-blue-300 hover:bg-blue-800 focus-ring"
                @click="sidebar=false"
                aria-label="{{ __('app.Close sidebar') }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="sidebar-icon h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Nav --}}
        <div id="admin-nav" data-spa-nav="true" x-data="lazyMenu()" x-init="init()" class="flex-1 overflow-y-auto">
            <template x-if="!loaded">
                <div class="space-y-3 px-3 py-4" aria-hidden="true">
                    <div class="h-3 w-24 rounded bg-white/10 skeleton"></div>
                    <div class="space-y-2">
                        <div class="h-10 rounded-lg bg-white/8 skeleton"></div>
                        <div class="h-10 rounded-lg bg-white/8 skeleton"></div>
                        <div class="h-10 rounded-lg bg-white/8 skeleton"></div>
                        <div class="h-10 rounded-lg bg-white/8 skeleton"></div>
                    </div>
                    <div class="h-3 w-20 rounded bg-white/10 skeleton"></div>
                    <div class="space-y-2">
                        <div class="h-10 rounded-lg bg-white/8 skeleton"></div>
                        <div class="h-10 rounded-lg bg-white/8 skeleton"></div>
                    </div>
                </div>
            </template>

            <template x-if="loaded" :key="'menu'">
                <nav class="space-y-1.5 px-3 py-4">
            {{-- Dashboard --}}
            @if($hasDashboard)
            {{-- UPDATED: Active state uses Primary Blue (Blue-700). Hover uses a lighter Blue-600/30 mix. --}}
            <a href="{{ route('dashboard') }}"
                data-no-spa="true"
                class="sidebar-menu-item focus-ring
                      {{ request()->routeIs('dashboard') ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive' }}">
                <div class="grid place-items-center w-6" aria-hidden="true">
                    <x-heroicon-o-home class="sidebar-icon h-4 w-4" aria-hidden="true" />
                </div>
                <span class="sidebar-menu-label truncate origin-left"
                    x-show="!compact"
                    x-transition:enter="motion-enter"
                    x-transition:enter-start="motion-slide-inline-start"
                    x-transition:enter-end="motion-slide-inline-end"
                    x-transition:leave="motion-leave"
                    x-transition:leave-start="motion-slide-inline-end"
                    x-transition:leave-end="motion-slide-inline-start">
                    {{ __('app.Dashboard') }}
                </span>
            </a>
            @endif

            @php
            $settingsDropdownVisible = ($hasSystemSettings && auth()->user()?->hasPermission('settings.manage'))
            || ($hasTerms && auth()->user()?->hasPermission('settings.manage'))
            || ($hasAbout && auth()->user()?->hasPermission('about.manage'))
            || $hasAudit;
            $showCaseOpsSection = ($hasCases && auth()->user()?->hasPermission('cases.view'))
                || ($hasCaseInspections && ($canManageInspectionRequests || $canManageInspectionFindings))
                || ($hasHearings && auth()->user()?->hasPermission('cases.view'))
                || ($hasRecordes && auth()->user()?->hasPermission('cases.view'))
                || ($hasCaseTypes && auth()->user()?->hasPermission('cases.types'))
                || ($hasAppeals && auth()->user()?->hasPermission('appeals.view'))
                || ($hasDecisions && auth()->user()?->hasPermission('decision.view'))
                || $canViewReports;
            $showPeopleSection = ($hasApplicants && auth()->user()?->hasPermission('applicants.view'))
                || $canManageUsers || $canManagePermissions || $canManageRoles || $canManageTeams;
            $showCommsSection = $hasNotifIndex || ($hasAnnouncements && auth()->user()?->hasPermission('announcements.manage'))
                || $canManageTemplates || $canViewLetters || $canComposeLetters;
            @endphp

            @if($showCaseOpsSection)
            <div class="sidebar-section-label" x-show="!compact">{{ __('app.case_management') }}</div>
            @endif


            {{-- Appeals --}}
            @if($hasAppeals && auth()->user()?->hasPermission('appeals.view'))
            <a href="{{ route('appeals.index') }}"
                class="sidebar-menu-item focus-ring
                {{ request()->routeIs('appeals.*') ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive' }}">
                <div class="grid place-items-center w-6" aria-hidden="true">
                    <x-heroicon-o-shield-check class="sidebar-icon h-4 w-4" aria-hidden="true" />
                </div>
                <span class="sidebar-menu-label truncate origin-left"
                    x-show="!compact"
                    x-transition:enter="motion-enter"
                    x-transition:enter-start="motion-slide-inline-start"
                    x-transition:enter-end="motion-slide-inline-end"
                    x-transition:leave="motion-leave"
                    x-transition:leave-start="motion-slide-inline-end"
                    x-transition:leave-end="motion-slide-inline-start">
                    {{ __('app.Appeals') }}
                </span>
            </a>
            @endif


            {{-- Decisions --}}

            @if($hasDecisions && auth()->user()?->hasPermission('decision.view'))
            <a href="{{ route('decisions.index') }}"
                class="sidebar-menu-item focus-ring
                {{ request()->routeIs('decisions.*') ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive' }}">
                <div class="grid place-items-center w-6" aria-hidden="true">
                    <x-heroicon-o-document-text class="sidebar-icon h-4 w-4" aria-hidden="true" />
                </div>
                <span class="sidebar-menu-label truncate origin-left"
                    x-show="!compact"
                    x-transition:enter="motion-enter"
                    x-transition:enter-start="motion-slide-inline-start"
                    x-transition:enter-end="motion-slide-inline-end"
                    x-transition:leave="motion-leave"
                    x-transition:leave-start="motion-slide-inline-end"
                    x-transition:leave-end="motion-slide-inline-start">
                    {{ __('app.Decisions') }}
                </span>
            </a>
            @endif

            {{-- Cases --}}
            @if($hasCases && auth()->user()?->hasPermission('cases.view'))
            <a href="{{ route('cases.index') }}"
                class="sidebar-menu-item focus-ring
                {{ request()->routeIs('cases.*') ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive' }}">
                <div class="grid place-items-center w-6" aria-hidden="true">
                    <x-heroicon-o-briefcase class="sidebar-icon h-4 w-4" aria-hidden="true" />
                </div>
                <span class="sidebar-menu-label truncate origin-left"
                    x-show="!compact"
                    x-transition:enter="motion-enter"
                    x-transition:enter-start="motion-slide-inline-start"
                    x-transition:enter-end="motion-slide-inline-end"
                    x-transition:leave="motion-leave"
                    x-transition:leave-start="motion-slide-inline-end"
                    x-transition:leave-end="motion-slide-inline-start">
                    {{ __('app.Cases') }}
                </span>
            </a>
            @endif

            {{-- Case Inspections --}}
            @if($hasCaseInspections && ($canManageInspectionRequests || $canManageInspectionFindings))
            <div x-data="{ open: {{ $inspectionMenuOpen ? 'true' : 'false' }}, loaded: {{ $inspectionMenuOpen ? 'true' : 'false' }} }" class="space-y-1">
                <button type="button"
                    class="sidebar-menu-toggle focus-ring
                    {{ $inspectionMenuOpen ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive' }}"
                    @click="if (!loaded) loaded = true; open = !open"
                    aria-haspopup="true"
                    :aria-expanded="open.toString()">
                    <span class="flex items-center gap-3">
                        <div class="grid place-items-center w-6" aria-hidden="true">
                            <x-heroicon-o-clipboard-document-check class="sidebar-icon h-4 w-4" aria-hidden="true" />
                        </div>
                        <span class="sidebar-menu-label truncate origin-left"
                            x-show="!compact"
                            x-transition:enter="motion-enter"
                            x-transition:enter-start="motion-slide-inline-start"
                            x-transition:enter-end="motion-slide-inline-end"
                            x-transition:leave="motion-leave"
                            x-transition:leave-start="motion-slide-inline-end"
                            x-transition:leave-end="motion-slide-inline-start">
                            {{ __('app.case_inspections') }}
                        </span>
                    </span>
                    <svg x-show="!compact" xmlns="http://www.w3.org/2000/svg" class="sidebar-icon h-4 w-4 transition-fast"
                        :class="{ 'rotate-90': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <template x-if="loaded">
                    <div x-show="open && !compact"
                        x-transition:enter="motion-enter-fast"
                        x-transition:enter-start="motion-slide-up-start"
                        x-transition:enter-end="motion-slide-up-end"
                        x-transition:leave="motion-leave"
                        x-transition:leave-start="motion-slide-up-end"
                        x-transition:leave-end="motion-slide-up-start"
                        class="pl-11 space-y-1">
                    @if($hasCaseInspectionRequests && $canManageInspectionRequests)
                    <a href="{{ route('case-inspection-requests.index') }}"
                        class="sidebar-submenu-item focus-ring
                        {{ $inspectionRequestsActive ? 'sidebar-submenu-item-active' : 'sidebar-submenu-item-inactive' }}">
                        <x-heroicon-o-document-text class="sidebar-icon h-4 w-4" aria-hidden="true" />
                        <span>{{ __('case_inspections.requests.index_title') }}</span>
                    </a>
                    @endif

                    @if($hasCaseInspectionFindings && $canManageInspectionFindings)
                    <a href="{{ route('case-inspection-findings.index') }}"
                        class="sidebar-submenu-item focus-ring
                        {{ $inspectionFindingsActive ? 'sidebar-submenu-item-active' : 'sidebar-submenu-item-inactive' }}">
                        <x-heroicon-o-clipboard class="sidebar-icon h-4 w-4" aria-hidden="true" />
                        <span>{{ __('case_inspections.findings.index_title') }}</span>
                    </a>
                    @endif
                    </div>
                </template>
            </div>
            @endif

            {{-- Hearings --}}
            @if($hasHearings && auth()->user()?->hasPermission('cases.view'))
            <a href="{{ route('admin.hearings.index') }}"
                class="sidebar-menu-item focus-ring
                {{ request()->routeIs('admin.hearings.*') ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive' }}">
                <div class="grid place-items-center w-6" aria-hidden="true">
                    <x-heroicon-o-calendar-days class="sidebar-icon h-4 w-4" aria-hidden="true" />
                </div>
                <span class="sidebar-menu-label truncate origin-left"
                    x-show="!compact"
                    x-transition:enter="motion-enter"
                    x-transition:enter-start="motion-slide-inline-start"
                    x-transition:enter-end="motion-slide-inline-end"
                    x-transition:leave="motion-leave"
                    x-transition:leave-start="motion-slide-inline-end"
                    x-transition:leave-end="motion-slide-inline-start">
                    {{ __('app.Hearings') }}
                </span>
            </a>
            @endif

            {{-- Records --}}
            @if($hasRecordes && auth()->user()?->hasPermission('cases.view'))
            <a href="{{ route('recordes.index') }}"
                class="sidebar-menu-item focus-ring
                {{ request()->routeIs('recordes.*') ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive' }}">
                <div class="grid place-items-center w-6" aria-hidden="true">
                    <x-heroicon-o-bookmark class="sidebar-icon h-4 w-4" aria-hidden="true" />
                </div>
                <span class="sidebar-menu-label truncate origin-left"
                    x-show="!compact"
                    x-transition:enter="motion-enter"
                    x-transition:enter-start="motion-slide-inline-start"
                    x-transition:enter-end="motion-slide-inline-end"
                    x-transition:leave="motion-leave"
                    x-transition:leave-start="motion-slide-inline-end"
                    x-transition:leave-end="motion-slide-inline-start">
                    {{ __('app.Records') }}
                </span>
            </a>
            @endif

            @if($canViewReports)
            <a href="{{ route('reports.index') }}"
                class="sidebar-menu-item focus-ring
                {{ request()->routeIs('reports.*') ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive' }}">
                <div class="grid place-items-center w-6" aria-hidden="true">
                    <x-heroicon-o-chart-bar class="sidebar-icon h-4 w-4" aria-hidden="true" />
                </div>
                <span class="sidebar-menu-label truncate origin-left"
                    x-show="!compact"
                    x-transition:enter="motion-enter"
                    x-transition:enter-start="motion-slide-inline-start"
                    x-transition:enter-end="motion-slide-inline-end"
                    x-transition:leave="motion-leave"
                    x-transition:leave-start="motion-slide-inline-end"
                    x-transition:leave-end="motion-slide-inline-start">
                    {{ __('app.Reports') }}
                </span>
            </a>
            @endif

            {{-- Applicants --}}
            @if($hasApplicants && auth()->user()?->hasPermission('applicants.view'))
            <a href="{{ route('applicants.index') }}"
                class="sidebar-menu-item focus-ring
                {{ $applicantsActive ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive' }}">
                <div class="grid place-items-center w-6" aria-hidden="true">
                    <x-heroicon-o-user-group class="sidebar-icon h-4 w-4" aria-hidden="true" />
                </div>
                <span class="sidebar-menu-label truncate origin-left"
                    x-show="!compact"
                    x-transition:enter="motion-enter"
                    x-transition:enter-start="motion-slide-inline-start"
                    x-transition:enter-end="motion-slide-inline-end"
                    x-transition:leave="motion-leave"
                    x-transition:leave-start="motion-slide-inline-end"
                    x-transition:leave-end="motion-slide-inline-start">
                    {{ __('app.Applicant') }}
                </span>
            </a>
            @endif

            {{-- Case Types --}}
            @if($hasCaseTypes && auth()->user()?->hasPermission('cases.types'))
            <a href="{{ route('case-types.index') }}"
                class="sidebar-menu-item focus-ring
                {{ request()->routeIs('case-types.*') ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive' }}">
                <div class="grid place-items-center w-6" aria-hidden="true">
                    <x-heroicon-o-tag class="sidebar-icon h-4 w-4" aria-hidden="true" />
                </div>
                <span class="sidebar-menu-label truncate origin-left"
                    x-show="!compact"
                    x-transition:enter="motion-enter"
                    x-transition:enter-start="motion-slide-inline-start"
                    x-transition:enter-end="motion-slide-inline-end"
                    x-transition:leave="motion-leave"
                    x-transition:leave-start="motion-slide-inline-end"
                    x-transition:leave-end="motion-slide-inline-start">
                    {{ __('app.case_types') }}
                </span>
            </a>
            @endif

            @if($showCommsSection)
            <div class="sidebar-section-label" x-show="!compact">{{ __('app.communication') }}</div>
            @endif

            {{-- Letters dropdown --}}
            @if($canManageTemplates || $canViewLetters || $canComposeLetters)
            <div x-data="{ open: {{ $letterMenuOpen ? 'true' : 'false' }}, loaded: {{ $letterMenuOpen ? 'true' : 'false' }} }" class="space-y-1">
                {{-- UPDATED: Main button active uses Primary Blue --}}
                <button type="button"
                    class="sidebar-menu-toggle focus-ring
                    {{ $letterMenuOpen ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive' }}"
                    @click="if (!loaded) loaded = true; open = !open"
                    aria-haspopup="true"
                    :aria-expanded="open.toString()">
                    <span class="flex items-center gap-3">
                        <div class="grid place-items-center w-6" aria-hidden="true">
                            <x-heroicon-o-envelope class="sidebar-icon h-4 w-4" aria-hidden="true" />
                        </div>
                        <span class="sidebar-menu-label truncate origin-left"
                            x-show="!compact"
                            x-transition:enter="motion-enter"
                            x-transition:enter-start="motion-slide-inline-start"
                            x-transition:enter-end="motion-slide-inline-end"
                            x-transition:leave="motion-leave"
                            x-transition:leave-start="motion-slide-inline-end"
                            x-transition:leave-end="motion-slide-inline-start">
                            {{ __('app.Letters') }}
                        </span>
                    </span>
                    <svg x-show="!compact" xmlns="http://www.w3.org/2000/svg" class="sidebar-icon h-4 w-4 transition-fast"
                        :class="{ 'rotate-90': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <template x-if="loaded">
                    <div x-show="open && !compact"
                        x-transition:enter="motion-enter-fast"
                        x-transition:enter-start="motion-slide-up-start"
                        x-transition:enter-end="motion-slide-up-end"
                        x-transition:leave="motion-leave"
                        x-transition:leave-start="motion-slide-up-end"
                        x-transition:leave-end="motion-slide-up-start"
                        class="pl-11 space-y-1">
                    @if($canManageTemplates)
                    {{-- UPDATED: Sub-menu active/hover uses Secondary Brand Orange for accent --}}
                    <a href="{{ route('letter-templates.index') }}"
                        class="sidebar-submenu-item focus-ring
                        {{ $letterTemplatesActive ? 'sidebar-submenu-item-active' : 'sidebar-submenu-item-inactive' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="sidebar-icon h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 4h10a2 2 0 012 2v11a2 2 0 01-2 2H8l-4 3V6a2 2 0 012-2h2z" />
                        </svg>
                        <span>{{ __('app.letter_templates') }}</span>
                    </a>
                    @endif

                    @if($hasLetterCategories && $canManageTemplates)
                    <a href="{{ route('letter-categories.index') }}"
                        class="sidebar-submenu-item focus-ring
                        {{ $letterCategoriesActive ? 'sidebar-submenu-item-active' : 'sidebar-submenu-item-inactive' }}">
                        <x-heroicon-o-rectangle-stack class="sidebar-icon h-4 w-4" aria-hidden="true" />
                        <span>{{ __('app.letter_categories') }}</span>
                    </a>
                    @endif

                    @if($canViewLetters)
                    <a href="{{ route('letters.index') }}"
                        class="sidebar-submenu-item focus-ring
                        {{ $lettersActive ? 'sidebar-submenu-item-active' : 'sidebar-submenu-item-inactive' }}">
                        <x-heroicon-o-envelope-open class="sidebar-icon h-4 w-4" aria-hidden="true" />
                        <span>{{ __('app.Letters') }}</span>
                    </a>
                    @endif

                    @if($canComposeLetters)
                    <a href="{{ route('letters.compose') }}"
                        class="sidebar-submenu-item focus-ring
                        {{ $composeActive ? 'sidebar-submenu-item-active' : 'sidebar-submenu-item-inactive' }}">
                        <x-heroicon-o-pencil-square class="sidebar-icon h-4 w-4" aria-hidden="true" />
                        <span>{{ __('app.compose_letter') }}</span>
                    </a>
                    @endif
                    </div>
                </template>
            </div>
            @endif

            {{-- Notifications --}}
            @if($hasNotifIndex)
            <a href="{{ route('admin.notifications.index') }}"
                class="sidebar-menu-item focus-ring
                {{ request()->routeIs('admin.notifications.*') ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive' }}">
                <div class="grid place-items-center w-6" aria-hidden="true">
                    <x-heroicon-o-bell class="sidebar-icon h-4 w-4" aria-hidden="true" />
                </div>
                <span class="sidebar-menu-label truncate origin-left"
                    x-show="!compact"
                    x-transition:enter="motion-enter"
                    x-transition:enter-start="motion-slide-inline-start"
                    x-transition:enter-end="motion-slide-inline-end"
                    x-transition:leave="motion-leave"
                    x-transition:leave-start="motion-slide-inline-end"
                    x-transition:leave-end="motion-slide-inline-start">
                    {{ __('app.Notifications') }}
                </span>
            </a>
            @endif

            {{-- Announcements --}}
            @if($hasAnnouncements && auth()->user()?->hasPermission('announcements.manage'))
            <a href="{{ route('announcements.index') }}"
                class="sidebar-menu-item focus-ring
                {{ request()->routeIs('announcements.*') ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive' }}">
                <div class="grid place-items-center w-6" aria-hidden="true">
                    <x-heroicon-o-megaphone class="sidebar-icon h-4 w-4" aria-hidden="true" />
                </div>
                <span class="sidebar-menu-label truncate origin-left"
                    x-show="!compact"
                    x-transition:enter="motion-enter"
                    x-transition:enter-start="motion-slide-inline-start"
                    x-transition:enter-end="motion-slide-inline-end"
                    x-transition:leave="motion-leave"
                    x-transition:leave-start="motion-slide-inline-end"
                    x-transition:leave-end="motion-slide-inline-start">
                    {{ __('app.Announcements') }}
                </span>
            </a>
            @endif

            @if($showPeopleSection)
            <div class="sidebar-section-label" x-show="!compact">{{ __('app.users_access') }}</div>
            @endif

            {{-- User Control --}}
            @if($canManageUsers || $canManagePermissions || $canManageRoles)
            <div x-data="{ open: {{ $userControlOpen ? 'true' : 'false' }}, loaded: {{ $userControlOpen ? 'true' : 'false' }} }" class="space-y-1">
                {{-- UPDATED: Main button active uses Primary Blue --}}
                <button type="button"
                    class="sidebar-menu-toggle focus-ring
                    {{ $userControlOpen ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive' }}"
                    @click="if (!loaded) loaded = true; open = !open"
                    aria-haspopup="true"
                    :aria-expanded="open.toString()">
                    <span class="flex items-center gap-3">
                        <div class="grid place-items-center w-6" aria-hidden="true">
                            <x-heroicon-o-users class="sidebar-icon h-4 w-4" aria-hidden="true" />
                        </div>
                        <span class="sidebar-menu-label truncate origin-left"
                            x-show="!compact"
                            x-transition:enter="motion-enter"
                            x-transition:enter-start="motion-slide-inline-start"
                            x-transition:enter-end="motion-slide-inline-end"
                            x-transition:leave="motion-leave"
                            x-transition:leave-start="motion-slide-inline-end"
                            x-transition:leave-end="motion-slide-inline-start">
                            {{ __('app.user_control') }}
                        </span>
                    </span>
                    <svg x-show="!compact" xmlns="http://www.w3.org/2000/svg" class="sidebar-icon h-4 w-4 transition-fast"
                        :class="{ 'rotate-90': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <template x-if="loaded">
                    <div x-show="open && !compact"
                        x-transition:enter="motion-enter-fast"
                        x-transition:enter-start="motion-slide-up-start"
                        x-transition:enter-end="motion-slide-up-end"
                        x-transition:leave="motion-leave"
                        x-transition:leave-start="motion-slide-up-end"
                        x-transition:leave-end="motion-slide-up-start"
                        class="pl-11 space-y-1">
                    @if($canManageUsers)
                    {{-- UPDATED: Sub-menu active/hover uses Secondary Brand Orange for accent --}}
                    <a href="{{ route('users.index') }}"
                        class="sidebar-submenu-item focus-ring
                        {{ $usersActive ? 'sidebar-submenu-item-active' : 'sidebar-submenu-item-inactive' }}">
                        <x-heroicon-o-user class="sidebar-icon h-4 w-4" aria-hidden="true" />
                        <span>{{ __('app.Users') }}</span>
                    </a>
                    @endif

                    @if($canManagePermissions)
                    <a href="{{ route('permissions.index') }}"
                        class="sidebar-submenu-item focus-ring
                        {{ $permissionsActive ? 'sidebar-submenu-item-active' : 'sidebar-submenu-item-inactive' }}">
                        <x-heroicon-o-key class="sidebar-icon h-4 w-4" aria-hidden="true" />
                        <span>{{ __('app.Permissions') }}</span>
                    </a>
                    @endif


                    @if($canManageTeams)
                    <a href="{{ route('teams.index') }}"
                        class="sidebar-submenu-item focus-ring
                        {{ $teamsActive ? 'sidebar-submenu-item-active' : 'sidebar-submenu-item-inactive' }}">
                        <x-heroicon-o-squares-2x2 class="sidebar-icon h-4 w-4" aria-hidden="true" />
                        <span>{{ __('app.Teams') }}</span>
                    </a>
                    @endif

                    @if($canManageRoles)
                    <a href="{{ route('roles.index') }}"
                        class="sidebar-submenu-item focus-ring
                {{ $rolesActive ? 'sidebar-submenu-item-active' : 'sidebar-submenu-item-inactive' }}">
                        <x-heroicon-o-check-badge class="sidebar-icon h-4 w-4" aria-hidden="true" />
                        <span>{{ __('app.Roles') }}</span>
                    </a>
                    @endif
                    </div>
                </template>
            </div>
            @endif

            @if($settingsDropdownVisible)
            <div class="sidebar-section-label" x-show="!compact">{{ __('app.system_section') }}</div>
            @endif

            {{-- Settings --}}
            @if($settingsDropdownVisible)
            <div x-data="{ open: {{ json_encode($settingsMenuOpen) }}, loaded: {{ $settingsMenuOpen ? 'true' : 'false' }} }" class="space-y-1">
                <button type="button"
                    class="sidebar-menu-toggle focus-ring
                {{ $settingsMenuOpen ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive' }}"
                    @click="if (!loaded) loaded = true; open = !open"
                    aria-haspopup="true"
                    :aria-expanded="open.toString()">
                    <span class="flex items-center gap-3">
                        <div class="grid place-items-center w-6" aria-hidden="true">
                            <x-heroicon-o-cog class="sidebar-icon h-4 w-4" aria-hidden="true" />
                        </div>
                        <span class="sidebar-menu-label truncate origin-left"
                            x-show="!compact"
                            x-transition:enter="motion-enter"
                            x-transition:enter-start="motion-slide-inline-start"
                            x-transition:enter-end="motion-slide-inline-end"
                            x-transition:leave="motion-leave"
                            x-transition:leave-start="motion-slide-inline-end"
                            x-transition:leave-end="motion-slide-inline-start">
                            {{ __('app.Settings') }}
                        </span>
                    </span>
                    <svg x-show="!compact" xmlns="http://www.w3.org/2000/svg" class="sidebar-icon h-4 w-4 transition-fast"
                        :class="open ? 'rotate-90' : 'rotate-0'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <template x-if="loaded">
                    <div x-show="open && !compact"
                        x-transition:enter="motion-enter-fast"
                        x-transition:enter-start="motion-slide-up-start"
                        x-transition:enter-end="motion-slide-up-end"
                        x-transition:leave="motion-leave"
                        x-transition:leave-start="motion-slide-up-end"
                        x-transition:leave-end="motion-slide-up-start"
                        class="pl-11 space-y-1">
                    @if($hasSystemSettings && auth()->user()?->hasPermission('settings.manage'))
                    <a href="{{ route('settings.system.edit') }}"
                        class="sidebar-submenu-item focus-ring
                    {{ request()->routeIs('settings.system.*') ? 'sidebar-submenu-item-active' : 'sidebar-submenu-item-inactive' }}">
                        <x-heroicon-o-adjustments-horizontal class="sidebar-icon h-4 w-4" aria-hidden="true" />
                        <span>{{ __('app.System_Settings') }}</span>
                    </a>
                    @endif

                    @if($hasTerms && auth()->user()?->hasPermission('settings.manage'))
                    <a href="{{ route('terms.index') }}"
                        class="sidebar-submenu-item focus-ring
                    {{ request()->routeIs('terms.*') ? 'sidebar-submenu-item-active' : 'sidebar-submenu-item-inactive' }}">
                        <x-heroicon-o-document-text class="sidebar-icon h-4 w-4" aria-hidden="true" />
                        <span>{{ __('app.Terms') }}</span>
                    </a>
                    @endif

                    @if($hasAbout && auth()->user()?->hasPermission('about.manage'))
                    <a href="{{ route('about.index') }}"
                        class="sidebar-submenu-item focus-ring
                    {{ request()->routeIs('about.*') ? 'sidebar-submenu-item-active' : 'sidebar-submenu-item-inactive' }}">
                        <x-heroicon-o-information-circle class="sidebar-icon h-4 w-4" aria-hidden="true" />
                        <span>{{ __('app.About') }}</span>
                    </a>
                    @endif

                    @if($hasAudit)
                    <a href="{{ route('admin.audit') }}"
                        class="sidebar-submenu-item focus-ring
                    {{ request()->routeIs('admin.audit') ? 'sidebar-submenu-item-active' : 'sidebar-submenu-item-inactive' }}">
                        <x-heroicon-o-eye class="sidebar-icon h-4 w-4" aria-hidden="true" />
                        <span>{{ __('app.System_Audit') }}</span>
                    </a>
                    @endif
                    </div>
                </template>
            </div>
            @endif

                </nav>
            </template>
        </div>
    </aside>

    {{-- Mobile overlay --}}
    <div class="md:hidden fixed inset-0 bg-black/40 z-30 transition-opacity-slow"
        x-show="sidebar" x-cloak @click="sidebar=false"
        x-transition:enter="motion-enter"
        x-transition:enter-start="motion-fade-start"
        x-transition:enter-end="motion-fade-end"
        x-transition:leave="motion-leave"
        x-transition:leave-start="motion-fade-end"
        x-transition:leave-end="motion-fade-start"></div>

    {{-- Main --}}
    <div id="admin-panel"
        class="flex min-h-screen flex-1 flex-col transition-padding-slow md:ml-64"
        :class="{
            'md:ml-20': compact,
            'md:ml-64': !compact
        }">

        {{-- Topbar --}}
        <header class="sticky top-0 relative z-50 flex items-center justify-between border-b border-slate-200/80 bg-white/90 px-3 py-3 shadow-sm backdrop-blur-xl md:px-6">
            <div class="flex items-center gap-2">
                {{-- Mobile: open sidebar --}}
                <button type="button"
                    {{-- UPDATED: Icon uses Primary Brand Blue --}}
                    class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-blue-600 focus-ring"
                    @click="sidebar=true"
                    aria-label="{{ __('app.Open sidebar') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                {{-- Desktop: collapse / expand --}}
                <button type="button"
                    {{-- UPDATED: Icon uses Primary Brand Blue --}}
                    class="hidden md:inline-flex items-center justify-center w-9 h-9 rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-blue-600 transition-fast focus-ring"
                    @click="toggleCompact()" :aria-pressed="compact.toString()"
                    aria-label="{{ __('app.Toggle sidebar width') }}">
                    <svg x-show="!compact" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" x-cloak
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 12H4m6 6l-6-6 6-6" />
                    </svg>
                    <svg x-show="compact" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" x-cloak
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 12h16m-6-6l6 6-6 6" />
                    </svg>
                </button>

                <div class="ml-1 md:ml-2">
                    <h1 class="text-xl font-semibold tracking-tight text-slate-900">@yield('page_header', $t)</h1>
                    @php $currentRouteName = request()->route()?->getName(); @endphp
                    @if($currentRouteName)
                    <div class="hidden lg:block text-[11px] font-medium text-slate-500 tracking-wide">{{ str_replace('.', ' / ', $currentRouteName) }}</div>
                    @endif
                </div>
            </div>

            @php
            use Illuminate\Support\Str;
            use Illuminate\Support\Carbon;

            $uid = auth()->id();

            // stable timestamps for queries
            $now = Carbon::now();
            $todayDisplay = $now->translatedFormat('l') . ', ' . \App\Support\EthiopianDate::format($now);
            $cut14 = (clone $now)->subDays(14);
            $in14 = (clone $now)->addDays(14);

            // defaults
            $adminUnseenMsgs = collect();
            $adminUnseenCases = collect();
            $adminUpcomingHearings = collect();
            $adminRespondentViews = collect();

            if ($uid) {
            $adminUnseenMsgs = \DB::table('case_messages as m')
            ->join('court_cases as c', 'c.id', '=', 'm.case_id')
            ->select('m.id','m.body','m.created_at','c.case_number','c.id as case_id')
            ->whereNotNull('m.sender_applicant_id')
            ->where('m.created_at', '>=', $cut14)
            ->whereNotExists(function($q) use ($uid) {
            $q->from('admin_notification_reads as nr')
            ->whereColumn('nr.source_id', 'm.id')
            ->where('nr.type', 'message')
            ->where('nr.user_id', $uid);
            })
            ->orderByDesc('m.created_at')
            ->limit(5)
            ->get();

            $adminUnseenCases = \DB::table('court_cases as c')
            ->select('c.id','c.case_number','c.title','c.created_at')
            ->where('c.status', 'pending')
            ->whereNull('c.assigned_user_id')
            ->where('c.created_at', '>=', $cut14)
            ->whereNotExists(function($q) use ($uid) {
            $q->from('admin_notification_reads as nr')
            ->whereColumn('nr.source_id', 'c.id')
            ->where('nr.type', 'case')
            ->where('nr.user_id', $uid);
            })
            ->orderByDesc('c.created_at')
            ->limit(5)
            ->get();

            $adminUpcomingHearings = \DB::table('case_hearings as h')
            ->join('court_cases as c', 'c.id', '=', 'h.case_id')
            ->select('h.id','h.hearing_at','c.id as case_id','c.case_number')
            ->where('c.assigned_user_id', $uid)
            ->whereBetween('h.hearing_at', [$now, $in14])
            ->whereNotExists(function($q) use ($uid) {
            $q->from('admin_notification_reads as nr')
            ->whereColumn('nr.source_id', 'h.id')
            ->where('nr.type', 'hearing')
            ->where('nr.user_id', $uid);
            })
            ->orderBy('h.hearing_at')
            ->limit(5)
            ->get();

            $adminRespondentViews = \DB::table('respondent_case_views as v')
            ->join('court_cases as c', 'c.id', '=', 'v.case_id')
            ->join('respondents as r', 'r.id', '=', 'v.respondent_id')
            ->select(
            'v.id',
            'v.viewed_at',
            'v.case_id',
            'c.case_number',
            \DB::raw(
            (\DB::getDriverName() === 'sqlite')
            ? "TRIM(COALESCE(r.first_name,'') || ' ' || COALESCE(r.middle_name,'') || ' ' || COALESCE(r.last_name,'')) as respondent_name"
            : "TRIM(CONCAT_WS(' ', r.first_name, r.middle_name, r.last_name)) as respondent_name"
            )
            )
            ->where(function($q) use ($uid) {
            $q->where('c.assigned_user_id', $uid)
            ->orWhereNull('c.assigned_user_id');
            })
            ->where('v.viewed_at', '>=', $cut14)
            ->whereNotExists(function($q) use ($uid) {
            $q->from('admin_notification_reads as nr')
            ->whereColumn('nr.source_id', 'v.id')
            ->where('nr.type', 'respondent_view')
            ->where('nr.user_id', $uid);
            })
            ->orderByDesc('v.viewed_at')
            ->limit(5)
            ->get();
            }

            $__adminNotifCount = $adminUnseenMsgs->count() + $adminUnseenCases->count() + $adminUpcomingHearings->count() + ($adminRespondentViews->count() ?? 0);
            $u = auth()->user();
            @endphp

            <div class="hidden md:flex flex-1 justify-center">
                {{-- UPDATED: Date display uses Primary Brand Blue (Authority) --}}
                <span id="top-date-display" class="rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-700">{{ $todayDisplay }}</span>
            </div>

            <div class="flex items-center gap-3 flex-shrink-0">
                <x-ui.theme-toggle />

                {{-- Language switcher --}}
                @auth
                @if($hasLangSwitch)
                <div x-data="{ open:false }" class="relative">
                    <button @click="open=!open"
                        class="flex items-center gap-1 px-3 py-1.5 rounded-xl border border-slate-300 bg-white/90 shadow-sm hover:bg-slate-50 text-sm font-medium focus-ring">
                        <span class="fi fi-{{ app()->getLocale() == 'am' ? 'et' : 'us' }}"></span>
                        {{-- UPDATED: Icon uses Primary Brand Blue --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 5h16M4 10h16M10 15h10M4 20h10" />
                        </svg>
                        <span class="text-sm">{{ __('app.Language') }}</span>
                    </button>

                    <div x-cloak x-show="open" @click.outside="open=false"
                        class="absolute right-0 mt-2 w-36 rounded-xl border border-slate-200 bg-white shadow-xl z-50 overflow-hidden">
                        <div class="p-1 space-y-1">
                            {{-- UPDATED: Active state uses Secondary Brand Orange --}}
                            <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
                                class="flex items-center gap-2 w-full px-3 py-2 text-sm hover:bg-gray-100 rounded-md transition
                                {{ app()->getLocale() == 'en' ? 'bg-orange-50 text-orange-700 font-semibold' : 'text-gray-700' }}">
                                <span class="fi fi-us"></span>
                                {{ __('app.English') }}
                            </a>
                            <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
                                class="flex items-center gap-2 w-full px-3 py-2 text-sm hover:bg-gray-100 rounded-md transition
                                {{ app()->getLocale() == 'am' ? 'bg-orange-50 text-orange-700 font-semibold' : 'text-gray-700' }}">
                                <span class="fi fi-et"></span>
                                {{ __('app.Amharic') }}
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Admin Notifications bell --}}
                @if($hasNotifIndex)
                <div class="relative" x-data="{ bell:false }">
                    <button @click="bell=!bell" type="button"
                        class="relative inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-2.5 py-1.5 hover:bg-slate-50 shadow-sm focus-ring"
                        aria-label="{{ __('app.Notifications') }}" :aria-expanded="bell.toString()">
                        {{-- UPDATED: Icon uses Primary Brand Blue --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0h6z" />
                        </svg>
                        @if($__adminNotifCount > 0)
                        {{-- Kept Red for alerts (standard practice for notifications) --}}
                        <span class="absolute top-0 right-0 transform translate-x-1/2 -translate-y-1/2 grid h-5 min-w-[20px] place-items-center rounded-full bg-red-600 px-1 text-[11px] font-bold text-white shadow-md">
                            {{ $__adminNotifCount > 99 ? '99+' : $__adminNotifCount }}
                        </span>
                        @endif
                    </button>

                    {{-- Dropdown --}}
                    <div x-cloak x-show="bell" @click.outside="bell=false"
                        x-transition:enter="motion-enter-fast"
                        x-transition:enter-start="motion-slide-up-start"
                        x-transition:enter-end="motion-slide-up-end"
                        x-transition:leave="motion-leave"
                        x-transition:leave-start="motion-slide-up-end"
                        x-transition:leave-end="motion-slide-up-start"
                        class="absolute right-0 mt-2 w-[32rem] max-w-[90vw] rounded-lg border border-gray-200 bg-white shadow-xl z-50">
                        <div class="p-3">
                            <div class="mb-2 flex items-center justify-between border-b pb-2">
                                <div class="text-base font-bold text-gray-800">{{ __('app.Notifications') }} ({{ $__adminNotifCount }})</div>
                                @if($__adminNotifCount > 0 && $hasNotifMarkAll)
                                <form method="POST" action="{{ route('admin.notifications.markAll') }}">
                                    @csrf
                                    <button type="submit" class="text-xs px-2 py-1 rounded-md border border-gray-300 bg-white hover:bg-gray-100 text-gray-600 focus-ring">
                                        {{ __('app.Mark all as seen') }}
                                    </button>
                                </form>
                                @endif
                            </div>

                            @if($__adminNotifCount === 0)
                            <div class="text-sm text-gray-500 py-4 text-center">{{ __('app.youre_all_caught_up') }} 🎉</div>
                            @else
                            {{-- Applicant messages --}}
                            @if($adminUnseenMsgs->isNotEmpty())
                            <div class="mt-3">
                                {{-- UPDATED: Notification headers use Primary Brand Blue --}}
                                <div class="text-sm font-bold text-blue-700 mb-1">{{ __('app.Applicant messages') }}</div>
                                <ul class="divide-y divide-gray-100">
                                    @foreach($adminUnseenMsgs as $m)
                                    @php
                                    $legacyApplicantUpdate = 'Applicant updated the case details. Please review the submission.';
                                    $displayBody = trim((string) $m->body) === $legacyApplicantUpdate
                                        ? __('cases.notifications.applicant_updated_submission')
                                        : (string) $m->body;
                                    @endphp
                                    <li class="py-2 flex items-center justify-between hover:bg-gray-50 rounded-md px-1">
                                        <a href="{{ $hasCases ? route('cases.show', $m->case_id) : '#' }}" class="text-sm flex-1 mr-4">
                                            <div class="font-medium text-gray-900 truncate">{{ $m->case_number }}</div>
                                            <div class="text-xs text-gray-600">
                                                {{ Str::limit($displayBody, 80) }}
                                                · {{ \Illuminate\Support\Carbon::parse($m->created_at)->diffForHumans() }}
                                            </div>
                                        </a>
                                        @if($hasNotifMarkOne)
                                        <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                                            @csrf
                                            <input type="hidden" name="type" value="message">
                                            <input type="hidden" name="sourceId" value="{{ $m->id }}">
                                            <button type="submit" class="flex-shrink-0 text-xs px-2 py-1 rounded-md border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 focus-ring">{{ __('app.Seen') }}</button>
                                        </form>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            {{-- New cases --}}
                            @if($adminUnseenCases->isNotEmpty())
                            <div class="mt-3">
                                {{-- UPDATED: Notification headers use Primary Brand Blue --}}
                                <div class="text-sm font-bold text-blue-700 mb-1">{{ __('app.New cases') }}</div>
                                <ul class="divide-y divide-gray-100">
                                    @foreach($adminUnseenCases as $c)
                                    <li class="py-2 flex items-center justify-between hover:bg-gray-50 rounded-md px-1">
                                        <a href="{{ $hasCases ? route('cases.show', $c->id) : '#' }}" class="text-sm flex-1 mr-4">
                                            <div class="font-medium text-gray-900 truncate">{{ $c->case_number }}</div>
                                            <div class="text-xs text-gray-600">
                                                {{ Str::limit($c->title, 80) }}
                                                · {{ \Illuminate\Support\Carbon::parse($c->created_at)->diffForHumans() }}
                                            </div>
                                        </a>
                                        @if($hasNotifMarkOne)
                                        <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                                            @csrf
                                            <input type="hidden" name="type" value="case">
                                            <input type="hidden" name="sourceId" value="{{ $c->id }}">
                                            <button type="submit" class="flex-shrink-0 text-xs px-2 py-1 rounded-md border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 focus-ring">{{ __('app.Seen') }}</button>
                                        </form>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            {{-- Upcoming hearings --}}
                            @if($adminUpcomingHearings->isNotEmpty())
                            <div class="mt-3">
                                {{-- UPDATED: Notification headers use Primary Brand Blue --}}
                                <div class="text-sm font-bold text-blue-700 mb-1">{{ __('app.Upcoming hearings') }}</div>
                                <ul class="divide-y divide-gray-100">
                                    @foreach($adminUpcomingHearings as $h)
                                    <li class="py-2 flex items-center justify-between hover:bg-gray-50 rounded-md px-1">
                                        <a href="{{ $hasCases ? route('cases.show', $h->case_id) : '#' }}" class="text-sm flex-1 mr-4">
                                            <div class="font-medium text-gray-900 truncate">
                                                {{ $h->case_number }} —
                                                {{ \App\Support\EthiopianDate::format($h->hearing_at, withTime: true) }}
                                            </div>
                                            <div class="text-xs text-gray-600">
                                                {{ optional($h)->type ?: 'Hearing' }} · {{ optional($h)->location ?: '—' }}
                                            </div>
                                        </a>
                                        @if($hasNotifMarkOne)
                                        <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                                            @csrf
                                            <input type="hidden" name="type" value="hearing">
                                            <input type="hidden" name="sourceId" value="{{ $h->id }}">
                                            <button type="submit" class="flex-shrink-0 text-xs px-2 py-1 rounded-md border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 focus-ring">
                                                {{ __('app.Seen') }}
                                            </button>
                                        </form>
                                        @endif
                                    </li>
                                    @endforeach

                                </ul>
                            </div>
                            @endif

                            {{-- Respondent views --}}
                            @if($adminRespondentViews->isNotEmpty())
                            <div class="mt-3">
                                {{-- UPDATED: Notification headers use Primary Brand Blue --}}
                                <div class="text-xs font-bold text-blue-700 mb-1">{{ __('app.admin_notifications.respondent_views') }}</div>
                                <ul class="divide-y divide-gray-100">
                                    @foreach($adminRespondentViews as $v)
                                    <li class="py-2 flex items-center justify-between hover:bg-gray-50 rounded-md px-1">
                                        <a href="{{ $hasCases ? route('cases.show', $v->case_id) : '#' }}" class="text-sm flex-1 mr-4">
                                            <div class="font-medium text-gray-900 truncate">{{ $v->case_number }}</div>
                                            <div class="text-xs text-gray-600">
                                                {{ __('app.admin_notifications.respondent_viewed_case', ['name' => ($v->respondent_name ?: __('app.admin_notifications.respondent_default'))]) }}
                                                · {{ \Illuminate\Support\Carbon::parse($v->viewed_at)->diffForHumans() }}
                                            </div>
                                        </a>
                                        @if($hasNotifMarkOne)
                                        <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                                            @csrf
                                            <input type="hidden" name="type" value="respondent_view">
                                            <input type="hidden" name="sourceId" value="{{ $v->id }}">
                                            <button type="submit" class="flex-shrink-0 text-xs px-2 py-1 rounded-md border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 focus-ring">{{ __('app.Seen') }}</button>
                                        </form>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <div class="mt-3 flex items-center justify-end border-t pt-2">
                                <a href="{{ route('admin.notifications.index') }}"
                                    class="text-sm px-3 py-1.5 rounded-md border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 font-medium focus-ring">
                                    {{ __('app.View all') }}
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                @endauth

                {{-- Profile / Logout --}}
                @php $u = auth()->user(); @endphp
                <div x-data="{ open:false }" class="relative">
                    <button @click="open=!open" class="flex items-center gap-3 rounded-full px-3 py-1.5 hover:bg-gray-100 focus-ring"
                        aria-haspopup="menu" :aria-expanded="open.toString()">
                        <span class="text-sm text-gray-700 hidden sm:inline font-medium">{{ __('app.hi_name', ['name' => $u->name ?? 'Admin']) }}</span>

                        @if($u?->avatar_url)
                        <img src="{{ $u->avatar_url }}" class="w-8 h-8 rounded-full object-cover" alt="{{ __('app.Avatar') }}">
                        @else
                        {{-- UPDATED: Avatar fallback uses Secondary Brand Orange --}}
                        <div class="w-8 h-8 rounded-full bg-orange-600 grid place-items-center font-bold text-white text-sm" aria-hidden="true">
                            {{ strtoupper(substr($u->name ?? 'A',0,1)) }}
                        </div>
                        @endif
                    </button>

                    <div x-cloak x-show="open" @click.outside="open=false"
                        x-transition:enter="motion-enter-fast"
                        x-transition:enter-start="motion-slide-up-start"
                        x-transition:enter-end="motion-slide-up-end"
                        x-transition:leave="motion-leave"
                        x-transition:leave-start="motion-slide-up-end"
                        x-transition:leave-end="motion-slide-up-start"
                        class="absolute right-0 mt-2 w-56 rounded-lg border border-gray-200 bg-white shadow-xl overflow-hidden">
                        <div class="px-4 py-2 text-xs text-gray-500 border-b border-gray-100">
                            {{ __('app.Signed in as') }} <span class="text-gray-800 font-medium">{{ $u?->email }}</span>
                        </div>

                        @if($hasProfileEdit)
                        <a href="{{ route('profile.edit') }}"
                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-fast focus-ring focus:bg-gray-100">
                            {{-- UPDATED: Icon uses Primary Brand Blue --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ __('app.Profile') }}
                        </a>
                        @endif

                        @if($hasLogout)
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition border-t border-gray-100 focus-ring focus:bg-gray-100">
                                {{-- UPDATED: Icon uses Primary Brand Blue --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                {{ __('app.Logout') }}
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="page-enter flex-1 p-4 md:p-6 xl:p-8 {{ $isCaseTypographyRoute ? 'case-font-scope case-typography' : '' }}">
            @if(session('success'))
            <x-ui.alert type="success" class="mb-6">
                {{ session('success') }}
            </x-ui.alert>
            @endif
            @if(session('error'))
            <x-ui.alert type="error" class="mb-6">
                {{ session('error') }}
            </x-ui.alert>
            @endif

            <div class="ui-page-admin">
                {{ $slot }}
            </div>
        </main>
    </div>

    {{-- Alpine helpers (no changes needed) --}}
    <script>
        function lazyMenu() {
            return {
                loaded: false,
                init() {
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            this.loaded = true;
                        });
                    });
                }
            }
        }

        function layoutState() {
            return {
                sidebar: false, // mobile only
                compact: false, // desktop collapse state (persisted)
                init() {
                    try {
                        const saved = localStorage.getItem('admin_sidebar_compact');
                        this.compact = saved === '1';
                    } catch (e) {}
                    this.$watch('compact', (val) => {
                        try {
                            localStorage.setItem('admin_sidebar_compact', val ? '1' : '0');
                        } catch (e) {}
                    });
                },
                toggleCompact() {
                    this.compact = !this.compact;
                }
            }
        }
    </script>
    <script>
        (() => {
            const nav = document.querySelector('[data-spa-nav]');
            const panelSelector = '#admin-panel';
            if (!nav || !window.history?.pushState) return;

            const sameOrigin = (url) => {
                try {
                    return new URL(url, window.location.href).origin === window.location.origin;
                } catch (_) {
                    return false;
                }
            };

            const shouldIgnoreClick = (event, link) => (
                event.defaultPrevented ||
                event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ||
                event.button !== 0 ||
                !link.href ||
                !sameOrigin(link.href) ||
                (link.getAttribute('target') && link.getAttribute('target') !== '_self') ||
                link.hasAttribute('download') ||
                link.getAttribute('href').startsWith('#') ||
                link.href.startsWith('mailto:') ||
                link.href.startsWith('tel:')
            );

            const executeInlineScripts = (container) => {
                if (!container) return;
                container.querySelectorAll('script').forEach((oldScript) => {
                    if (oldScript.src) return; // external scripts already on the page
                    const script = document.createElement('script');
                    [...oldScript.attributes].forEach((attr) => script.setAttribute(attr.name, attr.value));
                    script.textContent = oldScript.textContent;
                    oldScript.replaceWith(script);
                });
            };

            const swapPanel = (doc) => {
                const current = document.querySelector(panelSelector);
                const incoming = doc.querySelector(panelSelector);
                if (!current || !incoming) return false;
                current.replaceWith(incoming);
                executeInlineScripts(document.querySelector(panelSelector));
                return true;
            };

            const swapNav = (doc) => {
                const incomingNav = doc.querySelector('[data-spa-nav]');
                if (!incomingNav || !nav) return;
                nav.innerHTML = incomingNav.innerHTML;
            };

            const navigate = async (url, push = true) => {
                const panel = document.querySelector(panelSelector);
                panel?.setAttribute('aria-busy', 'true');

                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        },
                        credentials: 'include'
                    });

                    if (!response.ok) throw new Error(`Request failed with status ${response.status}`);

                    const html = await response.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');

                    const swapped = swapPanel(doc);
                    if (!swapped) {
                        window.location.href = url;
                        return;
                    }

                    swapNav(doc);

                    const title = doc.querySelector('title')?.textContent;
                    if (title) document.title = title;

                    if (push) {
                        history.pushState({
                            url
                        }, '', url);
                    }

                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                } catch (error) {
                    console.error('Falling back to full reload due to navigation error', error);
                    window.location.href = url;
                } finally {
                    document.querySelector(panelSelector)?.removeAttribute('aria-busy');
                }
            };

            nav.addEventListener('click', (event) => {
                const link = event.target.closest('a[href]');
                if (!link || !nav.contains(link)) return;
                if (link.dataset.noSpa === 'true') return;
                if (shouldIgnoreClick(event, link)) return;

                event.preventDefault();
                navigate(link.href);

                // Close sidebar on mobile after navigation
                if (document.body.__x?.$data?.sidebar !== undefined) {
                    document.body.__x.$data.sidebar = false;
                }
            });

            window.addEventListener('popstate', (event) => {
                if (event.state?.url) {
                    navigate(event.state.url, false);
                }
            });

            history.replaceState({
                url: window.location.href
            }, '', window.location.href);
        })();
    </script>
    @if(app()->getLocale() === 'am')
    {{-- Ethiopian calendar (jQuery calendars) --}}
    <script>
        (function() {
            const applyTopbarDate = () => {
                const el = document.getElementById('top-date-display');
                if (!el) return false;

                // Manual conversion from Gregorian to Ethiopian
                const g = new Date();
                const gY = g.getFullYear();
                const gM = g.getMonth() + 1; // 1-12
                const gD = g.getDate();

                // Gregorian to Julian Day Number
                const g2j = (y, m, d) => {
                    const a = Math.floor((14 - m) / 12);
                    const y2 = y + 4800 - a;
                    const m2 = m + 12 * a - 3;
                    return d + Math.floor((153 * m2 + 2) / 5) + 365 * y2 + Math.floor(y2 / 4) - Math.floor(y2 / 100) + Math.floor(y2 / 400) - 32045;
                };
                // Julian Day Number to Ethiopian
                const j2e = (j) => {
                    const r = (j - 1723856) % 1461;
                    const n = (r % 365) + 365 * Math.floor(r / 1460);
                    const year = 4 * Math.floor((j - 1723856) / 1461) + Math.floor(r / 365) - Math.floor(r / 1460);
                    const month = Math.floor(n / 30) + 1;
                    const day = (n % 30) + 1;
                    return {
                        year,
                        month,
                        day
                    };
                };

                const jdn = g2j(gY, gM, gD);
                const et = j2e(jdn);

                const days = ['እሑድ', 'ሰኞ', 'ማክሰኞ', 'ረቡዕ', 'ሐሙስ', 'ዓርብ', 'ቅዳሜ'];
                const months = ['መስከረም', 'ጥቅምት', 'ኅዳር', 'ታህሳስ', 'ጥር', 'የካቲት', 'መጋቢት', 'ሚያዚያ', 'ግንቦት', 'ሰኔ', 'ሐምሌ', 'ነሐሴ', 'ጳጉሜ'];
                const dayName = days[g.getDay()];
                const monthName = months[et.month - 1] || '';
                el.textContent = `${dayName}, ${monthName} ${et.day}, ${et.year} ዓ.ም`;
                return true;
            };

            // Run once DOM is ready (no dependencies)
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', applyTopbarDate);
            } else {
                applyTopbarDate();
            }
        })();
    </script>
    @endif
    @livewireScripts
    @stack('scripts')
</body>

</html>




