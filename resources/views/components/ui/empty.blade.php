@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'ui-empty']) }}>
    <div class="ui-empty-icon">
        {{ $icon ?? '' }}
    </div>

    @if($title)
        <h3 class="mt-4 text-sm font-semibold text-[var(--text)]">{{ $title }}</h3>
    @endif

    @if($description)
        <p class="mt-2 text-sm text-[var(--text-subtle)]">{{ $description }}</p>
    @endif

    @if(trim((string) $slot) !== '')
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>
