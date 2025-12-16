<x-admin-layout title="{{ __('cases.case_details') }}">
    @section('page_header', __('cases.case_details'))

    {{-- Search / filters (Modernized UI container) --}}
    <div class="p-4 mb-4 rounded-xl bg-white border border-gray-200 shadow-sm">
        <form method="GET" class="flex gap-3 items-end">
            <div class="flex-grow">
                <label for="q-search" class="block text-xs font-medium text-gray-700 mb-1">Search Case Title or Number</label>
                <input id="q-search" name="q" value="{{ $q ?? request('q','') }}" placeholder="{{ __('cases.search_placeholder') }}"
                    {{-- Updated input styling --}}
                    class="w-full md:w-96 px-3 py-2 rounded-lg bg-gray-50 text-gray-900 border border-gray-300 focus:ring-blue-500 focus:border-blue-500 transition shadow-inner">
            </div>
            {{-- Updated button styling --}}
            <button class="h-[40px] px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition shadow-md">
                {{ __('cases.search') }}
            </button>
            @if(($q ?? request('q','')) !== '')
            <a href="{{ route('cases.index') }}"
                class="h-[40px] px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium text-center transition shadow-md flex items-center">
                {{ __('cases.reset') }}
            </a>
            @endif
        </form>
    </div>


    {{-- Top filters (Modernized UI container) --}}
    <form method="GET" class="p-4 mb-6 rounded-xl bg-white border border-gray-200 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
            {{-- Status Filter --}}
            <div>
                <label for="filter-status" class="block text-xs font-medium text-gray-700 mb-1">{{ __('cases.filters.status') }}</label>
                <select id="filter-status" name="status"
                    class="w-full px-3 py-2 rounded-lg bg-gray-50 text-gray-900 border border-gray-300 focus:ring-blue-500 focus:border-blue-500 shadow-inner">
                    <option value="">{{ __('cases.filters.all_statuses') }}</option>
                    @foreach(['pending','active','adjourned','dismissed','closed'] as $s)
                    <option value="{{ $s }}" @selected(($status ?? '' )===$s)>{{ __('cases.status.' . $s) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Type Filter --}}
            <div>
                <label for="filter-type" class="block text-xs font-medium text-gray-700 mb-1">{{ __('cases.table.type') }}</label>
                <select id="filter-type" name="case_type_id"
                    class="w-full px-3 py-2 rounded-lg bg-gray-50 text-gray-900 border border-gray-300 focus:ring-blue-500 focus:border-blue-500 shadow-inner">
                    <option value="">{{ __('cases.filters.all_types') }}</option>
                    @foreach($types as $t)
                    <option value="{{ $t->id }}" @selected(($caseTypeId ?? null)===$t->id)>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Assignee Filter --}}
            <div>
                <label for="filter-assignee" class="block text-xs font-medium text-gray-700 mb-1">{{ __('cases.table.assignee') }}</label>
                <select id="filter-assignee" name="assignee_id"
                    class="w-full px-3 py-2 rounded-lg bg-gray-50 text-gray-900 border border-gray-300 focus:ring-blue-500 focus:border-blue-500 shadow-inner">
                    <option value="">{{ __('cases.filters.all_assignees') }}</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected(($assigneeId ?? null)===$u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Date Filters --}}
            <div class="md:col-span-2 flex gap-3">
                <div>
                    <label for="filter-from" class="block text-xs font-medium text-gray-700 mb-1">{{ __('cases.filters.from_date') }}</label>
                    <input id="filter-from" type="date" name="from" value="{{ optional($from)->format('Y-m-d') }}"
                        class="w-full px-3 py-2 rounded-lg bg-gray-50 text-gray-900 border border-gray-300 focus:ring-blue-500 focus:border-blue-500 shadow-inner">
                </div>
                <div>
                    <label for="filter-to" class="block text-xs font-medium text-gray-700 mb-1">{{ __('cases.filters.to_date') }}</label>
                    <input id="filter-to" type="date" name="to" value="{{ optional($to)->format('Y-m-d') }}"
                        class="w-full px-3 py-2 rounded-lg bg-gray-50 text-gray-900 border border-gray-300 focus:ring-blue-500 focus:border-blue-500 shadow-inner">
                </div>
            </div>
        </div>

        <div class="lg:col-span-6 flex flex-wrap gap-3 mt-4 justify-between items-center">
            <div class="flex gap-3">
                <button
                    class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition shadow-md">
                    {{ __('cases.filters.apply_filters') }}
                </button>

                @if(request()->hasAny(['q','status','case_type_id','assignee_id','from','to']) &&
                ($q||$status||$caseTypeId||$assigneeId||$from||$to))
                <a href="{{ route('cases.index') }}"
                    class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium transition shadow-md">
                    {{ __('cases.reset') }}
                </a>
                @endif

                <a href="{{ route('cases.export', request()->except('page')) }}"
                    class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition shadow-md">
                    {{ __('cases.export_csv') }}
                </a>
            </div>

            <span class="text-sm text-gray-600 self-center">
                {{ __('cases.showing_results', [
                    'first' => $cases->firstItem(),
                    'last' => $cases->lastItem(),
                    'total' => $cases->total()
                ]) }}
            </span>
        </div>
    </form>

    @php
    $canAssignCases = false;
    if ($user = auth()->user()) {
    $canAssignCases = $user->hasPermission('cases.assign.team')
    || $user->hasPermission('cases.assign.member')
    || $user->hasPermission('cases.assign');
    }
    @endphp

    {{-- Table Container (Updated styling) --}}
    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-md">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            {{-- Table Header (Updated styling) --}}
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('cases.table.number') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('cases.table.case_number') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('cases.table.title') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('cases.table.type') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('cases.table.assignee') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('cases.table.status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('cases.table.filing_date') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Review</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('cases.table.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($cases as $c)
                {{-- Added hover effect --}}
                <tr class="hover:bg-gray-50 transition duration-100">
                    <td class="px-6 py-4 text-sm text-gray-700 whitespace-nowrap">
                        {{ ($cases->firstItem() ?: 0) + $loop->index }}
                    </td>
                    <td class="px-6 py-4 font-medium text-blue-600 whitespace-nowrap">
                        <a href="{{ route('cases.show', $c->id) }}" class="hover:underline">{{ $c->case_number }}</a>
                    </td>
                    {{-- Increased padding to px-6 py-4 --}}
                    <td class="px-6 py-4 max-w-xs truncate text-sm text-gray-900" title="{{ $c->title }}">{{ $c->title }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $c->case_type }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $c->assignee_name ?? '-' }}</td>

                    {{-- Case Status (Updated styling to full rounded pill) --}}
                    <td class="px-6 py-4 whitespace-nowrap capitalize">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($c->status==='pending') bg-orange-50 text-orange-800 border border-orange-200
                            @elseif($c->status==='active') bg-blue-50 text-blue-800 border border-blue-200
                            @elseif(in_array($c->status,['closed','dismissed'])) bg-emerald-50 text-emerald-800 border border-emerald-200
                            @else bg-gray-50 text-gray-800 border border-gray-200 @endif">
                            {{ __('cases.status.' . $c->status) }}
                        </span>

                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        {{ \App\Support\EthiopianDate::format($c->filing_date) }}
                    </td>

                    {{-- Review cell (Updated styling to full rounded pill) --}}
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                        $rs = $c->review_status ?? 'accepted';
                        $canReview = ($isReviewer ?? false);
                        if (!$canReview && function_exists('userHasPermission')) {
                        $canReview = userHasPermission('cases.review');
                        }
                        if (!$canReview && auth()->check()) {
                        $canReview = auth()->user()->can('cases.review');
                        }
                        $displayRs = $canReview ? $rs : 'accepted';
                        @endphp
                        <span class="inline-flex items-center gap-1 text-xs px-2.5 py-0.5 rounded-full border font-medium
                            @class([
                                'bg-amber-50 text-amber-800 border-amber-200' => $displayRs==='awaiting_review',
                                'bg-emerald-50 text-emerald-800 border-emerald-200' => $displayRs==='accepted',
                                'bg-yellow-50 text-yellow-800 border-yellow-200' => $displayRs==='returned',
                                'bg-red-50 text-red-800 border-red-200' => $displayRs==='rejected',
                                'bg-gray-50 text-gray-700 border-gray-200' => !in_array($displayRs,['awaiting_review','accepted','returned','rejected'])
                            ])">
                            {{ ucfirst(str_replace('_',' ', $displayRs)) }}
                        </span>

                    </td>

                    {{-- Actions (Updated small buttons) --}}
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('cases.show', $c->id) }}"
                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ __('cases.table.view') }}
                            </a>

                            {{-- Use your @perm directive or @can; avoid calling unknown methods --}}
                            @if($canAssignCases)
                            <a href="{{ route('cases.assign.form', $c->id) }}"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent shadow-sm text-xs leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ __('cases.table.assign') }}
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    {{-- Updated colspan for 8 columns --}}
                    <td colspan="9" class="px-6 py-12 text-center text-gray-500 text-base">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9.25 10m.5 7h4.5M12 21a9 9 0 100-18 9 9 0 000 18z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('cases.table.no_cases') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('cases.table.no_cases_found') }}</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination links --}}
    <div class="mt-4">{{ $cases->links() }}</div>
</x-admin-layout>
