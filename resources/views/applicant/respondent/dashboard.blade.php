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

        <div class="grid gap-5 lg:grid-cols-3">
            {{-- Quick actions --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-4">
                <div>
                    <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('respondent.quick_actions') ?? 'Quick actions' }}</div>
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('respondent.dashboard') }}</h2>
                </div>
                <div class="grid gap-3">
                    <a href="{{ route('respondent.case.search') }}"
                        class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 hover:border-blue-300 hover:bg-blue-50">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 text-blue-700">
                            🔍
                        </span>
                        {{ __('respondent.find_case') }}
                    </a>
                    <a href="{{ route('respondent.responses.index') }}"
                        class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 hover:border-blue-300 hover:bg-blue-50">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-orange-100 text-orange-700">
                            📨
                        </span>
                        {{ __('respondent.my_responses') }}
                    </a>
                    <a href="{{ route('respondent.cases.my') }}"
                        class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 hover:border-blue-300 hover:bg-blue-50">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-indigo-700">
                            📄
                        </span>
                        {{ __('respondent.my_cases') }}
                    </a>
                </div>
            </div>

            {{-- Recent cases list --}}
            <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
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
        </div>

        {{-- Recent letters --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <div class="text-xs uppercase tracking-wide text-slate-500">Letters</div>
                    <h2 class="text-lg font-semibold text-slate-900">Recent approved letters</h2>
                </div>
                <a href="{{ route('respondent.cases.my') }}" class="text-sm font-semibold text-blue-700 hover:underline">
                    View my cases
                </a>
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
                        {{ \Illuminate\Support\Carbon::parse($letter->created_at)->format('M d, Y H:i') }}
                    </div>
                </div>
                @php
                    $letterPreviewUrl = \Illuminate\Support\Facades\URL::signedRoute('letters.case-preview', ['letter' => $letter->id]);
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
</x-applicant-layout>
