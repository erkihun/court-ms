@php
$permission = $permission ?? null;
$locales = config('app.locales', [config('app.locale', 'en')]);
$primaryLocale = config('app.locale', reset($locales));
$resolveValue = function (string $field, string $locale) use ($permission, $primaryLocale) {
    $key = "{$field}_translations";
    $oldValue = old("{$key}.{$locale}");

    if ($oldValue !== null) {
        return $oldValue;
    }

    if ($permission) {
        $translations = $permission->{$key} ?? [];

        if (isset($translations[$locale])) {
            return $translations[$locale];
        }

        if ($locale === $primaryLocale) {
            return $permission->{$field} ?? '';
        }
    }

    return '';
};

@endphp

@foreach(['label' => __('permissions.fields.label'), 'description' => __('permissions.fields.description')] as $field => $fieldLabel)
<div class="space-y-3">
    <div class="text-sm font-medium text-gray-700">
        <span>{{ $fieldLabel }}</span>
    </div>
    <div class="space-y-3">
        @foreach($locales as $locale)
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xs uppercase tracking-wide text-gray-500">
                <span>{{ strtoupper($locale) }}</span>
                @if($locale === $primaryLocale)
                <span class="text-gray-400">{{ __('permissions.fields.primary_locale_notice') }}</span>
                @endif
            </div>
            <input name="{{ $field }}_translations[{{ $locale }}]"
                value="{{ $resolveValue($field, $locale) }}"
                placeholder="{{ $fieldLabel }} ({{ strtoupper($locale) }})"
                class="w-full rounded-lg border px-3 py-2 text-sm">
            @error("{$field}_translations.{$locale}") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        @endforeach
    </div>
</div>
@endforeach
