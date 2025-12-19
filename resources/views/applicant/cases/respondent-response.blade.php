@php
    $responseBody = clean($response->description ?? '', 'cases');
    $respondentName = trim(
        (($response->first_name ?? '') . ' ' . ($response->middle_name ?? '') . ' ' . ($response->last_name ?? ''))
    );
@endphp

<x-applicant-layout :title="__('cases.respondent_response')" :breadcrumbs="[
    ['title' => __('cases.my_cases'), 'url' => route('applicant.cases.index')],
    ['title' => $case->case_number, 'url' => route('applicant.cases.show', $case->id)],
    ['title' => __('cases.respondent_response')],
]">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="border border-slate-200 rounded-xl bg-white px-4 py-3 shadow-sm flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class=" font-semibold text-slate-900">
                    {{ __('cases.case_number') }}: {{ $case->case_number }}
                </p>
                <p class="text-xs text-slate-500">
                    {{ __('cases.respondent_response') }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('applicant.cases.respondentResponses.reply', [$case->id, $response->id]) }}"
                   class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md border border-emerald-200 bg-emerald-50  font-medium text-emerald-700 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 transition">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                    </svg>
                    {{ __('respondent.give_response') }}
                </a>
                <a href="{{ route('applicant.cases.show', $case->id) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md border border-slate-300  font-medium text-slate-700 hover:bg-slate-50">
                    {{ __('cases.back_to_case') }}
                </a>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-5 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-lg font-semibold text-slate-900">
                        {{ $response->title ?? __('cases.respondent_response') }}
                    </h1>
                    <p class="text-xs text-slate-500">
                        {{ __('cases.case_number') }}: {{ $response->case_number ?? $case->case_number }}
                    </p>
                    @if($respondentName || $response->respondent_email || $response->respondent_phone)
                        <dl class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-xs text-slate-600">
                            @if($respondentName)
                                <div>
                                    <dt class="uppercase tracking-wide text-slate-500">{{ __('cases.respondent_defendant') }}</dt>
                                    <dd class="font-semibold text-slate-900">{{ $respondentName }}</dd>
                                </div>
                            @endif
                            @if(!empty($response->respondent_email))
                                <div>
                                    <dt class="uppercase tracking-wide text-slate-500">{{ __('cases.applicant_email') }}</dt>
                                    <dd class="text-slate-800">{{ $response->respondent_email }}</dd>
                                </div>
                            @endif
                            @if(!empty($response->respondent_phone))
                                <div>
                                    <dt class="uppercase tracking-wide text-slate-500">{{ __('cases.labels.phone') }}</dt>
                                    <dd class="text-slate-800">{{ $response->respondent_phone }}</dd>
                                </div>
                            @endif
                            @if(!empty($response->respondent_org))
                                <div>
                                    <dt class="uppercase tracking-wide text-slate-500">{{ __('cases.respondent_name') }}</dt>
                                    <dd class="text-slate-800">{{ $response->respondent_org }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif
                </div>
                <div class=" text-slate-600">
                    {{ \App\Support\EthiopianDate::format($response->created_at, withTime: true) }}
                </div>
            </div>

            @if(!empty($response->summary))
                <div class="rounded-md border border-blue-100 bg-blue-50 p-3  text-blue-900">
                    {{ $response->summary }}
                </div>
            @endif

            <div class="tiny-content  text-slate-800">
                {!! $responseBody ?: __('cases.no_description') !!}
            </div>

            @if(!empty($response->pdf_path))
                <div class="border-t border-slate-100 pt-4">
                    <div class=" text-slate-600 mb-2">
                        {{ __('cases.attachment') }}:
                        <span class="font-medium text-slate-800">{{ basename($response->pdf_path) }}</span>
                    </div>
                    <iframe
                        src="{{ route('applicant.respondent.responses.download', $response->id) }}?inline=1"
                        class="w-full rounded-lg border border-slate-200"
                        style="min-height: 600px;"
                        title="{{ $response->title ?? __('cases.download_pdf') }}">
                    </iframe>
                </div>
            @endif
        </div>
    </div>
</x-applicant-layout>
