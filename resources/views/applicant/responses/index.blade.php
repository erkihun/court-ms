<x-applicant-layout :title="__('respondent.response_of_response')" :breadcrumbs="[
    ['title' => __('dashboard.my_dashboard'), 'url' => route('applicant.dashboard')],
    ['title' => __('respondent.response_of_response')],
]">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('respondent.response_of_response') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('respondent.applicant_response_replies') }}</h1>
            </div>
            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">
                {{ $replies->total() }}
            </span>
        </div>

        @if($replies->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">
                {{ __('respondent.no_applicant_response_replies') }}
            </div>
        @else
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('cases.case_number') }}</th>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('respondent.response_number_label') }}</th>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('cases.status.new_status') }}</th>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('cases.filed') }}</th>
                                <th class="px-4 py-3 text-right font-semibold">{{ __('cases.labels.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($replies as $reply)
                                @php
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
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $reply->case_number }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $reply->response_number ?: __('cases.labels.not_available') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $reviewClass }}">
                                            {{ $reviewLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ \App\Support\EthiopianDate::format($reply->created_at, withTime: true) }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('applicant.cases.respondentResponses.replies.show', [$reply->case_id, $reply->respondent_response_id, $reply->id]) }}"
                                                class="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                                {{ __('cases.view') }}
                                            </a>
                                            @if(!$isAccepted)
                                                <a href="{{ route('applicant.cases.respondentResponses.replies.edit', [$reply->case_id, $reply->respondent_response_id, $reply->id]) }}"
                                                    class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                                    {{ __('cases.general.edit') }}
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                {{ $replies->links() }}
            </div>
        @endif
    </div>
</x-applicant-layout>
