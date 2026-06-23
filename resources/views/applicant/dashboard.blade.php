<x-applicant-layout title="{{ __('dashboard.my_dashboard') }}">
    @php
    $total = $total ?? 0;
    $pending = $pending ?? 0;
    $active = $active ?? 0;
    $closed = $closed ?? 0;
    $recent = $recent ?? collect();
    $lettersCount = $lettersCount ?? 0;
    $responsesCount = $responsesCount ?? 0;
    $decisionsCount = $decisionsCount ?? 0;
    $responseRepliesCount = $responseRepliesCount ?? 0;
    $caseLetters = $caseLetters ?? collect();
    $caseResponses = $caseResponses ?? collect();
    $caseDecisions = $caseDecisions ?? collect();
    $responseReplies = $responseReplies ?? collect();

    $pct = fn ($n) => $total > 0 ? round(($n / $total) * 100) : 0;
    @endphp

    <div class="space-y-6">
        {{-- Welcome Header --}}
        <div class="rounded-2xl p-5 sm:p-6 text-white shadow-lg" style="background: linear-gradient(135deg, rgb(var(--ac)), var(--primary-strong));">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    @php
                        $applicantUser = auth('applicant')->user();
                        $applicantName = $applicantUser?->full_name ?: $applicantUser?->name ?: $applicantUser?->email ?: __('applicant.user');
                        if ($applicantUser?->is_lawyer) {
                            $applicantName = __('dashboard.lawyer_title') . ' ' . $applicantName;
                        }
                    @endphp
                    <h4 class="text-xl sm:text-2xl font-bold truncate">{{ __('dashboard.welcome_back') }}, {{ $applicantName }}</h4>
                </div>
                <div class="shrink-0 rounded-xl bg-white/15 px-4 py-2 text-sm backdrop-blur-sm">
                    <div class="text-white/70 text-xs uppercase tracking-wide">{{ __('dashboard.today') }}</div>
                    <div class="font-semibold">{{ \App\Support\EthiopianDate::smartFormat(now(), false, '', 'h:i A', 'F j, Y') }}</div>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            {{-- Total Cases --}}
            <div class="dash-stat">
                <div class="flex items-center justify-between mb-3">
                    <div class="dash-stat-label">{{ __('dashboard.total_cases') }}</div>
                    <div class="dash-stat-icon" style="background: rgb(var(--ac) / 0.12); color: rgb(var(--ac));">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                </div>
                <div class="dash-stat-value">{{ $total }}</div>
            </div>

            {{-- Pending Cases --}}
            <div class="dash-stat">
                <div class="flex items-center justify-between mb-3">
                    <div class="dash-stat-label" style="color: var(--warning);">{{ __('dashboard.pending') }}</div>
                    <div class="dash-stat-icon" style="background: color-mix(in srgb, var(--warning) 14%, transparent); color: var(--warning);">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="dash-stat-value" style="color: var(--warning);">{{ $pending }}</div>
                <div class="mt-3 h-1.5 w-full rounded-full" style="background: color-mix(in srgb, var(--warning) 16%, transparent);">
                    <div class="h-1.5 rounded-full" style="width: {{ $pct($pending) }}%; background: var(--warning);"></div>
                </div>
            </div>

            {{-- Active Cases --}}
            <div class="dash-stat">
                <div class="flex items-center justify-between mb-3">
                    <div class="dash-stat-label" style="color: var(--success);">{{ __('dashboard.active') }}</div>
                    <div class="dash-stat-icon" style="background: color-mix(in srgb, var(--success) 14%, transparent); color: var(--success);">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="dash-stat-value" style="color: var(--success);">{{ $active }}</div>
                <div class="mt-3 h-1.5 w-full rounded-full" style="background: color-mix(in srgb, var(--success) 16%, transparent);">
                    <div class="h-1.5 rounded-full" style="width: {{ $pct($active) }}%; background: var(--success);"></div>
                </div>
            </div>

            {{-- Closed Cases --}}
            <div class="dash-stat">
                <div class="flex items-center justify-between mb-3">
                    <div class="dash-stat-label">{{ __('dashboard.closed') }}</div>
                    <div class="dash-stat-icon" style="background: var(--surface-soft); color: var(--text-subtle);">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
                <div class="dash-stat-value">{{ $closed }}</div>
                <div class="mt-3 h-1.5 w-full rounded-full" style="background: var(--surface-soft);">
                    <div class="h-1.5 rounded-full" style="width: {{ $pct($closed) }}%; background: var(--text-subtle);"></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[3fr_9fr] gap-6">
            {{-- Quick Actions --}}
            <div class="ui-card ui-card-body">
                <div class="flex items-center gap-3 mb-5">
                    <div class="dash-stat-icon" style="background: rgb(var(--ac) / 0.12); color: rgb(var(--ac));">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold" style="color: var(--text);">{{ __('dashboard.quick_actions') }}</h3>
                </div>

                <div class="space-y-2.5">
                    <a href="{{ route('applicant.cases.create') }}" class="dash-action dash-action-primary">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('dashboard.submit_new_case') }}</span>
                    </a>

                    <button type="button" data-panel-target="recent" class="dash-action panel-toggle active">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6a2 2 0 012 2v2h2a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2h2V7a2 2 0 012-2z"/>
                        </svg>
                        <span>{{ __('dashboard.recent_cases') }}</span>
                    </button>

                    <button type="button" data-panel-target="letters" class="dash-action panel-toggle">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 6 9-6m-18 0l9-6 9 6m-18 0v8a2 2 0 002 2h14a2 2 0 002-2V8"/>
                        </svg>
                        <span>{{ __('dashboard.letters') }}</span>
                        <span class="dash-action-count">{{ $lettersCount }}</span>
                    </button>

                    <button type="button" data-panel-target="responses" class="dash-action panel-toggle">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h5m-5 4h4M5 20l2.586-2.586A2 2 0 018.828 17H19a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v13z"/>
                        </svg>
                        <span>{{ __('dashboard.responses') }}</span>
                        <span class="dash-action-count">{{ $responsesCount }}</span>
                    </button>

                    <button type="button" data-panel-target="response-replies" class="dash-action panel-toggle">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8M8 14h5M6 4h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2z"/>
                        </svg>
                        <span>{{ __('respondent.response_of_response') }}</span>
                        <span class="dash-action-count">{{ $responseRepliesCount }}</span>
                    </button>

                    <button type="button" data-panel-target="decisions" class="dash-action panel-toggle">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7a2 2 0 10-4 0 2 2 0 004 0zm16 0a2 2 0 10-4 0 2 2 0 004 0zM2 7h20M6 7l4 10m-8 0h8m4-10l4 10m-8 0h8M12 4v2"/>
                        </svg>
                        <span>{{ __('dashboard.decisions') }}</span>
                        <span class="dash-action-count">{{ $decisionsCount }}</span>
                    </button>
                </div>
            </div>

            {{-- Panel Area --}}
            <div class="ui-card ui-card-body">
                {{-- Recent Cases Panel --}}
                <div data-panel="recent">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="dash-stat-icon" style="background: var(--surface-soft); color: var(--text-muted);">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold" style="color: var(--text);">{{ __('dashboard.recent_cases') }}</h3>
                        </div>
                        <a href="{{ route('applicant.cases.index') }}" class="text-sm font-medium inline-flex items-center gap-1" style="color: rgb(var(--ac));">
                            {{ __('dashboard.view_all') }}
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    @if($recent->isEmpty())
                        <div class="dash-empty py-12">
                            <svg class="h-12 w-12 mx-auto opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h4 class="mt-4 text-lg font-medium" style="color: var(--text);">{{ __('dashboard.no_cases_yet') }}</h4>
                            <p class="mt-2 max-w-sm mx-auto" style="color: var(--text-subtle);">{{ __('dashboard.no_cases_description') }}</p>
                            <a href="{{ route('applicant.cases.create') }}" class="mt-6 inline-flex items-center gap-2 px-5 py-2.5 text-white font-medium rounded-lg transition-colors" style="background: var(--warning);">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('dashboard.submit_first_case') }}
                            </a>
                        </div>
                    @else
                        {{-- Desktop Table --}}
                        <div class="hidden lg:block overflow-hidden rounded-xl border" style="border-color: var(--border);">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead style="background: var(--surface-soft);">
                                        <tr style="color: var(--text-subtle);">
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">{{ __('dashboard.case_number') }}</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">{{ __('dashboard.type') }}</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">{{ __('dashboard.status') }}</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">{{ __('dashboard.created') }}</th>
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">{{ __('dashboard.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y" style="--tw-divide-opacity: 1;">
                                        @foreach($recent->take(3) as $c)
                                        <tr class="border-t transition-colors" style="border-color: var(--border);">
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="font-mono font-semibold" style="color: rgb(var(--ac));">{{ $c->case_number }}</span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium" style="background: rgb(var(--ac) / 0.10); color: rgb(var(--ac));">
                                                    {{ $c->case_type }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                @php
                                                    $st = $c->status;
                                                    $stColor = $st === 'pending' ? 'var(--warning)' : ($st === 'active' ? 'var(--success)' : 'var(--text-subtle)');
                                                @endphp
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border"
                                                      style="background: color-mix(in srgb, {{ $stColor }} 12%, transparent); color: {{ $stColor }}; border-color: color-mix(in srgb, {{ $stColor }} 28%, transparent);">
                                                    @if($st==='pending')
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    @elseif($st==='active')
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                    @else
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    @endif
                                                    {{ __('cases.status.' . $c->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div style="color: var(--text-muted);">{{ \App\Support\EthiopianDate::format($c->created_at) }}</div>
                                                <div class="text-xs" style="color: var(--text-subtle);">{{ \App\Support\EthiopianDate::smartRelative($c->created_at) }}</div>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                                <a href="{{ route('applicant.cases.show', $c->id) }}"
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                                                   style="background: rgb(var(--ac) / 0.10); color: rgb(var(--ac));">
                                                    {{ __('dashboard.view') }}
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Mobile Cards View --}}
                        <div class="lg:hidden space-y-3">
                            @foreach($recent->take(3) as $c)
                            <div class="dash-list-item">
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="min-w-0">
                                        <span class="font-mono font-semibold" style="color: rgb(var(--ac));">{{ $c->case_number }}</span>
                                        <div class="mt-1">
                                            @php
                                                $stm = $c->status;
                                                $stmColor = $stm === 'pending' ? 'var(--warning)' : ($stm === 'active' ? 'var(--success)' : 'var(--text-subtle)');
                                            @endphp
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                  style="background: color-mix(in srgb, {{ $stmColor }} 12%, transparent); color: {{ $stmColor }};">
                                                {{ __('cases.status.' . $c->status) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-xs shrink-0" style="color: var(--text-subtle);">{{ \App\Support\EthiopianDate::smartRelative($c->created_at) }}</div>
                                </div>
                                <h4 class="font-medium mb-3" style="color: var(--text);">{{ $c->title }}</h4>
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs" style="background: rgb(var(--ac) / 0.10); color: rgb(var(--ac));">
                                        {{ $c->case_type }}
                                    </span>
                                    <a href="{{ route('applicant.cases.show', $c->id) }}" class="text-sm font-medium inline-flex items-center gap-1" style="color: rgb(var(--ac));">
                                        {{ __('dashboard.view') }}
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Letters Panel --}}
                <div class="hidden" data-panel="letters">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="dash-stat-icon" style="background: rgb(var(--ac) / 0.12); color: rgb(var(--ac));">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 6 9-6M4 6h16a1 1 0 011 1v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a1 1 0 011-1z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold" style="color: var(--text);">{{ __('dashboard.letters') }}</h3>
                                <p class="text-xs" style="color: var(--text-subtle);">{{ __('dashboard.letters_description') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('applicant.response-replies.index') }}" class="text-sm font-medium" style="color: rgb(var(--ac));">
                                {{ __('respondent.response_of_response') }}
                            </a>
                            <button type="button" data-panel-target="recent" class="text-sm font-medium" style="color: rgb(var(--ac));">
                                {{ __('dashboard.recent_cases') }}
                            </button>
                        </div>
                    </div>
                    <div class="space-y-2.5">
                        @forelse($caseLetters as $letter)
                        <div class="dash-list-item">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-semibold truncate" style="color: var(--text);">{{ $letter->subject ?? $letter->template_title ?? __('dashboard.letters') }}</div>
                                <div class="text-xs whitespace-nowrap" style="color: var(--text-subtle);">{{ \App\Support\EthiopianDate::format($letter->created_at) }}</div>
                            </div>
                            <div class="text-xs mt-1 flex flex-wrap gap-3" style="color: var(--text-subtle);">
                                <span>{{ __('dashboard.case_number') }}: {{ $letter->case_number ?? '--' }}</span>
                                @if(!empty($letter->reference_number))
                                <span>{{ __('dashboard.reference_number') }}: {{ $letter->reference_number }}</span>
                                @endif
                            </div>
                            <div class="mt-2">
                                <a href="{{ route('letters.case-preview', $letter->id) }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1 text-sm font-semibold" style="color: rgb(var(--ac));">
                                    {{ __('dashboard.view') }}
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7m7-7H3"/></svg>
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="dash-empty">{{ __('dashboard.no_letters') }}</div>
                        @endforelse
                    </div>
                </div>

                {{-- Responses Panel --}}
                <div class="hidden" data-panel="responses">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="dash-stat-icon" style="background: color-mix(in srgb, var(--success) 14%, transparent); color: var(--success);">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h5m-5 4h4M5 20l2.586-2.586A2 2 0 018.828 17H19a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v13z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold" style="color: var(--text);">{{ __('dashboard.responses') }}</h3>
                                <p class="text-xs" style="color: var(--text-subtle);">{{ __('dashboard.responses_description') }}</p>
                            </div>
                        </div>
                        <button type="button" data-panel-target="recent" class="text-sm font-medium" style="color: rgb(var(--ac));">
                            {{ __('dashboard.recent_cases') }}
                        </button>
                    </div>
                    <div class="space-y-2.5">
                        @forelse($caseResponses as $response)
                        <div class="dash-list-item">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-semibold truncate" style="color: var(--text);">{{ $response->title }}</div>
                                <div class="text-xs whitespace-nowrap" style="color: var(--text-subtle);">
                                    {{ \App\Support\EthiopianDate::smartRelative($response->created_at, '') }}
                                </div>
                            </div>
                            <div class="text-xs mt-1" style="color: var(--text-subtle);">
                                {{ __('dashboard.case_number') }}: {{ $response->case_number ?? '--' }}
                            </div>
                            <div class="mt-3 flex justify-end">
                                @if(!empty($response->case_id))
                                    <a href="{{ route('applicant.cases.respondentResponses.show', [$response->case_id, $response->id]) }}"
                                       class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-sm font-medium transition-colors"
                                       style="border-color: color-mix(in srgb, var(--success) 30%, transparent); color: var(--success); background: var(--surface-strong);">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                        {{ __('dashboard.view') }}
                                    </a>
                                @else
                                    <button type="button" disabled
                                       class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-sm font-medium cursor-not-allowed"
                                       style="border-color: var(--border); color: var(--text-subtle); background: var(--surface-strong);">
                                        {{ __('dashboard.view') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="dash-empty">{{ __('dashboard.no_responses') }}</div>
                        @endforelse
                    </div>
                </div>

                {{-- Response Of Response Panel --}}
                <div class="hidden" data-panel="response-replies">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="dash-stat-icon" style="background: color-mix(in srgb, var(--primary) 14%, transparent); color: rgb(var(--ac));">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8M8 14h5M6 4h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold" style="color: var(--text);">{{ __('respondent.response_of_response') }}</h3>
                                <p class="text-xs" style="color: var(--text-subtle);">{{ __('respondent.applicant_response_replies') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('applicant.response-replies.index') }}" class="text-sm font-medium" style="color: rgb(var(--ac));">
                            {{ __('dashboard.view_all') }}
                        </a>
                    </div>
                    <div class="space-y-2.5">
                        @forelse($responseReplies as $reply)
                        @php
                            $replyStatus = (string) ($reply->review_status ?? 'awaiting_review');
                            $replyStatusColor = match ($replyStatus) {
                                'accepted' => 'var(--success)',
                                'returned' => 'var(--warning)',
                                'rejected' => 'var(--danger)',
                                default => 'var(--warning)',
                            };
                            $replyStatusLabel = match ($replyStatus) {
                                'accepted' => __('cases.review_status.accepted'),
                                'returned' => __('cases.review_status.returned'),
                                'rejected' => __('cases.review_status.rejected'),
                                default => __('cases.review_status.awaiting_review'),
                            };
                        @endphp
                        <div class="dash-list-item">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-semibold truncate" style="color: var(--text);">{{ $reply->case_number }}</div>
                                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold"
                                      style="background: color-mix(in srgb, {{ $replyStatusColor }} 12%, transparent); color: {{ $replyStatusColor }}; border-color: color-mix(in srgb, {{ $replyStatusColor }} 28%, transparent);">{{ $replyStatusLabel }}</span>
                            </div>
                            <div class="text-xs mt-1 flex flex-wrap gap-3" style="color: var(--text-subtle);">
                                <span>{{ __('respondent.response_number_label') }}: {{ $reply->response_number ?: __('cases.labels.not_available') }}</span>
                                <span>{{ $reply->created_at ? \App\Support\EthiopianDate::format($reply->created_at, withTime: true) : '' }}</span>
                            </div>
                            @if(!empty($reply->review_note))
                            <div class="mt-2 text-xs" style="color: var(--text-muted);">
                                <span class="font-semibold" style="color: var(--text);">{{ __('cases.reviewer_note') }}</span>
                                {{ $reply->review_note }}
                            </div>
                            @endif
                            <div class="mt-3 flex justify-end">
                                <a href="{{ route('applicant.cases.respondentResponses.replies.show', [$reply->case_id, $reply->respondent_response_id, $reply->id]) }}"
                                    class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-sm font-medium transition-colors"
                                    style="border-color: rgb(var(--ac) / 0.30); color: rgb(var(--ac)); background: var(--surface-strong);">
                                    {{ __('dashboard.view') }}
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="dash-empty">{{ __('respondent.no_applicant_response_replies') }}</div>
                        @endforelse
                    </div>
                </div>

                {{-- Decisions Panel --}}
                <div class="hidden" data-panel="decisions">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="dash-stat-icon" style="background: var(--surface-soft); color: var(--text-muted);">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7a2 2 0 10-4 0 2 2 0 004 0zm16 0a2 2 0 10-4 0 2 2 0 004 0zM2 7h20M6 7l4 10m-8 0h8m4-10l4 10m-8 0h8M12 4v2"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold" style="color: var(--text);">{{ __('dashboard.decisions') }}</h3>
                                <p class="text-xs" style="color: var(--text-subtle);">{{ __('dashboard.decisions_description') }}</p>
                            </div>
                        </div>
                        <button type="button" data-panel-target="recent" class="text-sm font-medium" style="color: rgb(var(--ac));">
                            {{ __('dashboard.recent_cases') }}
                        </button>
                    </div>
                    <div class="space-y-2.5">
                        @forelse($caseDecisions as $decision)
                        <div class="dash-list-item">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-semibold truncate" style="color: var(--text);">{{ $decision->name ?? __('dashboard.decisions') }}</div>
                                <div class="text-xs whitespace-nowrap" style="color: var(--text-subtle);">
                                    @php
                                        $decisionDate = $decision->decision_date ?: $decision->created_at;
                                    @endphp
                                    {{ $decisionDate ? \App\Support\EthiopianDate::format($decisionDate) : '' }}
                                </div>
                            </div>
                            <div class="text-xs mt-1 flex flex-wrap gap-3 items-center" style="color: var(--text-subtle);">
                                <span>{{ __('dashboard.case_number') }}: {{ $decision->case_number ?? '--' }}</span>
                                @if(!empty($decision->status))
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-semibold border" style="background: var(--surface-soft); color: var(--text-muted); border-color: var(--border);">
                                    {{ __('decisions.status.' . $decision->status) }}
                                </span>
                                @endif
                                @if(($decision->status ?? null) === 'published' && !empty($decision->approved_at))
                                <a href="{{ route('applicant.decisions.download', $decision->id) }}"
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-semibold border border-emerald-300 bg-emerald-50 text-emerald-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                                    </svg>
                                    {{ __('decisions.download') }}
                                </a>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="dash-empty">{{ __('dashboard.no_decisions') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Tips Section --}}
        <div class="ui-card ui-card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="dash-stat-icon" style="background: rgb(var(--ac) / 0.12); color: rgb(var(--ac));">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold" style="color: var(--text);">{{ __('dashboard.quick_tips') }}</h3>
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl p-4" style="background: var(--surface-soft); border: 1px solid var(--border);">
                    <div class="font-medium mb-1.5" style="color: var(--text);">{{ __('dashboard.tip1_title') }}</div>
                    <p class="text-sm" style="color: var(--text-muted);">{{ __('dashboard.tip1_description') }}</p>
                </div>
                <div class="rounded-xl p-4" style="background: var(--surface-soft); border: 1px solid var(--border);">
                    <div class="font-medium mb-1.5" style="color: var(--text);">{{ __('dashboard.tip2_title') }}</div>
                    <p class="text-sm" style="color: var(--text-muted);">{{ __('dashboard.tip2_description') }}</p>
                </div>
                <div class="rounded-xl p-4" style="background: var(--surface-soft); border: 1px solid var(--border);">
                    <div class="font-medium mb-1.5" style="color: var(--text);">{{ __('dashboard.tip3_title') }}</div>
                    <p class="text-sm" style="color: var(--text-muted);">{{ __('dashboard.tip3_description') }}</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const buttons = document.querySelectorAll('[data-panel-target]');
            const panels = document.querySelectorAll('[data-panel]');
            const toggles = document.querySelectorAll('.panel-toggle');

            const setPanel = (name) => {
                panels.forEach(panel => {
                    panel.classList.toggle('hidden', panel.dataset.panel !== name);
                });
                toggles.forEach(btn => {
                    btn.classList.toggle('dash-action-active', btn.dataset.panelTarget === name);
                });
            };

            buttons.forEach(btn => {
                btn.addEventListener('click', () => setPanel(btn.dataset.panelTarget));
            });

            setPanel('recent');
        });
    </script>
</x-applicant-layout>
