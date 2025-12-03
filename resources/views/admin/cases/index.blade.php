<x-admin-layout title="{{ __('cases.case_details') }}">
    @section('page_header', __('cases.case_details'))

    {{-- Search / filters --}}
    <form method="GET" class="mb-4 flex gap-2">
        <input name="q" value="{{ $q ?? request('q','') }}" placeholder="{{ __('cases.search_placeholder') }}"
            class="w-full md:w-96 px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
        <button class="px-3 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white">{{ __('cases.search') }}</button>
        @if(($q ?? request('q','')) !== '')
        <a href="{{ route('cases.index') }}"
            class="px-3 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-800">{{ __('cases.reset') }}</a>
        @endif
    </form>

    {{-- Top filters --}}
    <form method="GET" class="mb-4 grid grid-cols-1 lg:grid-cols-6 gap-3">
        <select name="status" class="px-3 py-2 rounded bg-white text-gray-900 border border-gray-300">
            <option value="">{{ __('cases.filters.all_statuses') }}</option>
            @foreach(['pending','active','adjourned','dismissed','closed'] as $s)
            <option value="{{ $s }}" @selected(($status ?? '' )===$s)>{{ __('cases.status.' . $s) }}</option>
            @endforeach
        </select>

        <select name="case_type_id" class="px-3 py-2 rounded bg-white text-gray-900 border border-gray-300">
            <option value="">{{ __('cases.filters.all_types') }}</option>
            @foreach($types as $t)
            <option value="{{ $t->id }}" @selected(($caseTypeId ?? null)===$t->id)>{{ $t->name }}</option>
            @endforeach
        </select>

        <select name="assignee_id" class="px-3 py-2 rounded bg-white text-gray-900 border border-gray-300">
            <option value="">{{ __('cases.filters.all_assignees') }}</option>
            @foreach($users as $u)
            <option value="{{ $u->id }}" @selected(($assigneeId ?? null)===$u->id)>{{ $u->name }}</option>
            @endforeach
        </select>

        <div class="flex gap-2">
            <input type="date" name="from" value="{{ optional($from)->format('Y-m-d') }}"
                class="px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 w-full"
                placeholder="{{ __('cases.filters.from') }}">
            <input type="date" name="to" value="{{ optional($to)->format('Y-m-d') }}"
                class="px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 w-full"
                placeholder="{{ __('cases.filters.to') }}">
        </div>

        <div class="lg:col-span-6 flex flex-wrap gap-2">
            <button
                class="px-3 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white">{{ __('cases.filters.apply_filters') }}</button>

            @if(request()->hasAny(['q','status','case_type_id','assignee_id','from','to']) &&
            ($q||$status||$caseTypeId||$assigneeId||$from||$to))
            <a href="{{ route('cases.index') }}"
                class="px-3 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-800">{{ __('cases.reset') }}</a>
            @endif

            <a href="{{ route('cases.export', request()->query()) }}"
                class="px-3 py-2 rounded bg-emerald-600 hover:bg-emerald-700 text-white">{{ __('cases.export_csv') }}</a>

            <span class="ml-auto text-sm text-gray-600 self-center">
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

    <div class="overflow-x-auto rounded-xl border border-gray-300">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="p-3 text-left">{{ __('cases.table.case_number') }}</th>
                    <th class="p-3 text-left">{{ __('cases.table.title') }}</th>
                    <th class="p-3 text-left">{{ __('cases.table.type') }}</th>
                    <th class="p-3 text-left">{{ __('cases.table.assignee') }}</th>
                    <th class="p-3 text-left">{{ __('cases.table.status') }}</th>
                    <th class="p-3 text-left">{{ __('cases.table.filing_date') }}</th>
                    <th class="p-3 text-left">Review</th> {{-- new: show review status / actions --}}
                    <th class="p-3 text-left">{{ __('cases.table.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($cases as $c)
                <tr class="hover:bg-gray-50">
                    <td class="p-3 font-mono text-gray-900">{{ $c->case_number }}</td>
                    <td class="p-3 text-gray-900">{{ $c->title }}</td>
                    <td class="p-3 text-gray-900">{{ $c->case_type }}</td>
                    <td class="p-3 text-gray-900">{{ $c->assignee_name ?? '-' }}</td>

                    <td class="p-3 capitalize">
                        <span class="px-2 py-0.5 rounded text-xs
                            @if($c->status==='pending') bg-orange-100 text-orange-800 border border-orange-200
                            @elseif($c->status==='active') bg-blue-100 text-blue-800 border border-blue-200
                            @elseif(in_array($c->status,['closed','dismissed'])) bg-green-100 text-green-800 border border-green-200
                            @else bg-gray-100 text-gray-800 border border-gray-200 @endif">
                            {{ __('cases.status.' . $c->status) }}
                        </span>

                    </td>

                    <td class="p-3 text-gray-900">
                        {{ \Illuminate\Support\Carbon::parse($c->filing_date)->format('M d, Y') }}
                    </td>

                    {{-- Review cell --}}
                    <td class="p-3">
                        @php
                        $rs = $c->review_status ?? 'accepted';
                        // Be lenient: check multiple permission helpers so reviewers always see full status.
                        $canReview = ($isReviewer ?? false);
                        if (!$canReview && function_exists('userHasPermission')) {
                        $canReview = userHasPermission('cases.review');
                        }
                        if (!$canReview && auth()->check()) {
                        $canReview = auth()->user()->can('cases.review');
                        }
                        $displayRs = $canReview ? $rs : 'accepted';
                        @endphp
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded border
                            @class([
                              'bg-amber-50 text-amber-800 border-amber-200' => $displayRs==='awaiting_review',
                              'bg-green-50 text-green-800 border-green-200' => $displayRs==='accepted',
                              'bg-yellow-50 text-yellow-800 border-yellow-200' => $displayRs==='returned',
                              'bg-red-50 text-red-800 border-red-200' => $displayRs==='rejected',
                              'bg-gray-50 text-gray-700 border-gray-200' => !in_array($displayRs,['awaiting_review','accepted','returned','rejected'])
                            ])">
                            {{ ucfirst(str_replace('_',' ', $displayRs)) }}
                        </span>

                    </td>

                    {{-- Actions --}}
                    <td class="p-3">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('cases.show', $c->id) }}"
                                class="px-2 py-1 rounded bg-gray-200 hover:bg-gray-300 text-xs text-gray-800">
                                {{ __('cases.table.view') }}
                            </a>

                            {{-- Use your @perm directive or @can; avoid calling unknown methods --}}
                            @if($canAssignCases)
                            <a href="{{ route('cases.assign.form', $c->id) }}"
                                class="px-2 py-1 rounded bg-indigo-600 hover:bg-indigo-700 text-xs text-white">
                                {{ __('cases.table.assign') }}
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="p-6 text-center text-gray-500">{{ __('cases.table.no_cases_found') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $cases->links() }}</div>
</x-admin-layout>