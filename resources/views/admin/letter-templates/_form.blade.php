{{-- resources/views/letter-templates/_form.blade.php --}}
@php
$placeholders = $template->placeholders ?? [];
if (is_array($placeholders)) {
$placeholders = implode(', ', $placeholders);
}
$placeholderValue = old('placeholders', $placeholders);
@endphp

<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('letters.templates.form.title') }}<span class="text-red-500">*</span></label>
        <input type="text" name="title" value="{{ old('title', $template->title) }}"
            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-600 focus:border-blue-600" required>
        @error('title')
        <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
        @enderror
    </div>
    <select id="category-select" name="category"
        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-600 focus:border-blue-600">

        <option value="">Select Category</option>

        @foreach($caseTypes as $type)
        <option
            value="{{ $type->name }}"
            {{ old('category', $template->category) === $type->name ? 'selected' : '' }}>
            {{ $type->name }}
        </option>
        @endforeach
    </select>




    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('letters.templates.form.placeholders') }}</label>
        <textarea name="placeholders" rows="2"
            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
            placeholder="{{ __('letters.templates.form.placeholders_placeholder') }}">{{ $placeholderValue }}</textarea>
        <p class="text-xs text-gray-500 mt-1">{{ __('letters.templates.form.placeholders_example') }}</p>
        <p class="text-xs text-gray-500 italic">
            {!! __('letters.templates.form.placeholders_help') !!}
        </p>
        @error('placeholders')
        <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
        @enderror
    </div>
    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('letters.templates.form.header_image') }}</label>
            <input type="file" name="header_image" accept="image/*"
                class="mt-1 w-full text-sm text-gray-700 border border-gray-300 rounded-lg file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700">
            @if($template->header_image_path)
            <p class="text-xs text-gray-500 mt-1">{{ __('letters.templates.form.header_current', ['file' => basename($template->header_image_path)]) }}</p>
            @endif
            @if($template->header_image_path)
            <div class="mt-2 space-y-1">
                <img src="{{ asset('storage/' . $template->header_image_path) }}" class="h-20 border rounded" alt="Header preview">
                <label class="flex items-center gap-2 text-xs text-gray-600">
                    <input type="checkbox" name="remove_header_image" value="1">
                    {{ __('letters.templates.form.remove_header') }}
                </label>
            </div>
            @endif
            @error('header_image')
            <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('letters.templates.form.footer_image') }}</label>
            <input type="file" name="footer_image" accept="image/*"
                class="mt-1 w-full text-sm text-gray-700 border border-gray-300 rounded-lg file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700">
            @if($template->footer_image_path)
            <p class="text-xs text-gray-500 mt-1">{{ __('letters.templates.form.footer_current', ['file' => basename($template->footer_image_path)]) }}</p>
            @endif
            @if($template->footer_image_path)
            <div class="mt-2 space-y-1">
                <img src="{{ asset('storage/' . $template->footer_image_path) }}" class="h-20 border rounded" alt="Footer preview">
                <label class="flex items-center gap-2 text-xs text-gray-600">
                    <input type="checkbox" name="remove_footer_image" value="1">
                    {{ __('letters.templates.form.remove_footer') }}
                </label>
            </div>
            @endif
            @error('footer_image')
            <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
            @enderror
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('letters.templates.form.body') }}<span class="text-red-500">*</span></label>
        <textarea name="body" rows="10"
            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600" required
            placeholder="{{ __('letters.templates.form.body_placeholder') }}">{{ old('body', $template->body) }}</textarea>
        <p class="text-xs text-gray-500 italic">
            {{ __('letters.templates.form.body_help') }}
        </p>
        @error('body')
        <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
        @enderror
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('category-select');
        const prefixInput = document.querySelector('input[name="subject_prefix"]');

        categorySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const prifix = selectedOption.getAttribute('data-prifix') || "";
            prefixInput.value = prifix; // Set prefix field
        });
    });
</script>