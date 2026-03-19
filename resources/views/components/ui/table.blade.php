@props([
    'sticky' => true,
])

<div {{ $attributes->merge(['class' => 'ui-table-wrap']) }}>
    <div class="ui-table-scroll">
        <table class="ui-table">
            <thead @class(['sticky top-0 z-10' => $sticky])>
                {{ $head }}
            </thead>
            <tbody>
                {{ $body }}
            </tbody>
        </table>
    </div>

    @isset($footer)
        <div class="border-t border-[var(--border)] bg-[var(--surface-soft)] px-4 py-3">
            {{ $footer }}
        </div>
    @endisset
</div>
