{{-- resources/views/admin/decision-templates/_form.blade.php --}}
@php
$placeholders = $template->placeholders ?? [];
if (is_array($placeholders)) {
$placeholders = implode(', ', $placeholders);
}
$placeholderValue = old('placeholders', $placeholders);
@endphp

<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('decision_templates.form.title') }}<span class="text-red-500">*</span></label>
        <input type="text" name="title" value="{{ old('title', $template->title) }}"
            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-600 focus:border-blue-600" required>
        @error('title')
        <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('decision_templates.form.category') }}</label>
        <input type="text" name="category" value="{{ old('category', $template->category) }}"
            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
            placeholder="{{ __('decision_templates.form.category_placeholder') }}">
        @error('category')
        <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
            <input type="hidden" name="is_default" value="0">
            <input type="checkbox" name="is_default" value="1"
                @checked(old('is_default', $template->is_default))
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            {{ __('decision_templates.form.is_default') }}
        </label>
        <p class="text-xs text-gray-500 mt-1">{{ __('decision_templates.form.is_default_help') }}</p>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('decision_templates.form.placeholders') }}</label>
        <textarea name="placeholders" rows="2"
            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
            placeholder="{{ __('decision_templates.form.placeholders_placeholder') }}">{{ $placeholderValue }}</textarea>
        <p class="text-xs text-gray-500 mt-1">{{ __('decision_templates.form.placeholders_example') }}</p>
        <p class="text-xs text-gray-500 italic">
            {!! __('decision_templates.form.placeholders_help') !!}
        </p>
        @error('placeholders')
        <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('decision_templates.form.header_image') }}</label>
            <input type="file" name="header_image" accept="image/*"
                class="mt-1 w-full text-sm text-gray-700 border border-gray-300 rounded-lg file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700">
            @if($template->header_image_path)
            <p class="text-xs text-gray-500 mt-1">{{ __('decision_templates.form.header_current', ['file' => basename($template->header_image_path)]) }}</p>
            <div class="mt-2 space-y-1">
                <img src="{{ asset('storage/' . $template->header_image_path) }}" class="h-20 border rounded" alt="Header preview">
                <label class="flex items-center gap-2 text-xs text-gray-600">
                    <input type="checkbox" name="remove_header_image" value="1">
                    {{ __('decision_templates.form.remove_header') }}
                </label>
            </div>
            @endif
            @error('header_image')
            <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('decision_templates.form.footer_image') }}</label>
            <input type="file" name="footer_image" accept="image/*"
                class="mt-1 w-full text-sm text-gray-700 border border-gray-300 rounded-lg file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700">
            @if($template->footer_image_path)
            <p class="text-xs text-gray-500 mt-1">{{ __('decision_templates.form.footer_current', ['file' => basename($template->footer_image_path)]) }}</p>
            <div class="mt-2 space-y-1">
                <img src="{{ asset('storage/' . $template->footer_image_path) }}" class="h-20 border rounded" alt="Footer preview">
                <label class="flex items-center gap-2 text-xs text-gray-600">
                    <input type="checkbox" name="remove_footer_image" value="1">
                    {{ __('decision_templates.form.remove_footer') }}
                </label>
            </div>
            @endif
            @error('footer_image')
            <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('decision_templates.form.body') }}<span class="text-red-500">*</span></label>
        <textarea id="decision-template-body" name="body" rows="12"
            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600" required
            placeholder="{{ __('decision_templates.form.body_placeholder') }}">{{ old('body', $template->body) }}</textarea>
        <p class="text-xs text-gray-500 italic">
            {{ __('decision_templates.form.body_help') }}
        </p>
        @error('body')
        <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
        @enderror
    </div>
</div>

@push('scripts')
<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof tinymce === 'undefined') return;

        const TINY_BASE = "{{ asset('vendor/tinymce') }}";

        tinymce.init({
            base_url: TINY_BASE,
            suffix: '.min',
            license_key: 'gpl',
            branding: false,
            promotion: false,
            menubar: true,
            toolbar_mode: 'wrap',
            toolbar_sticky: false,
            plugins: 'lists link table code image advlist charmap fullscreen wordcount',
            toolbar: [
                'undo redo | fontfamily fontsize | bold italic underline strikethrough removeformat',
                '| forecolor backcolor | alignleft aligncenter alignright alignjustify',
                '| numlist bullist outdent indent | fullscreen code'
            ].join(' '),
            forced_root_block: 'p',
            content_style: `
                table { width: 100%; border-collapse: collapse }
                td, th { border: 1px solid #ddd; padding: 4px }
                body { font-size: 14px; line-height: 1.5 }
            `,
            resize: false,
            statusbar: true,
            selector: '#decision-template-body',
            height: 400,
        });

        // Sync TinyMCE content back to the textarea before submitting.
        const editorEl = document.getElementById('decision-template-body');
        const form = editorEl?.closest('form');
        form?.addEventListener('submit', () => {
            if (tinymce.get('decision-template-body')) {
                tinymce.triggerSave();
            }
        });
    });
</script>
@endpush
