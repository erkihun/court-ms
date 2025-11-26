{{-- resources/views/permissions/create.blade.php --}}
<x-admin-layout title="New Permission">
    @section('page_header','New Permission')

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('permissions.store') }}" class="space-y-4 rounded-xl border bg-white p-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700">Name <span class="text-red-600">*</span></label>
                <input name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-lg border px-3 py-2" required>
                <p class="text-xs text-gray-500 mt-1">Use <code>alpha-dash</code> (e.g. <em>cases.view</em>).</p>
                @error('name') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Label</label>
                <input name="label" value="{{ old('label') }}" class="mt-1 w-full rounded-lg border px-3 py-2">
                @error('label') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <input name="description" value="{{ old('description') }}" class="mt-1 w-full rounded-lg border px-3 py-2">
                @error('description') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="pt-2 flex gap-2">
                <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Create</button>
                <a href="{{ route('permissions.index') }}" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300">Cancel</a>
            </div>
        </form>
    </div>
</x-admin-layout>