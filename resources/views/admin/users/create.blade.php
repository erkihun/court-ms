<x-admin-layout title="New User">
    @section('page_header','New User')

    <div class="enterprise-page">
        <div class="enterprise-header">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="enterprise-title">Create User Account</h1>
                    <p class="enterprise-subtitle">Set identity, contact details, role assignments, and profile assets.</p>
                </div>
                <a href="{{ route('users.index') }}" class="btn btn-outline">Back to Users</a>
            </div>
        </div>

        <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" class="admin-form-shell">
            @csrf

            <div class="admin-form-main space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">First Name</label>
                        <input name="first_name" value="{{ old('first_name') }}" class="ui-input mt-1">
                        @error('first_name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Middle Name</label>
                        <input name="middle_name" value="{{ old('middle_name') }}" class="ui-input mt-1">
                        @error('middle_name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Last Name</label>
                        <input name="last_name" value="{{ old('last_name') }}" class="ui-input mt-1">
                        @error('last_name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Email</label>
                    <input name="email" type="email" value="{{ old('email') }}" class="ui-input mt-1">
                    @error('email') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Gender</label>
                        <select name="gender" class="ui-select mt-1">
                            <option value="">Select</option>
                            <option value="male" @selected(old('gender')==='male')>Male</option>
                            <option value="female" @selected(old('gender')==='female')>Female</option>
                        </select>
                        @error('gender') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Date of Birth</label>
                        <input name="date_of_birth" type="date" value="{{ old('date_of_birth') }}" class="ui-input mt-1">
                        @error('date_of_birth') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">National ID</label>
                        <input name="national_id" value="{{ old('national_id') }}"
                            inputmode="text" maxlength="19"
                            placeholder="XXXX XXXX XXXX XXXX"
                            pattern="[A-Za-z0-9]{4}\s[A-Za-z0-9]{4}\s[A-Za-z0-9]{4}\s[A-Za-z0-9]{4}"
                            oninput="
                                const cleaned = this.value.replace(/[^A-Za-z0-9]/g, '').slice(0,16).toUpperCase();
                                this.value = (cleaned.match(/.{1,4}/g) || []).join(' ');
                            "
                            class="ui-input mt-1"
                            aria-describedby="national_id_help">
                        @error('national_id') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        <p id="national_id_help" class="text-xs text-slate-500 mt-1">Enter 16 letters or digits; spaces are added automatically.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Position</label>
                        <input name="position" value="{{ old('position') }}" class="ui-input mt-1">
                        @error('position') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Phone</label>
                        <input name="phone" value="{{ old('phone') }}" class="ui-input mt-1">
                        @error('phone') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Address</label>
                        <input name="address" value="{{ old('address') }}" class="ui-input mt-1">
                        @error('address') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="admin-panel-muted text-sm text-amber-800 border-amber-200 bg-amber-50">
                    The user will be created with the default password
                    <span class="font-semibold">{{ config('auth.default_user_password', 'ChangeMe123!') }}</span>
                    and must change it at first login.
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Status</label>
                    <select name="status" class="ui-select mt-1">
                        <option value="active" @selected(old('status')==='active' )>Active</option>
                        <option value="inactive" @selected(old('status')==='inactive' )>Inactive</option>
                    </select>
                    @error('status') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="admin-form-side">
                <div class="admin-panel space-y-4">
                    <h3 class="text-sm font-semibold text-slate-900">Assign Roles</h3>
                    <div class="space-y-2 max-h-80 overflow-auto pr-1">
                        @foreach($roles as $role)
                        <label class="admin-checkbox-card">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="ui-checkbox"
                                @checked(collect(old('roles',[]))->contains($role->id))>
                            <span>{{ $role->name }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('roles') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="admin-panel space-y-4">
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50" x-data="{preview: null}">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Avatar</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-white border border-slate-300 overflow-hidden grid place-items-center">
                                <img x-show="preview" :src="preview" class="w-full h-full object-cover" alt="Avatar preview">
                                <span x-show="!preview" class="text-xs text-slate-500">No image</span>
                            </div>
                            <input type="file" name="avatar" accept="image/*"
                                @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                                class="enterprise-file-input">
                        </div>
                        @error('avatar') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4">
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50" x-data="{preview: null}">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Signature</label>
                            <img x-show="preview" :src="preview" class="max-h-20 border border-slate-200" alt="Signature preview">
                            <div x-show="!preview" class="text-xs text-slate-500 mb-2">No signature uploaded.</div>
                            <input type="file" name="signature" accept="image/png,image/webp,image/jpeg"
                                @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                                class="enterprise-file-input">
                            @error('signature') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50" x-data="{previewStamp: null}">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Stamp</label>
                            <img x-show="previewStamp" :src="previewStamp" class="max-h-20 border border-slate-200" alt="Stamp preview">
                            <div x-show="!previewStamp" class="text-xs text-slate-500 mb-2">No stamp uploaded.</div>
                            <input type="file" name="stamp" accept="image/png,image/webp,image/jpeg"
                                @change="previewStamp = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                                class="enterprise-file-input">
                            @error('stamp') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="enterprise-actions justify-end">
                    <a href="{{ route('users.index') }}" class="btn btn-outline">Cancel</a>
                    <button class="btn btn-primary">Create User</button>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>
