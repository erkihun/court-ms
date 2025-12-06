<x-admin-layout title="Edit User">
    @section('page_header','Edit User')

    <form method="POST" action="{{ route('users.update',$user) }}" enctype="multipart/form-data"
        class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @csrf @method('PATCH')

        {{-- Left: Basics --}}
        <div class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm space-y-4">
            @php
            $nameParts = preg_split('/\s+/', trim($user->name ?? ''), 3) ?: [];
            $firstName = $nameParts[0] ?? '';
            if (count($nameParts) >= 3) {
                $middleName = $nameParts[1] ?? '';
                $lastName = $nameParts[2] ?? '';
            } elseif (count($nameParts) === 2) {
                $middleName = '';
                $lastName = $nameParts[1] ?? '';
            } else {
                $middleName = '';
                $lastName = '';
            }
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-gray-700">First Name</label>
                    <input name="first_name" value="{{ old('first_name', $firstName) }}"
                        class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('first_name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-700">Middle Name</label>
                    <input name="middle_name" value="{{ old('middle_name', $middleName) }}"
                        class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('middle_name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-700">Last Name</label>
                    <input name="last_name" value="{{ old('last_name', $lastName) }}"
                        class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('last_name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="block text-sm text-gray-700">Email</label>
                <input name="email" type="email" value="{{ old('email',$user->email) }}"
                    class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                @error('email') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-700">Gender</label>
                    <select name="gender" class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                        <option value="">Select</option>
                        <option value="male" @selected(old('gender',$user->gender)==='male')>Male</option>
                        <option value="female" @selected(old('gender',$user->gender)==='female')>Female</option>
                        <option value="other" @selected(old('gender',$user->gender)==='other')>Other</option>
                    </select>
                    @error('gender') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-700">Date of Birth</label>
                    <input name="date_of_birth" type="date" value="{{ old('date_of_birth',$user->date_of_birth?->format('Y-m-d')) }}"
                        class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('date_of_birth') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-700">National ID</label>
                    <input name="national_id" value="{{ old('national_id',$user->national_id) }}"
                        inputmode="text" maxlength="19"
                        placeholder="XXXX XXXX XXXX XXXX"
                        pattern="[A-Za-z0-9]{4}\s[A-Za-z0-9]{4}\s[A-Za-z0-9]{4}\s[A-Za-z0-9]{4}"
                        oninput="
                            const cleaned = this.value.replace(/[^A-Za-z0-9]/g, '').slice(0,16).toUpperCase();
                            this.value = (cleaned.match(/.{1,4}/g) || []).join(' ');
                        "
                        class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                        aria-describedby="national_id_help_edit">
                    @error('national_id') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    <p id="national_id_help_edit" class="text-xs text-gray-500 mt-1">Enter 16 letters or digits; spaces are added automatically.</p>
                </div>
                <div>
                    <label class="block text-sm text-gray-700">Position</label>
                    <input name="position" value="{{ old('position',$user->position) }}"
                        class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('position') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-700">Phone</label>
                    <input name="phone" value="{{ old('phone',$user->phone) }}"
                        class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('phone') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-700">Address</label>
                    <input name="address" value="{{ old('address',$user->address) }}"
                        class="mt-1 w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('address') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>
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

            <div class="grid gap-4 md:grid-cols-2">
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

                {{-- Stamp --}}
                <div class="p-4 rounded-xl border border-gray-200 bg-gray-50" x-data="{previewStamp:null}">
                    <label class="block text-sm text-gray-700 mb-2">Stamp (PNG/WebP preferred, ≤ 2MB)</label>
                    <div class="space-y-2">
                        @if($user->stamp_url ?? false)
                        <img x-show="!previewStamp" src="{{ $user->stamp_url }}" class="max-h-20 border border-gray-200" alt="Stamp">
                        @else
                        <div x-show="!previewStamp" class="text-xs text-gray-500">No stamp uploaded.</div>
                        @endif
                        <img x-show="previewStamp" :src="previewStamp" class="max-h-20 border border-gray-200" alt="Stamp preview">
                        <input type="file" name="stamp" accept="image/png,image/webp,image/jpeg"
                            @change="previewStamp = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                            class="text-sm file:mr-3 file:px-3 file:py-1.5 file:rounded file:bg-white file:border file:border-gray-300 file:text-gray-700 hover:file:bg-gray-50">
                    </div>
                    @error('stamp') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <button class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white">Save</button>
                <a href="{{ route('users.index') }}" class="ml-2 px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">Cancel</a>
            </div>
        </div>
    </form>
</x-admin-layout>
