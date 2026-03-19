{{-- resources/views/permissions/edit.blade.php --}}
<x-admin-layout title="{{ __('permissions.edit.title') }}">
    @section('page_header', __('permissions.edit.title'))

    <div class="enterprise-page max-w-3xl">
        <div class="enterprise-header">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">{{ __('permissions.edit.title') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('permissions.fields.name') }}</p>
            </div>
            <a href="{{ route('permissions.index') }}" class="btn btn-outline">{{ __('permissions.edit.back_button') }}</a>
        </div>
        <form method="POST" action="{{ route('permissions.update',$permission) }}" class="admin-panel space-y-5">
            @csrf @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-gray-700">
                    {{ __('permissions.fields.name') }}
                    <span class="text-red-600">*</span>
                </label>
                <input name="name" value="{{ old('name', $permission->name) }}" class="ui-input mt-1" required>
                <p class="text-xs text-gray-500 mt-1">{{ __('permissions.create.name_hint') }}</p>
                @error('name') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            @include('admin.permissions._translations', ['permission' => $permission])

            <div class="pt-2 flex gap-2">
                <button class="btn btn-primary">{{ __('permissions.edit.save_button') }}</button>
                <a href="{{ route('permissions.index') }}" class="btn btn-outline">{{ __('permissions.edit.back_button') }}</a>
            </div>
        </form>
    </div>
</x-admin-layout>


