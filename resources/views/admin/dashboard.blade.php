{{-- resources/views/admin/dashboard.blade.php --}}
<x-admin-layout title="{{ __('app.dashboard') }}">
    @section('page_header', __('app.dashboard'))

    @php
    // ---- Safe defaults to avoid "Undefined variable" ----
    $totalCases = $totalCases ?? 0;
    $pendingCases = $pendingCases ?? 0;
    $resolvedCases = $resolvedCases ?? 0; // will be overwritten with active decisions below
    $activeCases = $activeCases ?? 0;

    $recent = collect($recent ?? []);
    $recentUsers = collect($recentUsers ?? []);

    $labels = $labels ?? [];
    $values = $values ?? [];
    $genderCounts = $genderCounts ?? [];
    $caseTypeCounts = $caseTypeCounts ?? [];

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

    {{-- KPI cards (UPDATED: Clean, easy-to-read style) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">

        {{-- Total cases (Primary Blue) --}}
        {{-- Design: Light blue background, strong blue icon, dark text --}}
        <div class="p-5 rounded-xl bg-white border border-blue-100 shadow-md transition hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="p-3 rounded-full bg-blue-100/70 text-blue-600 shadow-sm">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18" />
                    </svg>
                </div>
            </div>
            <p id="kpi-total-cases" class="text-4xl font-extrabold text-blue-700 mt-4">{{ number_format($totalCases) }}</p>
            <h2 class="text-sm uppercase tracking-wider font-semibold text-gray-500 mt-1">{{ __('dashboard.total_cases') }}</h2>
        </div>

        {{-- Pending (Brand Orange - High Attention) --}}
        {{-- Design: Light orange background, strong orange icon, dark text --}}
        <div class="p-5 rounded-xl bg-white border border-orange-100 shadow-md transition hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="p-3 rounded-full bg-orange-100/70 text-orange-600 shadow-sm">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                    </svg>
                </div>
            </div>
            <p id="kpi-pending-cases" class="text-4xl font-extrabold mt-4 text-orange-700">{{ number_format($pendingCases) }}</p>
            <h2 class="text-sm uppercase tracking-wider font-semibold text-gray-500 mt-1">{{ __('dashboard.pending') }}</h2>
        </div>

        {{-- Active cases (Teal) --}}
        <div class="p-5 rounded-xl bg-white border border-cyan-100 shadow-md transition hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="p-3 rounded-full bg-cyan-100/70 text-cyan-600 shadow-sm">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>
            <p id="kpi-active-cases" class="text-4xl font-extrabold mt-4 text-cyan-700">{{ number_format($activeCases) }}</p>
            <h2 class="text-sm uppercase tracking-wider font-semibold text-gray-500 mt-1">Active cases</h2>
        </div>

        {{-- Resolved (Emerald Green - Success) --}}
        {{-- Design: Light green background, strong green icon, dark text --}}
        <div class="p-5 rounded-xl bg-white border border-emerald-100 shadow-md transition hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="p-3 rounded-full bg-emerald-100/70 text-emerald-600 shadow-sm">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>
            <p id="kpi-resolved-cases" class="text-4xl font-extrabold mt-4 text-emerald-700">{{ number_format($resolvedCases) }}</p>
            <h2 class="text-sm uppercase tracking-wider font-semibold text-gray-500 mt-1">{{ __('dashboard.resolved') }}</h2>
        </div>

    </div>

    {{-- Recent cases (Rest of the dashboard remains the same as previous update) --}}
    <div class="p-4 rounded-xl border border-gray-200 bg-white">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-gray-900">{{ __('dashboard.recent_cases') }}</h3>
            <a href="{{ route('cases.index') }}"
                class="text-xs text-blue-700 hover:text-blue-800 hover:underline">{{ __('dashboard.view_all') }}</a>
        </div>

        <ul class="divide-y divide-gray-200">
            @forelse($recent->take(3) as $case)
            @php $status = (string)($case->status ?? ''); @endphp
            <li class="py-3 flex items-center justify-between">
                <div class="min-w-0">
                    <div class="font-medium text-gray-900 truncate">
                        <a href="{{ route('cases.show',$case->id) }}" class="hover:underline">
                            {{ $case->case_number }}
                        </a>
                    </div>
                    <div class="text-[12px] text-gray-600 truncate">{{ $case->title }}</div>
                </div>
                <span class="px-2 py-0.5 rounded text-[11px] capitalize {{ $statusChip($status) }}">
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

    <div class="p-0 mt-6 flex flex-col xl:flex-row gap-6">
        {{-- Line chart --}}
        <div class="p-6 rounded-xl border border-gray-200 bg-white xl:w-[60%]">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('dashboard.new_cases_per_month') }}</h3>
                <span class="text-[11px] text-gray-500">{{ __('dashboard.last_6_months') }}</span>
            </div>
            <div class="{{ empty($values) ? 'skeleton h-40 rounded-lg' : '' }}">
                <canvas id="casesChart"
                    data-labels='@json(array_values($labels))'
                    data-values='@json(array_values($values))'
                    data-dataset-label='{{ __("dashboard.new_cases") }}'
                    height="200"
                    aria-label="{{ __('dashboard.new_cases_per_month') }}"
                    role="img"></canvas>
            </div>
        </div>

        {{-- Gender pie --}}
        <div class="p-6 rounded-xl border border-gray-200 bg-white xl:w-[40%]">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('dashboard.applicants_by_gender') }}</h3>
                <span class="text-[11px] text-gray-500">{{ __('dashboard.last_30_days') }}</span>
            </div>
            <div class="mx-auto max-w-[360px] {{ empty($genderCounts) ? 'skeleton h-40 rounded-lg' : '' }}">
                <canvas id="genderChart"
                    data-labels='@json(array_keys($genderCounts))'
                    data-values='@json(array_values($genderCounts))'
                    height="200"
                    aria-label="{{ __('dashboard.applicants_by_gender') }}"
                    role="img"></canvas>
            </div>
        </div>
    </div>

    {{-- Cases by type --}}
    <div class="mt-6">
        <div class="p-6 rounded-xl border border-gray-200 bg-white">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('dashboard.cases_by_type') }}</h3>
                <a href="{{ route('cases.index') }}"
                    class="text-[11px] text-blue-700 hover:text-blue-800 hover:underline">{{ __('dashboard.view_report') }}</a>
            </div>
            <div class="{{ empty($caseTypeCounts) ? 'skeleton h-44 rounded-lg' : '' }}">
                <canvas id="caseTypeChart"
                    data-labels='@json(array_keys($caseTypeCounts))'
                    data-values='@json(array_values($caseTypeCounts))'
                    height="90"
                    aria-label="{{ __('dashboard.cases_by_type') }}"
                    role="img"></canvas>
            </div>
        </div>
    </div>

    {{-- System health --}}
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 rounded-xl border border-gray-200 bg-white">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-semibold text-gray-900">{{ __('dashboard.system_uptime') }}</h4>
                <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="1.8" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="text-[12px] text-gray-500 mb-2">{{ __('dashboard.last_restart') }}: {{ \App\Support\EthiopianDate::format(now()->subHours(5), withTime: true) }}</p>
            <p class="text-2xl font-bold text-emerald-600">99.9%</p>
        </div>

        <div class="p-4 rounded-xl border border-gray-200 bg-white">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-semibold text-gray-900">{{ __('dashboard.queue_status') }}</h4>
                <svg class="h-4 w-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="1.8" d="M3 7h18M3 12h12M3 17h6" />
                </svg>
            </div>
            <p class="text-[12px] text-gray-500 mb-2">{{ __('dashboard.jobs_waiting') }}</p>
            <p class="text-2xl font-bold text-blue-600">12</p>
        </div>

        <div class="p-4 rounded-xl border border-gray-200 bg-white">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-semibold text-gray-900">{{ __('dashboard.notifications_service') }}</h4>
                <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <p class="text-[12px] text-gray-500 mb-2">{{ __('dashboard.email_sms_gateways') }}</p>
            <span class="inline-flex items-center px-2.5 py-1 text-[12px] font-medium rounded-full bg-emerald-50 text-emerald-700">
                {{ __('dashboard.operational') }}
            </span>
        </div>
    </div>

    {{-- Recent users --}}
    <div class="mt-6 p-4 rounded-xl border border-gray-200 bg-white">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">{{ __('dashboard.recent_users') }}</h3>
                <p class="text-[12px] text-gray-500">{{ __('dashboard.latest_staff_activity') }}</p>
            </div>
            <a href="{{ route('users.index') }}"
                class="text-[11px] text-blue-700 hover:text-blue-800 hover:underline">{{ __('dashboard.view_all') }}</a>
        </div>

        <ul class="divide-y divide-gray-100 mt-3">
            @forelse($recentUsers as $user)
            @php
            $name = $user->name ?? 'User';
            $initials = strtoupper(mb_substr($name, 0, 2));
            $status = $user->status ?? 'inactive';
            $avatar = !empty($user->avatar_path) ? asset('storage/'.$user->avatar_path) : null;
            @endphp
            <li class="py-3 flex items-center justify-between">
                <div class="flex items-center gap-3 min-w-0">
                    @if($avatar)
                    <img src="{{ $avatar }}" alt="{{ $name }}" class="w-10 h-10 rounded-full object-cover ring-2 ring-white">
                    @else
                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 font-semibold grid place-items-center ring-2 ring-white">
                        {{ $initials }}
                    </div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $name }}</p>
                        <p class="text-[12px] text-gray-500 truncate">{{ $user->email }}</p>
                    </div>
                </div>
                @php
                $statusBadge = $status === 'active'
                ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                : 'bg-gray-50 text-gray-700 border border-gray-200';
                @endphp
                <span class="text-[11px] px-2 py-0.5 rounded-full {{ $statusBadge }}">
                    {{ ucfirst($status) }}
                </span>
            </li>
            @empty
            <li class="py-8 text-center text-gray-500">{{ __('dashboard.no_recent_users') }}</li>
            @endforelse
        </ul>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
