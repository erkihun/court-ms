{{-- resources/views/admin/decisions/index.blade.php --}}
<x-admin-layout title="{{ __('decisions.index.title') }}">
    @section('page_header', __('decisions.index.title'))
    @include('admin.decisions.partials.font-style')

    @php
    $canEditDecision = function_exists('userHasPermission')
        ? userHasPermission('decision.update')
        : (auth()->user()?->hasPermission('decision.update') ?? false);
    $canDeleteDecision = function_exists('userHasPermission')
        ? userHasPermission('decision.delete')
        : (auth()->user()?->hasPermission('decision.delete') ?? false);
    $searchLabel = __('app.Search');
    $decisionTotal = isset($decisions) && method_exists($decisions, 'total')
        ? $decisions->total()
        : ($decisions?->count() ?? 0);
    @endphp

    <div class="decision-ethiopic-font space-y-4">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-sm text-gray-600">{{ __('decisions.index.showing') }}</span>
                <span class="px-2 py-1 rounded-lg text-sm bg-gray-100 text-gray-800 border border-gray-200">
                    {{ number_format($decisionTotal) }} {{ __('decisions.index.total') }}
                </span>
                @if(!empty($search))
                <span class="px-2 py-1 rounded-lg text-sm bg-orange-100 text-orange-800 border border-orange-200">
                    {{ __('app.Search') }}: {{ $search }}
                </span>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" action="{{ route('decisions.index') }}" class="flex items-center gap-2">
                    <input name="q"
                        value="{{ $search ?? '' }}"
                        placeholder="{{ __('decisions.index.search_placeholder') }}"
                        class="px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-500">
                    <button type="submit"
                        class="px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-700 text-sm hover:bg-gray-50">
                        {{ $searchLabel }}
                    </button>
                    @if(!empty($search))
                    <a href="{{ route('decisions.index') }}"
                        class="px-3 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm border border-gray-200 hover:bg-gray-200">
                        {{ __('app.Reset') }}
                    </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-700 border-b border-gray-200">
                        <tr>
                            <th class="p-3 text-left font-medium w-12">{{ __('decisions.index.no') }}</th>
                            <th class="p-3 text-left font-medium">{{ __('decisions.index.case_number') }}</th>
                            <th class="p-3 text-left font-medium">{{ __('decisions.index.parties') }}</th>
                            <th class="p-3 text-left font-medium">{{ __('decisions.index.judge') }}</th>
                            <th class="p-3 text-left font-medium">{{ __('decisions.index.decision_date') }}</th>
                            <th class="p-3 text-left font-medium">{{ __('app.Status') }}</th>
                            <th class="p-3 text-left font-medium w-36">{{ __('decisions.index.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php
                        $rowOffset = method_exists($decisions, 'firstItem') ? (($decisions->firstItem() ?? 1) - 1) : 0;
                        @endphp
                        @forelse($decisions as $i => $decision)
                        <tr class="hover:bg-gray-50">
                            <td class="p-3 align-top text-gray-500">{{ $rowOffset + $i + 1 }}</td>
                            <td class="p-3 align-top">
                                <div class="text-sm font-semibold text-gray-900">
                                    @if($decision->courtCase)
                                    <a href="{{ route('cases.show', $decision->courtCase->id) }}" class="hover:underline text-blue-600" target="_blank">
                                        {{ $decision->case_number }}
                                    </a>
                                    @else
                                    {{ $decision->case_number ?: '—' }}
                                    @endif
                                </div>
                                @if($decision->case_filed_date)
                                <div class="text-[10px] text-gray-500 mt-1">
                                    {{ __('decisions.index.filed', ['date' => \App\Support\EthiopianDate::format($decision->case_filed_date)]) }}
                                </div>
                                @endif
                            </td>
                            <td class="p-3 align-top">
                                <div class="text-[11px] text-gray-500">
                                    <span class="uppercase tracking-wide text-[10px] text-gray-400">{{ __('decisions.index.applicant') }}</span>
                                    <div class="text-gray-900 mt-0.5">{{ $decision->applicant_full_name ?: ($decision->courtCase?->title ?: '—') }}</div>
                                </div>
                                <div class="text-[11px] text-gray-500 mt-2">
                                    <span class="uppercase tracking-wide text-[10px] text-gray-400">{{ __('decisions.index.respondent') }}</span>
                                    <div class="text-gray-900 mt-0.5">{{ $decision->respondent_full_name ?: '—' }}</div>
                                </div>
                            </td>
                            <td class="p-3 align-top text-gray-700">
                                {{ $decision->reviewing_admin_user_name
                                    ?: ($decision->reviewer?->name
                                    ?: (($decision->panel_judges[1]['admin_user_name'] ?? null)
                                    ?: ($decision->courtCase?->judge?->name ?: '—'))) }}
                            </td>
                            <td class="p-3 align-top text-gray-700">
                                {{ \App\Support\EthiopianDate::format($decision->decision_date, fallback: '—') }}
                            </td>
                            <td class="p-3 align-top">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold uppercase tracking-wide
                                    {{ $decision->status === 'published' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-700 border border-gray-200' }}">
                                    {{ $decision->status ? __('decisions.status.' . $decision->status) : '—' }}
                                </span>
                            </td>
                            @php
                            $middleJudgeId = $decision->panel_judges[1]['admin_user_id'] ?? null;
                            $isMiddleJudge = $middleJudgeId && auth()->id() === $middleJudgeId;
                            @endphp
                            <td class="p-3 align-top">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('decisions.show', $decision) }}"
                                        class="px-2.5 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs border border-gray-200">
                                        {{ __('decisions.index.view') }}
                                    </a>
                                    @if($canEditDecision && $isMiddleJudge && $decision->status !== 'published')
                                    <a href="{{ route('decisions.edit', $decision) }}"
                                        class="px-2.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs">
                                        {{ __('decisions.index.edit') }}
                                    </a>
                                    @endif
                                    @if($canDeleteDecision && $isMiddleJudge && $decision->status !== 'published')
                                    <form id="delForm-{{ $decision->id }}" action="{{ route('decisions.destroy', $decision) }}" method="POST" class="hidden">
                                        @csrf @method('DELETE')
                                    </form>
                                    <button @click.prevent="document.getElementById('delForm-{{ $decision->id }}')?.submit()"
                                        class="px-2.5 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs">
                                        {{ __('decisions.index.delete') }}
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500">
                                <svg class="h-10 w-10 mx-auto mb-2 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h6" />
                                </svg>
                                {{ __('decisions.index.empty') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists(($decisions ?? null), 'links'))
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between text-xs text-gray-600">
                <div>
                    {{ __('decisions.index.pagination', [
                        'from' => $decisions->firstItem() ?? 0,
                        'to' => $decisions->lastItem() ?? 0,
                        'total' => $decisions->total() ?? $decisions->count()
                    ]) }}
                </div>
                <div>{{ $decisions->withQueryString()->links() }}</div>
            </div>
            @endif
        </div>
    </div>
</x-admin-layout>

