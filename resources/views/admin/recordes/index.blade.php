<x-admin-layout title="{{ __('recordes.titles.index') }}">
    @section('page_header', __('recordes.titles.index'))

    <div class="enterprise-page">
        <div class="enterprise-toolbar">
            <form method="GET" class="enterprise-toolbar-block">
                <input
                    id="q-search"
                    name="q"
                    value="{{ $q ?? request('q', '') }}"
                    placeholder="{{ __('cases.search_placeholder') }}"
                    class="ui-input w-full md:w-96"
                >
                <button class="btn btn-primary">{{ __('cases.search') }}</button>
                @if(($q ?? request('q', '')) !== '')
                    <a href="{{ route('recordes.index') }}" class="btn btn-outline">{{ __('cases.reset') }}</a>
                @endif
            </form>
        </div>

        <form method="GET" class="enterprise-panel">
            <div class="enterprise-panel-body">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-6">
                    <div>
                        <label for="filter-status" class="mb-1 block text-xs font-medium text-slate-700">{{ __('cases.table.status') }}</label>
                        <select id="filter-status" name="status" class="ui-select">
                            <option value="">{{ __('cases.filters.all_statuses') }}</option>
                            @foreach(['pending', 'active', 'adjourned', 'dismissed', 'closed'] as $caseStatus)
                                <option value="{{ $caseStatus }}" @selected(($status ?? '') === $caseStatus)>
                                    {{ __('cases.status.' . $caseStatus) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="filter-type" class="mb-1 block text-xs font-medium text-slate-700">{{ __('cases.table.type') }}</label>
                        <select id="filter-type" name="case_type_id" class="ui-select">
                            <option value="">{{ __('cases.filters.all_types') }}</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" @selected(($caseTypeId ?? null) === $type->id)>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="filter-assignee" class="mb-1 block text-xs font-medium text-slate-700">{{ __('cases.table.assignee') }}</label>
                        <select id="filter-assignee" name="assignee_id" class="ui-select">
                            <option value="">{{ __('cases.filters.all_assignees') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(($assigneeId ?? null) === $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3 lg:col-span-2">
                        <div>
                            <label for="filter-from" class="mb-1 block text-xs font-medium text-slate-700">{{ __('cases.filters.from') }}</label>
                            <input id="filter-from" type="date" name="from" value="{{ optional($from)->format('Y-m-d') }}" class="ui-input">
                        </div>
                        <div>
                            <label for="filter-to" class="mb-1 block text-xs font-medium text-slate-700">{{ __('cases.filters.to') }}</label>
                            <input id="filter-to" type="date" name="to" value="{{ optional($to)->format('Y-m-d') }}" class="ui-input">
                        </div>
                    </div>
                </div>
            </div>

            <div class="enterprise-panel-header">
                <div class="enterprise-actions">
                    <button class="btn btn-primary">{{ __('cases.filters.apply_filters') }}</button>
                    @if(request()->hasAny(['q', 'status', 'case_type_id', 'assignee_id', 'from', 'to']) && ($q || $status || $caseTypeId || $assigneeId || $from || $to))
                        <a href="{{ route('recordes.index') }}" class="btn btn-outline">{{ __('cases.reset') }}</a>
                    @endif
                    <a href="{{ route('cases.index') }}" class="btn btn-outline">{{ __('recordes.buttons.back_to_cases') }}</a>
                </div>
                <span class="text-sm text-slate-600">
                    @if($cases->total() > 0)
                        {{ __('cases.showing_results', ['first' => $cases->firstItem(), 'last' => $cases->lastItem(), 'total' => $cases->total()]) }}
                    @else
                        {{ __('recordes.messages.no_cases') }}
                    @endif
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
                            <th>{{ __('recordes.labels.title') }}</th>
                            <th>{{ __('cases.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cases as $case)
                            @php
                                $reviewStatus = $case->review_status ?? 'accepted';
                                $displayReviewStatus = $isReviewer ? $reviewStatus : 'accepted';
                            @endphp
                            <tr>
                                <td>{{ ($cases->firstItem() ?: 0) + $loop->index }}</td>
                                <td class="font-semibold">
                                    <a href="{{ route('recordes.show', $case->id) }}" class="text-blue-700 hover:underline">
                                        {{ $case->case_number }}
                                    </a>
                                </td>
                                <td>{{ $case->applicant_name ?: '-' }}</td>
                                <td>{{ $case->case_type ?: '-' }}</td>
                                <td>{{ $case->team_name ?: '-' }}</td>
                                <td>{{ $case->assignee_name ?: '-' }}</td>
                                <td>
                                    <span class="enterprise-pill
                                        @if($case->status === 'pending') border-amber-200 bg-amber-50 text-amber-800
                                        @elseif($case->status === 'active') border-blue-200 bg-blue-50 text-blue-800
                                        @elseif(in_array($case->status, ['closed', 'dismissed'])) border-emerald-200 bg-emerald-50 text-emerald-800
                                        @else border-slate-200 bg-slate-100 text-slate-700 @endif">
                                        {{ __('cases.status.' . $case->status) }}
                                    </span>
                                </td>
                                <td>{{ \App\Support\EthiopianDate::format($case->filing_date) }}</td>
                                <td>
                                    <span
                                        @class([
                                            'enterprise-pill',
                                            'border-amber-200 bg-amber-50 text-amber-800' => $displayReviewStatus === 'awaiting_review',
                                            'border-emerald-200 bg-emerald-50 text-emerald-800' => $displayReviewStatus === 'accepted',
                                            'border-yellow-200 bg-yellow-50 text-yellow-800' => $displayReviewStatus === 'returned',
                                            'border-rose-200 bg-rose-50 text-rose-800' => $displayReviewStatus === 'rejected',
                                            'border-slate-200 bg-slate-100 text-slate-700' => !in_array($displayReviewStatus, ['awaiting_review', 'accepted', 'returned', 'rejected'], true),
                                        ])
                                    >
                                        {{ __('cases.review_status.' . $displayReviewStatus) }}
                                    </span>
                                    @if(!empty($case->reviewer_name))
                                        <div class="mt-1 text-xs text-slate-500">{{ __('cases.reviewed_by', ['name' => $case->reviewer_name]) }}</div>
                                    @endif
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($case->title, 48) ?: '-' }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('recordes.show', ['case' => $case->id]) }}" class="btn btn-outline !px-3 !py-1.5 !text-xs">
                                            {{ __('recordes.buttons.view') }}
                                        </a>
                                        <a href="{{ route('recordes.pdf', ['case' => $case->id]) }}" target="_blank" rel="noopener" class="btn btn-outline !px-3 !py-1.5 !text-xs">
                                            {{ __('recordes.buttons.pdf') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11">
                                    <div class="enterprise-empty">{{ __('recordes.messages.no_cases') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $cases->links() }}</div>
    </div>
</x-admin-layout>
