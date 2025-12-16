{{-- resources/views/admin/decisions/index.blade.php --}}
<x-admin-layout title="{{ __('decisions.index.title') }}">
    @section('page_header', __('decisions.index.title'))

    @php
    $canCreateDecision = function_exists('userHasPermission')
        ? userHasPermission('decision.create')
        : (auth()->user()?->hasPermission('decision.create') ?? false);
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

    <div class="space-y-4">
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
                        placeholder="{{ __('decisions.index.title') }} / Case #"
                        class="px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500">
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
                @if($canCreateDecision)
                <a href="{{ route('decisions.create') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-700">
                    <span class="text-xs">+</span>
                    {{ __('decisions.index.new') }}
                </a>
                @endif
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-700 border-b border-gray-200">
                        <tr>
                            <th class="p-3 text-left font-medium">{{ __('decisions.fields.name') }}</th>
                            <th class="p-3 text-left font-medium">Case &amp; Parties</th>
                            <th class="p-3 text-left font-medium">Decision Date</th>
                            <th class="p-3 text-left font-medium">{{ __('app.Status') }}</th>
                            <th class="p-3 text-left font-medium">Updated</th>
                            <th class="p-3 text-left font-medium w-36">{{ __('decisions.index.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($decisions as $decision)
                        <tr class="hover:bg-gray-50">
                            <td class="p-3 align-top">
                                <div class="font-medium text-gray-900">{{ $decision->name }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $decision->description ?: '—' }}
                                </div>
                            </td>
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
                                <div class="mt-2 grid grid-cols-2 gap-3 text-[11px] text-gray-500">
                                    <div>
                                        <span class="uppercase tracking-wide text-[10px] text-gray-400">Applicant</span>
                                        <div class="text-gray-900 mt-0.5">{{ $decision->applicant_full_name ?: '—' }}</div>
                                    </div>
                                    <div>
                                        <span class="uppercase tracking-wide text-[10px] text-gray-400">Respondent</span>
                                        <div class="text-gray-900 mt-0.5">{{ $decision->respondent_full_name ?: '—' }}</div>
                                    </div>
                                </div>
                                @if($decision->case_filed_date)
                                <div class="text-[10px] text-gray-500 mt-1">
                                    Filed {{ \App\Support\EthiopianDate::format($decision->case_filed_date) }}
                                </div>
                                @endif
                            </td>
                            <td class="p-3 align-top text-gray-700">
                                {{ \App\Support\EthiopianDate::format($decision->decision_date, fallback: '—') }}
                            </td>
                            <td class="p-3 align-top">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold uppercase tracking-wide
                                    {{ $decision->status === 'active' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : ($decision->status === 'draft' ? 'bg-gray-100 text-gray-700 border border-gray-200' : 'bg-orange-100 text-orange-700 border border-orange-200') }}">
                                    {{ ucfirst($decision->status ?: '—') }}
                                </span>
                            </td>
                            <td class="p-3 align-top text-xs text-gray-500">
                                {{ $decision->updated_at ? $decision->updated_at->diffForHumans() : '—' }}
                            </td>
                            @php
                            $middleJudgeId = $decision->panel_judges[1]['admin_user_id'] ?? null;
                            $isMiddleJudge = $middleJudgeId && auth()->id() === $middleJudgeId;
                            @endphp
                            <td class="p-3 align-top">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('decisions.show', $decision) }}"
                                        class="px-2.5 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs border border-gray-200">
                                        View
                                    </a>
                                    @if($canEditDecision && $isMiddleJudge)
                                    <a href="{{ route('decisions.edit', $decision) }}"
                                        class="px-2.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs">
                                        {{ __('decisions.index.edit') }}
                                    </a>
                                    @endif
                                    @if($canDeleteDecision && $isMiddleJudge)
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
                            <td colspan="6" class="p-8 text-center text-gray-500">
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
