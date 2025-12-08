{{-- resources/views/roles/index.blade.php --}}
<x-admin-layout title="{{ __('roles.index.title') }}">
    @section('page_header', __('roles.index.title'))
    <style>[x-cloak]{display:none !important;}</style>

    <div x-data="rolesPage()" class="space-y-4">

        {{-- Toolbar --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600">{{ __('roles.index.showing') }}</span>
                <span class="px-2 py-1 rounded-lg text-sm bg-gray-100 text-gray-800 border border-gray-200">
                    {{ number_format($roles->total()) }} {{ __('roles.index.total_roles') }}
                </span>
            </div>



            <a href="{{ route('roles.create') }}"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v16m8-8H4" />
                </svg>
                {{ __('roles.index.new_role') }}
            </a>
        </div>
    </div>

    @if(session('ok'))
    <div class="p-3 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200">{{ session('ok') }}</div>
    @endif

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-700 border-b border-gray-200 sticky top-0 z-10">
                    <tr>
                        <th class="p-3 text-left font-medium">{{ __('roles.fields.name') }}</th>
                        <th class="p-3 text-left font-medium">{{ __('roles.fields.description') }}</th>
                        <th class="p-3 text-left font-medium">{{ __('roles.fields.permissions') }}</th>
                        <th class="p-3 text-left font-medium">{{ __('roles.fields.users') }}</th>
                        <th class="p-3 text-left w-40 font-medium">{{ __('roles.index.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($roles as $r)
                    <tr class="hover:bg-gray-50">
                        {{-- Name --}}
                        <td class="p-3">
                            <a href="{{ route('roles.edit',$r) }}"
                                class="font-medium text-gray-900 hover:underline">{{ $r->name }}</a>
                        </td>

                        {{-- Description --}}
                        <td class="p-3 text-gray-600">
                            <span class="line-clamp-2">{{ $r->description ?? '—' }}</span>
                        </td>

                        {{-- Permissions with "+N more" expander --}}
                        <td class="p-3">
                            @php
                            $limit = 6;
                            $perm = $r->permissions;
                            $extra = max($perm->count() - $limit, 0);
                            @endphp

                            <div x-data="{open:false}" class="space-y-2">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($perm->take($limit) as $p)
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700 border border-gray-200">
                                        {{ $p->name }}
                                    </span>
                                    @endforeach

                                    @if($extra > 0)
                                    <button type="button" @click="open = !open"
                                        class="px-2 py-0.5 rounded-full text-xs border border-gray-300 text-gray-700 hover:bg-gray-50">
                                    +{{ $extra }} {{ __('roles.index.more') }}
                                    </button>
                                    @endif
                                </div>

                                @if($extra > 0)
                                <div x-show="open" x-transition class="flex flex-wrap gap-1.5">
                                    @foreach($perm->skip($limit) as $p)
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700 border border-gray-200">
                                        {{ $p->name }}
                                    </span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </td>

                        {{-- Users --}}
                        <td class="p-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-200">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M9 20H4v-2a3 3 0 015.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ $r->users_count }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="p-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('roles.edit',$r) }}"
                                    class="px-2.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs">
                                    Edit
                                </a>

                                <form id="delForm-{{ $r->id }}" action="{{ route('roles.destroy',$r) }}" method="POST" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                                <button
                                    @click.prevent="openDelete({ id: {{ $r->id }}, name: @js($r->name) })"
                                    class="px-2.5 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs">
                                    {{ __('roles.index.delete') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8">
                            <div class="text-center text-gray-500">
                                <svg class="h-10 w-10 mx-auto mb-2 text-gray-300" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="M9 17v-6a2 2 0 012-2h6" />
                                </svg>
                                No roles found.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer / Pagination --}}
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
            <div class="text-xs text-gray-600">
                Showing <span class="font-medium">{{ $roles->firstItem() ?? 0 }}</span> to
                <span class="font-medium">{{ $roles->lastItem() ?? 0 }}</span> of
                <span class="font-medium">{{ $roles->total() }}</span> results
            </div>
            <div>{{ $roles->withQueryString()->links() }}</div>
        </div>
    </div>

    {{-- Delete modal --}}
    <div
        x-cloak
        x-show="modal.open"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div x-cloak x-show="modal.open" x-transition
            class="w-full max-w-md rounded-xl bg-white shadow-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900">{{ __('roles.index.delete_title') }}</h3>
            </div>
                <div class="px-5 py-4 text-sm text-gray-700">
                    {{ __('roles.index.delete_confirm') }}
                    <span class="font-semibold text-gray-900" x-text="modal.name"></span>.
                </div>
            <div class="px-5 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-2">
                <button @click="modal.open=false"
                    class="px-3 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50">
                    {{ __('roles.index.cancel') }}
                </button>
                <button
                    @click="submitDelete()"
                    class="px-3 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
                    {{ __('roles.index.delete') }}
                </button>
            </div>
        </div>
    </div>
    </div>

    {{-- Alpine helpers --}}
    <script>
        function rolesPage() {
            return {
                modal: {
                    open: false,
                    id: null,
                    name: ''
                },
                openDelete({
                    id,
                    name
                }) {
                    this.modal = {
                        open: true,
                        id,
                        name
                    };
                },
                submitDelete() {
                    if (!this.modal.id) return;
                    document.getElementById('delForm-' + this.modal.id)?.submit();
                }
            }
        }
    </script>
</x-admin-layout>
