@csrf
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('about.form_title_label') }}</label>
        <input type="text" name="title" value="{{ old('title', $about->title ?? '') }}"
            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
        @error('title')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('about.form_slug_label') }}</label>
        <input type="text" name="slug" value="{{ old('slug', $about->slug ?? '') }}"
            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
        @error('slug')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('about.form_body_label') }}</label>
        <textarea id="about-body" name="body" rows="10"
            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">{{ old('body', $about->body ?? '') }}</textarea>
        @error('body')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="flex items-center space-x-2">
        <input type="checkbox" name="is_published" value="1" id="is_published"
            {{ old('is_published', $about->is_published ?? false) ? 'checked' : '' }}
            class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
        <label for="is_published" class="text-sm text-gray-700">{{ __('about.form_published_label') }}</label>
    </div>
</div>
