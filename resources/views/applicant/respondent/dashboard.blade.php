<x-applicant-layout :title="__('respondent.dashboard')" :as-respondent-nav="true">
    <div class="w-full max-w-[1800px] mx-auto space-y-6">
        {{-- Header --}}
        <div class="rounded-2xl p-5 sm:p-6 text-white shadow-lg" style="background: linear-gradient(135deg, rgb(var(--ac)), var(--primary-strong));">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl font-bold">{{ __('respondent.dashboard_title') }}</h1>
                    <p class="text-sm text-white/80 mt-1">{{ __('respondent.welcome_back') }}</p>
                </div>
                <div class="grid grid-cols-3 gap-2 sm:gap-3 lg:w-2/5">
                    <div class="rounded-xl bg-white/15 px-3 py-2.5 backdrop-blur-sm">
                        <div class="text-[11px] uppercase tracking-wide text-white/70">{{ __('respondent.my_cases') }}</div>
                        <div class="mt-1 text-xl font-bold">{{ $stats['cases'] ?? 0 }}</div>
                    </div>
                    <div class="rounded-xl bg-white/15 px-3 py-2.5 backdrop-blur-sm">
                        <div class="text-[11px] uppercase tracking-wide text-white/70">{{ __('respondent.my_responses') }}</div>
                        <div class="mt-1 text-xl font-bold">{{ $stats['responses'] ?? 0 }}</div>
                    </div>
                    <div class="rounded-xl bg-white/15 px-3 py-2.5 backdrop-blur-sm">
                        <div class="text-[11px] uppercase tracking-wide text-white/70">{{ __('respondent.notifications') }}</div>
                        <div class="mt-1 text-xl font-bold">{{ $stats['notifications'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[3fr_9fr]">
            {{-- Quick actions --}}
            <div class="ui-card ui-card-body">
                <div class="flex items-center gap-3 mb-5">
                    <div class="dash-stat-icon" style="background: rgb(var(--ac) / 0.12); color: rgb(var(--ac));">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-[11px] font-semibold uppercase tracking-wider" style="color: var(--text-subtle);">{{ __('respondent.quick_actions') }}</div>
                        <h2 class="text-base font-semibold" style="color: var(--text);">{{ __('respondent.dashboard') }}</h2>
                    </div>
                </div>

                <div class="space-y-2.5">
                    {{-- Find Case (search) --}}
                    <a href="{{ route('respondent.case.search') }}" class="dash-action dash-action-primary">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5a6 6 0 110 12 6 6 0 010-12zm5 11 5 5"/>
                        </svg>
                        <span>{{ __('respondent.find_case') }}</span>
                    </a>
                    {{-- My Responses (reply bubble) --}}
                    <a href="{{ route('respondent.responses.index') }}" class="dash-action">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11a4 4 0 014 4v0a4 4 0 01-4 4H7l-4 3V10zm0 0l4-4"/>
                        </svg>
                        <span>{{ __('respondent.my_responses') }}</span>
                    </a>
                    {{-- My Cases (briefcase) --}}
                    <a href="{{ route('respondent.cases.my') }}" class="dash-action">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7h-3V5a2 2 0 00-2-2H9a2 2 0 00-2 2v2H4a2 2 0 00-2 2v9a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zM9 5h6v2H9V5z"/>
                        </svg>
                        <span>{{ __('respondent.my_cases') }}</span>
                    </a>
                </div>

                <div class="space-y-2.5 pt-3 mt-3 border-t" style="border-color: var(--border);">
                    <button type="button" data-panel-target="recent-cases" class="dash-action panel-toggle">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l2.5 2.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ __('respondent.recent_cases') }}</span>
                    </button>
                    <button type="button" data-panel-target="letters" class="dash-action panel-toggle">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 6 9-6m-18 0l9-6 9 6m-18 0v8a2 2 0 002 2h14a2 2 0 002-2V8"/>
                        </svg>
                        <span>{{ __('respondent.letters') }}</span>
                    </button>
                    <button type="button" data-panel-target="response-replies" class="dash-action panel-toggle">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8M8 14h5M6 4h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2z"/>
                        </svg>
                        <span>{{ __('respondent.response_of_response') }}</span>
                        <span class="dash-action-count">{{ $stats['response_replies'] ?? 0 }}</span>
                    </button>
                </div>
            </div>

            <div class="space-y-5">
                {{-- Recent cases panel --}}
                <div data-panel="recent-cases" class="ui-card ui-card-body">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="text-[11px] font-semibold uppercase tracking-wider" style="color: var(--text-subtle);">{{ __('respondent.my_cases') }}</div>
                            <h2 class="text-base font-semibold" style="color: var(--text);">{{ __('respondent.case_found') }}</h2>
                        </div>
                        <a href="{{ route('respondent.cases.my') }}" class="text-sm font-medium" style="color: rgb(var(--ac));">
                            {{ __('respondent.view_case_details') }}
                        </a>
                    </div>
                    @if(!empty($recentCases) && count($recentCases))
                    <div class="space-y-2.5">
                        @foreach($recentCases as $case)
                        <div class="dash-list-item flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold" style="color: var(--text);">{{ $case->case_number ?? '-' }}</div>
                                <div class="text-xs truncate" style="color: var(--text-muted);">{{ $case->title ?? '-' }}</div>
                                <div class="text-[11px] mt-1" style="color: var(--text-subtle);">
                                    {{ __('respondent.status_label') }}: {{ $case->status ?? '-' }}
                                </div>
                            </div>
                            <a href="{{ route('respondent.cases.show', $case->case_number) }}"
                                class="text-sm font-medium shrink-0" style="color: rgb(var(--ac));">
                                {{ __('respondent.view_case_details') }}
                            </a>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="dash-empty">{{ __('respondent.no_notifications') }}</div>
                    @endif
                </div>

                {{-- Letters panel --}}
                <div data-panel="letters" class="hidden ui-card ui-card-body">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="text-[11px] font-semibold uppercase tracking-wider" style="color: var(--text-subtle);">{{ __('respondent.letters') }}</div>
                            <h2 class="text-base font-semibold" style="color: var(--text);">{{ __('respondent.recent_approved_letters') }}</h2>
                        </div>
                        <button type="button" data-panel-target="recent-cases" class="text-sm font-medium panel-toggle" style="color: rgb(var(--ac));">
                            {{ __('respondent.view_case_details') }}
                        </button>
                    </div>

                    @if(!empty($letters) && count($letters))
                    <div class="space-y-2.5">
                        @foreach($letters as $letter)
                        <div class="dash-list-item flex items-start justify-between gap-3">
                            <div class="space-y-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <div class="text-sm font-semibold" style="color: var(--text);">
                                        {{ $letter->subject ?? ($letter->template_title ?? __('respondent.letter')) }}
                                    </div>
                                    <span class="text-[11px] px-2 py-0.5 rounded-full border" style="background: color-mix(in srgb, var(--success) 14%, transparent); color: var(--success); border-color: color-mix(in srgb, var(--success) 28%, transparent);">
                                        {{ __('respondent.approved') }}
                                    </span>
                                </div>
                                <div class="text-xs flex flex-wrap gap-3" style="color: var(--text-muted);">
                                    <span>{{ __('respondent.case_label') }}: {{ $letter->case_number ?? '-' }}</span>
                                    <span>{{ __('respondent.reference_label') }}: {{ $letter->reference_number ?? '-' }}</span>
                                    <span>{{ __('respondent.template_label') }}: {{ $letter->template_title ?? '-' }}</span>
                                    <span>{{ __('respondent.author_label') }}: {{ $letter->author_name ?? '-' }}</span>
                                </div>
                                <div class="text-[11px]" style="color: var(--text-subtle);">
                                    {{ \App\Support\EthiopianDate::format($letter->created_at, withTime: true) }}
                                </div>
                            </div>
                            @php
                                try {
                                    $letterPreviewUrl = \Illuminate\Support\Facades\URL::signedRoute('letters.case-preview', ['letter' => $letter->id]);
                                } catch (\Throwable $e) {
                                    $letterPreviewUrl = url('/case-letters/' . $letter->id);
                                }
                            @endphp
                            <a href="{{ $letterPreviewUrl }}"
                                target="_blank" rel="noopener"
                                class="text-sm font-medium shrink-0" style="color: rgb(var(--ac));">
                                {{ __('respondent.view_details') }}
                            </a>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="dash-empty">{{ __('respondent.no_letters_available') }}</div>
                    @endif
                </div>

                {{-- Response of response panel --}}
                <div data-panel="response-replies" class="hidden ui-card ui-card-body">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="text-[11px] font-semibold uppercase tracking-wider" style="color: var(--text-subtle);">{{ __('respondent.response_of_response') }}</div>
                            <h2 class="text-base font-semibold" style="color: var(--text);">{{ __('respondent.applicant_response_replies') }}</h2>
                        </div>
                        <a href="{{ route('respondent.response-replies.index') }}" class="text-sm font-medium" style="color: rgb(var(--ac));">
                            {{ __('respondent.view_all') }}
                        </a>
                    </div>

                    @if(!empty($responseReplies) && count($responseReplies))
                    <div class="space-y-2.5">
                        @foreach($responseReplies as $reply)
                        @php
                            $applicantName = trim(($reply->applicant_first_name ?? '').' '.($reply->applicant_middle_name ?? '').' '.($reply->applicant_last_name ?? ''));
                        @endphp
                        <div class="dash-list-item flex items-start justify-between gap-3">
                            <div class="space-y-1 min-w-0">
                                <div class="text-sm font-semibold" style="color: var(--text);">{{ $reply->case_number ?? '-' }}</div>
                                <div class="text-xs" style="color: var(--text-muted);">
                                    {{ __('respondent.response_number_label') }}: {{ $reply->response_number ?: __('cases.labels.not_available') }}
                                </div>
                                <div class="text-xs" style="color: var(--text-subtle);">
                                    {{ __('cases.applicant_full_name') }}: {{ $applicantName ?: __('cases.labels.not_available') }}
                                </div>
                            </div>
                            <a href="{{ route('respondent.response-replies.show', $reply->id) }}"
                                class="text-sm font-medium shrink-0" style="color: rgb(var(--ac));">
                                {{ __('respondent.view_details') }}
                            </a>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="dash-empty">{{ __('respondent.no_applicant_response_replies') }}</div>
                    @endif
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const buttons = document.querySelectorAll('[data-panel-target]');
                const panels = document.querySelectorAll('[data-panel]');
                const toggles = document.querySelectorAll('.panel-toggle');
                if (!buttons.length || !panels.length) {
                    return;
                }
                const setPanel = (name) => {
                    panels.forEach(panel => panel.classList.toggle('hidden', panel.dataset.panel !== name));
                    toggles.forEach(btn => {
                        btn.classList.toggle('dash-action-active', btn.dataset.panelTarget === name);
                    });
                };
                buttons.forEach(btn => btn.addEventListener('click', () => setPanel(btn.dataset.panelTarget)));
                setPanel('recent-cases');
            });
        </script>
    </div>
</x-applicant-layout>
