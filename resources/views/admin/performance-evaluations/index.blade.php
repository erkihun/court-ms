<x-admin-layout title="{{ __('Performance Evaluations') }}">
@section('page_header', __('Performance Evaluations'))

<div class="space-y-6">

    {{-- Stats row --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
        @foreach([
            ['label' => 'Total',      'value' => $stats['total'],     'color' => 'bg-slate-50 border-slate-200 text-slate-700'],
            ['label' => 'Draft',      'value' => $stats['draft'],     'color' => 'bg-amber-50 border-amber-200 text-amber-700'],
            ['label' => 'Submitted',  'value' => $stats['submitted'], 'color' => 'bg-blue-50 border-blue-200 text-blue-700'],
            ['label' => 'Reviewed',   'value' => $stats['reviewed'],  'color' => 'bg-emerald-50 border-emerald-200 text-emerald-700'],
            ['label' => 'Avg Score',  'value' => $stats['avg_score'].'%', 'color' => 'bg-violet-50 border-violet-200 text-violet-700'],
        ] as $stat)
        <div class="rounded-xl border {{ $stat['color'] }} p-4 text-center">
            <div class="text-2xl font-extrabold">{{ $stat['value'] }}</div>
            <div class="mt-0.5 text-xs font-semibold uppercase tracking-wide opacity-70">{{ $stat['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Filter + action bar --}}
    <div class="flex flex-wrap items-end gap-3">
        <form method="GET" action="{{ route('performance-evaluations.index') }}" class="flex flex-wrap gap-2 flex-1">
            <select name="status" onchange="this.form.submit()"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">All statuses</option>
                @foreach(['draft','submitted','reviewed'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="user_id" onchange="this.form.submit()"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">All members</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
            <select name="period_type" onchange="this.form.submit()"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">All periods</option>
                @foreach(['monthly','quarterly','annual'] as $p)
                <option value="{{ $p }}" @selected(request('period_type') === $p)>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
            @if(request()->hasAny(['status','user_id','period_type']))
            <a href="{{ route('performance-evaluations.index') }}"
               class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50">Clear</a>
            @endif
        </form>
        <a href="{{ route('performance-evaluations.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Evaluation
        </a>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3 text-left">Member</th>
                    <th class="px-4 py-3 text-left">Period</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-center">Score</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-left">Evaluator</th>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($evaluations as $ev)
            @php
                $scoreColor = match(true) {
                    $ev->overall_score >= 85 => 'text-emerald-600',
                    $ev->overall_score >= 70 => 'text-blue-600',
                    $ev->overall_score >= 50 => 'text-amber-600',
                    default                  => 'text-red-600',
                };
                $statusColor = match($ev->status) {
                    'submitted' => 'bg-blue-50 text-blue-700 border border-blue-200',
                    'reviewed'  => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                    default     => 'bg-amber-50 text-amber-700 border border-amber-200',
                };
            @endphp
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2.5">
                        @if($ev->evaluatedUser?->avatar_path)
                        <img src="{{ asset('storage/'.$ev->evaluatedUser->avatar_path) }}"
                             class="h-8 w-8 rounded-full object-cover" alt="">
                        @else
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">
                            {{ strtoupper(substr($ev->evaluatedUser?->name ?? '?', 0, 1)) }}
                        </div>
                        @endif
                        <span class="font-medium text-gray-900">{{ $ev->evaluatedUser?->name ?? '—' }}</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-gray-600">
                    {{ $ev->period_start->format('M d') }} – {{ $ev->period_end->format('M d, Y') }}
                </td>
                <td class="px-4 py-3">
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-600">
                        {{ ucfirst($ev->period_type) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="text-lg font-extrabold {{ $scoreColor }}">{{ number_format($ev->overall_score, 1) }}%</span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusColor }}">
                        {{ ucfirst($ev->status) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-600">{{ $ev->evaluator?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $ev->created_at->format('M d, Y') }}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('performance-evaluations.show', $ev) }}"
                           class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">View</a>
                        @if($ev->status !== 'reviewed')
                        <a href="{{ route('performance-evaluations.edit', $ev) }}"
                           class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100">Edit</a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                    <div class="flex flex-col items-center gap-2">
                        <svg class="h-10 w-10 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm font-medium">No evaluations found</p>
                        <a href="{{ route('performance-evaluations.create') }}" class="text-blue-600 hover:underline text-xs">Create the first one</a>
                    </div>
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $evaluations->links() }}
</div>
</x-admin-layout>
