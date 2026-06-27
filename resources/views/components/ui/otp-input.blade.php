@props([
    'name' => 'code',
    'length' => 6,
    'autofocus' => false,
    'accent' => 'orange', // focus ring/border color, e.g. 'orange' or 'indigo'
])

@php
    $focusClasses = match ($accent) {
        'blue' => 'focus:ring-blue-500 focus:border-blue-500',
        'indigo' => 'focus:ring-indigo-500 focus:border-indigo-500',
        default => 'focus:ring-orange-400 focus:border-orange-400',
    };
@endphp

{{-- One box per digit. Boxes are display-only; the joined value is mirrored into a single hidden input named {{ $name }} so the backend receives the full code unchanged. --}}
<div
    x-data="otpInput({{ (int) $length }}, {{ $autofocus ? 'true' : 'false' }})"
    x-init="init()"
    {{ $attributes->merge(['class' => 'flex items-center justify-center gap-2 sm:gap-3']) }}
    @paste.prevent="onPaste($event)"
>
    <input type="hidden" name="{{ $name }}" :value="value">

    <template x-for="(d, i) in digits" :key="i">
        <input
            type="text"
            inputmode="numeric"
            autocomplete="one-time-code"
            maxlength="1"
            x-ref="box"
            x-model="digits[i]"
            @input="onInput(i, $event)"
            @keydown="onKeydown(i, $event)"
            @focus="$event.target.select()"
            class="h-12 w-10 sm:h-14 sm:w-12 rounded-lg border text-center text-xl sm:text-2xl font-mono font-semibold text-slate-900
                   focus:outline-none focus:ring-2 {{ $focusClasses }}
                   @error($name) border-red-400 @else border-slate-300 @enderror"
        >
    </template>
</div>

@once
<script>
    function otpInput(length, autofocus) {
        return {
            length,
            autofocus,
            digits: Array.from({ length }, () => ''),
            get value() {
                return this.digits.join('');
            },
            init() {
                if (this.autofocus) {
                    this.$nextTick(() => this.$refs.box?.[0]?.focus());
                }
            },
            onInput(i, e) {
                // Keep only the last entered digit, strip non-numerics.
                let v = (e.target.value || '').replace(/\D/g, '');
                this.digits[i] = v ? v[v.length - 1] : '';
                if (this.digits[i] && i < this.length - 1) {
                    this.$refs.box[i + 1].focus();
                }
            },
            onKeydown(i, e) {
                if (e.key === 'Backspace') {
                    if (this.digits[i]) {
                        this.digits[i] = '';
                    } else if (i > 0) {
                        this.$refs.box[i - 1].focus();
                        this.digits[i - 1] = '';
                        e.preventDefault();
                    }
                } else if (e.key === 'ArrowLeft' && i > 0) {
                    this.$refs.box[i - 1].focus();
                    e.preventDefault();
                } else if (e.key === 'ArrowRight' && i < this.length - 1) {
                    this.$refs.box[i + 1].focus();
                    e.preventDefault();
                }
            },
            onPaste(e) {
                const text = (e.clipboardData || window.clipboardData).getData('text');
                const nums = (text || '').replace(/\D/g, '').slice(0, this.length).split('');
                if (!nums.length) return;
                for (let i = 0; i < this.length; i++) {
                    this.digits[i] = nums[i] || '';
                }
                const next = Math.min(nums.length, this.length - 1);
                this.$nextTick(() => this.$refs.box[next].focus());
            },
        };
    }
</script>
@endonce
