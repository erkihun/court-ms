@php
    $applicantName = trim((($reply->applicant_first_name ?? '') . ' ' . ($reply->applicant_middle_name ?? '') . ' ' . ($reply->applicant_last_name ?? '')));
    $respondentName = trim((($response->first_name ?? '') . ' ' . ($response->middle_name ?? '') . ' ' . ($response->last_name ?? '')));
    $reviewStatus = (string) ($reply->review_status ?? 'awaiting_review');
    $isAccepted = $reviewStatus === 'accepted';
    $reviewClass = match ($reviewStatus) {
        'accepted' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'returned' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'rejected' => 'bg-red-50 text-red-700 border-red-200',
        default => 'bg-amber-50 text-amber-700 border-amber-200',
    };
    $reviewLabel = match ($reviewStatus) {
        'accepted' => __('cases.review_status.accepted'),
        'returned' => __('cases.review_status.returned'),
        'rejected' => __('cases.review_status.rejected'),
        default => __('cases.review_status.awaiting_review'),
    };
@endphp

<x-applicant-layout :title="__('respondent.response_of_response')" :breadcrumbs="[
    ['title' => __('cases.my_cases'), 'url' => route('applicant.cases.index')],
    ['title' => $case->case_number, 'url' => route('applicant.cases.show', $case->id)],
    ['title' => __('cases.respondent_response'), 'url' => route('applicant.cases.respondentResponses.show', [$case->id, $response->id])],
    ['title' => __('respondent.response_of_response')],
]">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('respondent.response_of_response') }}</p>
                    <h1 class="mt-1 text-2xl font-semibold text-slate-900">{{ __('respondent.response_of_response') }}</h1>
                    <p class="mt-2 text-sm text-slate-500">{{ \App\Support\EthiopianDate::format($reply->created_at, withTime: true) }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $reviewClass }}">
                        {{ $reviewLabel }}
                    </span>
                    @if(!$isAccepted)
                        <a href="{{ route('applicant.cases.respondentResponses.replies.edit', [$case->id, $response->id, $reply->id]) }}"
                            class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100">
                            {{ __('cases.buttons.update') }}
                        </a>
                        <form method="POST" action="{{ route('applicant.cases.respondentResponses.replies.destroy', [$case->id, $response->id, $reply->id]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100"
                                onclick="return confirm('{{ __('cases.confirm.remove_document') }}')">
                                {{ __('cases.general.delete') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('cases.case_number') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $case->case_number }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('respondent.response_number_label') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $response->response_number ?: __('cases.labels.not_available') }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('cases.applicant_full_name') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $applicantName ?: __('cases.labels.not_available') }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('cases.respondent_defendant') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $respondentName ?: __('cases.labels.not_available') }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('respondent.response_of_response_description') }}</h2>
            <div class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $reply->description }}</div>
            @if(!empty($reply->review_note))
                <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">
                    <span class="font-semibold text-slate-900">{{ __('cases.reviewer_note') }}</span>
                    {{ $reply->review_note }}
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('cases.attachment') }}</h2>
                <a href="{{ route('applicant.cases.respondentResponses.replies.download', [$case->id, $response->id, $reply->id]) }}"
                    class="text-sm font-semibold text-blue-700 hover:underline">
                    {{ __('respondent.download') }}
                </a>
            </div>
            <iframe
                src="{{ route('applicant.cases.respondentResponses.replies.download', [$case->id, $response->id, $reply->id, 'inline' => 1]) }}"
                class="mt-4 min-h-[80vh] w-full rounded-xl border border-slate-200"
                title="{{ __('respondent.response_of_response') }}">
            </iframe>
        </div>
    </div>
</x-applicant-layout>
