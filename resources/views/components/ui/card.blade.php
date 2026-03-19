@props([
    'padding' => 'md',
])

@php
    $paddings = [
        'none' => '',
        'sm' => 'p-4',
        'md' => 'p-5 sm:p-6',
        'lg' => 'p-6 sm:p-7',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'ui-card']) }}>
    @isset($header)
        <div class="ui-card-header">
            {{ $header }}
        </div>
    @endisset

    <div class="{{ $paddings[$padding] ?? $paddings['md'] }}">
        {{ $slot }}
    </div>
</div>
