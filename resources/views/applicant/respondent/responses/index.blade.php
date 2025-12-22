@php use Illuminate\Support\Str; @endphp

<x-applicant-layout title="{{ __('respondent.responses') }}" :as-respondent-nav="true">
    <div class="max-w-6xl mx-auto space-y-8 px-4 sm:px-6 lg:px-8"> {{-- Increased max-width and added padding --}}

        {{-- Header Section --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm uppercase tracking-wide text-blue-600 font-medium">{{ __('respondent.responses') }}</p> {{-- Changed text-slate-500 to blue-600 for emphasis --}}
                <h1 class="text-3xl font-bold text-slate-900">{{ __('respondent.responses') }}</h1> {{-- Increased font size/weight --}}
            </div>
            <a href="{{ route('respondent.responses.create') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-700 text-white text-base font-semibold shadow-md hover:bg-blue-800 transition duration-150 ease-in-out">
                {{-- Added Icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                {{ __('respondent.create_response') }}
            </a>
        </div>

        {{-- Responses List --}}
        @if($responses->isEmpty())
        <div class="rounded-xl border-2 border-dashed border-slate-300 bg-white p-10 text-center text-slate-500 shadow-sm"> {{-- Slightly more prominent empty state --}}
            <svg class="w-12 h-12 mx-auto mb-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="font-semibold text-lg">{{ __('respondent.no_responses') }}</p>
            <p class="text-sm">Start by creating your first response document.</p>
        </div>
        @else
        <div class="rounded-2xl border border-slate-200 bg-white shadow-xl overflow-hidden"> {{-- Better shadow for depth --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-slate-600 uppercase tracking-wider text-xs"> {{-- Slightly adjusted text size/tracking --}}
                        <tr>
                            <th class="px-6 py-3 text-left w-1/4 font-medium">{{ __('respondent.responses') }}</th> {{-- Added px-6 for better spacing --}}
                            <th class="px-6 py-3 text-left w-1/6 font-medium">Review status</th>
                            <th class="px-6 py-3 text-left w-1/5 font-medium">{{ __('respondent.case_number_label') }}</th>
                            <th class="px-6 py-3 text-left w-1/6 font-medium">{{ __('respondent.uploaded') }}</th>
                            <th class="px-6 py-3 text-right font-medium">{{ __('app.Actions') ?? 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @foreach($responses as $response)
                        @php
                            $respReviewStatus = $response->review_status ?? 'awaiting_review';
                            $respReviewNote = $response->review_note ?? null;
                            $respLocked = in_array($respReviewStatus, ['accepted', 'accept'], true);
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
                        <tr class="hover:bg-blue-50/50 transition duration-100"> {{-- Hover effect changed to blue --}}
                            <td class="px-6 py-4"> {{-- Added px-6 for better spacing and py-4 --}}
                                <div class="font-semibold text-slate-900">{{ $response->title }}</div>
                                @if(!empty($respReviewNote) && $respReviewStatus === 'returned')
                                <div class="mt-1 text-xs text-slate-600">
                                    <span class="font-semibold text-slate-700">{{ __('cases.reviewer_note') }}</span>
                                    {{ $respReviewNote }}
                                </div>
                                @endif
                                @if($response->pdf_path)
                                {{-- Enhanced PDF Submitted Pill with Icon and darker color --}}
                                <div class="mt-1 inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0113 2.586L15.414 5A2 2 0 0116 6.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ __('respondent.pdf_submitted') }}
                                </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                <span class="{{ $respReviewClass }}">{{ $respReviewLabel }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $response->case_number ?: '—' }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ \App\Support\EthiopianDate::format($response->created_at) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('respondent.responses.show', $response) }}"
                                        class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        {{ __('respondent.view_details') }}
                                    </a>
                                    @if(!$respLocked)
                                        <a href="{{ route('respondent.responses.edit', $response) }}"
                                            class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            {{ __('respondent.edit_response') }}
                                        </a>
                                        <form method="POST" action="{{ route('respondent.responses.destroy', $response) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100"
                                                onclick="return confirm('{{ __('respondent.delete') }}')">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                {{ __('respondent.delete') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Added Pagination Placeholder --}}
            @if(method_exists($responses, 'links'))
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                {{ $responses->links() }}
            </div>
            @endif
        </div>
        @endif
    </div>
</x-applicant-layout>
