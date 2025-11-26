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
        <label class="block text-sm font-medium text-gray-700">Title<span class="text-red-500">*</span></label>
        <input type="text" name="title" value="{{ old('title', $template->title) }}"
            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-600 focus:border-blue-600" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Category</label>
        <input type="text" name="category" value="{{ old('category', $template->category) }}"
            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
            placeholder="e.g. Hearing, Appeal">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Placeholders</label>
        <textarea name="placeholders" rows="2"
            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
            placeholder="Separate by comma or new line">{{ $placeholderValue }}</textarea>
        <p class="text-xs text-gray-500 mt-1">Example: {{ '{case_number}, {applicant_name}' }}</p>
    </div>
    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Header Image</label>
            <input type="file" name="header_image" accept="image/*"
                class="mt-1 w-full text-sm text-gray-700 border border-gray-300 rounded-lg file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700">
            @if($template->header_image_path)
            <div class="mt-2 space-y-1">
                <img src="{{ asset('storage/' . $template->header_image_path) }}" class="h-20 border rounded" alt="Header preview">
                <label class="flex items-center gap-2 text-xs text-gray-600">
                    <input type="checkbox" name="remove_header_image" value="1">
                    Remove current header
                </label>
            </div>
            @endif
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Footer Image</label>
            <input type="file" name="footer_image" accept="image/*"
                class="mt-1 w-full text-sm text-gray-700 border border-gray-300 rounded-lg file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700">
            @if($template->footer_image_path)
            <div class="mt-2 space-y-1">
                <img src="{{ asset('storage/' . $template->footer_image_path) }}" class="h-20 border rounded" alt="Footer preview">
                <label class="flex items-center gap-2 text-xs text-gray-600">
                    <input type="checkbox" name="remove_footer_image" value="1">
                    Remove current footer
                </label>
            </div>
            @endif
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Body<span class="text-red-500">*</span></label>
        <textarea name="body" rows="10"
            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600" required
            placeholder="Dear {applicant_name}, ...">{{ old('body', $template->body) }}</textarea>
    </div>
</div>
