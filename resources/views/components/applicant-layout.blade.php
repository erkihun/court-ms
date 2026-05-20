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
    <header x-data="{ mobileOpen: false }" class="sticky top-0 z-40 border-b border-white/10 bg-[#0c1445] text-white">
        <div class="{{ $shellWidthClass }} mx-auto px-4 flex h-14 items-center justify-between gap-3">

            {{-- ── Brand ──────────────────────────────────────────────── --}}
            <a href="{{ $actingRespondent ? route('respondent.dashboard') : (auth('applicant')->check() ? route('applicant.dashboard') : route('applicant.login')) }}"
               class="flex items-center gap-2.5 flex-shrink-0 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-white/40">
                @if($logoPath)
                <img src="{{ asset('storage/'.$logoPath) }}" alt="{{ $brandName }}" class="h-8 w-auto object-contain">
                @else
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white/15 border border-white/20 text-xs font-bold uppercase tracking-wide flex-shrink-0">
                    {{ \Illuminate\Support\Str::of($shortName)->substr(0,2) }}
                </span>
                @endif
                <span class="font-bold text-sm text-white/90 hidden sm:block truncate max-w-[200px] leading-tight">{{ $brandName }}</span>
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
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10M4 18h6"/>
                        </svg>
                        {{ __('respondent.my_cases') }}
                    </a>
                    <a href="{{ route('respondent.responses.index') }}"
                       class="ap-navlink {{ request()->routeIs('respondent.responses.*') ? 'ap-navlink-active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5h14M5 12h14M5 19h7"/>
                        </svg>
                        {{ __('respondent.my_responses') }}
                    </a>
                    @else
                    {{-- Applicant nav links --}}
                    <a href="{{ route('applicant.dashboard') }}"
                       class="ap-navlink {{ request()->routeIs('applicant.dashboard') ? 'ap-navlink-active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        {{ __('app.home') }}
                    </a>
                    <a href="{{ route('applicant.cases.index') }}"
                       class="ap-navlink {{ request()->routeIs('applicant.cases.*') ? 'ap-navlink-active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10M4 18h6"/>
                        </svg>
                        {{ __('app.my_cases') }}
                    </a>
                    @endif

                    {{-- Language pill (no dropdown) --}}
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

                    @if($actingRespondent)
                    {{-- Respondent notifications --}}
                    <div x-data="{ open: false }" class="relative" @close-notification-menus.window="open = false">
                        <button type="button"
                            @click.stop="open = !open; $dispatch('close-profile-menus')"
                            class="ap-icon-btn" aria-label="{{ __('respondent.notifications') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0h6z"/>
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
                                            <div class="text-[11px] text-slate-400 mt-1">{{ optional($item->at)->diffForHumans() }}</div>
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
                    {{-- Applicant notifications (msg + bell) --}}
                    <div x-data="{ open:false, tab:'notifications', messageModal:false, messageCount: {{ (int) $messageNotificationCount }} }"
                         class="relative" @close-notification-menus.window="open = false">
                        <div class="flex items-center gap-0.5">
                            <button type="button"
                                @click.stop="tab = 'messages'; open = true; if (messageCount > 0) { messageModal = true }; $dispatch('close-profile-menus')"
                                class="ap-icon-btn" :class="tab === 'messages' && open ? 'bg-white/15 text-white' : ''"
                                aria-label="{{ __('cases.navigation.messages') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 8h10M7 12h4m-4 4h6m2-8h3a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-6l-4 3v-3H7a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2z"/>
                                </svg>
                                @if($messageNotificationCount > 0)
                                <span class="ap-badge">{{ $messageNotificationCount > 9 ? '9+' : $messageNotificationCount }}</span>
                                @endif
                            </button>
                            <button type="button"
                                @click.stop="if (tab === 'notifications') { open = !open } else { tab = 'notifications'; open = true }; $dispatch('close-profile-menus')"
                                class="ap-icon-btn" :class="tab === 'notifications' && open ? 'bg-white/15 text-white' : ''"
                                aria-label="{{ __('app.Notifications') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0h6z"/>
                                </svg>
                                @if($notificationCount > 0)
                                <span class="ap-badge">{{ $notificationCount > 9 ? '9+' : $notificationCount }}</span>
                                @endif
                            </button>
                        </div>

                        {{-- Notification dropdown --}}
                        <div x-cloak x-show="open" @click.outside="open=false" class="ap-notif-dropdown">
                            <div class="p-3">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-semibold text-slate-700">
                                        <span x-show="tab === 'messages'" x-cloak>{{ __('cases.navigation.messages') }}</span>
                                        <span x-show="tab !== 'messages'" x-cloak>{{ __('app.Notifications') }}</span>
                                    </div>
                                    @if($hasAnyNotifications)
                                    <form method="POST" action="{{ route('applicant.notifications.markAll') }}">
                                        @csrf
                                        <button class="text-xs text-slate-700 px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">{{ __('app.Mark all as seen') }}</button>
                                    </form>
                                    @endif
                                </div>

                                <div class="mt-3 space-y-2" x-show="tab === 'messages'" x-cloak>
                                    @if($unseenMsgs->isEmpty())
                                    <div class="text-sm text-slate-500">{{ __('cases.messages_section.no_messages') }}</div>
                                    @else
                                    <ul class="divide-y">
                                        @foreach($unseenMsgs as $m)
                                        @php
                                        $legacyApplicantUpdate = 'Applicant updated the case details. Please review the submission.';
                                        $displayBody = trim((string) $m->body) === $legacyApplicantUpdate ? __('cases.notifications.applicant_updated_submission') : (string) $m->body;
                                        @endphp
                                        <li class="py-2 flex items-center justify-between gap-3">
                                            <a href="{{ route('applicant.cases.show', $m->case_id) }}" class="text-sm flex-1">
                                                <div class="font-medium text-slate-800">{{ $m->case_number }}</div>
                                                <div class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($displayBody, 80) }} <span class="text-slate-400">·</span> {{ \Illuminate\Support\Carbon::parse($m->created_at)->diffForHumans() }}</div>
                                            </a>
                                            <form method="POST" action="{{ route('applicant.notifications.markOne', ['type'=>'message','sourceId'=>$m->id]) }}">
                                                @csrf
                                                <button class="text-xs text-slate-700 px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">{{ __('app.Seen') }}</button>
                                            </form>
                                        </li>
                                        @endforeach
                                    </ul>
                                    @endif
                                </div>

                                <div class="mt-3 space-y-3" x-show="tab === 'notifications'" x-cloak>
                                    @if(!$hasOtherNotifications)
                                    <div class="text-sm text-slate-500">{{ __('app.youre_all_caught_up') }}</div>
                                    @else
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
                                                    <div class="text-xs text-slate-500">{{ $v->respondent_name ?: __('app.admin_notifications.respondent_default') }} <span class="text-slate-400">·</span> {{ \Illuminate\Support\Carbon::parse($v->viewed_at)->diffForHumans() }}</div>
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
                                            <li class="py-2 flex items-center justify-between gap-3">
                                                <a href="{{ route('applicant.cases.show', $s->case_id) }}" class="text-sm flex-1">
                                                    <div class="font-medium text-slate-800">{{ $s->case_number }}</div>
                                                    <div class="text-xs text-slate-500">{{ ucfirst($s->from_status) }} <span class="text-slate-400">·</span> <strong>{{ ucfirst($s->to_status) }}</strong> <span class="text-slate-400">·</span> {{ \Illuminate\Support\Carbon::parse($s->created_at)->diffForHumans() }}</div>
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

                        {{-- Message modal --}}
                        <div x-cloak x-show="messageModal"
                            x-transition:enter="motion-overlay-enter" x-transition:enter-start="motion-fade-start" x-transition:enter-end="motion-fade-end"
                            x-transition:leave="motion-overlay-leave" x-transition:leave-start="motion-fade-end" x-transition:leave-end="motion-fade-start"
                            @keydown.escape.window="messageModal=false"
                            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
                            <div class="absolute inset-0 bg-black/40" @click="messageModal=false"></div>
                            <div class="relative w-full max-w-lg rounded-xl border border-slate-200 bg-white shadow-2xl">
                                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                                    <h3 class="text-sm font-semibold text-slate-900">{{ __('cases.navigation.messages') }}</h3>
                                    <button type="button" class="text-slate-400 hover:text-slate-700" @click="messageModal=false">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <div class="max-h-96 overflow-auto p-5 space-y-3">
                                    @if($unseenMsgs->isEmpty())
                                    <div class="text-sm text-slate-500">{{ __('cases.messages_section.no_messages') }}</div>
                                    @else
                                    <ul class="divide-y">
                                        @foreach($unseenMsgs as $m)
                                        @php
                                        $legacyApplicantUpdate = 'Applicant updated the case details. Please review the submission.';
                                        $displayBody = trim((string) $m->body) === $legacyApplicantUpdate ? __('cases.notifications.applicant_updated_submission') : (string) $m->body;
                                        @endphp
                                        <li class="py-2">
                                            <div class="flex items-start gap-3">
                                                <div class="flex-1">
                                                    <a href="{{ route('applicant.cases.show', $m->case_id) }}" class="text-sm font-semibold text-slate-900 hover:text-blue-600">{{ $m->case_number }}</a>
                                                    <p class="text-xs text-slate-500 mt-0.5">{{ \Illuminate\Support\Str::limit($displayBody, 90) }} <span class="text-slate-400">·</span> {{ \Illuminate\Support\Carbon::parse($m->created_at)->diffForHumans() }}</p>
                                                </div>
                                                <form method="POST" action="{{ route('applicant.notifications.markOne', ['type'=>'message','sourceId'=>$m->id]) }}">
                                                    @csrf
                                                    <button class="text-xs text-slate-700 px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">{{ __('app.Seen') }}</button>
                                                </form>
                                            </div>
                                        </li>
                                        @endforeach
                                    </ul>
                                    @endif
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
                    class="md:hidden inline-flex h-9 w-9 items-center justify-center rounded-lg text-white/80 hover:bg-white/10 hover:text-white transition-colors"
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
             class="md:hidden border-t border-white/10 bg-[#0c1445] overflow-hidden">
            <div class="{{ $shellWidthClass }} mx-auto px-4 py-3 max-h-[80vh] overflow-y-auto">
                <ul class="space-y-0.5">

                    {{-- Language --}}
                    @unless($actingRespondent)
                    <li class="pb-2 mb-1 border-b border-white/10">
                        <div class="ap-mobile-section">{{ __('app.language') }}</div>
                        <div class="flex gap-2 px-1">
                            <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
                               class="flex-1 text-center rounded-lg py-2 text-sm font-bold transition-colors {{ app()->getLocale() == 'en' ? 'bg-white text-[#0c1445]' : 'bg-white/10 text-white/75 hover:bg-white/15 hover:text-white' }}">
                                <span class="fi fi-us mr-1 text-xs"></span> English
                            </a>
                            <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
                               class="flex-1 text-center rounded-lg py-2 text-sm font-bold transition-colors {{ app()->getLocale() == 'am' ? 'bg-orange-500 text-white' : 'bg-white/10 text-white/75 hover:bg-white/15 hover:text-white' }}">
                                <span class="fi fi-et mr-1 text-xs"></span> አማርኛ
                            </a>
                        </div>
                    </li>
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5a6 6 0 110 12 6 6 0 010-12zm5 11 5 5"/></svg>
                            {{ __('respondent.find_case') }}
                        </a>
                    </li>
                    <li class="pt-1 mt-1 border-t border-white/10">
                        <form method="POST" action="{{ route('respondent.switchToApplicant') }}">
                            @csrf
                            <button class="ap-mobile-link text-orange-300 hover:text-orange-200 hover:bg-orange-500/20">{{ __('app.switch_to_applicant') }}</button>
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
                            <button class="ap-mobile-link text-rose-300 hover:text-rose-200 hover:bg-rose-500/20">
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
                        <a href="{{ route('applicant.cases.index') }}" class="ap-mobile-link {{ request()->routeIs('applicant.cases.*') ? 'ap-mobile-link-active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10M4 18h6"/></svg>
                            {{ __('app.my_cases') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('applicant.cases.create') }}" class="ap-mobile-link {{ request()->routeIs('applicant.cases.create') ? 'ap-mobile-link-active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            {{ __('app.new_case') }}
                        </a>
                    </li>

                    {{-- Mobile notifications --}}
                    @if(auth('applicant')->check())
                    <li x-data="{ bell: false }" class="border-t border-white/10 pt-1 mt-1">
                        <button @click.stop="bell = !bell"
                            class="ap-mobile-link justify-between">
                            <span class="flex items-center gap-2.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0h6z"/></svg>
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
                            <button class="ap-mobile-link text-orange-300 hover:text-orange-200 hover:bg-orange-500/20">
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
                            <button class="ap-mobile-link text-rose-300 hover:text-rose-200 hover:bg-rose-500/20">
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

    {{-- Flash messages --}}
    <div class="mx-auto {{ $flashWidthClass }} px-4 pt-5">
        @if(session('success'))
        <x-ui.alert type="success" class="mb-4">
            <span class="mt-0.5">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <span>{{ session('success') }}</span>
        </x-ui.alert>
        @endif
        @if(session('error'))
        <x-ui.alert type="error" class="mb-4">
            <span class="mt-0.5">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M12 9v3m0 3h.01M12 5a7 7 0 100 14 7 7 0 000-14z" />
                </svg>
            </span>
            <span>{{ session('error') }}</span>
        </x-ui.alert>
        @endif

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
    <footer class="mt-12 border-t border-slate-200/80 bg-white/95 backdrop-blur">
        <div class="{{ $shellWidthClass }} mx-auto px-4 py-5">
            <div class="rounded-2xl border border-slate-200/80 bg-gradient-to-r from-white via-slate-50 to-white px-4 py-4 shadow-sm">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 text-sm font-bold text-white">
                            {{ \Illuminate\Support\Str::of($shortName)->substr(0, 2) }}
                        </span>
                        <div class="text-sm text-slate-600">
                            <div class="font-semibold text-slate-900">{{ $brandName }}</div>
                            <div>{{ $footerText }}</div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 font-semibold text-blue-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                            {{ __('app.court_portal') }}
                        </span>
                        @if($actingRespondent)
                        <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 font-semibold text-amber-700">
                            {{ __('respondent.respondent_view') }}
                        </span>
                        @endif
                        <span class="rounded-full border border-slate-200 bg-white px-3 py-1 font-medium text-slate-600">
                            {{ \App\Support\EthiopianDate::formatDate($footerNow) }}
                        </span>
                        <span class="rounded-full border border-slate-200 bg-white px-3 py-1 font-medium text-slate-600">
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

