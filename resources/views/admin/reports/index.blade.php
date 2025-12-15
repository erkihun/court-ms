<x-admin-layout title="{{ __('reports.title') }}">
    @section('page_header', __('reports.title'))

    @php
    $formatLabel = fn (?string $value) => $value
        ? \Illuminate\Support\Str::of($value)->replace('_', ' ')->title()
        : __('reports.unknown');
    @endphp

    <div class="space-y-6">
        <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-blue-600">{{ __('reports.summary') }}</p>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ __('reports.title') }}</h1>
                    <p class="text-sm text-slate-500">{{ __('reports.description') }}</p>
                </div>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($summaryCards as $card)
                <div class="rounded-2xl border border-gray-100 bg-gradient-to-br from-white to-slate-50 p-4 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                        {{ __('reports.cards.' . $card['key']) }}
                    </p>
                    <div class="mt-3 text-3xl font-bold text-slate-900">
                        {{ number_format($card['value']) }}
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-blue-600">{{ __('reports.filter.title') }}</p>
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('reports.filter.subtitle') }}</h2>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.print()"
                        class="px-4 py-2 rounded-full border border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-white">
                        {{ __('reports.print') }}
                    </button>
                </div>
            </div>

            <form method="GET" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500">{{ __('reports.filter.start') }}</label>
                    <input type="date" name="start_date"
                        value="{{ old('start_date', $filterParams['start_date'] ?? '') }}"
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500">{{ __('reports.filter.end') }}</label>
                    <input type="date" name="end_date"
                        value="{{ old('end_date', $filterParams['end_date'] ?? '') }}"
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500">{{ __('reports.filter.status') }}</label>
                    <select name="status" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">{{ __('reports.filter.status_all') }}</option>
                        @foreach($caseStatuses as $status)
                        <option value="{{ $status }}" {{ ($filterParams['status'] ?? '') === $status ? 'selected' : '' }}>
                            {{ $formatLabel($status) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500">{{ __('reports.filter.judge') }}</label>
                    <select name="judge_id" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">{{ __('reports.filter.judge_all') }}</option>
                        @foreach($judges as $judge)
                        <option value="{{ $judge->id }}" {{ ($filterParams['judge_id'] ?? '') == $judge->id ? 'selected' : '' }}>
                            {{ $judge->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                <div class="rounded-2xl border border-gray-100 bg-slate-50/60 p-4">
                    <h3 class="text-sm font-semibold text-slate-900">{{ __('reports.filter.summary') }}</h3>
                    <p class="text-sm text-slate-500 mt-1">{{ __('reports.filter.summary_hint') }}</p>
                    <table class="mt-3 w-full text-sm text-slate-700">
                        <tr class="border-b border-dashed border-slate-200">
                            <td class="py-2 text-slate-500">{{ __('reports.filter.cases_filtered') }}</td>
                            <td class="py-2 font-semibold text-slate-900 text-right">{{ number_format($filteredCaseCount) }}</td>
                        </tr>
                        @if($filterParams['start_date'] ?? null)
                        <tr class="border-b border-dashed border-slate-200">
                            <td class="py-2 text-slate-500">{{ __('reports.filter.start') }}</td>
                            <td class="py-2 text-slate-900 text-right">{{ $filterParams['start_date'] }}</td>
                        </tr>
                        @endif
                        @if($filterParams['end_date'] ?? null)
                        <tr class="border-b border-dashed border-slate-200">
                            <td class="py-2 text-slate-500">{{ __('reports.filter.end') }}</td>
                            <td class="py-2 text-slate-900 text-right">{{ $filterParams['end_date'] }}</td>
                        </tr>
                        @endif
                        @if($filterParams['status'] ?? null)
                        <tr class="border-b border-dashed border-slate-200">
                            <td class="py-2 text-slate-500">{{ __('reports.filter.status') }}</td>
                            <td class="py-2 text-slate-900 text-right">{{ $formatLabel($filterParams['status']) }}</td>
                        </tr>
                        @endif
                        @if($filterParams['judge_id'] ?? null)
                        <tr>
                            <td class="py-2 text-slate-500">{{ __('reports.filter.judge') }}</td>
                            <td class="py-2 text-slate-900 text-right">
                                {{ $judges->firstWhere('id', $filterParams['judge_id'])?->name ?? __('reports.unknown') }}
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-4">
                    <h3 class="text-sm font-semibold text-slate-900">{{ __('reports.filter.status_breakdown') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('reports.filter.status_hint') }}</p>
                    <div class="mt-3 space-y-2 text-sm text-slate-700">
                        @forelse($filteredCaseStatus as $row)
                        <div class="flex items-center justify-between">
                            <span>{{ $formatLabel($row->status) }}</span>
                            <span class="font-semibold text-slate-900">{{ number_format($row->total) }}</span>
                        </div>
                        @empty
                        <p class="text-[11px] text-slate-500">{{ __('reports.no_data') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="mt-4 rounded-2xl border border-gray-100 bg-white p-4">
                <h3 class="text-sm font-semibold text-slate-900">{{ __('reports.filter.judge_breakdown') }}</h3>
                <p class="text-xs text-slate-500">{{ __('reports.filter.judge_hint') }}</p>
                <div class="mt-3 overflow-auto">
                    <table class="w-full text-sm text-slate-700">
                        <thead class="text-[11px] uppercase tracking-[0.2em] text-slate-400">
                            <tr>
                                <th class="py-2 text-left">{{ __('reports.filter.judge') }}</th>
                                <th class="py-2 text-right">{{ __('reports.filter.cases') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($casesByJudge as $judgeRow)
                            <tr class="border-b border-dashed border-slate-200">
                                <td class="py-2 font-medium text-slate-900">{{ $judgeRow->judge_name }}</td>
                                <td class="py-2 text-right font-semibold text-slate-900">{{ number_format($judgeRow->total) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="py-3 text-xs text-slate-500">{{ __('reports.no_data') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-900">{{ __('reports.sections.cases_status') }}</h2>
                    <span class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        {{ number_format($caseStatusBreakdown->sum('total')) }} {{ __('reports.total') }}
                    </span>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse($caseStatusBreakdown as $row)
                    <div class="flex items-center justify-between text-sm text-slate-700">
                        <div>{{ $formatLabel($row->status) }}</div>
                        <div class="font-semibold text-slate-900">{{ number_format($row->total) }}</div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500">{{ __('reports.no_data') }}</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-900">{{ __('reports.sections.cases_types') }}</h2>
                    <span class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        {{ __('reports.cases') }}
                    </span>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse($caseTypeBreakdown as $type)
                    <div class="flex items-center justify-between text-sm text-slate-700">
                        <div class="truncate">{{ $type->name ?: __('reports.unknown') }}</div>
                        <div class="font-semibold text-slate-900">{{ number_format($type->total) }}</div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500">{{ __('reports.no_data') }}</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-900">{{ __('reports.sections.appeals_status') }}</h3>
                <div class="mt-3 space-y-2 text-sm text-slate-700">
                    @forelse($appealStatusBreakdown as $row)
                    <div class="flex items-center justify-between">
                        <span>{{ $formatLabel($row->status) }}</span>
                        <span class="font-semibold text-slate-900">{{ number_format($row->total) }}</span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500">{{ __('reports.no_data') }}</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-900">{{ __('reports.sections.decisions_status') }}</h3>
                <div class="mt-3 space-y-2 text-sm text-slate-700">
                    @forelse($decisionStatusBreakdown as $row)
                    <div class="flex items-center justify-between">
                        <span>{{ $formatLabel($row->status) }}</span>
                        <span class="font-semibold text-slate-900">{{ number_format($row->total) }}</span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500">{{ __('reports.no_data') }}</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-900">{{ __('reports.sections.letters_status') }}</h3>
                <div class="mt-3 space-y-2 text-sm text-slate-700">
                    @forelse($letterApprovalBreakdown as $row)
                    <div class="flex items-center justify-between">
                        <span>{{ $formatLabel($row->status) }}</span>
                        <span class="font-semibold text-slate-900">{{ number_format($row->total) }}</span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500">{{ __('reports.no_data') }}</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-900">{{ __('reports.sections.applicants_status') }}</h3>
                <div class="mt-3 space-y-2 text-sm text-slate-700">
                    @forelse($applicantStatusBreakdown as $row)
                    <div class="flex items-center justify-between">
                        <span>{{ $formatLabel($row->state) }}</span>
                        <span class="font-semibold text-slate-900">{{ number_format($row->total) }}</span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500">{{ __('reports.no_data') }}</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-900">{{ __('reports.sections.users_status') }}</h3>
                <div class="mt-3 space-y-2 text-sm text-slate-700">
                    @forelse($userStatusBreakdown as $row)
                    <div class="flex items-center justify-between">
                        <span>{{ $formatLabel($row->status) }}</span>
                        <span class="font-semibold text-slate-900">{{ number_format($row->total) }}</span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500">{{ __('reports.no_data') }}</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">{{ __('reports.sections.recent_cases') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('reports.recent_hint.cases') }}</p>
                    </div>
                    <a href="{{ route('cases.index') }}" class="text-xs font-semibold uppercase tracking-wide text-blue-600 hover:text-blue-800">
                        {{ __('reports.view_all', ['thing' => __('app.Cases')]) }}
                    </a>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse($recentCases as $case)
                    <a href="{{ route('cases.show', $case->id) }}" class="group flex flex-col gap-1 rounded-2xl border border-gray-100 p-3 text-sm text-slate-700 transition hover:border-blue-200 hover:bg-blue-50">
                        <div class="flex items-center justify-between text-slate-900">
                            <span class="font-semibold">{{ $case->case_number }}</span>
                            <span class="text-[11px] uppercase tracking-wide text-slate-500">{{ $formatLabel($case->status) }}</span>
                        </div>
                        <p class="text-xs text-slate-500">{{ $case->title }}</p>
                        <div class="flex items-center justify-between text-[11px] text-slate-400">
                            <span>{{ $case->applicant?->full_name ?? __('reports.unknown') }}</span>
                            <span>{{ optional($case->created_at)->diffForHumans() }}</span>
                        </div>
                    </a>
                    @empty
                    <p class="text-xs text-slate-500">{{ __('reports.no_data') }}</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">{{ __('reports.sections.recent_appeals') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('reports.recent_hint.appeals') }}</p>
                    </div>
                    <a href="{{ route('appeals.index') }}" class="text-xs font-semibold uppercase tracking-wide text-blue-600 hover:text-blue-800">
                        {{ __('reports.view_all', ['thing' => __('app.Appeals')]) }}
                    </a>
                </div>
                <div class="mt-4 space-y-3 text-sm text-slate-700">
                    @forelse($recentAppeals as $appeal)
                    <a href="{{ route('appeals.show', $appeal->id) }}" class="group flex flex-col gap-1 rounded-2xl border border-gray-100 p-3 transition hover:border-blue-200 hover:bg-blue-50">
                        <div class="flex items-center justify-between text-slate-900">
                            <span class="font-semibold">{{ $appeal->appeal_number }}</span>
                            <span class="text-[11px] uppercase tracking-wide text-slate-500">{{ $formatLabel($appeal->status) }}</span>
                        </div>
                        <p class="text-xs text-slate-500">{{ $appeal->case_number }}</p>
                        <div class="flex items-center justify-between text-[11px] text-slate-400">
                            <span>{{ $appeal->title }}</span>
                            <span>{{ \Illuminate\Support\Carbon::parse($appeal->created_at)->diffForHumans() }}</span>
                        </div>
                    </a>
                    @empty
                    <p class="text-xs text-slate-500">{{ __('reports.no_data') }}</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-admin-layout>
