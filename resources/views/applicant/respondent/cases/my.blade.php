<x-applicant-layout title="{{ __('respondent.my_cases') }}">
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm uppercase tracking-wide text-slate-500">{{ __('cases.navigation.title') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('respondent.my_cases') }}</h1>
            </div>
            <a href="{{ route('applicant.respondent.cases.search') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-700 text-white text-sm font-semibold hover:bg-blue-800">
                {{ __('respondent.find_case') }}
            </a>
        </div>

        @if($cases->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-200 bg-white p-6 text-center text-slate-600">
            {{ __('No cases viewed yet.') }}
        </div>
        @else
        <div class="grid gap-4">
            @foreach($cases as $case)
            @php
                $status = $case->status ?? 'pending';
                $statusClasses = match ($status) {
                    'pending' => 'bg-orange-50 text-orange-700 border-orange-200',
                    'active' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'closed', 'dismissed' => 'bg-slate-100 text-slate-700 border-slate-200',
                    default => 'bg-slate-50 text-slate-700 border-slate-200',
                };
            @endphp
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-3">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('cases.case_number') }}</p>
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-semibold text-slate-900">{{ $case->case_number }}</span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full border text-xs font-medium {{ $statusClasses }}">
                                {{ __('cases.status.' . ($status ?? 'pending')) }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('applicant.respondent.cases.show', $case->case_number) }}"
                        class="text-sm font-semibold text-blue-700 hover:underline">{{ __('respondent.view_case_details') }}</a>
                </div>
                <p class="text-sm text-slate-700">{{ $case->title }}</p>
                <div class="text-xs text-slate-500 flex flex-wrap gap-4">
                    <span>{{ __('cases.filed') }}: {{ optional($case->created_at)->format('M d, Y') }}</span>
                    <span>{{ __('cases.case_type') }}: {{ $case->case_type ?? '-' }}</span>
                </div>
            </article>
            @endforeach
        </div>
        @endif
    </div>
</x-respondant-layout>
