<x-admin-layout title="{{ __('Edit Evaluation') }}">
@section('page_header', __('Performance Evaluations'))

<div class="mx-auto max-w-3xl space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Edit Evaluation</h2>
            <p class="text-sm text-gray-500">{{ $evaluation->evaluatedUser?->name }} &middot; {{ $evaluation->period_start->format('M d') }} – {{ $evaluation->period_end->format('M d, Y') }}</p>
        </div>
        <a href="{{ route('performance-evaluations.show', $evaluation) }}"
           class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
    </div>

    <form method="POST" action="{{ route('performance-evaluations.update', $evaluation) }}" class="space-y-6">
        @csrf @method('PATCH')

        {{-- Basic info --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
            <h3 class="font-semibold text-gray-800">Evaluation Details</h3>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Staff Member <span class="text-red-500">*</span></label>
                    <select name="evaluated_user_id"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                        required>
                        @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('evaluated_user_id', $evaluation->evaluated_user_id) == $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Period Type</label>
                    <select name="period_type"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                        required>
                        @foreach(['monthly','quarterly','annual'] as $p)
                        <option value="{{ $p }}" @selected(old('period_type', $evaluation->period_type) === $p)>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Period Start</label>
                    <input type="date" name="period_start" value="{{ old('period_start', $evaluation->period_start->format('Y-m-d')) }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Period End</label>
                    <input type="date" name="period_end" value="{{ old('period_end', $evaluation->period_end->format('Y-m-d')) }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">General Notes</label>
                    <textarea name="notes" rows="3"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">{{ old('notes', $evaluation->notes) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Criteria scoring --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Criteria Scores</h3>
                <span class="text-xs text-gray-400">0 = Poor · 5 = Average · 10 = Excellent</span>
            </div>

            @foreach($criteria as $i => $criterion)
            @php
                $existing = $existingScores->get($criterion->id);
                $currentScore = old("scores.$i.score", $existing?->score ?? 5);
                $currentComment = old("scores.$i.comment", $existing?->comment ?? '');
            @endphp
            <input type="hidden" name="scores[{{ $i }}][criterion_id]" value="{{ $criterion->id }}">

            <div class="rounded-lg border border-gray-100 bg-slate-50 p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full border border-slate-300 bg-white px-2 py-0.5 text-[10px] font-semibold uppercase text-slate-500">{{ $criterion->category }}</span>
                            <span class="text-xs text-slate-400">Weight: {{ $criterion->weight }}%</span>
                        </div>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ $criterion->name }}</p>
                        @if($criterion->description)
                        <p class="text-xs text-gray-400 mt-0.5">{{ $criterion->description }}</p>
                        @endif
                    </div>
                    <div class="shrink-0 text-center">
                        <div id="score-display-{{ $criterion->id }}"
                             class="text-3xl font-extrabold tabular-nums w-12
                             {{ $currentScore >= 8 ? 'text-emerald-600' : ($currentScore >= 6 ? 'text-blue-600' : ($currentScore >= 4 ? 'text-amber-600' : 'text-red-500')) }}">
                            {{ $currentScore }}
                        </div>
                        <div class="text-[10px] text-gray-400">/10</div>
                    </div>
                </div>

                <div>
                    <input type="range" name="scores[{{ $i }}][score]"
                           min="0" max="10" step="1" value="{{ $currentScore }}"
                           class="w-full accent-blue-600 cursor-pointer"
                           oninput="document.getElementById('score-display-{{ $criterion->id }}').textContent = this.value; updateSliderColor(this);">
                    <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                        @for($n = 0; $n <= 10; $n++)<span>{{ $n }}</span>@endfor
                    </div>
                </div>

                <textarea name="scores[{{ $i }}][comment]" rows="1"
                          placeholder="Optional comment..."
                          class="w-full rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 focus:ring-2 focus:ring-blue-400 resize-none">{{ $currentComment }}</textarea>
            </div>
            @endforeach
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex gap-2">
                <a href="{{ route('performance-evaluations.show', $evaluation) }}"
                   class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Cancel</a>
                <form method="POST" action="{{ route('performance-evaluations.destroy', $evaluation) }}"
                      onsubmit="return confirm('Delete this evaluation?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-100">
                        Delete
                    </button>
                </form>
            </div>
            <div class="flex gap-2">
                <button type="submit" name="action" value="draft"
                    class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-100">
                    Save as Draft
                </button>
                <button type="submit" name="action" value="submitted"
                    class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">
                    Submit for Review
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function updateSliderColor(input) {
    const v = parseInt(input.value);
    const display = input.closest('.rounded-lg').querySelector('[id^="score-display-"]');
    if (!display) return;
    ['text-emerald-600','text-blue-600','text-amber-600','text-red-500'].forEach(c => display.classList.remove(c));
    if (v >= 8)      display.classList.add('text-emerald-600');
    else if (v >= 6) display.classList.add('text-blue-600');
    else if (v >= 4) display.classList.add('text-amber-600');
    else             display.classList.add('text-red-500');
}
</script>
</x-admin-layout>
