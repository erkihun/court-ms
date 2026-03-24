@php
    $applicantName = trim((($reply->applicant_first_name ?? '') . ' ' . ($reply->applicant_middle_name ?? '') . ' ' . ($reply->applicant_last_name ?? '')));
@endphp

<x-applicant-layout :title="__('respondent.response_of_response')" :as-respondent-nav="true">
    <div class="w-full max-w-[1600px] mx-auto space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('respondent.response_of_response') }}</p>
                    <h1 class="mt-1 text-2xl font-semibold text-slate-900">{{ __('respondent.response_of_response') }}</h1>
                </div>
                <a href="{{ route('respondent.response-replies.index') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    {{ __('respondent.back_to_responses') }}
                </a>
            </div>

            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('cases.case_number') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $reply->case_number }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('respondent.response_number_label') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $reply->response_number ?: __('cases.labels.not_available') }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('cases.applicant_full_name') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $applicantName ?: __('cases.labels.not_available') }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('respondent.response_of_response_description') }}</h2>
            <div class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $reply->description }}</div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('cases.attachment') }}</h2>
                <a href="{{ route('respondent.response-replies.download', $reply->id) }}"
                    class="text-sm font-semibold text-blue-700 hover:underline">
                    {{ __('respondent.download') }}
                </a>
            </div>
            <iframe
                src="{{ route('respondent.response-replies.download', [$reply->id, 'inline' => 1]) }}"
                class="mt-4 min-h-[80vh] w-full rounded-xl border border-slate-200"
                title="{{ __('respondent.response_of_response') }}">
            </iframe>
        </div>
    </div>
</x-applicant-layout>
