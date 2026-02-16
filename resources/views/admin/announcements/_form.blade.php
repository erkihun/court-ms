@props(['announcement' => null])

<div class="space-y-6">
    <div>
        <label class="block text-sm font-semibold text-gray-700">{{ __('announcements.form_title_label') }}</label>
        <input type="text"
            name="title"
            value="{{ old('title', $announcement?->title) }}"
            class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            maxlength="255"
        >
        @error('title')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700">{{ __('announcements.form_content_label') }}</label>
        <textarea id="announcement-content" name="content"
            rows="8"
            class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
        >{{ old('content', $announcement?->content) }}</textarea>
        @error('content')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700">{{ __('announcements.form_status_label') }}</label>
        <select name="status"
            class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            @foreach(['active' => __('announcements.status_active'), 'inactive' => __('announcements.status_inactive')] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $announcement?->status ?? 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
