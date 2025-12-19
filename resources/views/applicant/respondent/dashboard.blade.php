<x-applicant-layout :title="__('respondent.dashboard')" :as-respondent-nav="true">
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900 mb-1">{{ __('respondent.dashboard_title') }}</h1>
                    <p class="text-sm text-slate-600">{{ __('respondent.welcome_back') }}</p>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 lg:w-2/5">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('respondent.my_cases') }}</div>
                        <div class="mt-1 text-xl font-bold text-slate-900">{{ $stats['cases'] ?? 0 }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('respondent.my_responses') }}</div>
                        <div class="mt-1 text-xl font-bold text-slate-900">{{ $stats['responses'] ?? 0 }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('respondent.notifications') }}</div>
                        <div class="mt-1 text-xl font-bold text-slate-900">{{ $stats['notifications'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>



        <div class="grid gap-5 lg:grid-cols-[3fr_9fr]">
            {{-- Quick actions --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-4">
                <div>
                    <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('respondent.quick_actions') ?? 'Quick actions' }}</div>
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('respondent.dashboard') }}</h2>
                </div>
                <div class="space-y-3">
                    <a href="{{ route('respondent.case.search') }}"
                        class="group flex items-center gap-3 px-4 py-3.5 rounded-xl bg-gradient-to-r from-[#0d3b8f] to-[#1b63c3] text-white font-medium
                               hover:from-[#0b306b] hover:to-[#1550a3] focus:outline-none focus:ring-2 focus:ring-[#0d3b8f] focus:ring-offset-2 transition-all duration-200 hover:shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ __('respondent.find_case') }}</span>
                    </a>
                    <a href="{{ route('respondent.responses.index') }}"
                        class="group flex items-center gap-3 px-4 py-3.5 rounded-xl bg-gradient-to-r from-[#0d3b8f] to-[#1b63c3] text-white font-medium
                               hover:from-[#0b306b] hover:to-[#1550a3] focus:outline-none focus:ring-2 focus:ring-[#0d3b8f] focus:ring-offset-2 transition-all duration-200 hover:shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h6m-6 4h12M5 4h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" />
                        </svg>
                        <span>{{ __('respondent.my_responses') }}</span>
                    </a>
                    <a href="{{ route('respondent.cases.my') }}"
                        class="group flex items-center gap-3 px-4 py-3.5 rounded-xl bg-gradient-to-r from-[#0d3b8f] to-[#1b63c3] text-white font-medium
                               hover:from-[#0b306b] hover:to-[#1550a3] focus:outline-none focus:ring-2 focus:ring-[#0d3b8f] focus:ring-offset-2 transition-all duration-200 hover:shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span>{{ __('respondent.my_cases') }}</span>
                    </a>
                </div>
                <div class="space-y-3 pt-2">
                    <button type="button" data-panel-target="recent-cases"
                        class="w-full group flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl bg-gradient-to-r from-[#0d3b8f] to-[#1b63c3] text-white font-medium panel-toggle
                               hover:from-[#0b306b] hover:to-[#1550a3] focus:outline-none focus:ring-2 focus:ring-[#0d3b8f] focus:ring-offset-2 transition-all duration-200">
                        <span>{{ __('respondent.recent_cases') ?? 'Recent cases' }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <button type="button" data-panel-target="letters"
                        class="w-full group flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl bg-gradient-to-r from-[#0d3b8f] to-[#1b63c3] text-white font-medium panel-toggle
                               hover:from-[#0b306b] hover:to-[#1550a3] focus:outline-none focus:ring-2 focus:ring-[#0d3b8f] focus:ring-offset-2 transition-all duration-200">
                        <span>{{ __('respondent.letters') ?? 'Letters' }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 6 9-6m-18 0l9-6 9 6m-18 0v8a2 2 0 002 2h14a2 2 0 002-2V8" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="space-y-5">
                <div data-panel="recent-cases" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('respondent.my_cases') }}</div>
                            <h2 class="text-lg font-semibold text-slate-900">{{ __('respondent.case_found') }}</h2>
                        </div>
                        <a href="{{ route('respondent.cases.my') }}" class="text-sm font-semibold text-blue-700 hover:underline">
                            {{ __('respondent.view_case_details') }}
                        </a>
                    </div>
                    @if(!empty($recentCases) && count($recentCases))
                    <div class="divide-y divide-slate-100">
                        @foreach($recentCases as $case)
                        <div class="py-3 flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">{{ $case->case_number ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $case->title ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400 mt-1">
                                    {{ __('respondent.status_label') }}: {{ $case->status ?? '-' }}
                                </div>
                            </div>
                            <a href="{{ route('respondent.cases.show', $case->case_number) }}"
                                class="text-sm font-semibold text-blue-700 hover:underline">
                                {{ __('respondent.view_case_details') }}
                            </a>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="rounded-lg border border-dashed border-slate-200 p-6 text-sm text-slate-500">
                        {{ __('respondent.no_notifications') }}
                    </div>
                    @endif
                </div>

                <div data-panel="letters" class="hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('respondent.letters') ?? 'Letters' }}</div>
                            <h2 class="text-lg font-semibold text-slate-900">Recent approved letters</h2>
                        </div>
                        <button type="button" data-panel-target="recent-cases" class="text-sm font-semibold text-blue-700 hover:underline panel-toggle">
                            {{ __('respondent.view_case_details') }}
                        </button>
                    </div>

                    @if(!empty($letters) && count($letters))
                    <div class="divide-y divide-slate-100">
                        @foreach($letters as $letter)
                        <div class="py-3 flex items-start justify-between gap-3">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <div class="text-sm font-semibold text-slate-900">
                                        {{ $letter->subject ?? ($letter->template_title ?? 'Letter') }}
                                    </div>
                                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Approved
                                    </span>
                                </div>
                                <div class="text-xs text-slate-600 flex flex-wrap gap-3">
                                    <span>Case: {{ $letter->case_number ?? '-' }}</span>
                                    <span>Ref: {{ $letter->reference_number ?? '-' }}</span>
                                    <span>Template: {{ $letter->template_title ?? '-' }}</span>
                                    <span>Author: {{ $letter->author_name ?? '-' }}</span>
                                </div>
                            <div class="text-[11px] text-slate-500">
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
                            class="text-sm font-semibold text-blue-700 hover:underline">
                            View
                        </a>
                    </div>
                    @endforeach
                </div>
                    @else
                    <div class="rounded-lg border border-dashed border-slate-200 p-6 text-sm text-slate-500">
                        No letters available yet.
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const buttons = document.querySelectorAll('[data-panel-target]');
                const panels = document.querySelectorAll('[data-panel]');
                if (!buttons.length || !panels.length) {
                    return;
                }
                const setPanel = (name) => {
                    panels.forEach(panel => panel.classList.toggle('hidden', panel.dataset.panel !== name));
                    buttons.forEach(btn => {
                        const isActive = btn.dataset.panelTarget === name;
                        btn.classList.toggle('shadow-lg', isActive);
                        btn.classList.toggle('ring-2', isActive);
                        btn.classList.toggle('ring-offset-2', isActive);
                        btn.classList.toggle('ring-blue-400', isActive);
                    });
                };
                buttons.forEach(btn => btn.addEventListener('click', () => setPanel(btn.dataset.panelTarget)));
                setPanel('recent-cases');
            });
        </script>
    </div>
</x-applicant-layout>
