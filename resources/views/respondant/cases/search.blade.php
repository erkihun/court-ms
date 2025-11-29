<x-respondant-layout title="{{ __('respondent.find_case_title') }}">
    <div class="max-w-3xl mx-auto bg-white rounded-2xl border border-slate-200 shadow p-6">
        <h1 class="text-xl font-semibold text-slate-900 mb-2">{{ __('respondent.find_case_title') }}</h1>
        <p class="text-sm text-slate-600 mb-6">{{ __('respondent.find_case_description') }}</p>

        <form method="GET" action="{{ route('respondent.case.search') }}" class="space-y-4">
            <label class="block text-sm font-medium text-slate-700" for="case_number">{{ __('respondent.case_number_label') }}</label>
            <div class="flex gap-2">
                <input id="case_number" name="case_number" value="{{ old('case_number', $caseNumber) }}"
                    class="flex-1 rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                    placeholder="e.g. CMS-2024-0001" autocomplete="off">
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-blue-700 text-white text-sm font-semibold hover:bg-blue-800">
                    {{ __('respondent.search') }}
                </button>
            </div>
        </form>

        @if($caseNumber !== '')
        <div class="mt-6 space-y-3">
            @if($case)
            <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-blue-700 font-semibold">{{ __('respondent.case_found') }}</div>
                        <div class="text-lg font-semibold text-slate-900 mt-1">{{ $case->case_number }}</div>
                    </div>
                    <a href="{{ route('respondent.cases.show', $case->case_number) }}" class="text-sm text-blue-700 hover:underline">{{ __('respondent.view_case_details') }}</a>
                </div>
                <p class="text-sm text-slate-700 mt-2">{{ $case->title }}</p>
                <div class="text-sm text-slate-600 mt-3 grid grid-cols-2 gap-3">
                    <div>
                        <span class="text-xs uppercase tracking-wide text-slate-500">{{ __('respondent.status_label') }}</span>
                        <div class="text-sm font-medium text-slate-900">{{ ucfirst($case->status) }}</div>
                    </div>
                    <div>
                        <span class="text-xs uppercase tracking-wide text-slate-500">{{ __('respondent.filed') }}</span>
                        <div class="text-sm font-medium text-slate-900">{{ optional($case->created_at)->format('M d, Y') }}</div>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-xl border border-red-100 bg-red-50 p-4">
                        <div class="text-sm font-semibold text-red-700">{{ __('respondent.no_matching_case') }}</div>
            </div>
            @endif
        </div>
        @endif
    </div>
</x-respondant-layout>
