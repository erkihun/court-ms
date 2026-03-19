@props([
    'type' => 'info',
])

@php
    $types = [
        'success' => 'ui-badge ui-badge-emerald',
        'warning' => 'ui-badge ui-badge-amber',
        'danger' => 'ui-badge ui-badge-rose',
        'info' => 'ui-badge ui-badge-blue',
        'neutral' => 'ui-badge ui-badge-slate',
    ];
@endphp

<span {{ $attributes->merge(['class' => $types[$type] ?? $types['info']]) }}>
    {{ $slot }}
</span>
