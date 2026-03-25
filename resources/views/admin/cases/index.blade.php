<x-admin-layout title="{{ __('cases.case_details') }}">
    @section('page_header', __('cases.case_details'))

    <div class="enterprise-page">
        <div class="enterprise-toolbar">
            <form method="GET" class="enterprise-toolbar-block">
                <input id="q-search" name="q" value="{{ $q ?? request('q','') }}"
                    placeholder="{{ __('cases.search_placeholder') }}" class="ui-input w-full md:w-96">
                <button class="btn btn-primary">{{ __('cases.search') }}</button>
                @if(($q ?? request('q','')) !== '')
                <a href="{{ route('cases.index') }}" class="btn btn-outline">{{ __('cases.reset') }}</a>
                @endif
            </form>
        </div>

        <form method="GET" class="enterprise-panel">
            <div class="enterprise-panel-body">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                    <div>
                        <label for="filter-status" class="block text-xs font-medium text-slate-700 mb-1">{{ __('cases.table.status') }}</label>
                        <select id="filter-status" name="status" class="ui-select">
                            <option value="">{{ __('cases.filters.all_statuses') }}</option>
                            @foreach(['pending','active','adjourned','dismissed','closed'] as $s)
                            <option value="{{ $s }}" @selected(($status ?? '' )===$s)>{{ __('cases.status.' . $s) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="filter-type" class="block text-xs font-medium text-slate-700 mb-1">{{ __('cases.table.type') }}</label>
                        <select id="filter-type" name="case_type_id" class="ui-select">
                            <option value="">{{ __('cases.filters.all_types') }}</option>
                            @foreach($types as $t)
                            <option value="{{ $t->id }}" @selected(($caseTypeId ?? null)===$t->id)>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="filter-assignee" class="block text-xs font-medium text-slate-700 mb-1">{{ __('cases.table.assignee') }}</label>
                        <select id="filter-assignee" name="assignee_id" class="ui-select">
                            <option value="">{{ __('cases.filters.all_assignees') }}</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected(($assigneeId ?? null)===$u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-2 grid grid-cols-2 gap-3">
                        <div>
                            <label for="filter-from" class="block text-xs font-medium text-slate-700 mb-1">{{ __('cases.filters.from') }}</label>
                            <input id="filter-from" type="date" name="from" value="{{ optional($from)->format('Y-m-d') }}" class="ui-input">
                        </div>
                        <div>
                            <label for="filter-to" class="block text-xs font-medium text-slate-700 mb-1">{{ __('cases.filters.to') }}</label>
                            <input id="filter-to" type="date" name="to" value="{{ optional($to)->format('Y-m-d') }}" class="ui-input">
                        </div>
                    </div>
                </div>
            </div>

            <div class="enterprise-panel-header">
                <div class="enterprise-actions">
                    <button class="btn btn-primary">{{ __('cases.filters.apply_filters') }}</button>
                    @if(request()->hasAny(['q','status','case_type_id','assignee_id','from','to']) && ($q||$status||$caseTypeId||$assigneeId||$from||$to))
                    <a href="{{ route('cases.index') }}" class="btn btn-outline">{{ __('cases.reset') }}</a>
                    @endif
                    <a href="{{ route('cases.export', request()->except('page')) }}" class="btn btn-outline">{{ __('cases.export_csv') }}</a>
                </div>
                <span class="text-sm text-slate-600">
                    {{ __('cases.showing_results', [
                        'first' => $cases->firstItem(),
                        'last' => $cases->lastItem(),
                        'total' => $cases->total()
                    ]) }}
                </span>
            </div>
        </form>

        <div class="ui-table-wrap">
            <div class="ui-table-scroll">
                <table class="ui-table">
                    <thead>
                        <tr>
                            <th>{{ __('cases.table.number') }}</th>
                            <th>{{ __('cases.table.case_number') }}</th>
                            <th>{{ __('cases.applicant_name') }}</th>
                            <th>{{ __('cases.table.type') }}</th>
                            <th>{{ __('cases.table.team') }}</th>
                            <th>{{ __('cases.table.team_member') }}</th>
                            <th>{{ __('cases.table.status') }}</th>
                            <th>{{ __('cases.table.filing_date') }}</th>
                            <th>{{ __('cases.show.review_actions') }}</th>
                            <th>{{ __('cases.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cases as $c)
                        <tr>
                            <td>{{ ($cases->firstItem() ?: 0) + $loop->index }}</td>
                            <td class="font-semibold"><a href="{{ route('cases.show', $c->id) }}" class="text-blue-700 hover:underline">{{ $c->case_number }}</a></td>
                            <td>{{ $c->applicant_name ?? '-' }}</td>
                            <td>{{ $c->case_type }}</td>
                            <td>{{ $c->team_name ?? '-' }}</td>
                            <td>{{ $c->assignee_name ?? '-' }}</td>
                            <td>
                                <span class="enterprise-pill
                                    @if($c->status==='pending') border-amber-200 bg-amber-50 text-amber-800
                                    @elseif($c->status==='active') border-blue-200 bg-blue-50 text-blue-800
                                    @elseif(in_array($c->status,['closed','dismissed'])) border-emerald-200 bg-emerald-50 text-emerald-800
                                    @else border-slate-200 bg-slate-100 text-slate-700 @endif">
                                    {{ __('cases.status.' . $c->status) }}
                                </span>
                            </td>
                            <td>{{ \App\Support\EthiopianDate::format($c->filing_date) }}</td>
                            <td>
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
                                <span class="enterprise-pill
                                    @class([
                                        'border-amber-200 bg-amber-50 text-amber-800' => $displayRs==='awaiting_review',
                                        'border-emerald-200 bg-emerald-50 text-emerald-800' => $displayRs==='accepted',
                                        'border-yellow-200 bg-yellow-50 text-yellow-800' => $displayRs==='returned',
                                        'border-rose-200 bg-rose-50 text-rose-800' => $displayRs==='rejected',
                                        'border-slate-200 bg-slate-100 text-slate-700' => !in_array($displayRs,['awaiting_review','accepted','returned','rejected'])
                                    ])">
                                    {{ __('cases.review_status.' . $displayRs) }}
                                </span>
                                @if(!empty($c->reviewer_name))
                                <div class="text-xs text-slate-500 mt-1">{{ __('cases.reviewed_by', ['name' => $c->reviewer_name]) }}</div>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('cases.show', $c->id) }}" class="btn btn-outline !px-3 !py-1.5 !text-xs">{{ __('cases.table.view') }}</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10"><div class="enterprise-empty">{{ __('cases.table.no_cases_found') }}</div></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $cases->links() }}</div>
    </div>
</x-admin-layout>
