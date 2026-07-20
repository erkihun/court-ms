<x-admin-layout title="{{ __('users.new_user_title') }}">
    @section('page_header', __('users.new_user_title'))

    <div class="enterprise-page">
        <div class="enterprise-header">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="enterprise-title">{{ __('users.create_account_title') }}</h1>
                    <p class="enterprise-subtitle">{{ __('users.create_account_subtitle') }}</p>
                </div>
                <a href="{{ route('users.index') }}" class="btn btn-outline">{{ __('users.back_to_users') }}</a>
            </div>
        </div>

        <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" class="admin-form-shell">
            @csrf

            <div class="admin-form-main space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('users.first_name') }}</label>
                        <input name="first_name" value="{{ old('first_name') }}" class="ui-input mt-1">
                        @error('first_name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('users.middle_name') }}</label>
                        <input name="middle_name" value="{{ old('middle_name') }}" class="ui-input mt-1">
                        @error('middle_name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('users.last_name') }}</label>
                        <input name="last_name" value="{{ old('last_name') }}" class="ui-input mt-1">
                        @error('last_name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('users.email') }}</label>
                    <input name="email" type="email" value="{{ old('email') }}" class="ui-input mt-1">
                    @error('email') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('users.gender') }}</label>
                        <select name="gender" class="ui-select mt-1">
                            <option value="">{{ __('users.select') }}</option>
                            <option value="male" @selected(old('gender')==='male')>{{ __('users.male') }}</option>
                            <option value="female" @selected(old('gender')==='female')>{{ __('users.female') }}</option>
                        </select>
                        @error('gender') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('users.date_of_birth') }}</label>
                        <x-eth-date-input name="date_of_birth" :value="old('date_of_birth')" class="mt-1" />
                        @error('date_of_birth') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('users.national_id') }}</label>
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
                        <p id="national_id_help" class="text-xs text-slate-500 mt-1">{{ __('users.national_id_help') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('users.position') }}</label>
                        <input name="position" value="{{ old('position') }}" class="ui-input mt-1">
                        @error('position') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('users.phone') }}</label>
                        <input name="phone" value="{{ old('phone') }}" class="ui-input mt-1">
                        @error('phone') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('users.address') }}</label>
                        <input name="address" value="{{ old('address') }}" class="ui-input mt-1">
                        @error('address') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="admin-panel-muted text-sm text-amber-800 border-amber-200 bg-amber-50">
                    {{ __('users.default_password_notice', ['password' => config('auth.default_user_password', 'ChangeMe123!')]) }}
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('users.status') }}</label>
                    <select name="status" class="ui-select mt-1">
                        <option value="active" @selected(old('status')==='active' )>{{ __('users.active') }}</option>
                        <option value="inactive" @selected(old('status')==='inactive' )>{{ __('users.inactive') }}</option>
                    </select>
                    @error('status') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="admin-form-side">
                <div class="admin-panel space-y-4">
                    <h3 class="text-sm font-semibold text-slate-900">{{ __('users.assign_roles') }}</h3>
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
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('users.avatar') }}</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-white border border-slate-300 overflow-hidden grid place-items-center">
                                <img x-show="preview" :src="preview" class="w-full h-full object-cover" alt="{{ __('users.avatar_preview') }}">
                                <span x-show="!preview" class="text-xs text-slate-500">{{ __('users.no_image') }}</span>
                            </div>
                            <input type="file" name="avatar" accept="image/*"
                                @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                                class="enterprise-file-input">
                        </div>
                        @error('avatar') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4">
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50" x-data="{preview: null}">
                            <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('users.signature') }}</label>
                            <img x-show="preview" :src="preview" class="max-h-20 border border-slate-200" alt="{{ __('users.signature_preview') }}">
                            <div x-show="!preview" class="text-xs text-slate-500 mb-2">{{ __('users.no_signature') }}</div>
                            <input type="file" name="signature" accept="image/png,image/webp,image/jpeg"
                                @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                                class="enterprise-file-input">
                            @error('signature') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50" x-data="{previewStamp: null}">
                            <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('users.stamp') }}</label>
                            <img x-show="previewStamp" :src="previewStamp" class="max-h-20 border border-slate-200" alt="{{ __('users.stamp_preview') }}">
                            <div x-show="!previewStamp" class="text-xs text-slate-500 mb-2">{{ __('users.no_stamp') }}</div>
                            <input type="file" name="stamp" accept="image/png,image/webp,image/jpeg"
                                @change="previewStamp = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                                class="enterprise-file-input">
                            @error('stamp') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="enterprise-actions justify-end">
                    <a href="{{ route('users.index') }}" class="btn btn-outline">{{ __('users.cancel') }}</a>
                    <button class="btn btn-primary">{{ __('users.create_user') }}</button>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>
