<x-applicant-layout title="{{ __('respondent.my_cases') }}" :as-respondent-nav="true">
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm uppercase tracking-wide text-slate-500">{{ __('cases.navigation.title') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('respondent.my_cases') }}</h1>
            </div>
            @if(Route::has('respondent.case.search'))
            <a href="{{ route('respondent.case.search') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-700 text-white text-sm font-semibold hover:bg-blue-800">
                {{ __('respondent.find_case') }}
            </a>
            @endif
        </div>

        @if($cases->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-200 bg-white p-6 text-center text-slate-600">
            {{ __('No cases viewed yet.') }}
        </div>
        @else
        <div class="rounded-2xl border border-slate-200 bg-white shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-slate-600 uppercase tracking-wider text-xs">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium w-1/4">{{ __('cases.case_number') }}</th>
                            <th class="px-6 py-3 text-left font-medium w-1/3">{{ __('cases.case_title') ?? __('cases.title') }}</th>
                            <th class="px-6 py-3 text-right font-medium">{{ __('app.Actions') ?? 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @foreach($cases as $case)
                        @php
                        $status = $case->status ?? 'pending';
                        $statusClasses = match ($status) {
                        'pending' => 'bg-orange-50 text-orange-700 border-orange-200',
                        'active' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'closed', 'dismissed' => 'bg-slate-100 text-slate-700 border-slate-200',
                        default => 'bg-slate-50 text-slate-700 border-slate-200',
                        };
                        @endphp
                        <tr class="hover:bg-blue-50/50 transition duration-100">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-slate-900">{{ $case->case_number }}</span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full border text-[11px] font-semibold {{ $statusClasses }}">
                                        {{ __('cases.status.' . ($status ?? 'pending')) }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                {{ $case->case_type ?? '—' }}
                            </td>


                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('respondent.cases.show', $case->case_number) }}"
                                        class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        {{ __('respondent.view_case_details') }}
                                    </a>

                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(method_exists($cases, 'links'))
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                {{ $cases->links() }}
            </div>
            @endif
        </div>
        @endif
    </div>
</x-applicant-layout>