@props([
    'as' => 'div',
    'method' => null,
])

<{{ $as }} @if($method) method="{{ $method }}" @endif {{ $attributes->merge(['class' => 'ui-toolbar']) }}>
    {{ $slot }}
</{{ $as }}>
