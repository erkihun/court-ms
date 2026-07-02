@props(['title' => __('app.court_portal'), 'hideFooter' => false, 'asRespondentNav' => false])

@php
$layout = $publicLayout ?? [];
if (is_object($layout)) {
    $layout = method_exists($layout, 'toArray') ? $layout->toArray() : (array) $layout;
}
$systemSettings = $layout['systemSettings'] ?? null;
$brandName = $layout['brandName'] ?? config('app.name', __('app.court_ms'));
$shortName = $layout['shortName'] ?? $brandName;
$logoPath = $layout['logoPath'] ?? null;
$footerText = $layout['footerText'] ?? __('app.all_rights_reserved');
$notificationCount = $layout['notificationCount'] ?? 0;

// Admin-controlled language switcher visibility (system settings). Defaults to on.
$showLanguageSwitcher = ($systemSettings?->show_language_switcher ?? null);
if ($showLanguageSwitcher === null) {
    $showLanguageSwitcher = \App\Models\SystemSetting::current()->show_language_switcher ?? true;
}

$actingRespondent = $asRespondentNav || session('acting_as_respondent', false);
if (!$asRespondentNav && !request()->is('respondent/*') && !request()->routeIs('respondent.*')) {
session()->forget('acting_as_respondent');
$actingRespondent = false;
}

$layoutWidthClass = 'max-w-[1600px]';
$shellWidthClass = $layoutWidthClass;
$flashWidthClass = $layoutWidthClass;
$mainWidthClass = $layoutWidthClass;

$isCaseTypographyRoute = request()->routeIs('applicant.cases.*')
    || request()->routeIs('applicant.respondent.cases.*')
    || request()->routeIs('respondent.cases.*')
    || request()->routeIs('public.cases.*');

$unseenHearings = collect();
$unseenMsgs = collect();
$unseenStatus = collect();
$respondentViews = collect();
$messageNotificationCount = 0;
$otherNotificationCount = 0;
$hasOtherNotifications = false;
$hasAnyNotifications = false;
$respondentNotifCount = 0;
$respondentNotifList = collect();

if (auth('applicant')->check() && !$actingRespondent) {
$aid = auth('applicant')->id();
$tables = [
'court_cases',
'case_hearings',
'case_messages',
'case_status_logs',
'notification_reads',
'respondent_case_views',
'respondents',
];

$tablesExist = true;
foreach ($tables as $table) {
if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
$tablesExist = false;
break;
}
}

if ($tablesExist) {
$now = now();

$hearingBase = \DB::table('case_hearings as h')
->join('court_cases as c', 'c.id', '=', 'h.case_id')
        ->select('h.id', 'h.hearing_at', 'h.type', 'h.location', 'c.id as case_id', 'c.case_number')
->where('c.applicant_id', $aid)
->whereBetween('h.hearing_at', [$now->copy()->subDay(), $now->copy()->addDays(60)])
->whereNotExists(function ($q) use ($aid) {
$q->from('notification_reads as nr')
->whereColumn('nr.source_id', 'h.id')
->where('nr.type', 'hearing')
->where('nr.applicant_id', $aid);
})
->orderBy('h.hearing_at');

$statusBase = \DB::table('case_status_logs as l')
->join('court_cases as c', 'c.id', '=', 'l.case_id')
->select('l.id', 'l.from_status', 'l.to_status', 'l.created_at', 'c.id as case_id', 'c.case_number')
->where('c.applicant_id', $aid)
->where('l.created_at', '>=', $now->copy()->subDays(14))
->whereNotExists(function ($q) use ($aid) {
$q->from('notification_reads as nr')
->whereColumn('nr.source_id', 'l.id')
->where('nr.type', 'status')
->where('nr.applicant_id', $aid);
})
->orderByDesc('l.created_at');

$messageBase = \DB::table('case_messages as m')
->join('court_cases as c', 'c.id', '=', 'm.case_id')
->select('m.id', 'm.body', 'm.created_at', 'c.id as case_id', 'c.case_number')
->whereNotNull('m.sender_user_id')
->where('c.applicant_id', $aid)
->where('m.created_at', '>=', $now->copy()->subDays(14))
->whereNotExists(function ($q) use ($aid) {
$q->from('notification_reads as nr')
->whereColumn('nr.source_id', 'm.id')
->where('nr.type', 'message')
->where('nr.applicant_id', $aid);
})
->orderByDesc('m.created_at');

$respondentBase = \DB::table('respondent_case_views as v')
->join('court_cases as c', 'c.id', '=', 'v.case_id')
->join('respondents as r', 'r.id', '=', 'v.respondent_id')
->select(
'v.id',
'v.viewed_at',
'v.case_id',
'c.case_number',
\DB::raw("TRIM(CONCAT_WS(' ', r.first_name, r.middle_name, r.last_name)) as respondent_name")
)
->where('c.applicant_id', $aid)
->where('v.viewed_at', '>=', $now->copy()->subDays(14))
->whereNotExists(function ($q) use ($aid) {
$q->from('notification_reads as nr')
->whereColumn('nr.source_id', 'v.id')
->where('nr.type', 'respondent_view')
->where('nr.applicant_id', $aid);
})
->orderByDesc('v.viewed_at');

$unseenHearings = (clone $hearingBase)->limit(10)->get();
$unseenStatus = (clone $statusBase)->limit(10)->get();
$unseenMsgs = (clone $messageBase)->limit(10)->get();
$respondentViews = (clone $respondentBase)->limit(5)->get();

$hasOtherNotifications = $unseenHearings->isNotEmpty() || $unseenStatus->isNotEmpty() || $respondentViews->isNotEmpty();

$messageNotificationCount = (int) (clone $messageBase)->count();
$otherNotificationCount = (int) (clone $hearingBase)->count()
+ (int) (clone $statusBase)->count()
+ (int) (clone $respondentBase)->count();

$notificationCount = $otherNotificationCount;
$hasAnyNotifications = $messageNotificationCount > 0 || $otherNotificationCount > 0;
}
}

if ($actingRespondent && auth('applicant')->check()) {
$appUser = auth('applicant')->user();
try {
$tablesOk = \Illuminate\Support\Facades\Schema::hasTable('respondent_case_views')
&& \Illuminate\Support\Facades\Schema::hasTable('respondent_notification_reads')
&& \Illuminate\Support\Facades\Schema::hasTable('respondents')
&& \Illuminate\Support\Facades\Schema::hasTable('court_cases');
if ($tablesOk) {
$respondentId = optional(\App\Models\Respondent::where('email', $appUser->email)->first())->id;
if ($respondentId) {
$caseIds = \DB::table('respondent_case_views')
->where('respondent_id', $respondentId)
->pluck('case_id')
->all();

$now = now();

$viewBase = \DB::table('respondent_case_views as v')
->join('court_cases as c', 'c.id', '=', 'v.case_id')
->select('v.id', 'v.viewed_at as at', 'c.case_number', 'c.title', \DB::raw("'respondent_view' as notif_type"))
->where('v.respondent_id', $respondentId)
->whereNotExists(function ($q) use ($respondentId) {
$q->from('respondent_notification_reads as r')
->whereColumn('r.source_id', 'v.id')
->where('r.type', 'respondent_view')
->where('r.respondent_id', $respondentId);
});

$hearingBase = \DB::table('case_hearings as h')
->join('court_cases as c', 'c.id', '=', 'h.case_id')
->select('h.id', 'h.hearing_at as at', 'c.case_number', 'c.title', \DB::raw("'hearing' as notif_type"))
->whereIn('h.case_id', $caseIds)
->whereBetween('h.hearing_at', [$now->copy()->subDay(), $now->copy()->addDays(60)])
->whereNotExists(function ($q) use ($respondentId) {
$q->from('respondent_notification_reads as r')
->whereColumn('r.source_id', 'h.id')
->where('r.type', 'hearing')
->where('r.respondent_id', $respondentId);
});

$statusBase = \DB::table('case_status_logs as s')
->join('court_cases as c', 'c.id', '=', 's.case_id')
->select('s.id', 's.created_at as at', 'c.case_number', 'c.title', \DB::raw("'status' as notif_type"))
->whereIn('s.case_id', $caseIds)
->where('s.created_at', '>=', $now->copy()->subDays(14))
->whereNotExists(function ($q) use ($respondentId) {
$q->from('respondent_notification_reads as r')
->whereColumn('r.source_id', 's.id')
->where('r.type', 'status')
->where('r.respondent_id', $respondentId);
});

$messageBase = \DB::table('case_messages as m')
->join('court_cases as c', 'c.id', '=', 'm.case_id')
->select('m.id', 'm.created_at as at', 'c.case_number', 'c.title', \DB::raw("'message' as notif_type"))
->whereIn('m.case_id', $caseIds)
->whereNotNull('m.sender_user_id')
->where('m.created_at', '>=', $now->copy()->subDays(14))
->whereNotExists(function ($q) use ($respondentId) {
$q->from('respondent_notification_reads as r')
->whereColumn('r.source_id', 'm.id')
->where('r.type', 'message')
->where('r.respondent_id', $respondentId);
});

$respondentNotifCount = (int) (clone $viewBase)->count()
+ (int) (clone $hearingBase)->count()
+ (int) (clone $statusBase)->count()
+ (int) (clone $messageBase)->count();

$respondentNotifList = collect()
->merge((clone $viewBase)->limit(5)->get())
->merge((clone $hearingBase)->limit(5)->get())
->merge((clone $statusBase)->limit(5)->get())
->merge((clone $messageBase)->limit(5)->get())
->sortByDesc('at')
->take(8);
}
}
} catch (\Throwable $e) {
$respondentNotifCount = 0;
$respondentNotifList = collect();
}
}
@endphp


<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}" x-data="themeSystem()" x-init="init()">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>{{ $title }} | {{ $brandName }}</title>

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
            display: none !important;
        }
    </style>

    {{-- Optional favicon if you later store it in system_settings --}}
    @if(!empty($systemSettings?->favicon_path))
    <link rel="icon" href="{{ asset('storage/'.$systemSettings->favicon_path) }}">
    @endif

    @vite(['resources/css/app.css','resources/js/app.js'])
    @stack('head')

</head>

<body class="ui-shell min-h-screen font-ui text-[var(--text)]">

    {{-- Header / Nav --}}
    <header x-data="{ mobileOpen: false }"
            class="sticky top-0 z-40 border-b backdrop-blur-md"
            style="border-color: var(--border); background: color-mix(in srgb, var(--surface-strong) 88%, transparent);">
        <div class="{{ $shellWidthClass }} mx-auto px-4 flex h-14 items-center justify-between gap-3">

            {{-- ── Brand ──────────────────────────────────────────────── --}}
            <a href="{{ $actingRespondent ? route('respondent.dashboard') : (auth('applicant')->check() ? route('applicant.dashboard') : route('applicant.login')) }}"
               class="flex items-center gap-2.5 flex-shrink-0 rounded-lg focus:outline-none focus-visible:ring-2"
               style="--tw-ring-color: rgb(var(--ac) / 0.4);">
                @if($logoPath)
                <img src="{{ asset('storage/'.$logoPath) }}" alt="{{ $brandName }}" class="h-8 w-auto object-contain">
                @else
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-xs font-bold uppercase tracking-wide flex-shrink-0 text-white"
                      style="background: rgb(var(--ac));">
                    {{ \Illuminate\Support\Str::of($shortName)->substr(0,2) }}
                </span>
                @endif
                <span class="font-bold text-sm hidden sm:block truncate max-w-[200px] leading-tight" style="color: var(--text);">{{ $brandName }}</span>
            </a>

            {{-- ── Right zone ─────────────────────────────────────────── --}}
            <div class="flex items-center gap-1.5">
                <x-ui.theme-toggle />

                {{-- ── Desktop Nav (md+) ──────────────────────────────── --}}
                <nav class="hidden md:flex items-center gap-0.5">
                    @if(auth('applicant')->check() || $actingRespondent)
                    @php
                    $applicantUser = auth('applicant')->user();
                    $extractFirstName = function (?string $value): ?string {
                        $value = trim((string) $value);
                        if ($value === '') { return null; }
                        $parts = preg_split('/\s+/', $value);
                        return $parts[0] ?? null;
                    };
                    $applicantDisplayName = $extractFirstName($applicantUser?->first_name)
                        ?? $extractFirstName($applicantUser?->full_name)
                        ?? $extractFirstName($applicantUser?->name)
                        ?? ($actingRespondent ? __('respondent.respondent_label') : __('app.profile'));
                    @endphp

                    @if($actingRespondent)
                    {{-- Respondent nav links --}}
                    <a href="{{ route('respondent.dashboard') }}"
                       class="ap-navlink {{ request()->routeIs('respondent.dashboard') ? 'ap-navlink-active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        {{ __('respondent.dashboard') }}
                    </a>
                    <a href="{{ route('respondent.case.search') }}"
                       class="ap-navlink {{ request()->routeIs('respondent.case.search') ? 'ap-navlink-active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5a6 6 0 110 12 6 6 0 010-12zm5 11 5 5"/>
                        </svg>
                        {{ __('respondent.find_case') }}
                    </a>
                    <a href="{{ route('respondent.cases.my') }}"
                       class="ap-navlink {{ request()->routeIs('respondent.cases.*') ? 'ap-navlink-active' : '' }}">
                        {{-- My Cases (briefcase) --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7h-3V5a2 2 0 00-2-2H9a2 2 0 00-2 2v2H4a2 2 0 00-2 2v9a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zM9 5h6v2H9V5z"/>
                        </svg>
                        {{ __('respondent.my_cases') }}
                    </a>
                    <a href="{{ route('respondent.responses.index') }}"
                       class="ap-navlink {{ request()->routeIs('respondent.responses.*') ? 'ap-navlink-active' : '' }}">
                        {{-- My Responses (reply bubble) --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11a4 4 0 014 4v0a4 4 0 01-4 4H7l-4 3V10zm0 0l4-4"/>
                        </svg>
                        {{ __('respondent.my_responses') }}
                    </a>
                    @else
                    {{-- Applicant nav links --}}
                    <a href="{{ route('applicant.dashboard') }}"
                       class="ap-navlink {{ request()->routeIs('applicant.dashboard') ? 'ap-navlink-active' : '' }}">
                        {{-- Home --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        {{ __('app.home') }}
                    </a>
                    <a href="{{ route('applicant.cases.index') }}"
                       class="ap-navlink {{ request()->routeIs('applicant.cases.index') || request()->routeIs('applicant.cases.show') || request()->routeIs('applicant.cases.edit') ? 'ap-navlink-active' : '' }}">
                        {{-- My Cases (briefcase) --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7h-3V5a2 2 0 00-2-2H9a2 2 0 00-2 2v2H4a2 2 0 00-2 2v9a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zM9 5h6v2H9V5z"/>
                        </svg>
                        {{ __('app.my_cases') }}
                    </a>
                    <a href="{{ route('applicant.cases.create') }}"
                       class="ap-navlink {{ request()->routeIs('applicant.cases.create') ? 'ap-navlink-active' : '' }}">
                        {{-- New Case (document plus) --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6M7 3h7l5 5v11a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                        </svg>
                        {{ __('app.new_case') }}
                    </a>
                    @endif

                    {{-- Language pill (no dropdown) --}}
                    @if($showLanguageSwitcher)
                    <div class="ap-lang-switch mx-1" aria-label="{{ __('app.Language') }}">
                        <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
                           class="ap-lang-opt {{ app()->getLocale() === 'en' ? 'active' : '' }}">
                            <span class="fi fi-us text-xs"></span> EN
                        </a>
                        <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
                           class="ap-lang-opt {{ app()->getLocale() === 'am' ? 'active' : '' }}">
                            <span class="fi fi-et text-xs"></span> አማ
                        </a>
                    </div>
                    @endif

                    @if($actingRespondent)
                    {{-- Respondent notifications --}}
                    <div x-data="{ open: false }" class="relative" @close-notification-menus.window="open = false">
                        <button type="button"
                            @click.stop="open = !open; $dispatch('close-profile-menus')"
                            class="ap-icon-btn" aria-label="{{ __('respondent.notifications') }}">
                            {{-- Notifications (bell) --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/>
                            </svg>
                            @if(($respondentNotifCount ?? 0) > 0)
                            <span class="ap-badge">{{ $respondentNotifCount > 9 ? '9+' : $respondentNotifCount }}</span>
                            @endif
                        </button>
                        <div x-cloak x-show="open" @click.outside="open=false" class="ap-notif-dropdown" style="width:20rem">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                                <div>
                                    <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('respondent.notifications') }}</div>
                                    <div class="text-sm font-semibold text-slate-900">{{ $respondentNotifCount }} {{ __('respondent.notifications') }}</div>
                                </div>
                                <form method="POST" action="{{ route('respondent.notifications.markAll') }}">
                                    @csrf
                                    <button class="text-xs font-semibold text-blue-700 hover:underline" type="submit">
                                        {{ __('respondent.mark_all_as_read') ?? 'Mark all' }}
                                    </button>
                                </form>
                            </div>
                            <div class="max-h-80 overflow-auto divide-y divide-slate-100">
                                @forelse($respondentNotifList as $item)
                                <div class="px-4 py-3 text-sm">
                                    <div class="flex items-center justify-between gap-2">
                                        <div>
                                            <div class="text-[11px] inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 mb-1">
                                                {{ ucfirst(str_replace('_',' ', $item->notif_type ?? 'update')) }}
                                            </div>
                                            <div class="font-semibold text-slate-900">{{ $item->case_number ?? '—' }}</div>
                                            <div class="text-xs text-slate-500">{{ $item->title ?? __('respondent.view_case_details') }}</div>
                                            <div class="text-[11px] text-slate-400 mt-1">{{ \App\Support\EthiopianDate::smartRelative($item->at) }}</div>
                                        </div>
                                        <div class="flex flex-col gap-2 items-end">
                                            <a href="{{ route('respondent.cases.show', $item->case_number) }}" class="text-xs font-semibold text-blue-700 hover:underline">
                                                {{ __('respondent.view_case_details') }}
                                            </a>
                                            <form method="POST" action="{{ route('respondent.notifications.markOne') }}">
                                                @csrf
                                                <input type="hidden" name="type" value="{{ $item->notif_type ?? 'respondent_view' }}">
                                                <input type="hidden" name="sourceId" value="{{ $item->id }}">
                                                <button class="text-[11px] text-slate-500 hover:text-blue-700" type="submit">{{ __('respondent.mark_as_read') ?? 'Mark read' }}</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="px-4 py-4 text-sm text-slate-500">{{ __('respondent.no_notifications') }}</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Switch to applicant --}}
                    <form method="POST" action="{{ route('respondent.switchToApplicant') }}">
                        @csrf
                        <button type="submit" class="ap-navlink-orange">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17l4-4-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            {{ __('app.switch_to_applicant') }}
                        </button>
                    </form>
                    @else

                    @if(auth('applicant')->check())
                    {{-- Applicant notifications (single bell, all notifications) --}}
                    <div x-data="{ open:false }"
                         class="relative" @close-notification-menus.window="open = false">
                        <div class="flex items-center gap-0.5">
                            @php $totalNotifCount = (int) $messageNotificationCount + (int) $notificationCount; @endphp
                            <button type="button"
                                @click.stop="open = !open; $dispatch('close-profile-menus')"
                                class="ap-icon-btn" :class="open ? 'ap-icon-btn-active' : ''"
                                aria-label="{{ __('app.Notifications') }}">
                                {{-- Notifications (bell) --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/>
                                </svg>
                                @if($totalNotifCount > 0)
                                <span class="ap-badge">{{ $totalNotifCount > 9 ? '9+' : $totalNotifCount }}</span>
                                @endif
                            </button>
                        </div>

                        {{-- Notification dropdown (all notifications) --}}
                        <div x-cloak x-show="open" @click.outside="open=false" class="ap-notif-dropdown">
                            <div class="p-3">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-semibold text-slate-700">
                                        {{ __('app.Notifications') }}
                                    </div>
                                    @if($hasAnyNotifications)
                                    <form method="POST" action="{{ route('applicant.notifications.markAll') }}">
                                        @csrf
                                        <button class="text-xs text-slate-700 px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">{{ __('app.Mark all as seen') }}</button>
                                    </form>
                                    @endif
                                </div>

                                @if(!$hasAnyNotifications)
                                <div class="mt-3 text-sm text-slate-500">{{ __('app.youre_all_caught_up') }}</div>
                                @endif

                                {{-- Messages --}}
                                @if($unseenMsgs->isNotEmpty())
                                <div class="mt-3 space-y-2">
                                    <div class="mb-1 flex items-center justify-between">
                                        <div class="text-xs font-medium text-slate-500">{{ __('cases.navigation.messages') }}</div>
                                        <span class="text-[11px] rounded-full bg-slate-100 px-2 py-0.5 text-slate-700">{{ $unseenMsgs->count() }}</span>
                                    </div>
                                    <ul class="divide-y">
                                        @foreach($unseenMsgs as $m)
                                        @php
                                        $legacyApplicantUpdate = 'Applicant updated the case details. Please review the submission.';
                                        $displayBody = trim((string) $m->body) === $legacyApplicantUpdate ? __('cases.notifications.applicant_updated_submission') : (string) $m->body;
                                        @endphp
                                        <li class="py-2 flex items-center justify-between gap-3">
                                            <a href="{{ route('applicant.cases.show', $m->case_id) }}" class="text-sm flex-1">
                                                <div class="font-medium text-slate-800">{{ $m->case_number }}</div>
                                                <div class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($displayBody, 80) }} <span class="text-slate-400">·</span> {{ \App\Support\EthiopianDate::smartRelative($m->created_at) }}</div>
                                            </a>
                                            <form method="POST" action="{{ route('applicant.notifications.markOne', ['type'=>'message','sourceId'=>$m->id]) }}">
                                                @csrf
                                                <button class="text-xs text-slate-700 px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">{{ __('app.Seen') }}</button>
                                            </form>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif

                                <div class="mt-3 space-y-3">
                                    @if($hasOtherNotifications)
                                    @if($unseenHearings->isNotEmpty())
                                    <div>
                                        <div class="mb-1 flex items-center justify-between">
                                            <div class="text-xs font-medium text-slate-500">{{ __('app.Hearing') }}</div>
                                            <span class="text-[11px] rounded-full bg-slate-100 px-2 py-0.5 text-slate-700">{{ $unseenHearings->count() }}</span>
                                        </div>
                                        <ul class="divide-y">
                                            @foreach($unseenHearings as $h)
                                            <li class="py-2 flex items-center justify-between gap-3">
                                                <a href="{{ route('applicant.cases.show', $h->case_id) }}" class="text-sm flex-1">
                                                    <div class="font-medium text-slate-800">{{ $h->case_number }} <span class="text-slate-400">·</span> {{ \App\Support\EthiopianDate::format($h->hearing_at, withTime: true) }}</div>
                                                    <div class="text-xs text-slate-500">{{ $h->type ?: __('app.Hearing') }}@if($h->location) <span class="text-slate-400">·</span> {{ $h->location }}@endif</div>
                                                </a>
                                                <form method="POST" action="{{ route('applicant.notifications.markOne', ['type'=>'hearing','sourceId'=>$h->id]) }}">
                                                    @csrf
                                                    <button class="text-xs text-slate-700 px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">{{ __('app.Seen') }}</button>
                                                </form>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                    @if($respondentViews->isNotEmpty())
                                    <div>
                                        <div class="mb-1 flex items-center justify-between">
                                            <div class="text-xs font-medium text-slate-500">{{ __('app.admin_notifications.respondent_views') }}</div>
                                            <span class="text-[11px] rounded-full bg-slate-100 px-2 py-0.5 text-slate-700">{{ $respondentViews->count() }}</span>
                                        </div>
                                        <ul class="divide-y">
                                            @foreach($respondentViews as $v)
                                            <li class="py-2 flex items-center justify-between gap-3">
                                                <a href="{{ route('applicant.cases.show', $v->case_id) }}" class="text-sm flex-1">
                                                    <div class="font-medium text-slate-800">{{ $v->case_number }}</div>
                                                    <div class="text-xs text-slate-500">
                                                        {{ __('app.admin_notifications.respondent_viewed_case', ['name' => ($v->respondent_name ?: __('app.admin_notifications.respondent_default'))]) }}
                                                        <span class="text-slate-400">·</span>
                                                        {{ \App\Support\EthiopianDate::smartRelative($v->viewed_at) }}
                                                    </div>
                                                </a>
                                                <form method="POST" action="{{ route('applicant.notifications.markOne', ['type'=>'respondent_view','sourceId'=>$v->id]) }}">
                                                    @csrf
                                                    <button class="text-xs text-slate-700 px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">{{ __('app.Seen') }}</button>
                                                </form>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                    @if($unseenStatus->isNotEmpty())
                                    <div>
                                        <div class="mb-1 flex items-center justify-between">
                                            <div class="text-xs font-medium text-slate-500">{{ __('app.admin_notifications.status_updates') }}</div>
                                            <span class="text-[11px] rounded-full bg-slate-100 px-2 py-0.5 text-slate-700">{{ $unseenStatus->count() }}</span>
                                        </div>
                                        <ul class="divide-y">
                                            @foreach($unseenStatus as $s)
                                            @php
                                            $fromStatusKey = "app.status.{$s->from_status}";
                                            $toStatusKey = "app.status.{$s->to_status}";
                                            $fromStatus = trans()->has($fromStatusKey) ? __($fromStatusKey) : \Illuminate\Support\Str::headline($s->from_status ?? '-');
                                            $toStatus = trans()->has($toStatusKey) ? __($toStatusKey) : \Illuminate\Support\Str::headline($s->to_status ?? '-');
                                            @endphp
                                            <li class="py-2 flex items-center justify-between gap-3">
                                                <a href="{{ route('applicant.cases.show', $s->case_id) }}" class="text-sm flex-1">
                                                    <div class="font-medium text-slate-800">{{ $s->case_number }}</div>
                                                    <div class="text-xs text-slate-500">
                                                        {{ __('app.admin_notifications.status_changed', ['from' => $fromStatus, 'to' => $toStatus]) }}
                                                        <span class="text-slate-400">·</span>
                                                        {{ \App\Support\EthiopianDate::smartRelative($s->created_at) }}
                                                    </div>
                                                </a>
                                                <form method="POST" action="{{ route('applicant.notifications.markOne', ['type'=>'status','sourceId'=>$s->id]) }}">
                                                    @csrf
                                                    <button class="text-xs text-slate-700 px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">{{ __('app.Seen') }}</button>
                                                </form>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                    @endif
                                </div>

                                <div class="mt-3 border-t pt-2 flex items-center justify-between">
                                    <a href="{{ route('applicant.notifications.index') }}" class="text-xs text-slate-600 hover:text-slate-800">{{ __('app.View all') }}</a>
                                    <a href="{{ route('applicant.notifications.settings') }}" class="text-xs text-slate-600 hover:text-slate-800">{{ __('app.Settings') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Switch to respondent --}}
                    <form method="POST" action="{{ route('applicant.switchToRespondent') }}">
                        @csrf
                        <button type="submit" class="ap-navlink-orange">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17l4-4-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            {{ __('app.switch_to_respondent') }}
                        </button>
                    </form>
                    @endif {{-- end auth('applicant')->check() --}}
                    @endif {{-- end $actingRespondent else --}}

                    {{-- Profile dropdown --}}
                    <div x-data="{ open: false }" class="relative ml-0.5" @close-profile-menus.window="open = false">
                        <button @click.stop="open = !open; $dispatch('close-notification-menus')" type="button"
                            class="ap-profile-btn" :class="{ 'ap-profile-btn-active': open }">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="truncate max-w-[100px]">{{ $applicantDisplayName }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6"/>
                            </svg>
                        </button>
                        <div x-cloak x-show="open" @click.outside="open = false" class="ap-profile-dropdown">
                            @if($actingRespondent)
                            <a href="{{ route('respondent.profile.edit') }}" class="ap-dropdown-item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                {{ __('respondent.profile') }}
                            </a>
                            <div class="ap-dropdown-divider"></div>
                            <form method="POST" action="{{ route('respondent.logout') }}">
                                @csrf
                                <button type="submit" class="ap-dropdown-item text-rose-600 hover:bg-rose-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    {{ __('app.logout') }}
                                </button>
                            </form>
                            @else
                            <a href="{{ route('applicant.profile.edit') }}" class="ap-dropdown-item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                {{ __('app.profile') }}
                            </a>
                            <div class="ap-dropdown-divider"></div>
                            <form method="POST" action="{{ route('applicant.logout') }}">
                                @csrf
                                <button type="submit" class="ap-dropdown-item text-rose-600 hover:bg-rose-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    {{ __('app.logout') }}
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                    @else
                    {{-- Guest --}}
                    @if($showLanguageSwitcher)
                    <div class="ap-lang-switch mx-1" aria-label="{{ __('app.Language') }}">
                        <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
                           class="ap-lang-opt {{ app()->getLocale() === 'en' ? 'active' : '' }}">
                            <span class="fi fi-us text-xs"></span> EN
                        </a>
                        <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
                           class="ap-lang-opt {{ app()->getLocale() === 'am' ? 'active' : '' }}">
                            <span class="fi fi-et text-xs"></span> አማ
                        </a>
                    </div>
                    @endif
                    <a href="{{ route('applicant.register') }}" class="ap-navlink-cta">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        {{ __('app.register') }}
                    </a>
                    <a href="{{ route('applicant.login') }}" class="ap-navlink">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        {{ __('app.login') }}
                    </a>
                    @endif
                </nav>

                {{-- ── Hamburger (mobile only) ─────────────────────── --}}
                <button @click="mobileOpen = !mobileOpen"
                    class="ap-icon-btn md:hidden"
                    aria-label="{{ __('app.menu') }}" :aria-expanded="mobileOpen">
                    <svg x-show="!mobileOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileOpen" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ── Mobile panel (full-width, below bar) ──────────────── --}}
        <div x-cloak x-show="mobileOpen" @click.outside="mobileOpen = false"
             class="md:hidden border-t overflow-hidden"
             style="border-color: var(--border); background: var(--surface-strong);">
            <div class="{{ $shellWidthClass }} mx-auto px-4 py-3 max-h-[80vh] overflow-y-auto">
                <ul class="space-y-0.5">

                    {{-- Language --}}
                    @unless($actingRespondent)
                    @if($showLanguageSwitcher)
                    <li class="pb-2 mb-1 border-b" style="border-color: var(--border);">
                        <div class="ap-mobile-section">{{ __('app.language') }}</div>
                        <div class="flex gap-2 px-1">
                            <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
                               class="ap-mobile-lang {{ app()->getLocale() == 'en' ? 'ap-mobile-lang-active' : '' }}">
                                <span class="fi fi-us mr-1 text-xs"></span> English
                            </a>
                            <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
                               class="ap-mobile-lang {{ app()->getLocale() == 'am' ? 'ap-mobile-lang-active' : '' }}">
                                <span class="fi fi-et mr-1 text-xs"></span> አማርኛ
                            </a>
                        </div>
                    </li>
                    @endif
                    @endunless

                    @if(auth('applicant')->check() || $actingRespondent)

                    @if($actingRespondent)
                    {{-- Respondent mobile links --}}
                    <li>
                        <a href="{{ route('respondent.dashboard') }}" class="ap-mobile-link {{ request()->routeIs('respondent.dashboard') ? 'ap-mobile-link-active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            {{ __('respondent.dashboard') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('respondent.case.search') }}" class="ap-mobile-link {{ request()->routeIs('respondent.case.search') ? 'ap-mobile-link-active' : '' }}">
                            {{-- Find Case (search) --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5a6 6 0 110 12 6 6 0 010-12zm5 11 5 5"/></svg>
                            {{ __('respondent.find_case') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('respondent.cases.my') }}" class="ap-mobile-link {{ request()->routeIs('respondent.cases.*') ? 'ap-mobile-link-active' : '' }}">
                            {{-- My Cases (briefcase) --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7h-3V5a2 2 0 00-2-2H9a2 2 0 00-2 2v2H4a2 2 0 00-2 2v9a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zM9 5h6v2H9V5z"/></svg>
                            {{ __('respondent.my_cases') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('respondent.responses.index') }}" class="ap-mobile-link {{ request()->routeIs('respondent.responses.*') ? 'ap-mobile-link-active' : '' }}">
                            {{-- My Responses (reply bubble) --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11a4 4 0 014 4v0a4 4 0 01-4 4H7l-4 3V10zm0 0l4-4"/></svg>
                            {{ __('respondent.my_responses') }}
                        </a>
                    </li>
                    <li class="pt-1 mt-1 border-t border-white/10">
                        <form method="POST" action="{{ route('respondent.switchToApplicant') }}">
                            @csrf
                            <button class="ap-mobile-link ap-mobile-link-warn">{{ __('app.switch_to_applicant') }}</button>
                        </form>
                    </li>
                    <li class="pt-1 mt-1 border-t border-white/10">
                        <div class="ap-mobile-section">{{ $applicantDisplayName }}</div>
                        <a href="{{ route('respondent.profile.edit') }}" class="ap-mobile-link">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            {{ __('respondent.profile') }}
                        </a>
                        <form method="POST" action="{{ route('respondent.logout') }}">
                            @csrf
                            <button class="ap-mobile-link ap-mobile-link-danger">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                {{ __('app.logout') }}
                            </button>
                        </form>
                    </li>
                    @else
                    {{-- Applicant mobile links --}}
                    <li>
                        <a href="{{ route('applicant.dashboard') }}" class="ap-mobile-link {{ request()->routeIs('applicant.dashboard') ? 'ap-mobile-link-active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            {{ __('app.home') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('applicant.cases.index') }}" class="ap-mobile-link {{ request()->routeIs('applicant.cases.index') || request()->routeIs('applicant.cases.show') || request()->routeIs('applicant.cases.edit') ? 'ap-mobile-link-active' : '' }}">
                            {{-- My Cases (briefcase) --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7h-3V5a2 2 0 00-2-2H9a2 2 0 00-2 2v2H4a2 2 0 00-2 2v9a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zM9 5h6v2H9V5z"/></svg>
                            {{ __('app.my_cases') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('applicant.cases.create') }}" class="ap-mobile-link {{ request()->routeIs('applicant.cases.create') ? 'ap-mobile-link-active' : '' }}">
                            {{-- New Case (document plus) --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6M7 3h7l5 5v11a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                            {{ __('app.new_case') }}
                        </a>
                    </li>

                    {{-- Mobile notifications --}}
                    @if(auth('applicant')->check())
                    <li x-data="{ bell: false }" class="border-t border-white/10 pt-1 mt-1">
                        <button @click.stop="bell = !bell"
                            class="ap-mobile-link justify-between">
                            <span class="flex items-center gap-2.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                {{ __('app.notifications') }}
                            </span>
                            @if($notificationCount > 0)
                            <span class="ml-auto inline-flex items-center justify-center rounded-full bg-orange-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ $notificationCount > 9 ? '9+' : $notificationCount }}</span>
                            @endif
                        </button>
                        <div x-cloak x-show="bell" class="mx-1 mb-2">
                            <div class="rounded-xl border border-white/10 bg-white/5 max-h-64 overflow-auto">
                                @include('partials.applicant-notifications')
                            </div>
                        </div>
                    </li>
                    @endif

                    <li class="border-t border-white/10 pt-1 mt-1">
                        <form method="POST" action="{{ route('applicant.switchToRespondent') }}">
                            @csrf
                            <button class="ap-mobile-link ap-mobile-link-warn">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17l4-4-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                {{ __('app.switch_to_respondent') }}
                            </button>
                        </form>
                    </li>
                    <li class="border-t border-white/10 pt-1 mt-1">
                        <div class="ap-mobile-section">{{ $applicantDisplayName }}</div>
                        <a href="{{ route('applicant.profile.edit') }}" class="ap-mobile-link {{ request()->routeIs('applicant.profile.*') ? 'ap-mobile-link-active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            {{ __('app.profile') }}
                        </a>
                        <form method="POST" action="{{ route('applicant.logout') }}">
                            @csrf
                            <button class="ap-mobile-link ap-mobile-link-danger">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                {{ __('app.logout') }}
                            </button>
                        </form>
                    </li>
                    @endif

                    @else
                    {{-- Guest mobile --}}
                    <li>
                        <a href="{{ route('applicant.register') }}" class="ap-mobile-link">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            {{ __('app.register') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('applicant.login') }}" class="ap-mobile-link">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            {{ __('app.login') }}
                        </a>
                    </li>
                    @endif

                </ul>
            </div>
        </div>
    </header>

    @include('partials.admin-toasts')

    {{-- Page notices --}}
    <div class="mx-auto {{ $flashWidthClass }} px-4 pt-5">
        @if(!$actingRespondent)
        {{-- Email verification notice --}}
        @includeIf('applicant.partials.email-unverified')
        @endif
    </div>

    {{-- Page content --}}
    <main class="page-enter mx-auto {{ $mainWidthClass }} px-4 py-8 sm:py-10 {{ $isCaseTypographyRoute ? 'case-font-scope case-typography' : '' }}">

        <div class="space-y-6">
            {{ $slot }}
        </div>

    </main>

    @unless($hideFooter)
    {{-- Footer --}}
    @php $footerNow = now(); @endphp
    <footer class="mt-12 border-t backdrop-blur" style="border-color: var(--border); background: color-mix(in srgb, var(--surface-strong) 95%, transparent);">
        <div class="{{ $shellWidthClass }} mx-auto px-4 py-5">
            <div class="rounded-2xl border px-4 py-4 shadow-sm" style="border-color: var(--border); background: var(--surface);">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-sm font-bold text-white" style="background: rgb(var(--ac));">
                            {{ \Illuminate\Support\Str::of($shortName)->substr(0, 2) }}
                        </span>
                        <div class="text-sm" style="color: var(--text-muted);">
                            <div class="font-semibold" style="color: var(--text);">{{ $brandName }}</div>
                            <div>{{ $footerText }}</div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 font-semibold"
                              style="background: rgb(var(--ac) / 0.12); color: rgb(var(--ac)); border: 1px solid rgb(var(--ac) / 0.25);">
                            <span class="h-1.5 w-1.5 rounded-full" style="background: rgb(var(--ac));"></span>
                            {{ __('app.court_portal') }}
                        </span>
                        @if($actingRespondent)
                        <span class="rounded-full px-3 py-1 font-semibold"
                              style="background: color-mix(in srgb, var(--warning) 14%, transparent); color: var(--warning); border: 1px solid color-mix(in srgb, var(--warning) 30%, transparent);">
                            {{ __('respondent.respondent_view') }}
                        </span>
                        @endif
                        <span class="rounded-full px-3 py-1 font-medium" style="background: var(--surface-strong); border: 1px solid var(--border); color: var(--text-muted);">
                            {{ \App\Support\EthiopianDate::formatDate($footerNow) }}
                        </span>
                        <span class="rounded-full px-3 py-1 font-medium" style="background: var(--surface-strong); border: 1px solid var(--border); color: var(--text-muted);">
                            {{ \App\Support\EthiopianDate::formatTime($footerNow) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    @endunless
    @stack('scripts')
</body>

</html>
