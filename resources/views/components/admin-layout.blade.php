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

    $brandName = $systemSettings->app_name ?? config('app.name', 'CMS');
    $shortName = $systemSettings->short_name ?? 'CMS';
    $footerText = $systemSettings->footer_text ?? __('app.all_rights_reserved');
    $footerNow = now();
    @endphp
    <title>{{ $t }} | {{ $systemSettings->app_name ?? config('app.name','CMS') }}</title>
    <script>
        (() => {
            const theme = localStorage.getItem('theme') || 'system';
            const dark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.dataset.theme = theme;
            document.documentElement.dataset.accent = localStorage.getItem('accent') || 'blue';
        })();
    </script>
    <style>
        [x-cloak] {
            display: none !important
        }

        /* ── Page-load progress bar ─────────────────────────────── */
        #spa-progress {
            position: fixed;
            top: 0; left: 0;
            width: 0%;
            height: 3px;
            z-index: 9999;
            background: linear-gradient(90deg, rgb(var(--ac)) 0%, rgb(var(--ac-light,var(--ac))) 100%);
            border-radius: 0 3px 3px 0;
            opacity: 0;
            transition: width .22s ease, opacity .18s ease;
            pointer-events: none;
            box-shadow: 0 0 10px 1px rgb(var(--ac)/.45);
        }
        #spa-progress.is-running {
            opacity: 1;
        }
        #spa-progress.is-done {
            width: 100% !important;
            opacity: 0;
            transition: width .18s ease, opacity .35s ease .05s;
        }

        /* ── Full-screen court loader overlay ──────────────────── */
        #spa-loader {
            position: fixed;
            inset: 0;
            z-index: 9998;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,.38);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s ease;
        }
        #spa-loader.is-visible {
            opacity: 1;
            pointer-events: auto;
        }
        .spa-loader-wrap {
            position: relative;
            width: 88px;
            height: 88px;
        }
        /* The single combined SVG — ring + icon together, no separate elements */
        .spa-loader-wrap svg {
            display: block;
            width: 88px;
            height: 88px;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            background: transparent !important;
        }
        .spa-loader-arc {
            transform-origin: 44px 44px;
            animation: courtSpin 1.1s linear infinite;
        }
        @keyframes courtSpin {
            to { transform: rotate(360deg); }
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
    {{-- Top progress bar — driven by spaProgress() below --}}
    <div id="spa-progress" role="progressbar" aria-hidden="true"></div>

    {{-- Court loader overlay: single SVG — ring track + spinning arc + scales icon --}}
    <div id="spa-loader" aria-hidden="true">
        <div class="spa-loader-wrap">
            <svg viewBox="0 0 88 88" fill="none" xmlns="http://www.w3.org/2000/svg">
                {{-- Track circle --}}
                <circle cx="44" cy="44" r="40" stroke="rgba(255,255,255,0.13)" stroke-width="3.5"/>
                {{-- Spinning arc group --}}
                <g class="spa-loader-arc">
                    <circle cx="44" cy="44" r="40" stroke="rgb(var(--ac,59,130,246))" stroke-width="3.5"
                            stroke-linecap="round" stroke-dasharray="62 190" opacity=".95"/>
                </g>
                {{-- Scales of justice icon, centered at 44,44, scaled from 24x24 viewBox --}}
                <g transform="translate(20,20) scale(1.833)" stroke="white" stroke-width="1.5"
                   stroke-linecap="round" stroke-linejoin="round" fill="none"
                   filter="url(#iconShadow)">
                    <defs>
                        <filter id="iconShadow" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="1" stdDeviation="2" flood-color="rgba(0,0,0,0.5)"/>
                        </filter>
                    </defs>
                    <line x1="12" y1="3" x2="12" y2="21"/>
                    <path d="M3 6l9-3 9 3"/>
                    <path d="M6 6l-3 7a4 4 0 008 0L6 6z"/>
                    <path d="M18 6l-3 7a4 4 0 008 0L18 6z"/>
                    <line x1="5" y1="21" x2="19" y2="21"/>
                </g>
            </svg>
        </div>
    </div>

    @include('partials.admin-toasts')

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
    $hasDecisionTemplates = Route::has('decision-templates.index');
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
    $hasPerformanceEvaluationSettings = Route::has('settings.performance-evaluation.index');
    $hasTerms = Route::has('terms.index');
    $hasAbout = Route::has('about.index');
    $hasLetterTemplates = Route::has('letter-templates.index');
    $hasLetterCategories = Route::has('letter-categories.index');
    $hasLetterComposer = Route::has('letters.compose');
    $hasLetters = Route::has('letters.index');
    $hasAudit = Route::has('admin.audit');
    $hasLandingManager = Route::has('admin.landing.index');
    $hasReports = Route::has('reports.index');
    $hasAnnouncements = Route::has('announcements.index');
    $hasPerformanceEvaluations = Route::has('performance-evaluations.index');
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
    $decisionsListActive = request()->routeIs('decisions.*');
    $decisionTemplatesActive = request()->routeIs('decision-templates.*');
    $decisionMenuOpen = $decisionsListActive || $decisionTemplatesActive;
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
        || request()->routeIs('settings.performance-evaluation.*')
        || request()->routeIs('terms.*')
        || request()->routeIs('about.*')
        || request()->routeIs('admin.audit')
        || request()->routeIs('admin.landing.*');
    $canViewReports = $hasReports && auth()->user()?->hasPermission('reports.view');
    $canViewAudit = $hasAudit && auth()->user()?->hasPermission('audit.view');
    @endphp

    <aside
        class="fixed top-0 left-0 z-40 h-screen
               transform transition-transform-base
               -translate-x-full md:translate-x-0
               flex flex-col font-sidebar
               bg-[#0c1527]
               border-r border-white/[0.06] shadow-[1px_0_0_0_rgba(255,255,255,0.04),4px_0_24px_-4px_rgba(0,0,0,0.45)]
               w-72 transition-width-slow"
        :class="{
            'translate-x-0': sidebar,
            'md:w-[4.5rem]': compact,
            'md:w-64': !compact
        }"
        aria-label="{{ __('app.Sidebar') }}">

        {{-- Brand / collapse toggle row --}}
        <div class="flex items-center gap-2.5 px-3 py-3.5 border-b border-white/[0.07]"
             :class="compact ? 'justify-center' : ''">
            {{-- Logo / initial badge --}}
            <a href="{{ $hasDashboard ? route('dashboard') : url('/') }}" aria-label="{{ __('app.Dashboard') }}" class="focus-ring rounded-lg flex-shrink-0">
                @if(!empty($systemSettings?->logo_path))
                <img src="{{ asset('storage/'.$systemSettings->logo_path) }}"
                    alt="{{ $systemSettings->app_name ?? config('app.name','CMS') }}"
                    class="h-8 w-8 rounded-lg object-contain ring-1 ring-white/10">
                @else
                <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-blue-500 to-blue-700 grid place-items-center flex-shrink-0 ring-1 ring-blue-400/30 shadow-lg shadow-blue-900/40">
                    <span class="text-sm font-black text-white">{{ strtoupper(substr($systemSettings->short_name ?? config('app.name','C'), 0, 1)) }}</span>
                </div>
                @endif
            </a>

            {{-- App name (hidden in compact mode) --}}
            <div class="flex-1 min-w-0" x-show="!compact"
                x-transition:enter="motion-enter"
                x-transition:enter-start="motion-slide-inline-start"
                x-transition:enter-end="motion-slide-inline-end"
                x-transition:leave="motion-leave"
                x-transition:leave-start="motion-slide-inline-end"
                x-transition:leave-end="motion-slide-inline-start">
                <div class="text-white text-[13px] font-bold truncate leading-tight tracking-tight">{{ $systemSettings->app_name ?? config('app.name','CMS') }}</div>
                <div class="text-slate-500 text-[10px] font-medium tracking-wide truncate mt-px uppercase">Admin Portal</div>
            </div>

            {{-- Mobile close button --}}
            <button type="button"
                class="md:hidden flex-shrink-0 inline-flex items-center justify-center w-7 h-7 rounded-md text-slate-500 hover:text-slate-200 hover:bg-white/8 transition-colors duration-150 focus-ring"
                @click="sidebar=false"
                aria-label="{{ __('app.Close sidebar') }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Nav --}}
        <div id="admin-nav" data-spa-nav="true" class="flex-1 overflow-y-auto overflow-x-hidden scrollbar-thin">
                <nav class="space-y-0.5 px-2.5 py-3">
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
            || ($hasPerformanceEvaluationSettings && auth()->user()?->hasPermission('settings.manage'))
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


            {{-- Decisions dropdown --}}
            @php
            $canViewDecisions = $hasDecisions && auth()->user()?->hasPermission('decision.view');
            $canViewDecisionTemplates = $hasDecisionTemplates && auth()->user()?->hasPermission('decision.templet.view');
            @endphp
            @if($canViewDecisions || $canViewDecisionTemplates)
            <div x-data="{ open: {{ $decisionMenuOpen ? 'true' : 'false' }}, loaded: {{ $decisionMenuOpen ? 'true' : 'false' }} }" class="space-y-1">
                <button type="button"
                    class="sidebar-menu-toggle focus-ring
                    {{ $decisionMenuOpen ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive' }}"
                    @click="if (!loaded) loaded = true; open = !open"
                    aria-haspopup="true"
                    :aria-expanded="open.toString()">
                    <span class="flex items-center gap-3">
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
                        @if($canViewDecisions)
                        <a href="{{ route('decisions.index') }}"
                            class="sidebar-submenu-item focus-ring
                            {{ $decisionsListActive ? 'sidebar-submenu-item-active' : 'sidebar-submenu-item-inactive' }}">
                            <x-heroicon-o-document-text class="sidebar-icon h-4 w-4" aria-hidden="true" />
                            <span>{{ __('app.Decisions') }}</span>
                        </a>
                        @endif
                        @if($canViewDecisionTemplates)
                        <a href="{{ route('decision-templates.index') }}"
                            class="sidebar-submenu-item focus-ring
                            {{ $decisionTemplatesActive ? 'sidebar-submenu-item-active' : 'sidebar-submenu-item-inactive' }}">
                            <x-heroicon-o-document-duplicate class="sidebar-icon h-4 w-4" aria-hidden="true" />
                            <span>{{ __('decision_templates.title') }}</span>
                        </a>
                        @endif
                    </div>
                </template>
            </div>
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

            {{-- Performance Evaluations --}}
            @if($hasPerformanceEvaluations && auth()->user()?->hasPermission('performance-evaluations.view'))
            <a href="{{ route('performance-evaluations.index') }}"
                class="sidebar-menu-item focus-ring
                {{ request()->routeIs('performance-evaluations.*') ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive' }}">
                <div class="grid place-items-center w-6" aria-hidden="true">
                    <x-heroicon-o-chart-bar-square class="sidebar-icon h-4 w-4" aria-hidden="true" />
                </div>
                <span class="sidebar-menu-label truncate origin-left"
                    x-show="!compact"
                    x-transition:enter="motion-enter"
                    x-transition:enter-start="motion-slide-inline-start"
                    x-transition:enter-end="motion-slide-inline-end"
                    x-transition:leave="motion-leave"
                    x-transition:leave-start="motion-slide-inline-end"
                    x-transition:leave-end="motion-slide-inline-start">
                    {{ __('app.performance_evaluations') }}
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

                    @if($hasPerformanceEvaluationSettings && auth()->user()?->hasPermission('settings.manage'))
                    <a href="{{ route('settings.performance-evaluation.index') }}"
                        class="sidebar-submenu-item focus-ring
                    {{ request()->routeIs('settings.performance-evaluation.*') ? 'sidebar-submenu-item-active' : 'sidebar-submenu-item-inactive' }}">
                        <x-heroicon-o-chart-bar-square class="sidebar-icon h-4 w-4" aria-hidden="true" />
                        <span>{{ __('performance.settings.title') }}</span>
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

                    @if($hasLandingManager)
                    <a href="{{ route('admin.landing.index') }}"
                        class="sidebar-submenu-item focus-ring
                    {{ request()->routeIs('admin.landing.*') ? 'sidebar-submenu-item-active' : 'sidebar-submenu-item-inactive' }}">
                        <x-heroicon-o-home class="sidebar-icon h-4 w-4" aria-hidden="true" />
                        <span>Landing Page</span>
                    </a>
                    @endif

                    @if($canViewAudit)
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
        </div>

        {{-- User profile card --}}
        @php $__sidebarUser = auth()->user(); @endphp
        @if($__sidebarUser)
        <div class="sidebar-user-card">
            <div class="flex items-center gap-2.5" :class="compact ? 'justify-center' : ''">
                {{-- Avatar --}}
                @if(!empty($__sidebarUser->avatar_path))
                <img src="{{ asset('storage/'.$__sidebarUser->avatar_path) }}"
                    class="h-8 w-8 rounded-full object-cover ring-2 ring-white/10 flex-shrink-0 shadow-md"
                    alt="{{ $__sidebarUser->name }}">
                @else
                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-slate-600 to-slate-800 ring-1 ring-white/10 flex-shrink-0 grid place-items-center shadow-md">
                    <span class="text-xs font-bold text-slate-200">{{ strtoupper(substr($__sidebarUser->name ?? 'A', 0, 1)) }}</span>
                </div>
                @endif

                {{-- Name + role --}}
                <div class="flex-1 min-w-0" x-show="!compact"
                    x-transition:enter="motion-enter"
                    x-transition:enter-start="motion-slide-inline-start"
                    x-transition:enter-end="motion-slide-inline-end"
                    x-transition:leave="motion-leave"
                    x-transition:leave-start="motion-slide-inline-end"
                    x-transition:leave-end="motion-slide-inline-start">
                    <div class="text-[12.5px] font-semibold text-slate-200 truncate leading-tight">{{ $__sidebarUser->name ?? 'Admin' }}</div>
                    <div class="text-[10.5px] text-slate-500 truncate capitalize mt-px">{{ $__sidebarUser->user_type ?? 'Administrator' }}</div>
                </div>

                {{-- Logout --}}
                <div x-show="!compact"
                    x-transition:enter="motion-enter"
                    x-transition:enter-start="motion-slide-inline-start"
                    x-transition:enter-end="motion-slide-inline-end"
                    x-transition:leave="motion-leave"
                    x-transition:leave-start="motion-slide-inline-end"
                    x-transition:leave-end="motion-slide-inline-start">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex h-7 w-7 items-center justify-center rounded-md text-slate-600 hover:text-rose-400 hover:bg-rose-500/10 transition-colors duration-150 focus-ring"
                            title="{{ __('app.Logout') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif
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
            'md:ml-[4.5rem]': compact,
            'md:ml-64': !compact
        }">

        {{-- ══════════════════════════════════════════════════════
             Top Navigation Bar
             ══════════════════════════════════════════════════════ --}}
        @php
        use Illuminate\Support\Str;
        use Illuminate\Support\Carbon;

        $uid = auth()->id();
        $now = Carbon::now();
        $todayDisplay = $now->translatedFormat('l') . ', ' . \App\Support\EthiopianDate::format($now);
        $cut14 = (clone $now)->subDays(14);
        $in14  = (clone $now)->addDays(14);

        $adminUnseenMsgs       = collect();
        $adminUnseenCases      = collect();
        $adminUpcomingHearings = collect();
        $adminRespondentViews  = collect();
        $adminUnseenMsgCount = 0;
        $adminUnseenCaseCount = 0;
        $adminUpcomingHearingCount = 0;
        $adminRespondentViewCount = 0;

        $adminNotificationUser = auth()->user();
        $canSeeAdminCaseNotifications = $adminNotificationUser?->hasPermission('cases.view') ?? false;
        $adminNotificationMemberScopeIds = [];
        $adminNotificationLeaderTeamId = null;
        $adminNotificationIsTeamMember = false;
        $adminNotificationIsLeader = false;
        $adminNotificationCanAssignTeams = false;

        if ($uid && $canSeeAdminCaseNotifications) {
            $adminNotificationIsLeader = $adminNotificationUser?->hasPermission('cases.assign.member') ?? false;
            $adminNotificationCanAssignTeams = $adminNotificationUser?->hasPermission('cases.assign.team') ?? false;

            if ($adminNotificationIsLeader && !$adminNotificationCanAssignTeams) {
                $adminNotificationLeaderTeam = \App\Models\Team::with(['users' => fn ($q) => $q->where('status', 'active')->orderBy('name')])
                    ->where('team_leader_id', $uid)
                    ->first();
                $adminNotificationMemberScopeIds = collect([$uid])
                    ->merge($adminNotificationLeaderTeam?->users?->pluck('id') ?? collect())
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                $adminNotificationLeaderTeamId = $adminNotificationLeaderTeam?->id;
            }

            $adminNotificationIsTeamMember = \DB::table('team_user')->where('user_id', $uid)->exists();
        }

        $applyAdminCaseNotificationScope = function ($query, string $caseAlias = 'c') use (
            $uid,
            $adminNotificationMemberScopeIds,
            $adminNotificationLeaderTeamId,
            $adminNotificationIsTeamMember,
            $adminNotificationIsLeader,
            $adminNotificationCanAssignTeams
        ) {
            if (!empty($adminNotificationMemberScopeIds)) {
                return $query->where(function ($q) use ($caseAlias, $adminNotificationMemberScopeIds, $adminNotificationLeaderTeamId) {
                    $q->whereIn("{$caseAlias}.assigned_user_id", $adminNotificationMemberScopeIds);
                    if ($adminNotificationLeaderTeamId) {
                        $q->orWhere("{$caseAlias}.assigned_team_id", $adminNotificationLeaderTeamId);
                    }
                });
            }

            if ($adminNotificationIsTeamMember && !$adminNotificationIsLeader && !$adminNotificationCanAssignTeams) {
                return $query->where(function ($q) use ($caseAlias, $uid) {
                    $q->where("{$caseAlias}.assigned_member_user_id", $uid)
                        ->orWhere("{$caseAlias}.assigned_user_id", $uid);
                });
            }

            return $query;
        };

        if ($uid && $canSeeAdminCaseNotifications) {
            $adminUnseenMsgs = $applyAdminCaseNotificationScope(\DB::table('case_messages as m')
                ->join('court_cases as c', 'c.id', '=', 'm.case_id')
                ->select('m.id','m.body','m.created_at','c.case_number','c.id as case_id')
                ->whereNotNull('m.sender_applicant_id')
                ->where('m.created_at', '>=', $cut14)
                ->whereNotExists(fn($q) => $q->from('admin_notification_reads as nr')
                    ->whereColumn('nr.source_id', 'm.id')
                    ->where('nr.type', 'message')
                    ->where('nr.user_id', $uid)))
                ->orderByDesc('m.created_at')->limit(5)->get();

            $adminUnseenMsgCount = $applyAdminCaseNotificationScope(\DB::table('case_messages as m')
                ->join('court_cases as c', 'c.id', '=', 'm.case_id')
                ->whereNotNull('m.sender_applicant_id')
                ->where('m.created_at', '>=', $cut14)
                ->whereNotExists(fn($q) => $q->from('admin_notification_reads as nr')
                    ->whereColumn('nr.source_id', 'm.id')
                    ->where('nr.type', 'message')
                    ->where('nr.user_id', $uid)))
                ->count();

            $adminUnseenCases = $applyAdminCaseNotificationScope(\DB::table('court_cases as c')
                ->select('c.id','c.case_number','c.title','c.created_at')
                ->where('c.status', 'pending')
                ->whereNull('c.assigned_user_id')
                ->where('c.created_at', '>=', $cut14)
                ->whereNotExists(fn($q) => $q->from('admin_notification_reads as nr')
                    ->whereColumn('nr.source_id', 'c.id')
                    ->where('nr.type', 'case')
                    ->where('nr.user_id', $uid)))
                ->orderByDesc('c.created_at')->limit(5)->get();

            $adminUnseenCaseCount = $applyAdminCaseNotificationScope(\DB::table('court_cases as c')
                ->where('c.status', 'pending')
                ->whereNull('c.assigned_user_id')
                ->where('c.created_at', '>=', $cut14)
                ->whereNotExists(fn($q) => $q->from('admin_notification_reads as nr')
                    ->whereColumn('nr.source_id', 'c.id')
                    ->where('nr.type', 'case')
                    ->where('nr.user_id', $uid)))
                ->count();

            $adminUpcomingHearings = $applyAdminCaseNotificationScope(\DB::table('case_hearings as h')
                ->join('court_cases as c', 'c.id', '=', 'h.case_id')
                ->select('h.id','h.hearing_at','c.id as case_id','c.case_number')
                ->where('c.assigned_user_id', $uid)
                ->whereBetween('h.hearing_at', [$now, $in14])
                ->whereNotExists(fn($q) => $q->from('admin_notification_reads as nr')
                    ->whereColumn('nr.source_id', 'h.id')
                    ->where('nr.type', 'hearing')
                    ->where('nr.user_id', $uid)))
                ->orderBy('h.hearing_at')->limit(5)->get();

            $adminUpcomingHearingCount = $applyAdminCaseNotificationScope(\DB::table('case_hearings as h')
                ->join('court_cases as c', 'c.id', '=', 'h.case_id')
                ->where('c.assigned_user_id', $uid)
                ->whereBetween('h.hearing_at', [$now, $in14])
                ->whereNotExists(fn($q) => $q->from('admin_notification_reads as nr')
                    ->whereColumn('nr.source_id', 'h.id')
                    ->where('nr.type', 'hearing')
                    ->where('nr.user_id', $uid)))
                ->count();

            $adminRespondentViews = $applyAdminCaseNotificationScope(\DB::table('respondent_case_views as v')
                ->join('court_cases as c', 'c.id', '=', 'v.case_id')
                ->join('respondents as r', 'r.id', '=', 'v.respondent_id')
                ->select('v.id','v.viewed_at','v.case_id','c.case_number',
                    \DB::raw((\DB::getDriverName() === 'sqlite')
                        ? "TRIM(COALESCE(r.first_name,'') || ' ' || COALESCE(r.middle_name,'') || ' ' || COALESCE(r.last_name,'')) as respondent_name"
                        : "TRIM(CONCAT_WS(' ', r.first_name, r.middle_name, r.last_name)) as respondent_name"))
                ->where(fn($q) => $q->where('c.assigned_user_id', $uid)->orWhereNull('c.assigned_user_id'))
                ->where('v.viewed_at', '>=', $cut14)
                ->whereNotExists(fn($q) => $q->from('admin_notification_reads as nr')
                    ->whereColumn('nr.source_id', 'v.id')
                    ->where('nr.type', 'respondent_view')
                    ->where('nr.user_id', $uid)))
                ->orderByDesc('v.viewed_at')->limit(5)->get();

            $adminRespondentViewCount = $applyAdminCaseNotificationScope(\DB::table('respondent_case_views as v')
                ->join('court_cases as c', 'c.id', '=', 'v.case_id')
                ->join('respondents as r', 'r.id', '=', 'v.respondent_id')
                ->where(fn($q) => $q->where('c.assigned_user_id', $uid)->orWhereNull('c.assigned_user_id'))
                ->where('v.viewed_at', '>=', $cut14)
                ->whereNotExists(fn($q) => $q->from('admin_notification_reads as nr')
                    ->whereColumn('nr.source_id', 'v.id')
                    ->where('nr.type', 'respondent_view')
                    ->where('nr.user_id', $uid)))
                ->count();
        }

        $__adminNotifCount = $adminUnseenMsgCount
            + $adminUnseenCaseCount
            + $adminUpcomingHearingCount
            + $adminRespondentViewCount;

        $u = auth()->user();

        // Breadcrumb from route name
        $currentRouteName = request()->route()?->getName() ?? '';
        $breadcrumbParts  = array_filter(explode('.', $currentRouteName));
        @endphp

        <header class="topnav-root" role="banner">

            {{-- ── LEFT: sidebar toggle + breadcrumb ── --}}
            <div class="flex items-center gap-2 min-w-0 flex-1">

                {{-- Mobile: open sidebar --}}
                <button type="button" class="topnav-icon-btn md:hidden" @click="sidebar=true"
                    aria-label="{{ __('app.Open sidebar') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-[1.1rem] w-[1.1rem]" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                {{-- Desktop: collapse / expand sidebar --}}
                <button type="button" class="topnav-icon-btn hidden md:inline-flex"
                    @click="toggleCompact()" :aria-pressed="compact.toString()"
                    aria-label="{{ __('app.Toggle sidebar width') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-[1.1rem] w-[1.1rem]" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16"/>
                    </svg>
                </button>

                {{-- Divider --}}
                <span class="hidden md:block h-5 w-px bg-slate-200 dark:bg-slate-700 flex-shrink-0 mx-1" aria-hidden="true"></span>

                {{-- Breadcrumb --}}
                <nav class="topnav-breadcrumb hidden md:flex" aria-label="Breadcrumb">
                    @if(count($breadcrumbParts) > 1)
                        @foreach($breadcrumbParts as $i => $part)
                            @if($i > 0)
                            <span class="topnav-breadcrumb-sep" aria-hidden="true">/</span>
                            @endif
                            <span class="topnav-breadcrumb-item">{{ Str::headline($part) }}</span>
                        @endforeach
                    @else
                        <span class="topnav-breadcrumb-item">@yield('page_header', $t)</span>
                    @endif
                </nav>

                {{-- Mobile: just page title --}}
                <span class="md:hidden text-[14px] font-semibold text-slate-800 dark:text-slate-100 truncate">
                    @yield('page_header', $t)
                </span>
            </div>

            {{-- ── CENTER: global search ── --}}
            <div class="hidden md:flex flex-shrink-0 w-64 lg:w-80">
                @if($hasCases)
                <a href="{{ route('cases.index') }}"
                    class="topnav-search group"
                    title="{{ __('app.Search cases') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span class="flex-1 truncate">{{ __('app.Search cases') }}…</span>
                    <kbd class="hidden lg:inline-flex items-center gap-0.5 rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-1.5 py-0.5 text-[10px] font-mono text-slate-400 dark:text-slate-500 flex-shrink-0">
                        <span class="text-[11px]">⌘</span>K
                    </kbd>
                </a>
                @else
                <button type="button" class="topnav-search cursor-default" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span class="flex-1">{{ __('app.Search') }}…</span>
                    <kbd class="hidden lg:inline-flex items-center gap-0.5 rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-1.5 py-0.5 text-[10px] font-mono text-slate-400 flex-shrink-0">⌘K</kbd>
                </button>
                @endif
            </div>

            {{-- ── RIGHT: utilities ── --}}
            <div class="flex items-center gap-1 flex-shrink-0">

                {{-- Date chip (md+) --}}
                <span class="hidden lg:inline-flex items-center gap-1.5 rounded-md border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 px-2.5 py-1 text-[11px] font-medium text-slate-500 dark:text-slate-400 mr-0.5 flex-shrink-0 select-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 flex-shrink-0 opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ $todayDisplay }}
                </span>

                {{-- Theme toggle --}}
                <x-ui.theme-toggle />

                @auth
                {{-- Language switcher — visibility controlled by system settings --}}
                @if($hasLangSwitch && ($systemSettings?->show_language_switcher ?? true))
                <div class="hidden sm:flex items-center gap-0.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 p-0.5" role="group" aria-label="{{ __('app.Language') }}">
                    <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
                        class="rounded-md px-2.5 py-1 text-xs font-semibold transition-all duration-150
                        {{ app()->getLocale() === 'en'
                            ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm'
                            : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}">
                        EN
                    </a>
                    <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
                        class="rounded-md px-2.5 py-1 text-xs font-semibold transition-all duration-150
                        {{ app()->getLocale() === 'am'
                            ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm'
                            : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}">
                        AM
                    </a>
                </div>
                @endif

                {{-- Notification bell --}}
                @if($hasNotifIndex)
                <div class="relative"
                    x-data="{ bell:false }"
                    data-admin-notification-root
                    data-count-url="{{ Route::has('admin.notifications.count') ? route('admin.notifications.count') : '' }}"
                    data-initial-count="{{ $__adminNotifCount }}"
                    data-title="{{ __('app.admin_notifications.new_unread_title') }}"
                    data-singular="{{ __('app.admin_notifications.new_unread_singular') }}"
                    data-plural="{{ __('app.admin_notifications.new_unread_plural') }}"
                    data-brand="{{ $brandName }}"
                    data-logo-url="{{ !empty($systemSettings?->logo_path) ? asset('storage/'.$systemSettings->logo_path) : '' }}"
                    data-initials="{{ \Illuminate\Support\Str::of(strip_tags($shortName))->substr(0, 2)->upper() }}">
                    <button type="button" class="topnav-icon-btn relative" @click="bell=!bell"
                        aria-label="{{ __('app.Notifications') }}" :aria-expanded="bell.toString()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-[1.1rem] w-[1.1rem]" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 11-6 0h6z"/>
                        </svg>
                        @if($__adminNotifCount > 0)
                            <span class="topnav-notif-badge" data-admin-notification-badge aria-label="{{ $__adminNotifCount }} {{ __('app.Notifications') }}">
                                {{ $__adminNotifCount > 99 ? '99+' : $__adminNotifCount }}
                            </span>
                        @endif
                    </button>

                    {{-- Notification dropdown --}}
                    <div x-cloak x-show="bell" @click.outside="bell=false"
                        x-transition:enter="motion-enter-fast"
                        x-transition:enter-start="motion-slide-up-start"
                        x-transition:enter-end="motion-slide-up-end"
                        x-transition:leave="motion-leave"
                        x-transition:leave-start="motion-slide-up-end"
                        x-transition:leave-end="motion-slide-up-start"
                        class="topnav-dropdown w-[30rem] max-w-[92vw]"
                        role="dialog" aria-label="{{ __('app.Notifications') }}">

                        {{-- Header --}}
                        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="text-[13.5px] font-bold text-slate-800 dark:text-slate-100">{{ __('app.Notifications') }}</span>
                                @if($__adminNotifCount > 0)
                                <span class="inline-flex items-center justify-center h-5 min-w-[1.25rem] rounded-full bg-blue-100 dark:bg-blue-900/60 px-1.5 text-[10.5px] font-bold text-blue-700 dark:text-blue-300">
                                    {{ $__adminNotifCount }}
                                </span>
                                @endif
                            </div>
                            @if($__adminNotifCount > 0 && $hasNotifMarkAll)
                            <form method="POST" action="{{ route('admin.notifications.markAll') }}">
                                @csrf
                                <button type="submit"
                                    class="text-[11.5px] font-medium text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-150 focus-ring rounded">
                                    {{ __('app.Mark all as seen') }}
                                </button>
                            </form>
                            @endif
                        </div>

                        {{-- Body --}}
                        <div class="max-h-[26rem] overflow-y-auto overscroll-contain px-2 py-2 space-y-3">
                            @if($__adminNotifCount === 0)
                            <div class="flex flex-col items-center gap-2 py-8 text-slate-400 dark:text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 opacity-40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4"
                                        d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 11-6 0h6z"/>
                                </svg>
                                <span class="text-[13px]">{{ __('app.youre_all_caught_up') }}</span>
                            </div>
                            @else

                            {{-- Applicant messages --}}
                            @if($adminUnseenMsgs->isNotEmpty())
                            <div>
                                <div class="px-2 mb-1 text-[10.5px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500">
                                    {{ __('app.Applicant messages') }}
                                </div>
                                <ul class="space-y-0.5">
                                    @foreach($adminUnseenMsgs as $m)
                                    @php
                                        $legacyApplicantUpdate = 'Applicant updated the case details. Please review the submission.';
                                        $displayBody = trim((string) $m->body) === $legacyApplicantUpdate
                                            ? __('cases.notifications.applicant_updated_submission')
                                            : (string) $m->body;
                                    @endphp
                                    <li class="topnav-notif-row">
                                        <div class="flex-shrink-0 h-7 w-7 rounded-full bg-blue-100 dark:bg-blue-900/50 grid place-items-center mt-0.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-4 4-1-4z"/></svg>
                                        </div>
                                        <a href="{{ $hasCases ? route('cases.show', $m->case_id) : '#' }}" class="flex-1 min-w-0">
                                            <div class="text-[12.5px] font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $m->case_number }}</div>
                                            <div class="text-[11.5px] text-slate-500 dark:text-slate-400 truncate">{{ Str::limit($displayBody, 70) }}</div>
                                            <div class="text-[10.5px] text-slate-400 dark:text-slate-500 mt-0.5">{{ \App\Support\EthiopianDate::smartRelative($m->created_at) }}</div>
                                        </a>
                                        @if($hasNotifMarkOne)
                                        <form method="POST" action="{{ route('admin.notifications.markOne') }}" class="flex-shrink-0">
                                            @csrf
                                            <input type="hidden" name="type" value="message">
                                            <input type="hidden" name="sourceId" value="{{ $m->id }}">
                                            <button type="submit" class="text-[11px] px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 font-medium transition-colors focus-ring">{{ __('app.Seen') }}</button>
                                        </form>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            {{-- New cases --}}
                            @if($adminUnseenCases->isNotEmpty())
                            <div>
                                <div class="px-2 mb-1 text-[10.5px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500">
                                    {{ __('app.New cases') }}
                                </div>
                                <ul class="space-y-0.5">
                                    @foreach($adminUnseenCases as $c)
                                    <li class="topnav-notif-row">
                                        <div class="flex-shrink-0 h-7 w-7 rounded-full bg-emerald-100 dark:bg-emerald-900/40 grid place-items-center mt-0.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <a href="{{ $hasCases ? route('cases.show', $c->id) : '#' }}" class="flex-1 min-w-0">
                                            <div class="text-[12.5px] font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $c->case_number }}</div>
                                            <div class="text-[11.5px] text-slate-500 dark:text-slate-400 truncate">{{ Str::limit($c->title, 70) }}</div>
                                            <div class="text-[10.5px] text-slate-400 dark:text-slate-500 mt-0.5">{{ \App\Support\EthiopianDate::smartRelative($c->created_at) }}</div>
                                        </a>
                                        @if($hasNotifMarkOne)
                                        <form method="POST" action="{{ route('admin.notifications.markOne') }}" class="flex-shrink-0">
                                            @csrf
                                            <input type="hidden" name="type" value="case">
                                            <input type="hidden" name="sourceId" value="{{ $c->id }}">
                                            <button type="submit" class="text-[11px] px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 font-medium transition-colors focus-ring">{{ __('app.Seen') }}</button>
                                        </form>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            {{-- Upcoming hearings --}}
                            @if($adminUpcomingHearings->isNotEmpty())
                            <div>
                                <div class="px-2 mb-1 text-[10.5px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500">
                                    {{ __('app.Upcoming hearings') }}
                                </div>
                                <ul class="space-y-0.5">
                                    @foreach($adminUpcomingHearings as $h)
                                    <li class="topnav-notif-row">
                                        <div class="flex-shrink-0 h-7 w-7 rounded-full bg-amber-100 dark:bg-amber-900/40 grid place-items-center mt-0.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                        <a href="{{ $hasCases ? route('cases.show', $h->case_id) : '#' }}" class="flex-1 min-w-0">
                                            <div class="text-[12.5px] font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $h->case_number }}</div>
                                            <div class="text-[11.5px] text-slate-500 dark:text-slate-400">{{ \App\Support\EthiopianDate::format($h->hearing_at, withTime: true) }}</div>
                                        </a>
                                        @if($hasNotifMarkOne)
                                        <form method="POST" action="{{ route('admin.notifications.markOne') }}" class="flex-shrink-0">
                                            @csrf
                                            <input type="hidden" name="type" value="hearing">
                                            <input type="hidden" name="sourceId" value="{{ $h->id }}">
                                            <button type="submit" class="text-[11px] px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 font-medium transition-colors focus-ring">{{ __('app.Seen') }}</button>
                                        </form>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            {{-- Respondent views --}}
                            @if($adminRespondentViews->isNotEmpty())
                            <div>
                                <div class="px-2 mb-1 text-[10.5px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500">
                                    {{ __('app.admin_notifications.respondent_views') }}
                                </div>
                                <ul class="space-y-0.5">
                                    @foreach($adminRespondentViews as $v)
                                    <li class="topnav-notif-row">
                                        <div class="flex-shrink-0 h-7 w-7 rounded-full bg-purple-100 dark:bg-purple-900/40 grid place-items-center mt-0.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-purple-600 dark:text-purple-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </div>
                                        <a href="{{ $hasCases ? route('cases.show', $v->case_id) : '#' }}" class="flex-1 min-w-0">
                                            <div class="text-[12.5px] font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $v->case_number }}</div>
                                            <div class="text-[11.5px] text-slate-500 dark:text-slate-400 truncate">
                                                {{ __('app.admin_notifications.respondent_viewed_case', ['name' => ($v->respondent_name ?: __('app.admin_notifications.respondent_default'))]) }}
                                            </div>
                                            <div class="text-[10.5px] text-slate-400 dark:text-slate-500 mt-0.5">{{ \App\Support\EthiopianDate::smartRelative($v->viewed_at) }}</div>
                                        </a>
                                        @if($hasNotifMarkOne)
                                        <form method="POST" action="{{ route('admin.notifications.markOne') }}" class="flex-shrink-0">
                                            @csrf
                                            <input type="hidden" name="type" value="respondent_view">
                                            <input type="hidden" name="sourceId" value="{{ $v->id }}">
                                            <button type="submit" class="text-[11px] px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 font-medium transition-colors focus-ring">{{ __('app.Seen') }}</button>
                                        </form>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            @endif {{-- /$__adminNotifCount === 0 --}}
                        </div>

                        {{-- Footer --}}
                        @if($hasNotifIndex)
                        <div class="border-t border-slate-100 dark:border-slate-800 px-4 py-2.5">
                            <a href="{{ route('admin.notifications.index') }}"
                                class="flex items-center justify-center gap-1.5 text-[12.5px] font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors focus-ring rounded">
                                {{ __('app.View all') }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                        @endif
                    </div>
                    <div data-admin-notification-toast-region
                        class="pointer-events-none fixed bottom-5 right-5 z-[1100] flex w-[min(94vw,26rem)] flex-col gap-3 sm:bottom-6 sm:right-6"
                        style="top:auto!important;left:auto!important;right:1.5rem!important;bottom:1.5rem!important;transform:none!important;"></div>
                </div>
                @endif

                {{-- Divider --}}
                <span class="h-5 w-px bg-slate-200 dark:bg-slate-700 flex-shrink-0 mx-0.5" aria-hidden="true"></span>

                {{-- Profile dropdown --}}
                <div x-data="{ open:false }" class="relative">
                    <button type="button" @click="open=!open"
                        class="topnav-profile-btn"
                        aria-haspopup="menu" :aria-expanded="open.toString()">
                        @if($u?->avatar_url)
                        <img src="{{ $u->avatar_url }}" class="h-8 w-8 rounded-full object-cover ring-2 ring-slate-200 dark:ring-slate-700 flex-shrink-0" alt="{{ __('app.Avatar') }}">
                        @else
                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 grid place-items-center font-bold text-white text-[13px] flex-shrink-0 ring-2 ring-blue-200 dark:ring-blue-900" aria-hidden="true">
                            {{ strtoupper(substr($u->name ?? 'A', 0, 1)) }}
                        </div>
                        @endif
                        <div class="hidden sm:block min-w-0 text-left">
                            <div class="text-[12.5px] font-semibold text-slate-800 dark:text-slate-100 truncate max-w-[7rem]">{{ $u->name ?? 'Admin' }}</div>
                            <div class="text-[10.5px] text-slate-400 dark:text-slate-500 truncate max-w-[7rem] capitalize">{{ $u->user_type ?? 'Administrator' }}</div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="hidden sm:block h-3.5 w-3.5 text-slate-400 dark:text-slate-500 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-cloak x-show="open" @click.outside="open=false"
                        x-transition:enter="motion-enter-fast"
                        x-transition:enter-start="motion-slide-up-start"
                        x-transition:enter-end="motion-slide-up-end"
                        x-transition:leave="motion-leave"
                        x-transition:leave-start="motion-slide-up-end"
                        x-transition:leave-end="motion-slide-up-start"
                        class="topnav-dropdown w-60"
                        role="menu">

                        {{-- Identity header --}}
                        <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                            @if($u?->avatar_url)
                            <img src="{{ $u->avatar_url }}" class="h-9 w-9 rounded-full object-cover ring-2 ring-slate-200 dark:ring-slate-700 flex-shrink-0" alt="">
                            @else
                            <div class="h-9 w-9 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 grid place-items-center font-bold text-white text-sm flex-shrink-0">
                                {{ strtoupper(substr($u->name ?? 'A', 0, 1)) }}
                            </div>
                            @endif
                            <div class="min-w-0">
                                <div class="text-[13px] font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $u->name ?? 'Admin' }}</div>
                                <div class="text-[11px] text-slate-400 dark:text-slate-500 truncate">{{ $u?->email }}</div>
                            </div>
                        </div>

                        {{-- Menu items --}}
                        <div class="p-1.5 space-y-0.5" role="none">
                            @if($hasProfileEdit)
                            <a href="{{ route('profile.edit') }}" role="menuitem"
                                class="flex items-center gap-3 w-full rounded-lg px-3 py-2 text-[13px] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors duration-100 focus-ring">
                                <span class="h-6 w-6 rounded-md bg-blue-50 dark:bg-blue-950/50 grid place-items-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </span>
                                {{ __('app.Profile') }}
                            </a>
                            @endif

                            {{-- Language switcher (mobile fallback inside profile menu) --}}
                            @if($hasLangSwitch && ($systemSettings?->show_language_switcher ?? true))
                            <div class="sm:hidden px-3 py-2">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">{{ __('app.Language') }}</p>
                                <div class="flex items-center gap-0.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 p-0.5 w-fit" role="group">
                                    <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
                                        class="rounded-md px-3 py-1.5 text-xs font-semibold transition-all duration-150
                                        {{ app()->getLocale() === 'en'
                                            ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm'
                                            : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}">
                                        EN
                                    </a>
                                    <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
                                        class="rounded-md px-3 py-1.5 text-xs font-semibold transition-all duration-150
                                        {{ app()->getLocale() === 'am'
                                            ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm'
                                            : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}">
                                        AM
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>

                        @if($hasLogout)
                        <div class="border-t border-slate-100 dark:border-slate-800 p-1.5">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" role="menuitem"
                                    class="flex items-center gap-3 w-full rounded-lg px-3 py-2 text-[13px] text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors duration-100 focus-ring">
                                    <span class="h-6 w-6 rounded-md bg-rose-50 dark:bg-rose-950/40 grid place-items-center flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-rose-500 dark:text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    </span>
                                    {{ __('app.Logout') }}
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
                @endauth
            </div>

        </header>

        {{-- Page content --}}
        <main class="page-enter flex-1 p-4 md:p-5 xl:p-7 {{ $isCaseTypographyRoute ? 'case-font-scope case-typography' : '' }}">
            <div class="ui-page-admin">
                {{ $slot }}
            </div>
        </main>

        <footer class="border-t border-slate-200/60 dark:border-slate-800/60 bg-white/80 dark:bg-slate-950/80 backdrop-blur-sm">
            <div class="px-4 md:px-6 xl:px-8 py-3">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-blue-600/90 text-[11px] font-bold text-white flex-shrink-0">
                            {{ \Illuminate\Support\Str::of($shortName)->substr(0, 2) }}
                        </span>
                        <span class="text-[12px] text-slate-500 dark:text-slate-500">
                            <span class="font-semibold text-slate-700 dark:text-slate-400">{{ $brandName }}</span>
                            <span class="mx-1.5 opacity-30">·</span>
                            {{ $footerText }}
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5 text-[11px]">
                        <span class="inline-flex items-center gap-1 rounded-md border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 px-2 py-0.5 font-medium text-slate-500 dark:text-slate-500">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 flex-shrink-0"></span>
                            {{ __('app.court_portal') }}
                        </span>
                        <span class="rounded-md border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 px-2 py-0.5 font-medium text-slate-400 dark:text-slate-600">
                            {{ \App\Support\EthiopianDate::formatDate($footerNow) }}
                        </span>
                        <span class="rounded-md border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 px-2 py-0.5 font-medium text-slate-400 dark:text-slate-600 tabular-nums">
                            {{ \App\Support\EthiopianDate::formatTime($footerNow) }}
                        </span>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    {{-- Alpine helpers --}}
    <script>
        function layoutState() {
            return {
                sidebar: false,
                compact: false,
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
        /* ── Thin progress bar controller ───────────────────────── */
        window.spaProgress = (() => {
            const bar    = document.getElementById('spa-progress');
            const loader = document.getElementById('spa-loader');
            let timer = null;
            let width = 0;
            let loaderTimer = null;

            const set = (w) => {
                if (!bar) return;
                width = w;
                bar.style.width = w + '%';
            };

            const start = () => {
                if (!bar) return;
                clearTimeout(timer);
                clearTimeout(loaderTimer);
                width = 0;
                bar.style.transition = 'width .22s ease, opacity .18s ease';
                bar.style.width = '0%';
                bar.classList.remove('is-done');
                bar.classList.add('is-running');
                // Show loader overlay after 120ms (skip it for instant loads)
                loaderTimer = setTimeout(() => {
                    loader?.classList.add('is-visible');
                }, 120);
                // Trickle up to ~85% then stall
                const trickle = () => {
                    if (width < 30)      set(width + 10 + Math.random() * 8);
                    else if (width < 60) set(width + 4  + Math.random() * 5);
                    else if (width < 80) set(width + 2  + Math.random() * 3);
                    else if (width < 85) set(width + 0.5);
                    else return;
                    timer = setTimeout(trickle, 220 + Math.random() * 180);
                };
                timer = setTimeout(trickle, 80);
            };

            const done = () => {
                if (!bar) return;
                clearTimeout(timer);
                clearTimeout(loaderTimer);
                bar.style.transition = 'width .18s ease, opacity .35s ease .05s';
                bar.classList.add('is-done');
                bar.classList.remove('is-running');
                loader?.classList.remove('is-visible');
                setTimeout(() => { bar.style.width = '0%'; bar.classList.remove('is-done'); }, 500);
            };

            return { start, done };
        })();
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
                // Only replace if active-state markers actually changed — avoids
                // unnecessary DOM churn (flash) on every SPA navigation.
                const incomingHtml = incomingNav.innerHTML;
                if (nav.innerHTML !== incomingHtml) {
                    nav.innerHTML = incomingHtml;
                }
            };

            const navigate = async (url, push = true) => {
                const panel = document.querySelector(panelSelector);
                panel?.setAttribute('aria-busy', 'true');
                panel?.classList.add('is-loading');
                window.spaProgress?.start();

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
                    document.querySelector(panelSelector)?.classList.remove('is-loading');
                    window.spaProgress?.done();
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
    <script>
        /* Hard page-load: run progress bar from 0 → done on DOMContentLoaded */
        (() => {
            window.spaProgress?.start();
            const finish = () => window.spaProgress?.done();
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                finish();
            } else {
                document.addEventListener('DOMContentLoaded', finish, { once: true });
            }
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
    @if($hasNotifIndex)
    <script>
        (function() {
            const root = document.querySelector('[data-admin-notification-root]');
            if (!root || !root.dataset.countUrl) {
                return;
            }

            if (window.__adminNotificationPoller?.stop) {
                window.__adminNotificationPoller.stop();
            }

            let currentCount = Number.parseInt(root.dataset.initialCount || '0', 10) || 0;
            let pollTimer = null;

            const toastRegion = root.querySelector('[data-admin-notification-toast-region]');
            const bellButton = root.querySelector('.topnav-icon-btn');
            const title = root.dataset.title || 'New notification';
            const singular = root.dataset.singular || '1 new unread notification';
            const plural = root.dataset.plural || ':count new unread notifications';
            const brand = root.dataset.brand || '';
            const logoUrl = root.dataset.logoUrl || '';
            const initials = root.dataset.initials || 'CM';

            const formatCount = (count) => count > 99 ? '99+' : String(count);
            const messageFor = (delta) => delta === 1 ? singular : plural.replace(':count', String(delta));
            const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            })[char]);

            if (toastRegion && toastRegion.parentElement !== document.body) {
                document.body.appendChild(toastRegion);
                Object.assign(toastRegion.style, {
                    top: 'auto',
                    left: 'auto',
                    right: '1.5rem',
                    bottom: '1.5rem',
                    transform: 'none',
                });
            }

            const updateBadge = (count) => {
                let badge = root.querySelector('[data-admin-notification-badge]');

                if (count < 1) {
                    badge?.remove();
                    return;
                }

                if (!badge && bellButton) {
                    badge = document.createElement('span');
                    badge.className = 'topnav-notif-badge';
                    badge.dataset.adminNotificationBadge = '';
                    bellButton.appendChild(badge);
                }

                if (badge) {
                    badge.textContent = formatCount(count);
                    badge.setAttribute('aria-label', `${count} {{ __('app.Notifications') }}`);
                }
            };

            const removeToast = (toast) => {
                toast.classList.add('translate-y-8', 'opacity-0', 'scale-95');
                toast.classList.remove('translate-y-0', 'opacity-100');
                window.setTimeout(() => toast.remove(), 220);
            };

            const logoMarkup = () => {
                if (logoUrl) {
                    return `<img src="${escapeHtml(logoUrl)}" alt="${escapeHtml(brand)}" class="max-h-11 max-w-14 object-contain">`;
                }

                return `<div class="grid h-11 w-11 place-items-center rounded-2xl bg-slate-900 text-xs font-black uppercase tracking-wide text-white shadow-lg shadow-slate-900/20 dark:bg-white dark:text-slate-950">${escapeHtml(initials)}</div>`;
            };

            const playNotificationSound = () => {
                try {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (!AudioContext) {
                        return;
                    }

                    const audioContext = window.__caseNotificationAudioContext || new AudioContext();
                    window.__caseNotificationAudioContext = audioContext;

                    if (audioContext.state === 'suspended') {
                        audioContext.resume().catch(() => {});
                    }

                    const now = audioContext.currentTime;
                    const gain = audioContext.createGain();
                    const firstTone = audioContext.createOscillator();
                    const secondTone = audioContext.createOscillator();

                    gain.gain.setValueAtTime(0.0001, now);
                    gain.gain.exponentialRampToValueAtTime(0.12, now + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.42);
                    gain.connect(audioContext.destination);

                    firstTone.type = 'sine';
                    firstTone.frequency.setValueAtTime(740, now);
                    firstTone.connect(gain);
                    firstTone.start(now);
                    firstTone.stop(now + 0.16);

                    secondTone.type = 'sine';
                    secondTone.frequency.setValueAtTime(980, now + 0.18);
                    secondTone.connect(gain);
                    secondTone.start(now + 0.18);
                    secondTone.stop(now + 0.42);
                } catch (error) {
                    return;
                }
            };

            const showToast = (delta) => {
                if (!toastRegion || delta < 1) {
                    return;
                }

                playNotificationSound();

                const toast = document.createElement('div');
                toast.className = [
                    'pointer-events-auto relative overflow-hidden rounded-[1.35rem] border border-blue-200/90',
                    'bg-white/[0.96] p-0 text-slate-900 shadow-2xl shadow-slate-950/[0.18]',
                    'ring-1 ring-white/70 backdrop-blur-xl transition duration-300 ease-out translate-y-10 scale-95 opacity-0',
                    'dark:border-blue-400/25 dark:bg-slate-950/[0.96] dark:text-slate-100 dark:ring-white/10'
                ].join(' ');
                toast.innerHTML = `
                    <span class="absolute inset-x-0 bottom-0 h-1 bg-blue-600"></span>
                    <div class="flex items-stretch">
                        <div class="flex w-20 flex-shrink-0 items-center justify-center border-r border-slate-200/70 bg-slate-50/80 px-3 dark:border-slate-800 dark:bg-slate-900/80">
                            ${logoMarkup()}
                        </div>
                        <div class="min-w-0 flex-1 px-4 py-3.5">
                            <div class="flex items-start gap-3 pr-8">
                                <span class="mt-0.5 inline-flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-blue-600 text-white shadow-sm shadow-blue-600/25">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-4 4-1-4z"/>
                                    </svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">${escapeHtml(brand)}</span>
                                    <span class="mt-0.5 block text-sm font-semibold leading-5 text-slate-900 dark:text-slate-100">${escapeHtml(title)}</span>
                                    <span class="mt-1 block text-xs leading-5 text-slate-500 dark:text-slate-400">${escapeHtml(messageFor(delta))}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="absolute right-3 top-3 grid h-7 w-7 place-items-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/40 dark:hover:bg-slate-800 dark:hover:text-slate-100" aria-label="{{ __('app.Close details') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                `;

                toast.querySelector('button')?.addEventListener('click', () => removeToast(toast));
                toastRegion.prepend(toast);
                window.requestAnimationFrame(() => {
                    toast.classList.remove('translate-y-10', 'scale-95', 'opacity-0');
                    toast.classList.add('translate-y-0', 'opacity-100');
                });
                window.setTimeout(() => removeToast(toast), 10000);
            };

            const poll = async () => {
                try {
                    const response = await fetch(root.dataset.countUrl, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();
                    const nextCount = Number.parseInt(data.count || '0', 10) || 0;
                    const delta = nextCount - currentCount;

                    updateBadge(nextCount);
                    if (delta > 0) {
                        showToast(delta);
                    }

                    currentCount = nextCount;
                } catch (error) {
                    return;
                }
            };

            pollTimer = window.setInterval(poll, 20000);
            window.setTimeout(poll, 3500);

            window.__adminNotificationPoller = {
                stop() {
                    if (pollTimer) {
                        window.clearInterval(pollTimer);
                    }
                },
            };
        })();
    </script>
    @endif
    @auth
    @php($sessionSettings = $systemSettings ?? null)
    <script>
        (() => {
            const lifetime = Number(@js((int) ($sessionSettings?->session_lifetime ?? config('session.lifetime', 120)))) * 60 * 1000;
            const warning = Number(@js((int) ($sessionSettings?->session_warning_minutes ?? 5))) * 60 * 1000;
            const extendOnActivity = @js((bool) ($sessionSettings?->session_extend_on_activity ?? true));
            if (!lifetime || warning >= lifetime) return;
            let timer;
            let warned = false;
            const schedule = () => {
                window.clearTimeout(timer);
                warned = false;
                timer = window.setTimeout(() => {
                    warned = true;
                    const extend = window.confirm('{{ __('settings.session_warning_browser') }}');
                    if (extend) window.location.reload();
                }, lifetime - warning);
            };
            ['click', 'keydown', 'mousemove', 'touchstart'].forEach((event) => {
                window.addEventListener(event, () => { if (extendOnActivity && !warned) schedule(); }, { passive: true });
            });
            schedule();
        })();
    </script>
    @endauth
    @livewireScripts
    @stack('scripts')
</body>

</html>
