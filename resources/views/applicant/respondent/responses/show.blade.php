@props(['response'])

<x-applicant-layout :title="__('respondent.response_title', ['title' => $response->title])" :as-respondent-nav="true">
    <div class="max-w-4xl mx-auto bg-white rounded-2xl border border-slate-200 shadow p-6 space-y-6">
        @php
            $respReviewStatus = $response->review_status ?? 'awaiting_review';
            $respReviewBase = 'inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium border';
            $respReviewClass = match ($respReviewStatus) {
                'awaiting_review' => $respReviewBase.' bg-amber-50 text-amber-800 border-amber-200',
                'returned' => $respReviewBase.' bg-yellow-50 text-yellow-800 border-yellow-200',
                'rejected' => $respReviewBase.' bg-red-50 text-red-800 border-red-200',
                default => $respReviewBase.' bg-emerald-50 text-emerald-800 border-emerald-200',
            };
            $respReviewLabel = match ($respReviewStatus) {
                'awaiting_review' => __('cases.review_status.awaiting_review'),
                'returned' => __('cases.review_status.returned'),
                'rejected' => __('cases.review_status.rejected'),
                default => __('cases.review_status.accepted'),
            };
        @endphp
        <div class="flex flex-col gap-1">
            <p class="text-xs uppercase tracking-wide text-slate-500">{{ __('respondent.response_title_label') }}</p>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-semibold text-slate-900">{{ $response->title }}</h1>
                <span class="{{ $respReviewClass }}">{{ $respReviewLabel }}</span>
            </div>
            <p class="text-sm text-slate-500">{{ \App\Support\EthiopianDate::format($response->created_at, withTime: true) }}</p>
            @if(!empty($response->case_number))
            <p class="text-xs text-slate-500">{{ __('respondent.case_number_label') }}: {{ $response->case_number }}</p>
            @endif
        </div>

        <div class="space-y-2 text-sm text-slate-700">
            <h2 class="text-base font-semibold text-slate-800">{{ __('respondent.description_label') }}</h2>
            <p>{{ $response->description }}</p>
        </div>

        @if(!empty($response->review_note) && ($response->review_status ?? 'awaiting_review') !== 'accepted')
        <div class="text-sm text-slate-700">
            <span class="font-semibold text-slate-800">{{ __('cases.reviewer_note') }}</span>
            {{ $response->review_note }}
        </div>
        @endif

        @if(!empty($response->pdf_path))
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-900">{{ __('respondent.response_pdf') }}</p>
                <a href="{{ route('applicant.respondent.responses.download', $response) }}"
                    class="text-sm text-blue-700 hover:underline">{{ __('respondent.download') }}</a>
            </div>
            <div class="rounded-lg border border-slate-200 overflow-hidden bg-white">
                <iframe
                    src="{{ route('applicant.respondent.responses.download', [$response, 'inline' => 1]) }}#toolbar=0&navpanes=0&scrollbar=0"
                    loading="lazy"
                    class="w-full"
                    style="min-height: 420px;"
                    title="{{ $response->title }}">
                </iframe>
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
