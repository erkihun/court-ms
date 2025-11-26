<x-admin-layout title="Edit User">
    @section('page_header','Edit User')

    <form method="POST" action="{{ route('users.update',$user) }}" enctype="multipart/form-data"
        class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @csrf @method('PATCH')

        {{-- Left: Basics --}}
        <div class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm space-y-4">
            <div>
                <label class="block text-sm text-gray-700">Name</label>
                <input name="name" value="{{ old('name',$user->name) }}"
                    class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                @error('name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-700">Email</label>
                <input name="email" type="email" value="{{ old('email',$user->email) }}"
                    class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                @error('email') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-700">Status</label>
                <select name="status"
                    class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    <option value="active" @selected(old('status',$user->status)==='active')>Active</option>
                    <option value="inactive" @selected(old('status',$user->status)==='inactive')>Inactive</option>
                </select>
                @error('status') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-700">New Password (optional)</label>
                    <input name="password" type="password"
                        class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('password') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-700">Confirm Password</label>
                    <input name="password_confirmation" type="password"
                        class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                </div>
            </div>
        </div>

        {{-- Right: Roles + Files --}}
        <div class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm space-y-6">
            <div>
                <h3 class="text-sm text-gray-700 mb-3">Roles</h3>
                <div class="space-y-2 max-h-80 overflow-auto pr-2">
                    @foreach($roles as $role)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                            @checked($user->roles->contains('id',$role->id))
                        class="rounded border-gray-300 bg-white text-blue-600 focus:ring-blue-500">
                        <span class="text-gray-700">{{ $role->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Avatar --}}
            <div class="p-4 rounded-xl border border-gray-200 bg-gray-50" x-data="{preview:null}">
                <label class="block text-sm text-gray-700 mb-2">Avatar (JPG/PNG/WebP, ≤ 2MB)</label>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-gray-100 border border-gray-300 overflow-hidden grid place-items-center">
                        @if($user->avatar_url)
                        <img x-show="!preview" src="{{ $user->avatar_url }}" class="w-full h-full object-cover" alt="Avatar">
                        @else
                        <span x-show="!preview" class="text-xs text-gray-500">No image</span>
                        @endif
                        <img x-show="preview" :src="preview" class="w-full h-full object-cover" alt="Avatar preview">
                    </div>
                    <input type="file" name="avatar" accept="image/*"
                        @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                        class="text-sm file:mr-3 file:px-3 file:py-1.5 file:rounded file:bg-white file:border file:border-gray-300 file:text-gray-700 hover:file:bg-gray-50">
                </div>
                @error('avatar') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Signature --}}
            <div class="p-4 rounded-xl border border-gray-200 bg-gray-50" x-data="{preview:null}">
                <label class="block text-sm text-gray-700 mb-2">Signature (PNG/WebP preferred, ≤ 2MB)</label>
                <div class="space-y-2">
                    @if($user->signature_url)
                    <img x-show="!preview" src="{{ $user->signature_url }}" class="max-h-20 border border-gray-200" alt="Signature">
                    @else
                    <div x-show="!preview" class="text-xs text-gray-500">No signature uploaded.</div>
                    @endif
                    <img x-show="preview" :src="preview" class="max-h-20 border border-gray-200" alt="Signature preview">
                    <input type="file" name="signature" accept="image/png,image/webp,image/jpeg"
                        @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                        class="text-sm file:mr-3 file:px-3 file:py-1.5 file:rounded file:bg-white file:border file:border-gray-300 file:text-gray-700 hover:file:bg-gray-50">
                </div>
                @error('signature') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <button class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white">Save</button>
                <a href="{{ route('users.index') }}" class="ml-2 px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">Cancel</a>
            </div>
        </div>
    </form>
</x-admin-layout>