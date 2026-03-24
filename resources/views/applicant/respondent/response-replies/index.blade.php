<x-applicant-layout :title="__('respondent.response_of_response')" :as-respondent-nav="true">
    <div class="w-full max-w-[1800px] mx-auto space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm uppercase tracking-wide text-slate-500">{{ __('respondent.quick_actions') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('respondent.response_of_response') }}</h1>
            </div>
            <a href="{{ route('respondent.dashboard') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-slate-300 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50">
                {{ __('respondent.dashboard') }}
            </a>
        </div>

        @if($replies->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
                {{ __('respondent.no_applicant_response_replies') }}
            </div>
        @else
            <div class="rounded-2xl border border-slate-200 bg-white shadow-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-600 uppercase tracking-wider text-xs">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">{{ __('cases.case_number') }}</th>
                                <th class="px-6 py-3 text-left font-medium">{{ __('respondent.response_number_label') }}</th>
                                <th class="px-6 py-3 text-left font-medium">{{ __('cases.applicant_full_name') }}</th>
                                <th class="px-6 py-3 text-left font-medium">{{ __('respondent.uploaded') }}</th>
                                <th class="px-6 py-3 text-right font-medium">{{ __('cases.labels.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-800">
                            @foreach($replies as $reply)
                                @php
                                    $applicantName = trim(($reply->applicant_first_name ?? '').' '.($reply->applicant_middle_name ?? '').' '.($reply->applicant_last_name ?? ''));
                                @endphp
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 font-semibold text-slate-900">{{ $reply->case_number }}</td>
                                    <td class="px-6 py-4 text-slate-700">{{ $reply->response_number ?: __('cases.labels.not_available') }}</td>
                                    <td class="px-6 py-4 text-slate-700">{{ $applicantName ?: __('cases.labels.not_available') }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ \App\Support\EthiopianDate::format($reply->created_at, withTime: true) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('respondent.response-replies.show', $reply->id) }}"
                                                class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50">
                                                {{ __('respondent.view_details') }}
                                            </a>
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
