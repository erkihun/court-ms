@props([
    'type' => 'info',
])

@php
    $types = [
        'success' => 'ui-alert ui-alert-success',
        'error' => 'ui-alert ui-alert-error',
        'warning' => 'ui-alert border border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200',
        'info' => 'ui-alert ui-alert-info',
    ];
@endphp

<div {{ $attributes->merge(['class' => $types[$type] ?? $types['info']]) }}>
    {{ $slot }}
</div>
