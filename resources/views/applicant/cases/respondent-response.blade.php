@php
    $responseBody = clean($response->description ?? '', 'cases');
    $respondentName = trim(
        (($response->first_name ?? '') . ' ' . ($response->middle_name ?? '') . ' ' . ($response->last_name ?? ''))
    );
    $replyStatusClass = static function (string $status): string {
        return match ($status) {
            'accepted' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'returned' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
            'rejected' => 'bg-red-50 text-red-700 border-red-200',
            default => 'bg-amber-50 text-amber-700 border-amber-200',
        };
    };
    $replyStatusLabel = static function (string $status): string {
        return match ($status) {
            'accepted' => __('cases.review_status.accepted'),
            'returned' => __('cases.review_status.returned'),
            'rejected' => __('cases.review_status.rejected'),
            default => __('cases.review_status.awaiting_review'),
        };
    };
@endphp

<x-applicant-layout :title="__('cases.respondent_response')" :breadcrumbs="[
    ['title' => __('cases.my_cases'), 'url' => route('applicant.cases.index')],
    ['title' => $case->case_number, 'url' => route('applicant.cases.show', $case->id)],
    ['title' => __('cases.respondent_response')],
]">
    <div class="w-full max-w-[1600px] mx-auto space-y-6">
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
                <a href="{{ route('applicant.cases.respondentResponses.replies.create', [$case->id, $response->id]) }}"
                   class="inline-flex items-center gap-2 rounded-md border border-blue-200 bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                    </svg>
                    {{ __('respondent.give_response_of_response') }}
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
                    @if(!empty($response->response_number))
                        <p class="text-xs font-semibold text-slate-600">
                            {{ __('respondent.response_number_label') }}: {{ $response->response_number }}
                        </p>
                    @endif
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
                        class="w-full rounded-lg border border-slate-200 min-h-[70vh] lg:min-h-[85vh]"
                        title="{{ $response->title ?? __('cases.download_pdf') }}">
                    </iframe>
                </div>
            @endif
        </div>

        <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('respondent.applicant_response_replies') }}</h2>
                    <p class="text-sm text-slate-500">{{ __('respondent.response_of_response') }}</p>
                </div>
                <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">
                    {{ ($replies ?? collect())->count() }}
                </span>
            </div>

            @if(($replies ?? collect())->isEmpty())
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">
                    {{ __('respondent.no_applicant_response_replies') }}
                </div>
            @else
                <div class="space-y-3">
                    @foreach($replies as $reply)
                        @php
                            $reviewStatus = (string) ($reply->review_status ?? 'awaiting_review');
                            $isAccepted = $reviewStatus === 'accepted';
                        @endphp
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="space-y-2">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        {{ __('respondent.response_of_response') }}
                                    </p>
                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $replyStatusClass($reviewStatus) }}">
                                        {{ $replyStatusLabel($reviewStatus) }}
                                    </span>
                                    <p class="text-sm text-slate-700">{{ \Illuminate\Support\Str::limit($reply->description, 180) }}</p>
                                    @if(!empty($reply->review_note))
                                        <p class="text-xs text-slate-600">
                                            <span class="font-semibold text-slate-700">{{ __('cases.reviewer_note') }}</span>
                                            {{ $reply->review_note }}
                                        </p>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500">{{ \App\Support\EthiopianDate::format($reply->created_at, withTime: true) }}</p>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                <a href="{{ route('applicant.cases.respondentResponses.replies.show', [$case->id, $response->id, $reply->id]) }}"
                                    class="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                    {{ __('cases.view') }}
                                </a>
                                @if(!$isAccepted)
                                    <a href="{{ route('applicant.cases.respondentResponses.replies.edit', [$case->id, $response->id, $reply->id]) }}"
                                        class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                        {{ __('cases.general.edit') }}
                                    </a>
                                    <form method="POST" action="{{ route('applicant.cases.respondentResponses.replies.destroy', [$case->id, $response->id, $reply->id]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100"
                                            onclick="return confirm('{{ __('cases.confirm.remove_document') }}')">
                                            {{ __('cases.general.delete') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-applicant-layout>
