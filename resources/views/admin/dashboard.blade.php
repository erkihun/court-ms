{{-- resources/views/admin/dashboard.blade.php --}}
<x-admin-layout title="{{ __('app.Dashboard') }}">
    @section('page_header', __('app.Dashboard'))

    @php
    $systemSettings = $systemSettings ?? \App\Models\SystemSetting::current();
    $totalCases = $totalCases ?? 0;
    $pendingCases = $pendingCases ?? 0;
    $resolvedCases = $resolvedCases ?? 0;
    $activeCases = $activeCases ?? 0;

    $recent = collect($recent ?? []);
    $teamCaseCounts = collect($teamCaseCounts ?? []);
    $memberCaseCounts = collect($memberCaseCounts ?? []);

    $labels = $labels ?? [];
    $values = $values ?? [];
    $genderCounts = $genderCounts ?? [];
    $caseTypeCounts = $caseTypeCounts ?? [];
    $maxTeamCases = max((int) $teamCaseCounts->max('cases_count'), 1);
    $maxMemberCases = max((int) $memberCaseCounts->max('cases_count'), 1);

    $statusChip = function (string $s) {
        return match ($s) {
            'pending'              => 'bg-orange-100/60 text-orange-700 border border-orange-200 dark:bg-orange-900/30 dark:text-orange-300 dark:border-orange-800',
            'active'               => 'bg-blue-100/60 text-blue-700 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800',
            'closed', 'dismissed'  => 'bg-emerald-100/60 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800',
            default                => 'bg-[var(--surface-soft)] text-[var(--text-muted)] border border-[var(--border)]',
        };
    };
    @endphp

    @php
    try {
        $resolvedCases = \App\Models\Decision::where('status', 'active')->distinct('court_case_id')->count('court_case_id');
    } catch (\Throwable $e) {}

    if (empty($activeCases)) {
        try {
            $activeCases = \App\Models\CourtCase::where('status', 'active')->count();
        } catch (\Throwable $e) {
            $activeCases = 0;
        }
    }
    @endphp

    @php
    // Pass locale and today to JS — all calendar logic runs client-side
    $calLocale  = app()->getLocale(); // 'am' or 'en'
    $calToday   = ['y' => (int)now()->format('Y'), 'm' => (int)now()->format('n'), 'd' => (int)now()->format('j')];
    @endphp

    @push('styles')
    <style>
        .dash-panel {
            border: 1px solid var(--border);
            border-radius: 1rem;
            background: var(--surface-strong);
            box-shadow: 0 4px 24px rgba(15,23,42,.05);
        }
        .dash-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border-bottom: 1px solid var(--border);
            padding: 1rem 1.25rem;
        }
        .dash-panel-title {
            font-size: .875rem;
            font-weight: 700;
            color: var(--text);
        }
        .dash-panel-sub {
            font-size: .75rem;
            color: var(--text-subtle);
            margin-top: .1rem;
        }
        .dash-panel-body { padding: 1.25rem; }

        /* KPI cards */
        .dash-kpi {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 1rem;
            background: var(--surface-strong);
            padding: 1.1rem;
            box-shadow: 0 4px 18px rgba(15,23,42,.05);
            transition: transform 150ms ease, box-shadow 150ms ease;
        }
        .dash-kpi:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(15,23,42,.08);
        }
        .dash-kpi-value {
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: -.03em;
            color: var(--text);
            margin-top: 1.1rem;
            line-height: 1;
        }
        .dash-kpi-label {
            font-size: .8rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-top: .35rem;
        }
        .dash-kpi-icon {
            display: grid;
            height: 2.625rem;
            width: 2.625rem;
            place-items: center;
            border-radius: .75rem;
        }

        /* Progress */
        .dash-progress-track {
            margin-top: .5rem;
            height: .375rem;
            overflow: hidden;
            border-radius: 999px;
            background: var(--border);
        }
        .dash-progress-fill {
            height: 100%;
            border-radius: inherit;
            background: rgb(var(--ac));
        }

        /* Workload row */
        .dash-workload-row {
            display: grid;
            grid-template-columns: minmax(0,1fr) auto;
            gap: 1rem;
            align-items: center;
            padding: .85rem 0;
        }
        .dash-workload-name {
            font-size: .875rem;
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .dash-workload-sub {
            font-size: .72rem;
            color: var(--text-subtle);
        }

        /* Dividers */
        .dash-divide > * + * { border-top: 1px solid var(--border); }

        /* Member cards */
        .dash-member-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .9rem;
        }
        @media (max-width: 900px) {
            .dash-member-strip { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 560px) {
            .dash-member-strip { grid-template-columns: 1fr; }
        }
        .dash-member-card {
            min-width: 0;
            border: 1px solid var(--border);
            border-radius: .9rem;
            background: var(--surface-soft);
            padding: .9rem;
        }
        .dash-member-name {
            font-size: .875rem;
            font-weight: 700;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .dash-member-team {
            font-size: .72rem;
            color: var(--text-subtle);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .dash-member-count {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text);
            line-height: 1;
        }
        .dash-member-count-label {
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: var(--text-subtle);
        }
        .dash-case-chip {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: var(--surface-strong);
            padding: .1rem .5rem;
            font-size: .68rem;
            font-weight: 600;
            color: var(--text-muted);
        }
        .dash-case-chip-count { color: var(--text); }

        /* Chart */
        .dash-chart-frame { height: 18rem; }

        /* Workload summary boxes */
        .dash-stat-box {
            border-radius: .75rem;
            border: 1px solid var(--border);
            background: var(--surface-soft);
            padding: .75rem;
        }
        .dash-stat-box-label {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: var(--text-subtle);
        }
        .dash-stat-box-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text);
            margin-top: .25rem;
        }

        /* Health panel */
        .dash-health-card {
            border: 1px solid var(--border);
            border-radius: .875rem;
            background: var(--surface-strong);
            padding: 1rem;
        }
        .dash-health-title {
            font-size: .875rem;
            font-weight: 600;
            color: var(--text);
        }
        .dash-health-sub {
            font-size: .72rem;
            color: var(--text-subtle);
            margin-bottom: .5rem;
        }

        /* Recent cases row */
        .dash-case-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .75rem 0;
        }
        .dash-case-number {
            font-size: .875rem;
            font-weight: 700;
            color: var(--text);
        }
        .dash-case-number a { color: inherit; }
        .dash-case-number a:hover { color: rgb(var(--ac)); text-decoration: underline; }
        .dash-case-title {
            font-size: .72rem;
            color: var(--text-subtle);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Empty state */
        .dash-empty {
            border-radius: .75rem;
            border: 1.5px dashed var(--border);
            background: var(--surface-soft);
            padding: 2rem 1rem;
            text-align: center;
            font-size: .875rem;
            color: var(--text-subtle);
        }

        /* ── Mini calendar ─────────────────────────────────── */
        .dash-cal {
            border: 1px solid var(--border);
            border-radius: 1rem;
            background: var(--surface-strong);
            box-shadow: 0 4px 24px rgba(15,23,42,.05);
            padding: 1.1rem 1rem 1rem;
            user-select: none;
        }
        .dash-cal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: .75rem;
        }
        .dash-cal-month {
            font-size: .8125rem;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -.01em;
            line-height: 1.2;
        }
        .dash-cal-year {
            font-size: .68rem;
            color: var(--text-subtle);
            margin-top: .05rem;
        }
        .dash-cal-nav {
            display: flex;
            gap: .25rem;
        }
        .dash-cal-nav button {
            width: 1.5rem;
            height: 1.5rem;
            border-radius: .375rem;
            border: 1px solid var(--border);
            background: var(--surface-soft);
            color: var(--text-muted);
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: background .12s, color .12s;
            line-height: 0;
        }
        .dash-cal-nav button:hover {
            background: rgb(var(--ac)/.1);
            color: rgb(var(--ac));
            border-color: rgb(var(--ac)/.3);
        }
        .dash-cal-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
            text-align: center;
        }
        .dash-cal-dow {
            font-size: .6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--text-subtle);
            padding: .25rem 0 .375rem;
        }
        .dash-cal-day {
            font-size: .72rem;
            font-weight: 500;
            color: var(--text-muted);
            border-radius: .375rem;
            padding: .3rem 0;
            line-height: 1;
        }
        .dash-cal-day.other-month { color: var(--border-strong); }
        .dash-cal-day.today {
            background: rgb(var(--ac));
            color: #fff;
            font-weight: 700;
            border-radius: .5rem;
        }
        .dash-cal-footer {
            margin-top: .875rem;
            padding-top: .75rem;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
        }
        .dash-cal-today-label {
            font-size: .7rem;
            color: var(--text-subtle);
        }
        .dash-cal-today-date {
            font-size: .8rem;
            font-weight: 700;
            color: var(--text);
        }

        /* Skeleton shimmer */
        .skeleton {
            position: relative;
            overflow: hidden;
            background: var(--border);
        }
        .skeleton::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent 0%, var(--border-strong) 50%, transparent 100%);
            animation: shimmer 1.35s infinite;
        }
        @keyframes shimmer {
            0%   { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
    </style>
    @endpush

    <div class="space-y-6">

    {{-- Top section: calendar left + header/KPIs right --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-[240px_1fr]">

        {{-- ── Mini Calendar ── --}}
        <div class="dash-cal"
             x-data="dashCal('{{ $calLocale }}', {{ json_encode($calToday) }})"
             x-init="init()">
            <div class="dash-cal-head">
                <div>
                    <div class="dash-cal-month" x-text="monthLabel"></div>
                    <div class="dash-cal-year" x-text="yearLabel"></div>
                </div>
                <div class="dash-cal-nav">
                    <button @click="prev()" type="button" aria-label="Previous month">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button @click="goToday()" type="button" aria-label="Today"
                            style="font-size:.55rem;font-weight:700;width:auto;padding:0 .35rem;">
                        <span x-text="isAmharic ? 'ዛሬ' : 'Today'"></span>
                    </button>
                    <button @click="next()" type="button" aria-label="Next month">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <div class="dash-cal-grid">
                <template x-for="h in dowHeaders" :key="'h'+h">
                    <div class="dash-cal-dow" x-text="h"></div>
                </template>
                <template x-for="cell in cells" :key="cell.key">
                    <div class="dash-cal-day"
                         :class="{'other-month': !cell.cur, 'today': cell.today}"
                         x-text="cell.d"></div>
                </template>
            </div>

            <div class="dash-cal-footer">
                <svg class="w-3.5 h-3.5 shrink-0" style="color:rgb(var(--ac));" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="dash-cal-today-date" x-text="todayLabel"></span>
            </div>
        </div>

        {{-- ── Header + KPI cards ── --}}
        <div class="flex flex-col gap-4">

            @php
            $sysName    = $systemSettings?->app_name  ?? config('app.name', 'Court MS');
            $sysShort   = $systemSettings?->short_name ?: $sysName;
            $sysInitial = mb_strtoupper(mb_substr($sysName, 0, 1));
            $sysSub     = $systemSettings?->welcome_message ?: ($systemSettings?->address ?? null);
            @endphp
            <div class="enterprise-header" style="padding-bottom:0;border-bottom:none;margin-bottom:0;">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3 min-w-0">
                        @if($systemSettings?->logo_path)
                        <img src="{{ asset('storage/'.$systemSettings->logo_path) }}"
                             alt="{{ $sysName }}"
                             class="h-10 w-10 shrink-0 rounded-lg object-contain">
                        @else
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-white text-sm font-bold"
                             style="background:rgb(var(--ac));">
                            {{ $sysInitial }}
                        </div>
                        @endif
                        <div class="min-w-0">
                            <h1 class="enterprise-title truncate">{{ $sysName }}</h1>
                            @if($sysSub)
                            <p class="enterprise-subtitle truncate">{{ $sysSub }}</p>
                            @endif
                        </div>
                    </div>
                    <x-ui.badge type="info" class="admin-toolbar-chip self-start sm:self-auto shrink-0">{{ $sysShort }}</x-ui.badge>
                </div>
            </div>

            {{-- KPI cards --}}
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">

                <div class="dash-kpi">
                    <div class="flex items-start justify-between gap-2">
                        <div class="dash-kpi-icon" style="background:rgb(59 130 246/.12);color:rgb(59 130 246);">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18"/>
                            </svg>
                        </div>
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                              style="background:rgb(59 130 246/.12);color:rgb(59 130 246);">{{ __('dashboard.all_time') }}</span>
                    </div>
                    <p id="kpi-total-cases" class="dash-kpi-value">{{ number_format($totalCases) }}</p>
                    <p class="dash-kpi-label">{{ __('dashboard.total_cases') }}</p>
                </div>

                <div class="dash-kpi">
                    <div class="flex items-start justify-between gap-2">
                        <div class="dash-kpi-icon" style="background:rgb(249 115 22/.12);color:rgb(234 88 12);">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
                            </svg>
                        </div>
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                              style="background:rgb(249 115 22/.12);color:rgb(234 88 12);">{{ __('dashboard.pending') }}</span>
                    </div>
                    <p id="kpi-pending-cases" class="dash-kpi-value">{{ number_format($pendingCases) }}</p>
                    <p class="dash-kpi-label">{{ __('dashboard.pending') }}</p>
                </div>

                <div class="dash-kpi">
                    <div class="flex items-start justify-between gap-2">
                        <div class="dash-kpi-icon" style="background:rgb(6 182 212/.12);color:rgb(8 145 178);">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                              style="background:rgb(6 182 212/.12);color:rgb(8 145 178);">{{ __('dashboard.active') }}</span>
                    </div>
                    <p id="kpi-active-cases" class="dash-kpi-value">{{ number_format($activeCases) }}</p>
                    <p class="dash-kpi-label">{{ __('dashboard.active_cases_label') }}</p>
                </div>

                <div class="dash-kpi">
                    <div class="flex items-start justify-between gap-2">
                        <div class="dash-kpi-icon" style="background:rgb(16 185 129/.12);color:rgb(5 150 105);">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                              style="background:rgb(16 185 129/.12);color:rgb(5 150 105);">{{ __('dashboard.resolved') }}</span>
                    </div>
                    <p id="kpi-resolved-cases" class="dash-kpi-value">{{ number_format($resolvedCases) }}</p>
                    <p class="dash-kpi-label">{{ __('dashboard.resolved_cases_label') }}</p>
                </div>

            </div>
        </div>

    </div>{{-- /top section --}}

    {{-- Cases by member --}}
    <section class="dash-panel">
        <div class="dash-panel-header">
            <div>
                <h3 class="dash-panel-title">{{ __('dashboard.cases_by_member') }}</h3>
                <p class="dash-panel-sub">{{ __('dashboard.assigned_cases_by_member') }}</p>
            </div>
            <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold"
                  style="background:rgb(6 182 212/.1);color:rgb(8 145 178);border:1px solid rgb(6 182 212/.2);">
                {{ number_format($memberCaseCounts->sum('cases_count')) }}
            </span>
        </div>
        <div class="dash-panel-body">
            <div class="dash-member-strip">
                @forelse($memberCaseCounts as $member)
                @php
                $memberPercent = min(100, ((int) $member->cases_count / $maxMemberCases) * 100);
                $isActive = ($member->status ?? null) === 'active';
                @endphp
                <article class="dash-member-card">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            @if($member->avatar_path)
                            <img src="{{ asset('storage/'.$member->avatar_path) }}"
                                 alt="{{ $member->name }}"
                                 class="h-9 w-9 shrink-0 rounded-full object-cover ring-2 ring-[var(--surface-strong)] shadow-sm">
                            @else
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-bold ring-2 ring-[var(--surface-strong)] shadow-sm"
                                 style="background:rgb(var(--ac)/.15);color:rgb(var(--ac));">
                                {{ strtoupper(substr($member->name, 0, 1)) }}
                            </div>
                            @endif
                            <div class="min-w-0">
                                <p class="dash-member-name">{{ $member->name }}</p>
                                <p class="dash-member-team">{{ $member->team_name ?: __('dashboard.no_team') }}</p>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold
                            {{ $isActive
                                ? 'bg-emerald-100/60 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800'
                                : 'bg-[var(--surface-strong)] text-[var(--text-subtle)] border border-[var(--border)]' }}">
                            {{ ucfirst($member->status ?? 'inactive') }}
                        </span>
                    </div>

                    <div class="mt-3 flex items-end justify-between gap-3">
                        <div>
                            <p class="dash-member-count">{{ number_format((int) $member->cases_count) }}</p>
                            <p class="dash-member-count-label">{{ __('app.Cases') }}</p>
                        </div>
                    </div>

                    @if(($member->case_type_counts ?? collect())->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach($member->case_type_counts as $caseType)
                        @php
                            $caseTypeLabel = ($caseType['label'] ?? '') === '__unknown__'
                                ? __('dashboard.unknown_case_type')
                                : ($caseType['label'] ?? __('dashboard.unknown_case_type'));
                        @endphp
                        <span class="dash-case-chip">
                            <span class="max-w-[8rem] truncate">{{ $caseTypeLabel }}</span>
                            <span class="dash-case-chip-count">{{ number_format((int) ($caseType['count'] ?? 0)) }}</span>
                        </span>
                        @endforeach
                    </div>
                    @endif

                    <div class="dash-progress-track">
                        <div class="dash-progress-fill" style="width:{{ $memberPercent }}%"></div>
                    </div>
                </article>
                @empty
                <div class="dash-empty">{{ __('dashboard.no_member_case_counts') }}</div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Cases by team --}}
    <section class="dash-panel">
        <div class="dash-panel-header">
            <div>
                <h3 class="dash-panel-title">{{ __('dashboard.cases_by_team') }}</h3>
                <p class="dash-panel-sub">{{ __('dashboard.assigned_cases_by_team') }}</p>
            </div>
            <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold"
                  style="background:rgb(59 130 246/.1);color:rgb(59 130 246);border:1px solid rgb(59 130 246/.2);">
                {{ number_format($teamCaseCounts->sum('cases_count')) }}
            </span>
        </div>
        <div class="dash-panel-body">
            <ul class="dash-divide">
                @forelse($teamCaseCounts as $team)
                @php $teamPercent = min(100, ((int) $team->cases_count / $maxTeamCases) * 100); @endphp
                <li class="dash-workload-row">
                    <div class="min-w-0">
                        <p class="dash-workload-name">{{ $team->name }}</p>
                        <p class="dash-workload-sub">{{ __('dashboard.team') }}</p>
                        <div class="dash-progress-track">
                            <div class="dash-progress-fill" style="background:rgb(16 185 129);width:{{ $teamPercent }}%"></div>
                        </div>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-bold"
                          style="background:rgb(16 185 129/.12);color:rgb(5 150 105);border:1px solid rgb(16 185 129/.25);">
                        {{ number_format((int) $team->cases_count) }}
                    </span>
                </li>
                @empty
                <li class="py-8 text-center" style="color:var(--text-subtle);">{{ __('dashboard.no_team_case_counts') }}</li>
                @endforelse
            </ul>
        </div>
    </section>

    {{-- Recent cases + workload summary --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        <section class="dash-panel xl:col-span-2">
            <div class="dash-panel-header">
                <div>
                    <h3 class="dash-panel-title">{{ __('dashboard.recent_cases') }}</h3>
                    <p class="dash-panel-sub">{{ __('dashboard.recent_cases_description') }}</p>
                </div>
                <a href="{{ route('cases.index') }}"
                   style="font-size:.75rem;color:rgb(var(--ac));"
                   class="hover:underline">{{ __('dashboard.view_all') }}</a>
            </div>
            <div class="dash-panel-body">
                <ul class="dash-divide">
                    @forelse($recent->take(5) as $case)
                    @php $status = (string)($case->status ?? ''); @endphp
                    <li class="dash-case-row">
                        <div class="min-w-0">
                            <p class="dash-case-number">
                                <a href="{{ route('cases.show',$case->id) }}">{{ $case->case_number }}</a>
                            </p>
                            <p class="dash-case-title">{{ $case->title }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold capitalize {{ $statusChip($status) }}">
                            {{ __("cases.status.$status") }}
                        </span>
                    </li>
                    @empty
                    <li class="py-8 text-center" style="color:var(--text-subtle);">{{ __('dashboard.no_recent_cases') }}</li>
                    @endforelse
                </ul>
            </div>
        </section>

        <section class="dash-panel">
            <div class="dash-panel-header">
                <h3 class="dash-panel-title">{{ __('dashboard.case_workload') }}</h3>
            </div>
            <div class="dash-panel-body space-y-3">
                <div class="dash-stat-box">
                    <p class="dash-stat-box-label">{{ __('dashboard.cases_by_team') }}</p>
                    <p class="dash-stat-box-value">{{ number_format($teamCaseCounts->sum('cases_count')) }}</p>
                </div>
                <div class="dash-stat-box">
                    <p class="dash-stat-box-label">{{ __('dashboard.cases_by_member') }}</p>
                    <p class="dash-stat-box-value">{{ number_format($memberCaseCounts->sum('cases_count')) }}</p>
                </div>
            </div>
        </section>

    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">

        <section class="dash-panel xl:col-span-3">
            <div class="dash-panel-header">
                <div>
                    <h3 class="dash-panel-title">{{ __('dashboard.new_cases_per_month') }}</h3>
                    <p class="dash-panel-sub">{{ __('dashboard.case_activity') }}</p>
                </div>
                <span style="font-size:.7rem;color:var(--text-subtle);">{{ __('dashboard.last_6_months') }}</span>
            </div>
            <div class="dash-panel-body">
                <div class="dash-chart-frame {{ empty($values) ? 'skeleton rounded-lg' : '' }}">
                    <canvas id="casesChart"
                        data-labels='@json(array_values($labels))'
                        data-values='@json(array_values($values))'
                        data-dataset-label='{{ __("dashboard.new_cases") }}'
                        height="200"
                        aria-label="{{ __('dashboard.new_cases_per_month') }}"
                        role="img"></canvas>
                </div>
            </div>
        </section>

        <section class="dash-panel xl:col-span-2">
            <div class="dash-panel-header">
                <h3 class="dash-panel-title">{{ __('dashboard.applicants_by_gender') }}</h3>
                <span style="font-size:.7rem;color:var(--text-subtle);">{{ __('dashboard.last_30_days') }}</span>
            </div>
            <div class="dash-panel-body">
                <div class="dash-chart-frame mx-auto max-w-[360px] {{ empty($genderCounts) ? 'skeleton rounded-lg' : '' }}">
                    <canvas id="genderChart"
                        data-labels='@json(array_keys($genderCounts))'
                        data-values='@json(array_values($genderCounts))'
                        height="200"
                        aria-label="{{ __('dashboard.applicants_by_gender') }}"
                        role="img"></canvas>
                </div>
            </div>
        </section>

    </div>

    {{-- Cases by type --}}
    <section class="dash-panel">
        <div class="dash-panel-header">
            <div>
                <h3 class="dash-panel-title">{{ __('dashboard.cases_by_type') }}</h3>
                <p class="dash-panel-sub">{{ __('dashboard.case_type_distribution') }}</p>
            </div>
            <a href="{{ route('cases.index') }}"
               style="font-size:.72rem;color:rgb(var(--ac));" class="hover:underline">{{ __('dashboard.view_report') }}</a>
        </div>
        <div class="dash-panel-body">
            <div class="h-64 {{ empty($caseTypeCounts) ? 'skeleton rounded-lg' : '' }}">
                <canvas id="caseTypeChart"
                    data-labels='@json(array_keys($caseTypeCounts))'
                    data-values='@json(array_values($caseTypeCounts))'
                    height="90"
                    aria-label="{{ __('dashboard.cases_by_type') }}"
                    role="img"></canvas>
            </div>
        </div>
    </section>

    {{-- System health --}}
    <section>
        <div class="mb-3 flex items-center justify-between">
            <h3 style="font-size:.875rem;font-weight:700;color:var(--text);">{{ __('dashboard.service_health') }}</h3>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

            <div class="dash-health-card">
                <div class="flex items-center justify-between mb-1">
                    <h4 class="dash-health-title">{{ __('dashboard.system_uptime') }}</h4>
                    <svg class="h-4 w-4" style="color:rgb(16 185 129);" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="1.8" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="dash-health-sub">{{ __('dashboard.last_restart') }}: {{ \App\Support\EthiopianDate::format(now()->subHours(5), withTime: true) }}</p>
                <p style="font-size:1.5rem;font-weight:800;color:rgb(16 185 129);">99.9%</p>
            </div>

            <div class="dash-health-card">
                <div class="flex items-center justify-between mb-1">
                    <h4 class="dash-health-title">{{ __('dashboard.queue_status') }}</h4>
                    <svg class="h-4 w-4" style="color:rgb(59 130 246);" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="1.8" d="M3 7h18M3 12h12M3 17h6"/>
                    </svg>
                </div>
                <p class="dash-health-sub">{{ __('dashboard.jobs_waiting') }}</p>
                <p style="font-size:1.5rem;font-weight:800;color:rgb(59 130 246);">12</p>
            </div>

            <div class="dash-health-card">
                <div class="flex items-center justify-between mb-1">
                    <h4 class="dash-health-title">{{ __('dashboard.notifications_service') }}</h4>
                    <svg class="h-4 w-4" style="color:rgb(16 185 129);" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <p class="dash-health-sub">{{ __('dashboard.email_sms_gateways') }}</p>
                <x-ui.badge type="success" class="px-2.5 py-1 text-[12px] font-medium">
                    {{ __('dashboard.operational') }}
                </x-ui.badge>
            </div>

        </div>
    </section>

    </div>{{-- /space-y-6 --}}

    @push('scripts')
    <script>
    /* ── Mini calendar Alpine component ─────────────────────────────
       Supports two modes driven by $locale passed from PHP:
         'en'  → Gregorian calendar, English labels
         'am'  → Ethiopian (GC) calendar, Amharic labels
    ─────────────────────────────────────────────────────────────── */
    function dashCal(locale, phpToday) {
        // ── Ethiopian conversion helpers (mirrors EthiopianDate.php) ──
        const ET_EPOCH = 1724220.5; // JD of Meskerem 1, Year 1

        function gregToJD(y, m, d) {
            if (m < 3) { m += 12; y--; }
            const a = Math.floor(y / 100);
            const b = 2 - a + Math.floor(a / 4);
            return Math.floor(365.25 * (y + 4716)) + Math.floor(30.6001 * (m + 1)) + d + b - 1524.5;
        }

        function etToJD(ey, em, ed) {
            let yr = ey < 0 ? ey + 1 : ey;
            return ed + (em - 1) * 30 + (yr - 1) * 365 + Math.floor(yr / 4) + ET_EPOCH - 1;
        }

        function jdToGreg(jd) {
            const z = Math.floor(jd + 0.5);
            const a = Math.floor((z - 1867216.25) / 36524.25);
            const A = z + 1 + a - Math.floor(a / 4);
            const B = A + 1524;
            const C = Math.floor((B - 122.1) / 365.25);
            const D = Math.floor(365.25 * C);
            const E = Math.floor((B - D) / 30.6001);
            const day   = B - D - Math.floor(30.6001 * E);
            const month = E < 14 ? E - 1 : E - 13;
            const year  = month > 2 ? C - 4716 : C - 4715;
            return { y: year, m: month, d: day };
        }

        function gregToEt(y, m, d) {
            const jd = gregToJD(y, m, d);
            const c  = Math.floor(jd) + 0.5 - ET_EPOCH;
            let ey = Math.floor((c - Math.floor((c + 366) / 1461)) / 365) + 1;
            if (ey <= 0) ey--;
            const yearStart = etToJD(ey, 1, 1);
            const doy  = Math.floor(jd) + 0.5 - yearStart + 1;
            const em   = Math.floor((doy - 1) / 30) + 1;
            const ed   = Math.round(doy - (em - 1) * 30);
            return { y: ey, m: em, d: ed };
        }

        // First Gregorian day of an Ethiopian month (needed for day-of-week padding)
        function etMonthStart(ey, em) {
            const jd = etToJD(ey, em, 1);
            return jdToGreg(jd); // returns {y,m,d}
        }

        // Days in Ethiopian month (30 for months 1-12; 5 or 6 for Pagumē)
        function etMonthDays(ey, em) {
            if (em <= 12) return 30;
            // Pagumē: leap year in ET if ey % 4 === 3
            return (ey % 4 === 3) ? 6 : 5;
        }

        // Day-of-week (0=Sun) for any Gregorian date
        function gregDow(y, m, d) {
            return new Date(y, m - 1, d).getDay();
        }

        // ── Static label tables ──
        const GREG_MONTHS = ['January','February','March','April','May','June',
                             'July','August','September','October','November','December'];
        const ET_MONTHS   = ['መስከረም','ጥቅምት','ኅዳር','ታህሳስ','ጥር','የካቲት',
                             'መጋቢት','ሚያዝያ','ግንቦት','ሰኔ','ሐምሌ','ነሐሴ','ጳጉሜን'];
        const GREG_DOW    = ['Su','Mo','Tu','We','Th','Fr','Sa'];
        const ET_DOW      = ['እሑ','ሰኞ','ማክ','ረቡ','ሐሙ','አርብ','ቅዳ'];

        const isAmharic = locale === 'am';

        // Today in Gregorian
        const gt = phpToday; // {y, m, d}
        // Today in Ethiopian
        const et = gregToEt(gt.y, gt.m, gt.d);

        return {
            isAmharic,
            // current displayed month state
            year:  isAmharic ? et.y  : gt.y,
            month: isAmharic ? et.m  : gt.m,  // 1-based in both modes
            cells: [],
            dowHeaders: isAmharic ? ET_DOW : GREG_DOW,

            get monthLabel() {
                if (isAmharic) return ET_MONTHS[this.month - 1] ?? '';
                return GREG_MONTHS[this.month - 1] ?? '';
            },
            get yearLabel() {
                return isAmharic ? this.year + ' ዓ.ም' : String(this.year);
            },
            get todayLabel() {
                if (isAmharic) {
                    return ET_MONTHS[et.m - 1] + ' ' + et.d + '፣ ' + et.y + ' ዓ.ም';
                }
                return GREG_MONTHS[gt.m - 1] + ' ' + gt.d + ', ' + gt.y;
            },

            init() { this.build(); },

            build() {
                const cells = [];
                if (isAmharic) {
                    // Ethiopian grid
                    const days = etMonthDays(this.year, this.month);
                    // DOW of first day of this ET month
                    const firstGreg = etMonthStart(this.year, this.month);
                    const startDow  = gregDow(firstGreg.y, firstGreg.m, firstGreg.d);

                    // Prev-month padding (Pagumē or month 12)
                    const prevMonth = this.month === 1 ? 13 : this.month - 1;
                    const prevYear  = this.month === 1 ? this.year - 1 : this.year;
                    const prevDays  = etMonthDays(prevYear, prevMonth);
                    for (let i = startDow - 1; i >= 0; i--) {
                        cells.push({ d: prevDays - i, cur: false, today: false, key: 'p' + i });
                    }
                    // Current month days
                    for (let d = 1; d <= days; d++) {
                        const isToday = d === et.d && this.month === et.m && this.year === et.y;
                        cells.push({ d, cur: true, today: isToday, key: 'c' + d });
                    }
                } else {
                    // Gregorian grid
                    const startDow = new Date(this.year, this.month - 1, 1).getDay();
                    const days     = new Date(this.year, this.month, 0).getDate();
                    const prevDays = new Date(this.year, this.month - 1, 0).getDate();

                    for (let i = startDow - 1; i >= 0; i--) {
                        cells.push({ d: prevDays - i, cur: false, today: false, key: 'p' + i });
                    }
                    for (let d = 1; d <= days; d++) {
                        const isToday = d === gt.d && this.month === gt.m && this.year === gt.y;
                        cells.push({ d, cur: true, today: isToday, key: 'c' + d });
                    }
                }
                // Trailing filler
                const rem = cells.length % 7;
                if (rem !== 0) {
                    for (let i = 1; i <= 7 - rem; i++) {
                        cells.push({ d: i, cur: false, today: false, key: 'n' + i });
                    }
                }
                this.cells = cells;
            },

            prev() {
                if (isAmharic) {
                    if (this.month === 1) { this.month = 13; this.year--; }
                    else { this.month--; }
                } else {
                    if (this.month === 1) { this.month = 12; this.year--; }
                    else { this.month--; }
                }
                this.build();
            },
            next() {
                if (isAmharic) {
                    if (this.month === 13) { this.month = 1; this.year++; }
                    else { this.month++; }
                } else {
                    if (this.month === 12) { this.month = 1; this.year++; }
                    else { this.month++; }
                }
                this.build();
            },
            goToday() {
                this.year  = isAmharic ? et.y  : gt.y;
                this.month = isAmharic ? et.m  : gt.m;
                this.build();
            }
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') return;

        // Read computed CSS variable colours so charts match the active theme
        const style = getComputedStyle(document.documentElement);
        const acRaw  = style.getPropertyValue('--ac').trim() || '59 130 246';
        const acRgb  = `rgb(${acRaw})`;
        const acFill = `rgba(${acRaw.replace(/ /g,',')},0.15)`;

        const isDark = document.documentElement.classList.contains('dark');
        const tickColor   = isDark ? 'rgba(255,255,255,0.45)' : '#6b7280';
        const gridColor   = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(209,213,219,0.5)';
        const legendColor = isDark ? 'rgba(255,255,255,0.7)'  : '#374151';

        const parseJSON = (el, key, fb) => {
            try { return JSON.parse(el.dataset[key] || '') ?? fb; } catch { return fb; }
        };

        // ── Line chart ──
        const casesEl = document.getElementById('casesChart');
        if (casesEl) {
            const labels = parseJSON(casesEl, 'labels', []);
            const values = parseJSON(casesEl, 'values', []);
            if (labels.length && values.length) {
                casesEl._chartInstance = new Chart(casesEl.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: casesEl.dataset.datasetLabel || '',
                            data: values,
                            tension: 0.35,
                            fill: true,
                            borderColor: acRgb,
                            backgroundColor: acFill,
                            pointBackgroundColor: acRgb,
                            pointBorderColor: acRgb,
                            pointRadius: 3,
                            pointHoverRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { mode: 'index', intersect: false }
                        },
                        interaction: { mode: 'nearest', intersect: false },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: tickColor }
                            },
                            y: {
                                grid: { color: gridColor },
                                ticks: { color: tickColor, precision: 0 },
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }

        // ── Pie chart ──
        const genderEl = document.getElementById('genderChart');
        if (genderEl) {
            const gLabels = parseJSON(genderEl, 'labels', []);
            const gValues = parseJSON(genderEl, 'values', []);
            const palette = [acRgb, 'rgba(249,115,22,0.85)', 'rgba(16,185,129,0.85)', 'rgba(139,92,246,0.85)'];
            if (gLabels.length && gValues.length) {
                genderEl._chartInstance = new Chart(genderEl.getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: gLabels,
                        datasets: [{
                            data: gValues,
                            backgroundColor: gLabels.map((_, i) => palette[i % palette.length]),
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: legendColor, padding: 16, boxWidth: 12 }
                            }
                        }
                    }
                });
            }
        }

        // ── Bar chart ──
        const caseTypeEl = document.getElementById('caseTypeChart');
        if (caseTypeEl) {
            const cLabels = parseJSON(caseTypeEl, 'labels', []);
            const cValues = parseJSON(caseTypeEl, 'values', []);
            if (cLabels.length && cValues.length) {
                caseTypeEl._chartInstance = new Chart(caseTypeEl.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: cLabels,
                        datasets: [{
                            label: '{{ __("dashboard.cases_by_type") }}',
                            data: cValues,
                            backgroundColor: acFill,
                            borderColor: acRgb,
                            borderWidth: 1.5,
                            borderRadius: 6,
                            maxBarThickness: 36
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: tickColor }
                            },
                            y: {
                                grid: { color: gridColor },
                                ticks: { color: tickColor, precision: 0 },
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }
    });
    </script>
    @endpush
</x-admin-layout>
