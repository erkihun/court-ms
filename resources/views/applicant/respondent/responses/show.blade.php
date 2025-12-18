@props(['response'])

<x-applicant-layout :title="__('respondent.response_title', ['title' => $response->title])" :as-respondent-nav="true">
    <div class="max-w-4xl mx-auto bg-white rounded-2xl border border-slate-200 shadow p-6 space-y-6">
        <div class="flex flex-col gap-1">
            <p class="text-xs uppercase tracking-wide text-slate-500">{{ __('respondent.response_title_label') }}</p>
            <h1 class="text-2xl font-semibold text-slate-900">{{ $response->title }}</h1>
            <p class="text-sm text-slate-500">{{ \App\Support\EthiopianDate::format($response->created_at, withTime: true) }}</p>
            @if(!empty($response->case_number))
            <p class="text-xs text-slate-500">{{ __('respondent.case_number_label') }}: {{ $response->case_number }}</p>
            @endif
        </div>

        <div class="space-y-2 text-sm text-slate-700">
            <h2 class="text-base font-semibold text-slate-800">{{ __('respondent.description_label') }}</h2>
            <p>{{ $response->description }}</p>
        </div>

        @if(!empty($response->pdf_path))
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-900">{{ __('respondent.response_pdf') }}</p>
                <a href="{{ route('applicant.respondent.responses.download', $response) }}"
                    class="text-sm text-blue-700 hover:underline">{{ __('respondent.download') }}</a>
            </div>
        </div>
        @endif

        <div class="text-right">
            <a href="{{ route('respondent.responses.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                {{ __('respondent.back_to_responses') }}
            </a>
        </div>
    </div>
</x-applicant-layout>
