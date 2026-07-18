<x-admin-layout title="{{ __('performance.detail_title') }}">
@section('page_header', __('performance.title'))

@php
    $ev = $evaluation;
    $scoreColor = match(true) {
        $ev->overall_score >= 85 => 'text-emerald-600',
        $ev->overall_score >= 70 => 'text-blue-600',
        $ev->overall_score >= 50 => 'text-amber-600',
        default                  => 'text-red-600',
    };
    $scoreLabel = match(true) {
        $ev->overall_score >= 85 => __('performance.score_labels.excellent'),
        $ev->overall_score >= 70 => __('performance.score_labels.good'),
        $ev->overall_score >= 50 => __('performance.score_labels.satisfactory'),
        default                  => __('performance.score_labels.needs_improvement'),
    };
    $statusColor = match($ev->status) {
        'submitted' => 'bg-blue-50 text-blue-700 border border-blue-200',
        'reviewed'  => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        default     => 'bg-amber-50 text-amber-700 border border-amber-200',
    };
@endphp

<div class="mx-auto max-w-3xl space-y-6">

    {{-- Top bar --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('performance-evaluations.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700">&larr; {{ __('performance.actions.back_to_list') }}</a>
        <div class="flex gap-2">
            @if($ev->status !== 'reviewed' && auth()->user()?->hasPermission('performance-evaluations.update'))
            <a href="{{ route('performance-evaluations.edit', $ev) }}"
               class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100">{{ __('performance.actions.edit') }}</a>
            @endif
        </div>
    </div>

    {{-- Hero card --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            {{-- Member info --}}
            <div class="flex items-center gap-4">
                @if($ev->evaluatedUser?->avatar_path)
                <img src="{{ asset('storage/'.$ev->evaluatedUser->avatar_path) }}"
                     class="h-16 w-16 rounded-full object-cover ring-4 ring-blue-100" alt="">
                @else
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-violet-600 text-2xl font-bold text-white">
                    {{ strtoupper(substr($ev->evaluatedUser?->name ?? '?', 0, 1)) }}
                </div>
                @endif
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $ev->evaluatedUser?->name ?? __('performance.not_available') }}</h2>
                    <p class="text-sm text-gray-500">
                        {{ \App\Support\EthiopianDate::smartFormat($ev->period_start, false, __('performance.not_available'), 'h:i A', 'M d, Y') }} -
                        {{ \App\Support\EthiopianDate::smartFormat($ev->period_end, false, __('performance.not_available'), 'h:i A', 'M d, Y') }}
                        &nbsp;&middot;&nbsp;
                        <span>{{ __("performance.periods.{$ev->period_type}") }}</span>
                    </p>
                    <span class="mt-1 inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusColor }}">
                        {{ __("performance.statuses.{$ev->status}") }}
                    </span>
                </div>
            </div>

            {{-- Score ring --}}
            <div class="flex flex-col items-center">
                <div class="text-5xl font-black {{ $scoreColor }} tabular-nums">
                    {{ number_format($ev->overall_score, 1) }}<span class="text-2xl text-gray-400">%</span>
                </div>
                <div class="mt-1 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $scoreLabel }}</div>
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="mt-5">
            <div class="h-3 w-full overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full transition-all duration-700
                    {{ $ev->overall_score >= 85 ? 'bg-emerald-500' : ($ev->overall_score >= 70 ? 'bg-blue-500' : ($ev->overall_score >= 50 ? 'bg-amber-400' : 'bg-red-500')) }}"
                    style="width: {{ min(100, $ev->overall_score) }}%"></div>
            </div>
        </div>

        {{-- Meta --}}
        <div class="mt-4 grid grid-cols-2 gap-2 text-xs text-gray-500 sm:grid-cols-4">
            <div><span class="font-medium text-gray-700">{{ __('performance.fields.evaluator') }}</span><br>{{ $ev->evaluator?->name ?? __('performance.not_available') }}</div>
            <div><span class="font-medium text-gray-700">{{ __('performance.fields.created') }}</span><br>{{ \App\Support\EthiopianDate::smartFormat($ev->created_at, false, __('performance.not_available'), 'h:i A', 'M d, Y') }}</div>
            @if($ev->reviewer)
            <div><span class="font-medium text-gray-700">{{ __('performance.fields.reviewed_by') }}</span><br>{{ $ev->reviewer->name }}</div>
            <div><span class="font-medium text-gray-700">{{ __('performance.fields.reviewed_at') }}</span><br>{{ \App\Support\EthiopianDate::smartFormat($ev->reviewed_at, false, __('performance.not_available'), 'h:i A', 'M d, Y') }}</div>
            @endif
        </div>

        @if($ev->notes)
        <div class="mt-4 rounded-lg border border-slate-100 bg-slate-50 p-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">{{ __('performance.fields.notes') }}</p>
            <p class="text-sm text-slate-700">{{ $ev->notes }}</p>
        </div>
        @endif
    </div>

    {{-- Criteria breakdown --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
        <h3 class="font-semibold text-gray-800">{{ __('performance.criteria_breakdown') }}</h3>

        @foreach($ev->scores->sortBy('criterion.sort_order') as $score)
        @php
            $pct = $score->score * 10;
            $barColor = match(true) {
                $score->score >= 8 => 'bg-emerald-500',
                $score->score >= 6 => 'bg-blue-500',
                $score->score >= 4 => 'bg-amber-400',
                default            => 'bg-red-500',
            };
            $textColor = match(true) {
                $score->score >= 8 => 'text-emerald-600',
                $score->score >= 6 => 'text-blue-600',
                $score->score >= 4 => 'text-amber-600',
                default            => 'text-red-600',
            };
            $category = $score->criterion?->category;
            $categoryLabel = $category && \Illuminate\Support\Facades\Lang::has("performance.categories.$category")
                ? __("performance.categories.$category")
                : $category;
        @endphp
        <div>
            <div class="flex items-center justify-between mb-1">
                <div>
                    <span class="text-sm font-semibold text-gray-800">{{ $score->criterion?->local_name }}</span>
                    <span class="ml-2 text-[10px] text-gray-400 uppercase">{{ $categoryLabel ?: __('performance.not_available') }} &middot; {{ $score->criterion?->weight }}%</span>
                </div>
                <span class="text-lg font-extrabold {{ $textColor }} tabular-nums">{{ $score->score }}<span class="text-xs text-gray-400">/10</span></span>
            </div>
            <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
            </div>
            @if($score->comment)
            <p class="mt-1 text-xs text-gray-500 italic">{{ $score->comment }}</p>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Reviewer notes --}}
    @if($ev->reviewer_notes)
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700 mb-1">{{ __('performance.fields.reviewer_notes') }}</p>
        <p class="text-sm text-emerald-900">{{ $ev->reviewer_notes }}</p>
    </div>
    @endif

    {{-- Review action (for submitted evaluations) --}}
    @if($ev->status === 'submitted' && auth()->user()?->hasPermission('performance-evaluations.review'))
    <div class="rounded-xl border border-blue-200 bg-blue-50 p-5">
        <h3 class="text-sm font-semibold text-blue-800 mb-3">{{ __('performance.review.title') }}</h3>
        <form method="POST" action="{{ route('performance-evaluations.review', $ev) }}" class="space-y-3">
            @csrf
            <textarea name="reviewer_notes" rows="3" placeholder="{{ __('performance.placeholders.reviewer_notes') }}"
                class="w-full rounded-lg border border-blue-200 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"></textarea>
            <div class="flex justify-end">
                <button type="submit"
                    class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700">
                    {{ __('performance.actions.approve_reviewed') }}
                </button>
            </div>
        </form>
    </div>
    @endif

</div>
</x-admin-layout>
