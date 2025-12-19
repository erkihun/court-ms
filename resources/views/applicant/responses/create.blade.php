@php
    $caseDescription = clean($case->description ?? '', 'cases');
    $responseBody = clean($response->description ?? '', 'cases');
    $respondentName = trim(
        (($response->first_name ?? '') . ' ' . ($response->middle_name ?? '') . ' ' . ($response->last_name ?? ''))
    );
@endphp

<x-applicant-layout :title="__('respondent.give_response')" :breadcrumbs="[
    ['title' => __('cases.my_cases'), 'url' => route('applicant.cases.index')],
    ['title' => $case->case_number, 'url' => route('applicant.cases.show', $case->id)],
    ['title' => __('respondent.give_response')],
]">
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6 space-y-4">
            <div class="flex flex-wrap items-start gap-6 justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">
                        {{ __('cases.case_information') }}
                    </p>
                    <h1 class="text-2xl font-semibold text-slate-900 mt-1">
                        {{ $case->title ?? __('cases.case_number') . ' ' . $case->case_number }}
                    </h1>
                    <p class=" text-slate-500 mt-1">
                        {{ __('cases.case_number') }}: <span class="font-medium text-slate-900">{{ $case->case_number }}</span>
                        @if(!empty($case->status))
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 ml-2">
                                {{ ucfirst($case->status) }}
                            </span>
                        @endif
                    </p>
                </div>
                <div class="text-right  text-slate-600">
                    <p>{{ __('cases.labels.filed') }}: {{ optional($case->filing_date)->toDateString() ?? '--' }}</p>
                    <p>{{ __('cases.labels.type') }}: {{ $case->case_type_name ?? __('cases.labels.not_available') }}</p>
                </div>
            </div>

            @if(!empty($caseDescription))
                <div class="border border-slate-100 rounded-xl p-4 bg-slate-50/70  text-slate-700 tiny-content">
                    {!! $caseDescription !!}
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 shadow-inner p-6 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-emerald-700 font-semibold">
                        {{ __('cases.respondent_response') }}
                    </p>
                    <h2 class="text-xl font-semibold text-emerald-900 mt-1">
                        {{ $response->title ?? __('cases.respondent_response') }}
                    </h2>
                    <p class=" text-emerald-800 mt-1">
                        {{ __('cases.case_number') }}: <span class="font-medium">{{ $response->case_number }}</span>
                    </p>
                    @if($respondentName)
                        <p class=" text-emerald-800">
                            {{ __('cases.respondent_defendant') }}: <span class="font-medium">{{ $respondentName }}</span>
                        </p>
                    @endif
                </div>
                <div class=" text-emerald-800">
                    {{ \App\Support\EthiopianDate::format($response->created_at, withTime: true) }}
                </div>
            </div>
            @if(!empty($response->summary))
                <div class="rounded-xl border border-emerald-100 bg-white/80 p-3  text-emerald-900">
                    <strong class="block text-xs uppercase tracking-wide text-emerald-600">{{ __('cases.labels.summary') }}</strong>
                    <p>{{ $response->summary }}</p>
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">
                        {{ __('respondent.give_response') }}
                    </p>
                    <h3 class="text-xl font-semibold text-slate-900">
                        {{ __('cases.forms.provide_details') }}
                    </h3>
                </div>
                <a href="{{ route('applicant.cases.show', $case->id) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-300  font-medium text-slate-700 hover:bg-slate-50 transition">
                    {{ __('cases.back_to_case') }}
                </a>
            </div>

            <form method="POST" action="{{ route('applicant.respondent.responses.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <input type="hidden" name="case_number" value="{{ $case->case_number }}">
                <input type="hidden" name="parent_response_id" value="{{ $response->id }}">

                <div>
                    <label for="title" class="block  font-semibold text-slate-700 mb-1">
                        {{ __('cases.labels.title') }}
                    </label>
                <input type="text" name="title" id="title"
                           class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"
                           value="{{ old('title') }}" required>
                    @error('title')
                        <p class=" text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="summary" class="block  font-semibold text-slate-700 mb-1">
                        {{ __('cases.labels.summary') }}
                    </label>
                    <textarea name="summary" id="summary" rows="2"
                              class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">{{ old('summary') }}</textarea>
                    @error('summary')
                        <p class=" text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block  font-semibold text-slate-700 mb-1">
                        {{ __('cases.labels.description') }}
                    </label>
                    <textarea name="description" id="description" rows="6"
                              class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">{{ old('description') }}</textarea>
                    @error('description')
                        <p class=" text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="pdf" class="block  font-semibold text-slate-700 mb-1">
                        {{ __('cases.attachment') }}
                    </label>
                    <input type="file" name="pdf" id="pdf"
                           class="block w-full  text-slate-600 file:mr-4 file:py-2 file:px-4
                                  file:rounded-md file:border-0 file: file:font-semibold
                                  file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
                           accept="application/pdf" required>
                    @error('pdf')
                        <p class=" text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('applicant.cases.show', $case->id) }}"
                       class="px-4 py-2 rounded-lg border border-slate-300  font-semibold text-slate-600 hover:bg-slate-50">
                        {{ __('cases.buttons.cancel') }}
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-emerald-600 text-white  font-semibold hover:bg-emerald-700 focus:ring-2 focus:ring-offset-1 focus:ring-emerald-500 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('cases.buttons.submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-applicant-layout>
