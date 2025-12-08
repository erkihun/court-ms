<x-admin-layout title="{{ __('roles.edit.title') }}">
    @section('page_header', __('roles.edit.title'))

    <div class="max-w-6xl mx-auto space-y-6">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ __('roles.edit.title') }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ __('roles.edit.subtitle', ['name' => $role->name]) }}</p>
            </div>
            <a href="{{ route('roles.index') }}"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('roles.edit.back') }}
            </a>
        </div>

        <form method="POST" action="{{ route('roles.update',$role) }}" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @csrf @method('PATCH')

            {{-- Role meta --}}
            <div class="lg:col-span-1 space-y-4">
                <div class="p-5 rounded-xl border border-gray-200 bg-white shadow-sm space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('roles.fields.name') }}</label>
                        <input name="name" value="{{ old('name',$role->name) }}"
                            class="mt-1 w-full px-3 py-2 rounded-lg bg-white text-gray-900 border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-500 mt-1">{{ __('roles.edit.name_hint') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('roles.fields.description') }}</label>
                        <textarea name="description" rows="3"
                            class="mt-1 w-full px-3 py-2 rounded-lg bg-white text-gray-900 border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">{{ old('description',$role->description) }}</textarea>
                        @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-500 mt-1">{{ __('roles.edit.description_hint') }}</p>
                    </div>
                </div>

                <div class="p-5 rounded-xl border border-gray-200 bg-white shadow-sm space-y-3">
                    <h3 class="text-sm font-semibold text-gray-800">{{ __('roles.edit.summary_title') }}</h3>
                    <ul class="text-sm text-gray-700 space-y-2">
                        <li class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            {{ __('roles.edit.summary_permissions', ['count' => $perms->count()]) }}
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                            {{ __('roles.edit.summary_assigned', ['count' => $role->users_count ?? $role->users()->count()]) }}
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Permissions --}}
            <div class="lg:col-span-2 p-5 rounded-xl border border-gray-200 bg-white shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">{{ __('roles.fields.permissions') }}</h3>
                        <p class="text-xs text-gray-500">{{ __('roles.edit.permissions_hint') }}</p>
                    </div>
                    <button type="button" id="toggle-all"
                        class="text-xs px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                        {{ __('roles.edit.select_all') }}
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-[28rem] overflow-auto pr-2" id="perm-list">
                    @foreach($perms as $perm)
                    <label class="flex items-center gap-3 text-gray-700 border border-gray-200 rounded-lg px-3 py-2 hover:bg-gray-50">
                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                            @checked($role->permissions->contains('id',$perm->id))
                            class="rounded border-gray-300 bg-white text-indigo-600 focus:ring-indigo-500">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900">{{ $perm->name }}</div>
                            @if(!empty($perm->labelLocalized))
                            <div class="text-xs text-gray-500">{{ $perm->labelLocalized }}</div>
                            @endif
                        </div>
                    </label>
                    @endforeach
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('roles.index') }}"
                        class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-200">
                        {{ __('roles.edit.cancel') }}
                    </a>
                    <button class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium">
                        {{ __('roles.edit.save') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        (() => {
            const toggleBtn = document.getElementById('toggle-all');
            const list = document.getElementById('perm-list');
            if (!toggleBtn || !list) return;

            toggleBtn.addEventListener('click', () => {
                const boxes = list.querySelectorAll('input[type="checkbox"]');
                const checkedCount = Array.from(boxes).filter(b => b.checked).length;
                const shouldCheck = checkedCount !== boxes.length;
                boxes.forEach(b => b.checked = shouldCheck);
                toggleBtn.textContent = shouldCheck
                    ? @json(__('roles.edit.deselect_all'))
                    : @json(__('roles.edit.select_all'));
            });
        })();
    </script>
    @endpush
</x-admin-layout>
