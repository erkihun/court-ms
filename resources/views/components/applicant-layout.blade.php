@props(['title' => __('app.court_portal'), 'hideFooter' => false, 'asRespondentNav' => false])

@php
$layout = $publicLayout ?? [];
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
->select('h.id', 'h.hearing_at', 'h.location', 'h.type', 'c.id as case_id', 'c.case_number')
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
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>{{ $title }} | {{ $brandName }}</title>

    {{-- Optional favicon if you later store it in system_settings --}}
    @if(!empty($systemSettings?->favicon_path))
    <link rel="icon" href="{{ asset('storage/'.$systemSettings->favicon_path) }}">
    @endif

    @vite(['resources/css/app.css','resources/js/app.js'])
    @stack('head')

</head>

<body class="min-h-screen bg-slate-50 text-slate-800">

    {{-- Header / Nav --}}
    <header class="sticky top-0 z-40 bg-blue-800 text-white border-b border-blue-900/60 shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ $actingRespondent ? route('respondent.dashboard') : (auth('applicant')->check() ? route('applicant.dashboard') : route('applicant.login')) }}"
                class="flex items-center gap-2">
                <div class="flex items-center gap-2">
                    @if($logoPath)
                    <div class=" rounded-lg flex items-center justify-center ">
                        <img src="{{ asset('storage/'.$logoPath) }}" alt="{{ $brandName }}"
                            class="h-9 w-auto object-contain">
                    </div>
                    @else
                    <div
                        class="h-9 w-9 rounded-lg bg-blue-900/60 flex items-center justify-center border border-blue-700/80  font-semibold uppercase tracking-wide">
                        {{ \Illuminate\Support\Str::of($shortName)->substr(0,2) }}
                    </div>
                    @endif

                    <div class="flex flex-col leading-tight">
                        <span class="font-semibold text-base md:text-lg">
                            {{ $brandName }}</br>{{ $shortName }}
                        </span>

                    </div>
                </div>
            </a>

            <nav x-data="{ open:false }" class="relative">
                {{-- Desktop --}}
                <ul class="hidden md:flex items-center gap-4 text-sm">
                    {{-- Language Switcher --}}
                    <li x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-blue-600 bg-blue-700  font-medium hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-800 focus:ring-orange-400">
                            <span class="fi fi-{{ app()->getLocale() == 'am' ? 'et' : 'us' }}"></span>
                            <span>{{ __('app.Language') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 9l6 6 6-6" />
                            </svg>
                        </button>

                        <div x-cloak x-show="open" @click.outside="open = false"
                            class="absolute right-0 mt-2 w-36 rounded-md border border-slate-200 bg-white shadow-lg z-50">
                            <div class="p-2 space-y-1 text-slate-700 text-sm">
                                <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
                                    class="flex items-center gap-2 w-full px-3 py-2 rounded hover:bg-slate-50 {{ app()->getLocale() == 'en' ? 'bg-blue-50 text-blue-700' : '' }}">
                                    <span class="fi fi-us"></span>
                                    {{ __('app.English') }}
                                </a>
                                <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
                                    class="flex items-center gap-2 w-full px-3 py-2 rounded hover:bg-slate-50 {{ app()->getLocale() == 'am' ? 'bg-orange-50 text-orange-700' : '' }}">
                                    <span class="fi fi-et"></span>
                                    {{ __('app.Amharic') }}
                                </a>
                            </div>
                        </div>
                    </li>

                    @if($actingRespondent)
                    <li x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                            class="relative inline-flex items-center gap-2 px-3 py-2 rounded-full border border-blue-700 bg-blue-700/80 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-800 focus:ring-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0h6z" />
                            </svg>
                            @if(($respondentNotifCount ?? 0) > 0)
                            <span class="inline-flex items-center justify-center rounded-full bg-orange-500 px-2 py-0.5 text-[11px] font-semibold text-white">
                                {{ $respondentNotifCount }}
                            </span>
                            @endif
                        </button>
                        <div x-cloak x-show="open" @click.outside="open=false"
                            class="absolute right-0 mt-2 w-80 rounded-xl border border-slate-200 bg-white shadow-lg text-slate-800 z-50">
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
                                            <a href="{{ route('respondent.cases.show', $item->case_number) }}"
                                                class="text-xs font-semibold text-blue-700 hover:underline">
                                                {{ __('respondent.view_case_details') }}
                                            </a>
                                            <form method="POST" action="{{ route('respondent.notifications.markOne') }}">
                                                @csrf
                                                <input type="hidden" name="type" value="{{ $item->notif_type ?? 'respondent_view' }}">
                                                <input type="hidden" name="sourceId" value="{{ $item->id }}">
                                                <button class="text-[11px] text-slate-500 hover:text-blue-700" type="submit">
                                                    {{ __('respondent.mark_as_read') ?? 'Mark read' }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="px-4 py-4 text-sm text-slate-500">
                                    {{ __('respondent.no_notifications') }}
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </li>
                    @endif

                    @if(auth('applicant')->check() || $actingRespondent)
                    @php
                    $applicantUser = auth('applicant')->user();
                    if ($actingRespondent) {
                        $applicantDisplayName = $applicantUser?->full_name ?? $applicantUser?->first_name ?? __('respondent.respondent_label');
                    } else {
                        $applicantDisplayName = $applicantUser?->full_name ?? $applicantUser?->name ?? __('app.profile');
                    }
                    @endphp
                    {{-- Common base style for nav items (desktop) --}}
                    @php
                    $navBase = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full font-medium
                    transition-colors';
                    $navIdle = 'text-blue-50 hover:bg-blue-700 hover:text-white';
                    $navActive = 'bg-white text-blue-800';
                    $navDangerActive = 'bg-orange-500 text-white';
                    @endphp

                    @if($actingRespondent)
                        <li>
                            <a href="{{ route('respondent.dashboard') }}"
                                class="{{ $navBase }} {{ request()->routeIs('respondent.dashboard') ? $navActive : $navIdle }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                {{ __('respondent.dashboard') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('respondent.case.search') }}"
                                class="{{ $navBase }} {{ request()->routeIs('respondent.case.search') ? $navActive : $navIdle }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5a6 6 0 110 12 6 6 0 010-12zm5 11 5 5" />
                                </svg>
                                {{ __('respondent.find_case') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('applicant.dashboard') }}"
                                class="{{ $navBase }} {{ $navIdle }} bg-orange-500 text-white hover:bg-orange-600 border border-orange-500">
                                {{ __('app.switch_to_applicant') }}
                            </a>
                        </li>
                    @else
                        <li>
                            <a href="{{ route('applicant.dashboard') }}"
                                class="{{ $navBase }} {{ request()->routeIs('applicant.dashboard') ? $navActive : $navIdle }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                {{ __('app.home') }}
                            </a>
                        </li>
                        {{-- removed my_cases / my_responses from top nav --}}
                        <li>
                            <form method="POST" action="{{ route('applicant.switchToRespondent') }}">
                                @csrf
                                <button type="submit"
                                    class="{{ $navBase }} {{ $navIdle }} bg-orange-500 text-white hover:bg-orange-600 border border-orange-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 17l4-4-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    {{ __('app.switch_to_respondent') }}
                                </button>
                            </form>
                        </li>
                    @endif


                    @if(!$actingRespondent)
                    {{-- Notifications (desktop) --}}
                    <li x-data="{ open:false, tab:'notifications', messageModal:false }" class="relative">
                        <div class="flex items-center gap-2">
                            <button type="button"
                                @click="tab = 'messages'; open = true; if ($messageNotificationCount > 0) { messageModal = true }"
                                class="relative inline-flex items-center gap-1.5 rounded-full border border-blue-500 px-3 py-1.5 bg-blue-700 text-blue-50 font-medium hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-800 focus:ring-orange-400"
                                :class="tab === 'messages' ? 'bg-white text-blue-800' : ''"
                                aria-label="{{ __('cases.messages') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                        d="M7 8h10M7 12h4m-4 4h6m2-8h3a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-6l-4 3v-3H7a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2z" />
                                </svg>
                                @if($messageNotificationCount > 0)
                                <span
                                    class="absolute -top-1 -right-1 grid h-5 min-w-[20px] place-items-center rounded-full bg-orange-500 px-1 text-[11px] font-semibold text-white">
                                    {{ $messageNotificationCount > 9 ? '9+' : $messageNotificationCount }}
                                </span>
                                @endif
                            </button>
                            <button type="button"
                                @click="if (tab === 'notifications') { open = !open } else { tab = 'notifications'; open = true }"
                                class="relative inline-flex items-center gap-1.5 rounded-full border border-blue-500 px-3 py-1.5 bg-blue-700 text-blue-50 font-medium hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-800 focus:ring-orange-400"
                                :class="tab === 'notifications' ? 'bg-white text-blue-800' : ''"
                                aria-label="{{ __('app.Notifications') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                        d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0h6z" />
                                </svg>
                                @if($notificationCount > 0)
                                <span
                                    class="absolute -top-1 -right-1 grid h-5 min-w-[20px] place-items-center rounded-full bg-orange-500 px-1 text-[11px] font-semibold text-white">
                                    {{ $notificationCount > 9 ? '9+' : $notificationCount }}
                                </span>
                                @endif
                            </button>
                        </div>

                        {{-- Dropdown --}}
                        <div x-cloak x-show="open" @click.outside="open=false"
                            class="absolute right-0 z-50 mt-2 w-[28rem] max-w-[90vw] rounded-md border border-slate-200 bg-white shadow-xl">
                            <div class="p-3">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-semibold text-slate-700">
                                        <span x-show="tab === 'messages'" x-cloak>{{ __('cases.messages') }}</span>
                                        <span x-show="tab !== 'messages'" x-cloak>{{ __('app.Notifications') }}</span>
                                    </div>
                                    @if($hasAnyNotifications)
                                    <form method="POST" action="{{ route('applicant.notifications.markAll') }}">
                                        @csrf
                                        <button class="text-xs text-slate-700 px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">
                                            {{ __('app.Mark all as seen') }}
                                        </button>
                                    </form>
                                    @endif
                                </div>

                                <div class="mt-3 space-y-2" x-show="tab === 'messages'" x-cloak>
                                    @if($unseenMsgs->isEmpty())
                                    <div class="text-sm text-slate-500">
                                        {{ __('cases.messages_section.no_messages') }}
                                    </div>
                                    @else
                                    <ul class="divide-y">
                                        @foreach($unseenMsgs as $m)
                                        <li class="py-2 flex items-center justify-between gap-3">
                                            <a href="{{ route('applicant.cases.show', $m->case_id) }}"
                                                class="text-sm flex-1">
                                                <div class="font-medium text-slate-800">{{ $m->case_number }}</div>
                                                <div class="text-xs text-slate-500">
                                                    {{ \Illuminate\Support\Str::limit($m->body, 80) }}
                                                    <span class="text-slate-400">·</span>
                                                    {{ \Illuminate\Support\Carbon::parse($m->created_at)->diffForHumans() }}
                                                </div>
                                            </a>
                                            <form method="POST"
                                                action="{{ route('applicant.notifications.markOne', ['type'=>'message','sourceId'=>$m->id]) }}">
                                                @csrf
                                                <button
                                                    class="text-xs text-slate-700 px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">
                                                    {{ __('app.Seen') }}
                                                </button>
                                            </form>
                                        </li>
                                        @endforeach
                                    </ul>
                                    @endif
                                </div>

                                <div class="mt-3 space-y-3" x-show="tab === 'notifications'" x-cloak>
                                    @if(!$hasOtherNotifications)
                                    <div class="text-sm text-slate-500">
                                        {{ __('app.youre_all_caught_up') }}
                                    </div>
                                    @else
                                    @if($unseenHearings->isNotEmpty())
                                    <div>
                                        <div class="mb-1 flex items-center justify-between">
                                            <div class="text-xs font-medium text-slate-500">{{ __('app.Hearing') }}</div>
                                            <span class="text-[11px] rounded-full bg-slate-100 px-2 py-0.5 text-slate-700">
                                                {{ $unseenHearings->count() }}
                                            </span>
                                        </div>
                                        <ul class="divide-y">
                                            @foreach($unseenHearings as $h)
                                            <li class="py-2 flex items-center justify-between gap-3">
                                                <a href="{{ route('applicant.cases.show', $h->case_id) }}"
                                                    class="text-sm flex-1">
                                                    <div class="font-medium text-slate-800">
                                                        {{ $h->case_number }}
                                                        <span class="text-slate-400">·</span>
                                                        {{ \Illuminate\Support\Carbon::parse($h->hearing_at)->format('M d, Y H:i') }}
                                                    </div>
                                                    <div class="text-xs text-slate-500">
                                                        {{ $h->type ?: __('app.Hearing') }}
                                                        @if($h->location)
                                                        <span class="text-slate-400">·</span> {{ $h->location }}
                                                        @endif
                                                    </div>
                                                </a>
                                                <form method="POST"
                                                    action="{{ route('applicant.notifications.markOne', ['type'=>'hearing','sourceId'=>$h->id]) }}">
                                                    @csrf
                                                    <button
                                                        class="text-xs text-slate-700 px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">
                                                        {{ __('app.Seen') }}
                                                    </button>
                                                </form>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif

                                    @if($respondentViews->isNotEmpty())
                                    <div>
                                        <div class="mb-1 flex items-center justify-between">
                                            <div class="text-xs font-medium text-slate-500">Respondents viewed</div>
                                            <span class="text-[11px] rounded-full bg-slate-100 px-2 py-0.5 text-slate-700">
                                                {{ $respondentViews->count() }}
                                            </span>
                                        </div>
                                        <ul class="divide-y">
                                            @foreach($respondentViews as $v)
                                            <li class="py-2 flex items-center justify-between gap-3">
                                                <a href="{{ route('applicant.cases.show', $v->case_id) }}"
                                                    class="text-sm flex-1">
                                                    <div class="font-medium text-slate-800">{{ $v->case_number }}</div>
                                                    <div class="text-xs text-slate-500">
                                                        {{ $v->respondent_name ?: 'Respondent' }}
                                                        <span class="text-slate-400">·</span>
                                                        {{ \Illuminate\Support\Carbon::parse($v->viewed_at)->diffForHumans() }}
                                                    </div>
                                                </a>
                                                <form method="POST"
                                                    action="{{ route('applicant.notifications.markOne', ['type'=>'respondent_view','sourceId'=>$v->id]) }}">
                                                    @csrf
                                                    <button
                                                        class="text-xs text-slate-700 px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">
                                                        {{ __('app.Seen') }}
                                                    </button>
                                                </form>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif

                                    @if($unseenStatus->isNotEmpty())
                                    <div>
                                        <div class="mb-1 flex items-center justify-between">
                                            <div class="text-xs font-medium text-slate-500">Status updates</div>
                                            <span class="text-[11px] rounded-full bg-slate-100 px-2 py-0.5 text-slate-700">
                                                {{ $unseenStatus->count() }}
                                            </span>
                                        </div>
                                        <ul class="divide-y">
                                            @foreach($unseenStatus as $s)
                                            <li class="py-2 flex items-center justify-between gap-3">
                                                <a href="{{ route('applicant.cases.show', $s->case_id) }}"
                                                    class="text-sm flex-1">
                                                    <div class="font-medium text-slate-800">{{ $s->case_number }}</div>
                                                    <div class="text-xs text-slate-500">
                                                        {{ ucfirst($s->from_status) }}
                                                        <span class="text-slate-400">·</span>
                                                        <strong>{{ ucfirst($s->to_status) }}</strong>
                                                        <span class="text-slate-400">·</span>
                                                        {{ \Illuminate\Support\Carbon::parse($s->created_at)->diffForHumans() }}
                                                    </div>
                                                </a>
                                                <form method="POST"
                                                    action="{{ route('applicant.notifications.markOne', ['type'=>'status','sourceId'=>$s->id]) }}">
                                                    @csrf
                                                    <button
                                                        class="text-xs text-slate-700 px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">
                                                        {{ __('app.Seen') }}
                                                    </button>
                                                </form>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                    @endif
                                </div>

                                <div class="mt-3 border-t pt-2 flex items-center justify-between">
                                    <a href="{{ route('applicant.notifications.index') }}"
                                        class="text-xs text-slate-600 hover:text-slate-800">{{ __('app.View all') }}</a>
                                    <a href="{{ route('applicant.notifications.settings') }}"
                                        class="text-xs text-slate-600 hover:text-slate-800">Settings</a>
                                </div>
                            </div>
                        </div>

                        {{-- Message modal --}}
                        <div x-cloak x-show="messageModal"
                            x-transition.opacity.duration.200
                            @keydown.escape.window="messageModal=false"
                            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
                            <div class="absolute inset-0 bg-black/40" @click="messageModal=false"></div>
                            <div class="relative w-full max-w-lg rounded-xl border border-slate-200 bg-white shadow-2xl">
                                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                                    <h3 class="text-sm font-semibold text-slate-900">{{ __('cases.messages') }}</h3>
                                    <button type="button" class="text-slate-400 hover:text-slate-700" @click="messageModal=false">
                                        <span class="sr-only">Close</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="max-h-96 overflow-auto p-5 space-y-3">
                                    @if($unseenMsgs->isEmpty())
                                    <div class="text-sm text-slate-500">
                                        {{ __('cases.messages_section.no_messages') }}
                                    </div>
                                    @else
                                    <ul class="divide-y">
                                        @foreach($unseenMsgs as $m)
                                        <li class="py-2">
                                            <div class="flex items-start gap-3">
                                                <div class="flex-1">
                                                    <a href="{{ route('applicant.cases.show', $m->case_id) }}"
                                                        class="text-sm font-semibold text-slate-900 hover:text-blue-600">
                                                        {{ $m->case_number }}
                                                    </a>
                                                    <p class="text-xs text-slate-500 mt-0.5">
                                                        {{ \Illuminate\Support\Str::limit($m->body, 90) }}
                                                        <span class="text-slate-400">·</span>
                                                        {{ \Illuminate\Support\Carbon::parse($m->created_at)->diffForHumans() }}
                                                    </p>
                                                </div>
                                                <form method="POST"
                                                    action="{{ route('applicant.notifications.markOne', ['type'=>'message','sourceId'=>$m->id]) }}">
                                                    @csrf
                                                    <button
                                                        class="text-xs text-slate-700 px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">
                                                        {{ __('app.Seen') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </li>
                                        @endforeach
                                    </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </li>
                    @endif

                    {{-- Applicant menu --}}
                    <li x-data="{ open: false }" class="relative">
                        <button @click="open = !open" type="button"
                            class="{{ $navBase }} {{ $navIdle }} whitespace-nowrap"
                            :class="{ 'bg-white text-blue-800': open }">
                            <span class="truncate">{{ $applicantDisplayName }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 9l6 6 6-6" />
                            </svg>
                        </button>
                        <div x-cloak x-show="open" @click.outside="open = false"
                            class="absolute right-0 z-50 mt-2 w-48 rounded-md border border-slate-200 bg-white shadow-xl">
                            @if($actingRespondent)
                                <a href="{{ route('respondent.profile.edit') }}"
                                    class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ __('respondent.profile') }}
                                </a>
                                <form method="POST" action="{{ route('respondent.logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="flex w-full items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        {{ __('app.logout') }}
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('applicant.profile.edit') }}"
                                    class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ __('app.profile') }}
                                </a>
                                <form method="POST" action="{{ route('applicant.logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="flex w-full items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        {{ __('app.logout') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </li>
                    @else
                    <li>
                        <a href="{{ route('applicant.register') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full  font-semibold bg-orange-500 text-white hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-800 focus:ring-orange-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            {{ __('app.register') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('applicant.login') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full  font-semibold border border-white/60 text-white hover:bg-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            {{ __('app.login') }}
                        </a>
                    </li>
                    @endif
                </ul>

                {{-- Mobile trigger --}}
                <button @click="open = !open"
                    class="md:hidden inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-blue-600 bg-blue-700  font-medium hover:bg-blue-600"
                    aria-label="{{ __('app.menu') }}" aria-haspopup="true" :aria-expanded="open">
                    {{ __('app.menu') }}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                {{-- Mobile menu --}}
                <div x-cloak x-show="open" @click.outside="open=false"
                    class="md:hidden absolute right-0 mt-2 w-64 rounded-md border border-slate-200 bg-white shadow-xl text-slate-700">
                    <ul class="py-2 text-sm">
                        {{-- Mobile Language Switcher --}}
                        <li class="border-b border-slate-100 pb-2 mb-2">
                            <div class="px-4 pt-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                {{ __('app.language') }}
                            </div>
                            <div class="px-4 pb-2 flex gap-2">
                                <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
                                    class="flex-1 px-2 py-1  rounded-full border text-center
                                   {{ app()->getLocale() == 'en' ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-50 border-slate-200' }}">
                                    English
                                </a>
                                <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
                                    class="flex-1 px-2 py-1  rounded-full border text-center
                                   {{ app()->getLocale() == 'am' ? 'bg-orange-500 text-white border-orange-500' : 'bg-slate-50 border-slate-200' }}">
                                    አማርኛ
                                </a>
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('applicant.dashboard') }}"
                                class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 {{ request()->routeIs('applicant.dashboard') ? 'text-blue-700 font-medium' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                {{ __('app.home') }}
                            </a>
                        </li>

                        @if(auth('applicant')->check() || $actingRespondent)
                            @if($actingRespondent)
                                <li>
                                    <a href="{{ route('respondent.dashboard') }}"
                                        class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 {{ request()->routeIs('respondent.dashboard') ? 'text-blue-700 font-medium' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                        </svg>
                                        {{ __('respondent.dashboard') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('respondent.case.search') }}"
                                        class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 {{ request()->routeIs('respondent.case.search') ? 'text-blue-700 font-medium' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5a6 6 0 110 12 6 6 0 010-12zm5 11 5 5" />
                                        </svg>
                                        {{ __('respondent.find_case') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('applicant.dashboard') }}"
                                        class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 text-orange-600 font-semibold">
                                        {{ __('app.switch_to_applicant') }}
                                    </a>
                                </li>
                                <li class="border-t border-slate-100 mt-2 pt-2 space-y-1">
                                    <div class="px-4 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                        {{ $applicantDisplayName }}
                                    </div>
                                    <a href="{{ route('respondent.profile.edit') }}"
                                        class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50">
                                        {{ __('respondent.profile') }}
                                    </a>
                                    <form method="POST" action="{{ route('respondent.logout') }}">
                                        @csrf
                                        <button
                                            class="flex items-center gap-2 w-full text-left px-4 py-2 hover:bg-slate-50 text-slate-700">
                                            {{ __('app.logout') }}
                                        </button>
                                    </form>
                                </li>
                            @else
                                <li>
                                    <a href="{{ route('applicant.cases.create') }}"
                                        class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 {{ request()->routeIs('applicant.cases.create') ? 'text-orange-600 font-medium' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        {{ __('app.new_case') }}
                                    </a>
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('applicant.switchToRespondent') }}">
                                        @csrf
                                        <button
                                            class="flex items-center gap-2 w-full text-left px-4 py-2 hover:bg-blue-50 text-blue-700">
                                            {{ __('app.switch_to_respondent') }}
                                        </button>
                                    </form>
                                </li>

                                {{-- Mobile: notifications (inline list) --}}
                                <li x-data="{ bell:false }" class="relative">
                                    <button @click="bell=!bell"
                                        class="flex w-full items-center justify-between px-4 py-2 hover:bg-slate-50">
                                        <div class="flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                                    d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0h6z" />
                                            </svg>
                                            {{ __('app.notifications') }}
                                        </div>
                                        @if($notificationCount > 0)
                                        <span
                                            class="ml-2 inline-flex items-center justify-center rounded-full bg-orange-500 px-2 py-0.5 text-[11px] font-semibold text-white">
                                            {{ $notificationCount > 9 ? '9+' : $notificationCount }}
                                        </span>
                                        @endif
                                    </button>
                                    <div x-cloak x-show="bell" class="px-2 pb-2">
                                        <div class="rounded-md border border-slate-200 bg-white max-h-80 overflow-auto">
                                            @include('partials.applicant-notifications')
                                        </div>
                                    </div>
                                </li>

                                <li class="border-t border-slate-100 mt-2 pt-2 space-y-1">
                                    <div class="px-4 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                        {{ $applicantDisplayName }}
                                    </div>
                                    <a href="{{ route('applicant.profile.edit') }}"
                                        class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 {{ request()->routeIs('applicant.profile.*') ? 'text-blue-700 font-medium' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        {{ __('app.profile') }}
                                    </a>
                                    <form method="POST" action="{{ route('applicant.logout') }}">
                                        @csrf
                                        <button
                                            class="flex items-center gap-2 w-full text-left px-4 py-2 hover:bg-slate-50 text-slate-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            {{ __('app.logout') }}
                                        </button>
                                    </form>
                                </li>
                            @endif
                        @else
                        <li>
                            <a href="{{ route('applicant.register') }}"
                                class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                {{ __('app.register') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('applicant.login') }}"
                                class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                {{ __('app.login') }}
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    {{-- Flash messages --}}
    <div class="max-w-6xl mx-auto px-4 pt-4">
        @if(session('success'))
        <div
            class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm flex items-start gap-2">
            <span class="mt-0.5">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <span>{{ session('success') }}</span>
        </div>
        @endif
        @if(session('error'))
        <div
            class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm flex items-start gap-2">
            <span class="mt-0.5">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M12 9v3m0 3h.01M12 5a7 7 0 100 14 7 7 0 000-14z" />
                </svg>
            </span>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if(!$actingRespondent)
        {{-- Email verification notice --}}
        @includeIf('applicant.partials.email-unverified')
        @endif
    </div>

    {{-- Page content --}}
    <main class="max-w-7xl mx-auto px-4 py-8">

        {{ $slot }}

    </main>

    @unless($hideFooter)
    {{-- Footer --}}
    <footer class="mt-10 border-t border-slate-200 bg-white">
        <div
            class="max-w-7xl mx-auto px-4 py-5  sm:text-sm text-slate-500 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <div>
                Ac {{ date('Y') }} <span class="font-semibold text-blue-700">{{ $brandName }}</span>.
                <span class="text-slate-500">{{ $footerText }}</span>
            </div>
            <div class="flex items-center gap-2 text-[11px] uppercase tracking-wide text-slate-400">
                <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>
                <span>{{ __('app.court_portal') }}</span>
            </div>
        </div>
    </footer>
    @endunless
</body>

</html>
