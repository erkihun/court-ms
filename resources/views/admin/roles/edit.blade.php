<x-admin-layout title="Edit Role">
    @section('page_header','Edit Role')

    <form method="POST" action="{{ route('roles.update',$role) }}" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @csrf @method('PATCH')

        <div class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm space-y-4">
            <div>
                <label class="block text-sm text-gray-700">Name</label>
                <input name="name" value="{{ old('name',$role->name) }}"
                    class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                @error('name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-700">Description</label>
                <input name="description" value="{{ old('description',$role->description) }}"
                    class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                @error('description') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm">
            <h3 class="text-sm text-gray-700 mb-3 font-medium">Permissions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-96 overflow-auto pr-2">
                @foreach($perms as $perm)
                <label class="flex items-center gap-2 text-gray-700">
                    <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                        @checked($role->permissions->contains('id',$perm->id))
                    class="rounded border-gray-300 bg-white text-blue-600 focus:ring-blue-500">
                    <span class="text-sm">{{ $perm->name }}</span>
                </label>
                @endforeach
            </div>

            <div class="mt-4">
                <button class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white">Save</button>
                <a href="{{ route('roles.index') }}" class="ml-2 px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">Cancel</a>
            </div>
        </div>
    </form>
</x-admin-layout>