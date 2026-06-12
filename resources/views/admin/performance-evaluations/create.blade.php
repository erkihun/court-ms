<x-admin-layout title="{{ __('performance.new_title') }}">
@section('page_header', __('performance.title'))

<div class="mx-auto max-w-3xl space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-gray-900">{{ __('performance.new_evaluation') }}</h2>
            <p class="text-sm text-gray-500">{{ __('performance.subtitle') }}</p>
        </div>
        <a href="{{ route('performance-evaluations.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700">&larr; {{ __('performance.actions.back_to_list') }}</a>
    </div>

    <form method="POST" action="{{ route('performance-evaluations.store') }}" class="space-y-6">
        @csrf

        {{-- Basic info --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
            <h3 class="font-semibold text-gray-800">{{ __('performance.details') }}</h3>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('performance.fields.staff_member') }} <span class="text-red-500">*</span></label>
                    <select name="evaluated_user_id"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 @error('evaluated_user_id') border-red-400 @enderror"
                        required>
                        <option value="">{{ __('performance.placeholders.select_member') }}</option>
                        @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('evaluated_user_id') == $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                    @error('evaluated_user_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('performance.fields.period_type') }} <span class="text-red-500">*</span></label>
                    <select name="period_type"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                        required>
                        @foreach(['monthly','quarterly','annual'] as $p)
                        <option value="{{ $p }}" @selected(old('period_type','monthly') === $p)>{{ __("performance.periods.$p") }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('performance.fields.period_start') }} <span class="text-red-500">*</span></label>
                    <x-eth-date-input name="period_start" :value="old('period_start')" required />
                    @error('period_start')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('performance.fields.period_end') }} <span class="text-red-500">*</span></label>
                    <x-eth-date-input name="period_end" :value="old('period_end')" required />
                    @error('period_end')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('performance.fields.general_notes') }}</label>
                    <textarea name="notes" rows="3"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                        placeholder="{{ __('performance.placeholders.general_notes') }}">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Criteria scoring --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">{{ __('performance.criteria_scores') }}</h3>
                <span class="text-xs text-gray-400">{{ __('performance.score_hint') }}</span>
            </div>

            @foreach($criteria as $i => $criterion)
            @php
                $old = old("scores.$i.score", 5);
                $category = $criterion->category;
                $categoryLabel = \Illuminate\Support\Facades\Lang::has("performance.categories.$category")
                    ? __("performance.categories.$category")
                    : $category;
            @endphp
            <input type="hidden" name="scores[{{ $i }}][criterion_id]" value="{{ $criterion->id }}">

            <div class="rounded-lg border border-gray-100 bg-slate-50 p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full border border-slate-300 bg-white px-2 py-0.5 text-[10px] font-semibold uppercase text-slate-500">
                                {{ $categoryLabel }}
                            </span>
                            <span class="text-xs text-slate-400">{{ __('performance.fields.weight') }}: {{ $criterion->weight }}%</span>
                        </div>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ $criterion->local_name }}</p>
                        @if($criterion->description)
                        <p class="text-xs text-gray-400 mt-0.5">{{ $criterion->description }}</p>
                        @endif
                    </div>
                    <div class="shrink-0 text-center">
                        <div id="score-display-{{ $criterion->id }}"
                             class="text-3xl font-extrabold text-blue-600 tabular-nums w-12">{{ $old }}</div>
                        <div class="text-[10px] text-gray-400">/10</div>
                    </div>
                </div>

                {{-- Slider --}}
                <div>
                    <input type="range"
                           name="scores[{{ $i }}][score]"
                           min="0" max="10" step="1"
                           value="{{ $old }}"
                           class="w-full accent-blue-600 cursor-pointer"
                           oninput="document.getElementById('score-display-{{ $criterion->id }}').textContent = this.value;
                                    updateSliderColor(this);">
                    <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                        @for($n = 0; $n <= 10; $n++)
                        <span>{{ $n }}</span>
                        @endfor
                    </div>
                </div>

                {{-- Comment --}}
                <textarea name="scores[{{ $i }}][comment]"
                          rows="1"
                          placeholder="{{ __('performance.placeholders.criterion_comment') }}"
                          class="w-full rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 focus:ring-2 focus:ring-blue-400 resize-none">{{ old("scores.$i.comment") }}</textarea>
            </div>
            @endforeach
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <a href="{{ route('performance-evaluations.index') }}"
               class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">{{ __('performance.actions.cancel') }}</a>
            <div class="flex gap-2">
                <button type="submit" name="action" value="draft"
                    class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-100">
                    {{ __('performance.actions.save_draft') }}
                </button>
                <button type="submit" name="action" value="submitted"
                    class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">
                    {{ __('performance.actions.submit_review') }}
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
    display.className = display.className.replace(/text-\w+-600/, '');
    if (v >= 8)      display.classList.add('text-emerald-600');
    else if (v >= 6) display.classList.add('text-blue-600');
    else if (v >= 4) display.classList.add('text-amber-600');
    else             display.classList.add('text-red-500');
}
document.querySelectorAll('input[type=range]').forEach(updateSliderColor);
</script>
</x-admin-layout>
