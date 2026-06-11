@props([
    'name',
    'value' => null,
    'id' => null,
    'required' => false,
    'placeholder' => null,
    // Optional Alpine expression (evaluated in the parent scope) that, when
    // truthy, disables the trigger. e.g. disabled-when="!canEdit"
    'disabledWhen' => null,
])

@php
    // Normalise the incoming value to a Gregorian Y-m-d string for the hidden field.
    $gregValue = '';
    if ($value instanceof \DateTimeInterface) {
        $gregValue = $value->format('Y-m-d');
    } elseif (is_string($value) && trim($value) !== '') {
        try {
            $gregValue = \Illuminate\Support\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            $gregValue = '';
        }
    }

    $locale = app()->getLocale();
    $today = [
        'y' => (int) now()->format('Y'),
        'm' => (int) now()->format('n'),
        'd' => (int) now()->format('j'),
    ];
    $fieldId = $id ?? ('eth-date-' . \Illuminate\Support\Str::slug($name) . '-' . \Illuminate\Support\Str::random(4));
    $ph = $placeholder ?? ($locale === 'am' ? 'ቀን ይምረጡ' : 'Select date');
@endphp

<div
    x-data="ethDatePicker({
        locale: @js($locale),
        value: @js($gregValue),
        today: @js($today)
    })"
    @keydown.escape="open = false"
    @click.outside="open = false"
    class="relative"
>
    {{-- Real value submitted to the server: always Gregorian Y-m-d --}}
    <input type="hidden" name="{{ $name }}" :value="gregValue" @if($required) data-required="1" @endif>

    {{-- Visible trigger --}}
    <button
        type="button"
        id="{{ $fieldId }}"
        @click="open = !open; if (open) build()"
        @if($disabledWhen) x-bind:disabled="{{ $disabledWhen }}" @endif
        {{ $attributes->merge(['class' => 'ui-input flex items-center justify-between gap-2 text-left disabled:bg-slate-100 disabled:text-slate-500 disabled:cursor-not-allowed']) }}
    >
        <span x-text="display || @js($ph)" :class="display ? '' : 'text-slate-400'"></span>
        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00 2 2z"/>
        </svg>
    </button>

    {{-- Calendar popover --}}
    <div
        x-show="open"
        x-cloak
        x-transition.opacity
        class="absolute z-50 mt-1.5 w-72 rounded-xl border p-3 shadow-xl"
        style="background: var(--surface-strong); border-color: var(--border-strong);"
    >
        {{-- Header: month / nav --}}
        <div class="flex items-center justify-between mb-2">
            <div class="text-sm font-semibold" style="color: var(--text);">
                <span x-text="monthLabel"></span>
                <span x-text="yearLabel" class="text-[var(--text-subtle)]"></span>
            </div>
            <div class="flex items-center gap-1">
                <button type="button" @click="prev()" class="p-1.5 rounded-lg hover:bg-[var(--surface-soft)]" aria-label="Previous month">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" @click="next()" class="p-1.5 rounded-lg hover:bg-[var(--surface-soft)]" aria-label="Next month">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        {{-- Day-of-week headers --}}
        <div class="grid grid-cols-7 gap-0.5 mb-1">
            <template x-for="(dow, i) in dowHeaders" :key="i">
                <div class="text-center text-[11px] font-medium text-[var(--text-subtle)] py-1" x-text="dow"></div>
            </template>
        </div>

        {{-- Day cells --}}
        <div class="grid grid-cols-7 gap-0.5">
            <template x-for="cell in cells" :key="cell.key">
                <button
                    type="button"
                    @click="pick(cell)"
                    :disabled="!cell.cur"
                    class="h-8 rounded-lg text-sm transition-colors"
                    :class="{
                        'text-[var(--text-subtle)] opacity-40': !cell.cur,
                        'hover:bg-[var(--surface-soft)]': cell.cur,
                        'ring-1 ring-[var(--primary)] font-semibold': cell.today && !cell.selected,
                        'bg-[var(--primary)] text-white font-semibold': cell.selected,
                        'text-[var(--text)]': cell.cur && !cell.selected
                    }"
                    x-text="cell.d"
                ></button>
            </template>
        </div>

        {{-- Footer: clear / today --}}
        <div class="flex items-center justify-between mt-2 pt-2 border-t" style="border-color: var(--border);">
            <button type="button" @click="clear()" class="text-xs font-medium text-[var(--text-subtle)] hover:text-[var(--text)]">
                {{ $locale === 'am' ? 'አጽዳ' : 'Clear' }}
            </button>
            <button type="button" @click="goToday()" class="text-xs font-medium text-[var(--primary)] hover:opacity-80">
                {{ $locale === 'am' ? 'ዛሬ' : 'Today' }}
            </button>
        </div>
    </div>
</div>
