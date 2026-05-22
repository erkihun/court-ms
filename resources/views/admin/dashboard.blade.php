{{-- resources/views/admin/dashboard.blade.php --}}
<x-admin-layout title="{{ __('app.Dashboard') }}">
    @section('page_header', __('app.Dashboard'))

    @php
    // ---- Safe defaults to avoid "Undefined variable" ----
    $totalCases = $totalCases ?? 0;
    $pendingCases = $pendingCases ?? 0;
    $resolvedCases = $resolvedCases ?? 0; // will be overwritten with active decisions below
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

    // Status chip helper (consistent across the app)
    $statusChip = function (string $s) {
    return match ($s) {
    'pending' => 'bg-orange-50 text-orange-800 border border-orange-200',
    'active' => 'bg-blue-50 text-blue-800 border border-blue-200',
    'closed', 'dismissed' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    default => 'bg-gray-50 text-gray-700 border border-gray-200',
    };
    };
    @endphp

    @php
    // Override resolvedCases to count cases that have an active decision.
    try {
    $resolvedCases = \App\Models\Decision::where('status', 'active')->distinct('court_case_id')->count('court_case_id');
    } catch (\Throwable $e) {
    // Fallback gracefully if DB not reachable in view context.
    }

    // Compute active cases (status = active) if not already provided.
    if (empty($activeCases)) {
    try {
    $activeCases = \App\Models\CourtCase::where('status', 'active')->count();
    } catch (\Throwable $e) {
    $activeCases = $activeCases ?? 0;
    }
    }
    @endphp

    @push('styles')
    <style>
        .dashboard-modern {
            --dash-border: rgba(226, 232, 240, 0.9);
            --dash-muted: #64748b;
            --dash-strong: #0f172a;
        }

        .dash-panel {
            border: 1px solid var(--dash-border);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
        }

        .dash-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border-bottom: 1px solid var(--dash-border);
            padding: 1rem 1.25rem;
        }

        .dash-panel-body {
            padding: 1.25rem;
        }

        .dash-kpi {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--dash-border);
            border-radius: 1rem;
            background: #fff;
            padding: 1.1rem;
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.055);
            transition: transform 160ms ease, border-color 160ms ease, box-shadow 160ms ease;
        }

        .dash-kpi:hover {
            transform: translateY(-2px);
            box-shadow: 0 22px 44px rgba(15, 23, 42, 0.08);
        }

        .dash-kpi-icon {
            display: grid;
            height: 2.75rem;
            width: 2.75rem;
            place-items: center;
            border-radius: 0.85rem;
        }

        .dash-action {
            border: 1px solid var(--dash-border);
            border-radius: 0.85rem;
            background: #f8fafc;
            padding: 0.85rem;
            transition: background-color 160ms ease, border-color 160ms ease, transform 160ms ease;
        }

        .dash-action:hover {
            transform: translateY(-1px);
            border-color: #bfdbfe;
            background: #eff6ff;
        }

        .dash-workload-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 1rem;
            align-items: center;
            padding: 0.85rem 0;
        }

        .dash-progress-track {
            margin-top: 0.5rem;
            height: 0.45rem;
            overflow: hidden;
            border-radius: 999px;
            background: #e2e8f0;
        }

        .dash-progress-fill {
            height: 100%;
            border-radius: inherit;
            background: #2563eb;
        }

        .dash-member-strip {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(14rem, 1fr));
            gap: 0.9rem;
        }

        .dash-member-card {
            min-width: 0;
            scroll-snap-align: start;
            border: 1px solid var(--dash-border);
            border-radius: 0.9rem;
            background: #f8fafc;
            padding: 0.9rem;
        }

        .dash-chart-frame {
            height: 18rem;
        }

        /* Tiny skeleton for first paint */
        .skeleton {
            position: relative;
            overflow: hidden;
            background: #F3F4F6;
        }

        .skeleton::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(243, 244, 246, 0) 0%, rgba(229, 231, 235, .9) 50%, rgba(243, 244, 246, 0) 100%);
            animation: shimmer 1.35s infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }
    </style>
    @endpush

    <div class="dashboard-modern space-y-6">
    <div class="enterprise-header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="enterprise-title">{{ __('app.Dashboard') }}</h1>
                <p class="enterprise-subtitle">{{ __('dashboard.operational_overview_subtitle') }}</p>
            </div>
            <x-ui.badge type="info" class="admin-toolbar-chip">{{ __('dashboard.court_operations') }}</x-ui.badge>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="dash-kpi">
            <div class="flex items-start justify-between gap-3">
                <div class="dash-kpi-icon bg-blue-50 text-blue-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18" />
                    </svg>
                </div>
                <span class="rounded-full bg-blue-50 px-2 py-1 text-[11px] font-semibold text-blue-700">{{ __('dashboard.all_time') }}</span>
            </div>
            <p id="kpi-total-cases" class="mt-5 text-4xl font-extrabold tracking-tight text-slate-950">{{ number_format($totalCases) }}</p>
            <h2 class="mt-1 text-sm font-semibold text-slate-500">{{ __('dashboard.total_cases') }}</h2>
        </div>

        <div class="dash-kpi">
            <div class="flex items-start justify-between gap-3">
                <div class="dash-kpi-icon bg-orange-50 text-orange-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                    </svg>
                </div>
                <span class="rounded-full bg-orange-50 px-2 py-1 text-[11px] font-semibold text-orange-700">{{ __('dashboard.pending') }}</span>
            </div>
            <p id="kpi-pending-cases" class="mt-5 text-4xl font-extrabold tracking-tight text-slate-950">{{ number_format($pendingCases) }}</p>
            <h2 class="mt-1 text-sm font-semibold text-slate-500">{{ __('dashboard.pending') }}</h2>
        </div>

        <div class="dash-kpi">
            <div class="flex items-start justify-between gap-3">
                <div class="dash-kpi-icon bg-cyan-50 text-cyan-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <span class="rounded-full bg-cyan-50 px-2 py-1 text-[11px] font-semibold text-cyan-700">{{ __('dashboard.active') }}</span>
            </div>
            <p id="kpi-active-cases" class="mt-5 text-4xl font-extrabold tracking-tight text-slate-950">{{ number_format($activeCases) }}</p>
            <h2 class="mt-1 text-sm font-semibold text-slate-500">{{ __('dashboard.active_cases_label') }}</h2>
        </div>

        <div class="dash-kpi">
            <div class="flex items-start justify-between gap-3">
                <div class="dash-kpi-icon bg-emerald-50 text-emerald-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <span class="rounded-full bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-700">{{ __('dashboard.resolved') }}</span>
            </div>
            <p id="kpi-resolved-cases" class="mt-5 text-4xl font-extrabold tracking-tight text-slate-950">{{ number_format($resolvedCases) }}</p>
            <h2 class="mt-1 text-sm font-semibold text-slate-500">{{ __('dashboard.resolved_cases_label') }}</h2>
        </div>
    </div>

    <section class="dash-panel">
        <div class="dash-panel-header">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">{{ __('dashboard.cases_by_member') }}</h3>
                <p class="text-[12px] text-gray-500">{{ __('dashboard.assigned_cases_by_member') }}</p>
            </div>
            <span class="rounded-full border border-cyan-100 bg-cyan-50 px-2.5 py-1 text-[11px] font-semibold text-cyan-700">
                {{ number_format($memberCaseCounts->sum('cases_count')) }}
            </span>
        </div>

        <div class="dash-panel-body">
            <div class="dash-member-strip">
                @forelse($memberCaseCounts as $member)
                @php
                $memberPercent = min(100, ((int) $member->cases_count / $maxMemberCases) * 100);
                $statusBadge = ($member->status ?? null) === 'active'
                ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                : 'bg-gray-50 text-gray-700 border border-gray-200';
                @endphp
                <article class="dash-member-card">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            @if($member->avatar_path)
                            <img src="{{ asset('storage/' . $member->avatar_path) }}"
                                 alt="{{ $member->name }}"
                                 class="h-9 w-9 shrink-0 rounded-full object-cover ring-2 ring-white shadow-sm">
                            @else
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-700 ring-2 ring-white shadow-sm">
                                {{ strtoupper(substr($member->name, 0, 1)) }}
                            </div>
                            @endif
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-gray-900">{{ $member->name }}</p>
                                <p class="truncate text-[12px] text-gray-500">{{ $member->team_name ?: __('dashboard.no_team') }}</p>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $statusBadge }}">
                            {{ ucfirst($member->status ?? 'inactive') }}
                        </span>
                    </div>

                    <div class="mt-3 flex items-end justify-between gap-3">
                        <div>
                            <p class="text-2xl font-extrabold text-slate-950">{{ number_format((int) $member->cases_count) }}</p>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">{{ __('app.Cases') }}</p>
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
                        <span class="inline-flex min-w-0 items-center gap-1 rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[11px] font-semibold text-slate-600">
                            <span class="max-w-[8rem] truncate">{{ $caseTypeLabel }}</span>
                            <span class="text-slate-900">{{ number_format((int) ($caseType['count'] ?? 0)) }}</span>
                        </span>
                        @endforeach
                    </div>
                    @endif

                    <div class="dash-progress-track">
                        <div class="dash-progress-fill" style="width: {{ $memberPercent }}%"></div>
                    </div>
                </article>
                @empty
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                    {{ __('dashboard.no_member_case_counts') }}
                </div>
                @endforelse
            </div>
        </div>
    </section>

    <section>
        <div class="dash-panel">
            <div class="dash-panel-header">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('dashboard.cases_by_team') }}</h3>
                    <p class="text-[12px] text-gray-500">{{ __('dashboard.assigned_cases_by_team') }}</p>
                </div>
                <span class="rounded-full border border-blue-100 bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700">
                    {{ number_format($teamCaseCounts->sum('cases_count')) }}
                </span>
            </div>

            <div class="dash-panel-body">
            <ul class="divide-y divide-gray-100">
                @forelse($teamCaseCounts as $team)
                @php $teamPercent = min(100, ((int) $team->cases_count / $maxTeamCases) * 100); @endphp
                <li class="dash-workload-row">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-gray-900">{{ $team->name }}</p>
                        <p class="text-[12px] text-gray-500">{{ __('dashboard.team') }}</p>
                        <div class="dash-progress-track">
                            <div class="dash-progress-fill bg-emerald-500" style="width: {{ $teamPercent }}%"></div>
                        </div>
                    </div>
                    <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                        {{ number_format((int) $team->cases_count) }}
                    </span>
                </li>
                @empty
                <li class="py-8 text-center text-gray-500">{{ __('dashboard.no_team_case_counts') }}</li>
                @endforelse
            </ul>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    {{-- Recent cases --}}
    <section class="dash-panel xl:col-span-2">
        <div class="dash-panel-header">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">{{ __('dashboard.recent_cases') }}</h3>
                <p class="mt-0.5 text-xs text-slate-500">{{ __('dashboard.recent_cases_description') }}</p>
            </div>
            <a href="{{ route('cases.index') }}"
                class="text-xs text-blue-700 hover:text-blue-800 hover:underline">{{ __('dashboard.view_all') }}</a>
        </div>

        <div class="dash-panel-body">
        <ul class="divide-y divide-gray-100">
            @forelse($recent->take(5) as $case)
            @php $status = (string)($case->status ?? ''); @endphp
            <li class="flex items-center justify-between gap-4 py-3">
                <div class="min-w-0">
                    <div class="truncate font-semibold text-gray-900">
                        <a href="{{ route('cases.show',$case->id) }}" class="hover:underline">
                            {{ $case->case_number }}
                        </a>
                    </div>
                    <div class="text-[12px] text-gray-600 truncate">{{ $case->title }}</div>
                </div>
                <span class="text-[11px] capitalize {{ $statusChip($status) }}">
                    {{ __("cases.status.$status") }}
                </span>
            </li>
            @empty
            <li class="py-8 text-center text-gray-500">
                {{ __('dashboard.no_recent_cases') }}
            </li>
            @endforelse
        </ul>
        </div>
    </section>

    <section class="dash-panel">
        <div class="dash-panel-header">
            <h3 class="text-sm font-semibold text-slate-900">{{ __('dashboard.case_workload') }}</h3>
        </div>
        <div class="dash-panel-body space-y-3">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <div class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ __('dashboard.cases_by_team') }}</div>
                <div class="mt-1 text-2xl font-extrabold text-slate-950">{{ number_format($teamCaseCounts->sum('cases_count')) }}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <div class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ __('dashboard.cases_by_member') }}</div>
                <div class="mt-1 text-2xl font-extrabold text-slate-950">{{ number_format($memberCaseCounts->sum('cases_count')) }}</div>
            </div>
        </div>
    </section>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">
        {{-- Line chart --}}
        <section class="dash-panel xl:col-span-3">
            <div class="dash-panel-header">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('dashboard.new_cases_per_month') }}</h3>
                    <p class="mt-0.5 text-xs text-slate-500">{{ __('dashboard.case_activity') }}</p>
                </div>
                <span class="text-[11px] text-gray-500">{{ __('dashboard.last_6_months') }}</span>
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

        {{-- Gender pie --}}
        <section class="dash-panel xl:col-span-2">
            <div class="dash-panel-header">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('dashboard.applicants_by_gender') }}</h3>
                <span class="text-[11px] text-gray-500">{{ __('dashboard.last_30_days') }}</span>
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
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('dashboard.cases_by_type') }}</h3>
                    <p class="mt-0.5 text-xs text-slate-500">{{ __('dashboard.case_type_distribution') }}</p>
                </div>
                <a href="{{ route('cases.index') }}"
                    class="text-[11px] text-blue-700 hover:text-blue-800 hover:underline">{{ __('dashboard.view_report') }}</a>
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
            <h3 class="text-sm font-semibold text-slate-900">{{ __('dashboard.service_health') }}</h3>
        </div>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="dash-panel p-4">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-semibold text-gray-900">{{ __('dashboard.system_uptime') }}</h4>
                <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="1.8" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="text-[12px] text-gray-500 mb-2">{{ __('dashboard.last_restart') }}: {{ \App\Support\EthiopianDate::format(now()->subHours(5), withTime: true) }}</p>
            <p class="text-2xl font-bold text-emerald-600">99.9%</p>
        </div>

        <div class="dash-panel p-4">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-semibold text-gray-900">{{ __('dashboard.queue_status') }}</h4>
                <svg class="h-4 w-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="1.8" d="M3 7h18M3 12h12M3 17h6" />
                </svg>
            </div>
            <p class="text-[12px] text-gray-500 mb-2">{{ __('dashboard.jobs_waiting') }}</p>
            <p class="text-2xl font-bold text-blue-600">12</p>
        </div>

        <div class="dash-panel p-4">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-semibold text-gray-900">{{ __('dashboard.notifications_service') }}</h4>
                <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <p class="text-[12px] text-gray-500 mb-2">{{ __('dashboard.email_sms_gateways') }}</p>
            <x-ui.badge type="success" class="px-2.5 py-1 text-[12px] font-medium">
                {{ __('dashboard.operational') }}
            </x-ui.badge>
        </div>
    </div>
    </section>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Chart === 'undefined') return;

            // Util: parse data safely
            const parseJSON = (el, key, fallback) => {
                try {
                    return JSON.parse(el.dataset[key] || '') ?? fallback;
                } catch (e) {
                    return fallback;
                }
            };

            // Cases line chart
            const casesEl = document.getElementById('casesChart');
            if (casesEl) {
                const labels = parseJSON(casesEl, 'labels', []);
                const values = parseJSON(casesEl, 'values', []);
                const datasetLabel = casesEl.dataset.datasetLabel || '';
                const lineColor = 'rgba(37, 99, 235, 1)'; // blue
                const fillColor = 'rgba(249, 115, 22, 0.16)'; // orange tint
                const pointColor = 'rgba(249, 115, 22, 0.9)'; // orange

                if (labels.length && values.length) {
                    const chart = new Chart(casesEl.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [{
                                label: datasetLabel,
                                data: values,
                                tension: 0.3,
                                fill: true,
                                borderColor: lineColor,
                                backgroundColor: fillColor,
                                pointBackgroundColor: pointColor,
                                pointBorderColor: pointColor,
                                pointHoverBackgroundColor: pointColor,
                                pointHoverBorderColor: pointColor,
                                pointRadius: 2,
                                pointHoverRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false
                                }
                            },
                            interaction: {
                                mode: 'nearest',
                                intersect: false
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        color: '#6b7280'
                                    }
                                },
                                y: {
                                    grid: {
                                        color: 'rgba(209, 213, 219, .5)'
                                    },
                                    ticks: {
                                        color: '#6b7280',
                                        precision: 0
                                    },
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                    casesEl._chartInstance = chart;
                }
            }

            // Gender pie
            const genderEl = document.getElementById('genderChart');
            if (genderEl) {
                const gLabels = parseJSON(genderEl, 'labels', []);
                const gValues = parseJSON(genderEl, 'values', []);
                const genderPalette = [
                    'rgba(37,99,235,0.85)', // blue
                    'rgba(249,115,22,0.85)' // orange
                ];

                if (gLabels.length && gValues.length) {
                    const chart = new Chart(genderEl.getContext('2d'), {
                        type: 'pie',
                        data: {
                            labels: gLabels,
                            datasets: [{
                                data: gValues,
                                backgroundColor: gLabels.map((_, idx) => genderPalette[idx % genderPalette.length]),
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: '#374151'
                                    }
                                }
                            }
                        }
                    });
                    genderEl._chartInstance = chart;
                }
            }

            // Case type bar
            const caseTypeEl = document.getElementById('caseTypeChart');
            if (caseTypeEl) {
                const cLabels = parseJSON(caseTypeEl, 'labels', []);
                const cValues = parseJSON(caseTypeEl, 'values', []);

                if (cLabels.length && cValues.length) {
                    const chart = new Chart(caseTypeEl.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: cLabels,
                            datasets: [{
                                label: '{{ __("dashboard.cases_by_type") }}',
                                data: cValues,
                                backgroundColor: 'rgba(249, 115, 22, 0.65)', // orange
                                borderColor: 'rgba(37, 99, 235, 1)', // blue
                                borderWidth: 1,
                                borderRadius: 6,
                                maxBarThickness: 36
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        color: '#374151'
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        color: '#374151',
                                        precision: 0
                                    }
                                }
                            }
                        }
                    });
                    caseTypeEl._chartInstance = chart;
                }
            }
        });
    </script>
    @endpush
</x-admin-layout>

