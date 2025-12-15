<x-admin-layout title="{{ __('Edit Case Type') }}">
    @section('page_header', __('Edit Case Type'))

    {{-- Modern Card Container --}}
    <div class="max-w-7xl p-6 rounded-xl border border-gray-200 bg-white shadow-lg">
        <form method="POST" action="{{ route('case-types.update', $type->id) }}" class="space-y-6">
            @csrf @method('PATCH')

            {{-- Name Field --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Name') }} <span class="text-rose-600">*</span></label>
                <input id="name" name="name" value="{{ old('name', $type->name) }}" required
                    {{-- Updated Input Styling: rounded-lg, shadow-sm, cleaner focus ring --}}
                    class="mt-1 w-full px-4 py-2 rounded-lg bg-gray-50 border border-gray-300 shadow-sm text-gray-900 focus:ring-indigo-500 focus:border-indigo-500 transition">
                @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Action Buttons --}}
            <div class="pt-2 flex gap-3">
                {{-- Cancel Button (Secondary style) --}}
                <a href="{{ route('case-types.index') }}"
                    class="inline-flex items-center h-10 px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium transition shadow-sm">
                    {{ __('Cancel') }}
                </a>
                {{-- Update Button (Primary style) --}}
                <button type="submit"
                    class="inline-flex items-center h-10 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition shadow-md">
                    {{ __('Update') }}
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
