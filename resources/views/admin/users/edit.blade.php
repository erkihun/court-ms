<x-admin-layout title="{{ __('users.edit_user_title') }}">
    @section('page_header', __('users.edit_user_title'))

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

    <div class="enterprise-page">
        <div class="enterprise-header">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="enterprise-title">{{ __('users.edit_profile_title') }}</h1>
                    <p class="enterprise-subtitle">{{ __('users.edit_profile_subtitle') }}</p>
                </div>
                <a href="{{ route('users.show',$user) }}" class="btn btn-outline">{{ __('users.view_profile') }}</a>
            </div>
        </div>

        <form method="POST" action="{{ route('users.update',$user) }}" enctype="multipart/form-data" class="admin-form-shell">
            @csrf @method('PATCH')

            <div class="admin-form-main space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('users.first_name') }}</label>
                        <input name="first_name" value="{{ old('first_name', $firstName) }}" class="ui-input mt-1">
                        @error('first_name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('users.middle_name') }}</label>
                        <input name="middle_name" value="{{ old('middle_name', $middleName) }}" class="ui-input mt-1">
                        @error('middle_name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('users.last_name') }}</label>
                        <input name="last_name" value="{{ old('last_name', $lastName) }}" class="ui-input mt-1">
                        @error('last_name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('users.email') }}</label>
                    <input name="email" type="email" value="{{ old('email',$user->email) }}" class="ui-input mt-1">
                    @error('email') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('users.gender') }}</label>
                        <select name="gender" class="ui-select mt-1">
                            <option value="">{{ __('users.select') }}</option>
                            <option value="male" @selected(old('gender',$user->gender)==='male')>{{ __('users.male') }}</option>
                            <option value="female" @selected(old('gender',$user->gender)==='female')>{{ __('users.female') }}</option>
                        </select>
                        @error('gender') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('users.date_of_birth') }}</label>
                        <x-eth-date-input name="date_of_birth" :value="old('date_of_birth', $user->date_of_birth?->format('Y-m-d'))" class="mt-1" />
                        @error('date_of_birth') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('users.national_id') }}</label>
                        <input name="national_id" value="{{ old('national_id',$user->national_id) }}"
                            inputmode="text" maxlength="19"
                            placeholder="XXXX XXXX XXXX XXXX"
                            pattern="[A-Za-z0-9]{4}\s[A-Za-z0-9]{4}\s[A-Za-z0-9]{4}\s[A-Za-z0-9]{4}"
                            oninput="
                                const cleaned = this.value.replace(/[^A-Za-z0-9]/g, '').slice(0,16).toUpperCase();
                                this.value = (cleaned.match(/.{1,4}/g) || []).join(' ');
                            "
                            class="ui-input mt-1"
                            aria-describedby="national_id_help_edit">
                        @error('national_id') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        <p id="national_id_help_edit" class="text-xs text-slate-500 mt-1">{{ __('users.national_id_help') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('users.position') }}</label>
                        <input name="position" value="{{ old('position',$user->position) }}" class="ui-input mt-1">
                        @error('position') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('users.phone') }}</label>
                        <input name="phone" value="{{ old('phone',$user->phone) }}" class="ui-input mt-1">
                        @error('phone') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('users.address') }}</label>
                        <input name="address" value="{{ old('address',$user->address) }}" class="ui-input mt-1">
                        @error('address') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('users.status') }}</label>
                    <select name="status" class="ui-select mt-1">
                        <option value="active" @selected(old('status',$user->status)==='active')>{{ __('users.active') }}</option>
                        <option value="inactive" @selected(old('status',$user->status)==='inactive')>{{ __('users.inactive') }}</option>
                    </select>
                    @error('status') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('users.new_password_optional') }}</label>
                        <input name="password" type="password" class="ui-input mt-1">
                        @error('password') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('users.confirm_password') }}</label>
                        <input name="password_confirmation" type="password" class="ui-input mt-1">
                    </div>
                </div>
            </div>

            <div class="admin-form-side">
                <div class="admin-panel space-y-4">
                    <h3 class="text-sm font-semibold text-slate-900">{{ __('users.roles') }}</h3>
                    <div class="space-y-2 max-h-80 overflow-auto pr-1">
                        @foreach($roles as $role)
                        <label class="admin-checkbox-card">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="ui-checkbox"
                                @checked($user->roles->contains('id',$role->id))>
                            <span>{{ $role->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="admin-panel space-y-4">
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50" x-data="{preview:null}">
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('users.avatar') }}</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-white border border-slate-300 overflow-hidden grid place-items-center">
                                @if($user->avatar_url)
                                <img x-show="!preview" src="{{ $user->avatar_url }}" class="w-full h-full object-cover" alt="{{ __('users.avatar') }}">
                                @else
                                <span x-show="!preview" class="text-xs text-slate-500">{{ __('users.no_image') }}</span>
                                @endif
                                <img x-show="preview" :src="preview" class="w-full h-full object-cover" alt="{{ __('users.avatar_preview') }}">
                            </div>
                            <input type="file" name="avatar" accept="image/*"
                                @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                                class="enterprise-file-input">
                        </div>
                        @error('avatar') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4">
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50" x-data="{preview:null}">
                            <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('users.signature') }}</label>
                            @if($user->signature_url)
                            <img x-show="!preview" src="{{ $user->signature_url }}" class="max-h-20 border border-slate-200" alt="{{ __('users.signature') }}">
                            @else
                            <div x-show="!preview" class="text-xs text-slate-500 mb-2">{{ __('users.no_signature') }}</div>
                            @endif
                            <img x-show="preview" :src="preview" class="max-h-20 border border-slate-200" alt="{{ __('users.signature_preview') }}">
                            <input type="file" name="signature" accept="image/png,image/webp,image/jpeg"
                                @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                                class="enterprise-file-input mt-2">
                            @error('signature') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50" x-data="{previewStamp:null}">
                            <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('users.stamp') }}</label>
                            @if($user->stamp_url ?? false)
                            <img x-show="!previewStamp" src="{{ $user->stamp_url }}" class="max-h-20 border border-slate-200" alt="{{ __('users.stamp') }}">
                            @else
                            <div x-show="!previewStamp" class="text-xs text-slate-500 mb-2">{{ __('users.no_stamp') }}</div>
                            @endif
                            <img x-show="previewStamp" :src="previewStamp" class="max-h-20 border border-slate-200" alt="{{ __('users.stamp_preview') }}">
                            <input type="file" name="stamp" accept="image/png,image/webp,image/jpeg"
                                @change="previewStamp = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                                class="enterprise-file-input mt-2">
                            @error('stamp') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="enterprise-actions justify-end">
                    <a href="{{ route('users.index') }}" class="btn btn-outline">{{ __('users.cancel') }}</a>
                    <button class="btn btn-primary">{{ __('users.save') }}</button>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>
