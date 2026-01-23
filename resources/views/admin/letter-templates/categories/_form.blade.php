{{-- resources/views/admin/letter-templates/categories/_form.blade.php --}}
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Category name<span class="text-red-500">*</span></label>
        <input type="text" name="name" value="{{ old('name', $category->name) }}"
            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-600 focus:border-blue-600" required>
        @error('name')
        <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" rows="2"
            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
            placeholder="Optional short description">{{ old('description', $category->description) }}</textarea>
        @error('description')
        <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
        @enderror
    </div>
</div>
