@props([
    'id' => null,
    'value' => '04:00',
    'dataAttr' => null, // e.g. 'hearing-time' → renders data-hearing-time on the hidden field
])

@php
    $fieldId = $id ?? ('eth-time-' . \Illuminate\Support\Str::random(5));
@endphp

{{-- Ethiopian time picker (Amharic locale). Writes a 24h "HH:MM" Ethiopian
     wall-clock value into a hidden field that keeps the original id/attrs, so
     the existing hearing conversion JS reads it exactly as before. --}}
<div
    x-data="ethTimePicker({ value: @js($value) })"
    @click.outside="open = false"
    @keydown.escape="open = false"
    class="relative"
>
    <input type="hidden" id="{{ $fieldId }}" x-ref="hidden"
        @if($dataAttr) data-{{ $dataAttr }} @endif
        {{ $attributes->only(['name']) }}
        value="{{ $value }}">

    <button type="button"
        @click="open = !open"
        class="w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-lg bg-white border border-gray-300 text-gray-900 text-left focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-150">
        <span x-text="display"></span>
        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </button>

    <div x-show="open" x-cloak x-transition.opacity
        class="absolute z-50 mt-1 w-56 max-h-64 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl p-1.5">
        <template x-for="slot in slots" :key="slot.value">
            <button type="button"
                @click="choose(slot.value)"
                class="w-full text-left px-3 py-1.5 rounded-md text-sm hover:bg-emerald-50"
                :class="selected === slot.value ? 'bg-emerald-600 text-white font-semibold hover:bg-emerald-600' : 'text-gray-700'"
                x-text="slot.label"></button>
        </template>
    </div>
</div>
