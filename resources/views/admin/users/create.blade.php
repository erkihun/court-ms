<x-admin-layout title="New User">
    @section('page_header','New User')

    <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data"
        class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @csrf

        {{-- Left: Basic info --}}
        <div class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-gray-700">First Name</label>
                    <input name="first_name" value="{{ old('first_name') }}"
                        class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('first_name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-700">Middle Name</label>
                    <input name="middle_name" value="{{ old('middle_name') }}"
                        class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('middle_name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-700">Last Name</label>
                    <input name="last_name" value="{{ old('last_name') }}"
                        class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('last_name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-700">Email</label>
                <input name="email" type="email" value="{{ old('email') }}"
                    class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                @error('email') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-700">Gender</label>
                    <select name="gender" class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                        <option value="">Select</option>
                        <option value="male" @selected(old('gender')==='male')>Male</option>
                        <option value="female" @selected(old('gender')==='female')>Female</option>
                        <option value="other" @selected(old('gender')==='other')>Other</option>
                    </select>
                    @error('gender') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-700">Date of Birth</label>
                    <input name="date_of_birth" type="date" value="{{ old('date_of_birth') }}"
                        class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('date_of_birth') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-700">National ID</label>
                    <input name="national_id" value="{{ old('national_id') }}"
                        inputmode="text" maxlength="19"
                        placeholder="XXXX XXXX XXXX XXXX"
                        pattern="[A-Za-z0-9]{4}\s[A-Za-z0-9]{4}\s[A-Za-z0-9]{4}\s[A-Za-z0-9]{4}"
                        oninput="
                            const cleaned = this.value.replace(/[^A-Za-z0-9]/g, '').slice(0,16).toUpperCase();
                            this.value = (cleaned.match(/.{1,4}/g) || []).join(' ');
                        "
                        class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                        aria-describedby="national_id_help">
                    @error('national_id') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    <p id="national_id_help" class="text-xs text-gray-500 mt-1">Enter 16 letters or digits; spaces are added automatically.</p>
                </div>
                <div>
                    <label class="block text-sm text-gray-700">Position</label>
                    <input name="position" value="{{ old('position') }}"
                        class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('position') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-700">Phone</label>
                    <input name="phone" value="{{ old('phone') }}"
                        class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('phone') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-700">Address</label>
                    <input name="address" value="{{ old('address') }}"
                        class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('address') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                The user will be created with the default password
                <span class="font-semibold">{{ config('auth.default_user_password', 'ChangeMe123!') }}</span>
                and must change it at first login.
            </div>

            <div>
                <label class="block text-sm text-gray-700">Status</label>
                <select name="status"
                    class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    <option value="active" @selected(old('status')==='active' )>Active</option>
                    <option value="inactive" @selected(old('status')==='inactive' )>Inactive</option>
                </select>
                @error('status') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Right: Roles + Files --}}
        <div class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm space-y-6">
            <div>
                <h3 class="text-sm text-gray-700 mb-3">Assign Roles</h3>
                <div class="space-y-2 max-h-80 overflow-auto pr-2">
                    @foreach($roles as $role)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                            class="rounded border-gray-300 bg-white text-blue-600 focus:ring-blue-500"
                            @checked(collect(old('roles',[]))->contains($role->id))>
                        <span class="text-gray-700">{{ $role->name }}</span>
                    </label>
                    @endforeach
                </div>
                @error('roles') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Avatar upload --}}
            <div class="p-4 rounded-xl border border-gray-200 bg-gray-50" x-data="{preview: null}">
                <label class="block text-sm text-gray-700 mb-2">Avatar (JPG/PNG/WebP, ≤ 2MB)</label>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-gray-100 border border-gray-300 overflow-hidden grid place-items-center">
                        <img x-show="preview" :src="preview" class="w-full h-full object-cover" alt="Avatar preview">
                        <span x-show="!preview" class="text-xs text-gray-500">No image</span>
                    </div>
                    <input type="file" name="avatar" accept="image/*"
                        @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                        class="text-sm file:mr-3 file:px-3 file:py-1.5 file:rounded file:bg-white file:border file:border-gray-300 file:text-gray-700 hover:file:bg-gray-50">
                </div>
                @error('avatar') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                {{-- Signature upload --}}
                <div class="p-4 rounded-xl border border-gray-200 bg-gray-50" x-data="{preview: null}">
                    <label class="block text-sm text-gray-700 mb-2">Signature (PNG/WebP preferred, ≤ 2MB)</label>
                    <div class="space-y-2">
                        <img x-show="preview" :src="preview" class="max-h-20 border border-gray-200" alt="Signature preview">
                        <div x-show="!preview" class="text-xs text-gray-500">No signature uploaded.</div>
                        <input type="file" name="signature" accept="image/png,image/webp,image/jpeg"
                            @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                            class="text-sm file:mr-3 file:px-3 file:py-1.5 file:rounded file:bg-white file:border file:border-gray-300 file:text-gray-700 hover:file:bg-gray-50">
                    </div>
                    @error('signature') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Stamp upload --}}
                <div class="p-4 rounded-xl border border-gray-200 bg-gray-50" x-data="{previewStamp: null}">
                    <label class="block text-sm text-gray-700 mb-2">Stamp (PNG/WebP preferred, ≤ 2MB)</label>
                    <div class="space-y-2">
                        <img x-show="previewStamp" :src="previewStamp" class="max-h-20 border border-gray-200" alt="Stamp preview">
                        <div x-show="!previewStamp" class="text-xs text-gray-500">No stamp uploaded.</div>
                        <input type="file" name="stamp" accept="image/png,image/webp,image/jpeg"
                            @change="previewStamp = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                            class="text-sm file:mr-3 file:px-3 file:py-1.5 file:rounded file:bg-white file:border file:border-gray-300 file:text-gray-700 hover:file:bg-gray-50">
                    </div>
                    @error('stamp') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <button class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white">Create User</button>
                <a href="{{ route('users.index') }}" class="ml-2 px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">Cancel</a>
            </div>
        </div>
    </form>
</x-admin-layout>
