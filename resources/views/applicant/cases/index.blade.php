<x-applicant-layout title="{{ __('cases.my_cases') }}">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">
                {{ __('cases.my_cases') }}
            </h1>

        </div>

        <a href="{{ route('applicant.cases.create') }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-md bg-orange-500 text-white  font-medium
                  hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-orange-400 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4v16m8-8H4" />
            </svg>
            <span>{{ __('cases.new_case') }}</span>
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full ">
            <thead class="bg-slate-50">
                <tr class="border-b border-slate-200">
                    <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        {{ __('cases.table.case_number') }}
                    </th>
                    <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        {{ __('cases.table.title') }}
                    </th>
                    <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        {{ __('cases.table.type') }}
                    </th>
                    <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        {{ __('cases.table.status') }}
                    </th>
                    <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        Review
                    </th>
                    <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        {{ __('cases.table.filed') }}
                    </th>
                    <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        {{ __('cases.table.actions') }}
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($cases as $c)
                <tr class="hover:bg-slate-50/80">
                    <td class="p-3 font-mono text-xs text-slate-800">
                        {{ $c->case_number }}
                    </td>
                    <td class="p-3 text-slate-900">
                        <div class="line-clamp-2">
                            {{ $c->title }}
                        </div>
                    </td>
                    <td class="p-3 text-slate-700">
                        {{ $c->case_type }}
                    </td>
                    <td class="p-3 capitalize">
                        @php
                        $status = $c->status;
                        $badgeBase = 'inline-flex items-center px-2.5 py-0.5 rounded-full border text-xs font-medium';
                        $badgeClass = match(true) {
                        $status === 'pending' =>
                        $badgeBase.' bg-orange-50 text-orange-700 border-orange-200',
                        $status === 'active' =>
                        $badgeBase.' bg-blue-50 text-blue-700 border-blue-200',
                        in_array($status, ['closed','dismissed']) =>
                        $badgeBase.' bg-slate-100 text-slate-700 border-slate-200',
                        default =>
                        $badgeBase.' bg-slate-50 text-slate-700 border-slate-200',
                        };
                        @endphp
                        <span class="{{ $badgeClass }}">
                            {{ __('cases.status.' . $status) }}
                        </span>
                    </td>
                    <td class="p-3">
                        @php
                        $review = $c->review_status ?? 'accepted';
                        $reviewBase = 'inline-flex items-center px-2.5 py-0.5 rounded-full border text-xs font-medium';
                        $reviewClass = match($review) {
                        'awaiting_review' => $reviewBase.' bg-amber-50 text-amber-800 border-amber-200',
                        'returned' => $reviewBase.' bg-yellow-50 text-yellow-800 border-yellow-200',
                        'rejected' => $reviewBase.' bg-red-50 text-red-800 border-red-200',
                        default => $reviewBase.' bg-green-50 text-green-800 border-green-200',
                        };
                        $reviewLabel = match($review) {
                        'awaiting_review' => 'Awaiting admin approval',
                        'returned' => 'Needs correction',
                        'rejected' => 'Rejected',
                        default => 'Accepted',
                        };
                        @endphp
                        <div class="space-y-1">
                            <span class="{{ $reviewClass }}">
                                {{ $reviewLabel }}
                            </span>
                            @if(!empty($c->review_note) && in_array($review, ['returned','rejected']))
                            <div class="text-[11px] text-slate-600 leading-snug line-clamp-2">
                                {{ $c->review_note }}
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="p-3 text-slate-700 whitespace-nowrap">
                        {{ \App\Support\EthiopianDate::format($c->filing_date) }}
                    </td>
                    <td class="p-3">
                        <div class="flex items-center gap-2">
                            {{-- View button --}}
                            <a href="{{ route('applicant.cases.show',$c->id) }}"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-600 text-white text-xs font-medium
                                          hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>{{ __('cases.table.view') }}</span>
                            </a>

                            {{-- Delete button (only if status is pending) --}}
                            @if($c->status === 'pending')
                            <form method="POST" action="{{ route('applicant.cases.destroy', $c->id) }}"
                                onsubmit="return confirm('{{ __('cases.table.delete_confirm') ?? __('Are you sure you want to delete this case?') }}');">
                                @csrf
                                @method('DELETE')
                                <button
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-red-600 text-white text-xs font-medium
                                           hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4
                                               a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span>{{ __('cases.general.delete') ?? __('Delete') }}</span>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="p-6 text-center text-slate-500 ">
                        {{ __('cases.table.no_cases_yet') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex justify-end">
        {{ $cases->links() }}
    </div>
</x-applicant-layout>
