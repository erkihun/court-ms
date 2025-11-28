{{-- resources/views/permissions/index.blade.php --}}
<x-admin-layout title="Permissions">
    @section('page_header','Permissions')
    <style>[x-cloak]{display:none !important;}</style>

    <div x-data="permissionsPage()" class="space-y-4">

        {{-- Toolbar --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600">Showing</span>
                <span class="px-2 py-1 rounded-lg text-sm bg-gray-100 text-gray-800 border border-gray-200">
                    {{ number_format($permissions->total()) }} permissions
                </span>
            </div>

            <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
                <form method="GET" class="flex items-center gap-2">
                    <div class="relative">
                        <input
                            type="text"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Search permissions…"
                            class="w-64 pl-9 pr-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        <svg class="absolute left-2.5 top-2.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>


                </form>

                <a href="{{ route('permissions.create') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v16m8-8H4" />
                    </svg>
                    New Permission
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
                            <th class="p-3 text-left font-medium">Name</th>
                            <th class="p-3 text-left font-medium">Label</th>
                            <th class="p-3 text-left font-medium">Description</th>
                            <th class="p-3 text-left font-medium">Roles</th>
                            <th class="p-3 text-left font-medium">Users</th>
                            <th class="p-3 text-left w-40 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($permissions as $perm)
                        <tr class="hover:bg-gray-50">
                            {{-- Name --}}
                            <td class="p-3">
                                <a href="{{ route('permissions.edit',$perm) }}"
                                    class="font-medium text-gray-900 hover:underline">{{ $perm->name }}</a>
                            </td>

                            {{-- Label --}}
                            <td class="p-3 text-gray-700">{{ $perm->label ?: '—' }}</td>

                            {{-- Description --}}
                            <td class="p-3 text-gray-600">
                                <span class="line-clamp-2">{{ $perm->description }}</span>
                            </td>

                            {{-- Roles with "+N more" expander --}}
                            <td class="p-3">
                                @php
                                $limit = 6;
                                $rs = $perm->roles;
                                $extra = max($rs->count() - $limit, 0);
                                @endphp

                                <div x-data="{open:false}" class="space-y-2">
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($rs->take($limit) as $r)
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700 border border-gray-200">
                                            {{ $r->name }}
                                        </span>
                                        @endforeach

                                        @if($extra > 0)
                                        <button type="button" @click="open = !open"
                                            class="px-2 py-0.5 rounded-full text-xs border border-gray-300 text-gray-700 hover:bg-gray-50">
                                            +{{ $extra }} more
                                        </button>
                                        @endif
                                    </div>

                                    @if($extra > 0)
                                    <div x-show="open" x-transition class="flex flex-wrap gap-1.5">
                                        @foreach($rs->skip($limit) as $r)
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700 border border-gray-200">
                                            {{ $r->name }}
                                        </span>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $perm->roles_count }} role(s)
                                </div>
                            </td>

                            {{-- Users count (from selectSub) --}}
                            <td class="p-3">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-200">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M9 20H4v-2a3 3 0 015.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $perm->users_count }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="p-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('permissions.edit',$perm) }}"
                                        class="px-2.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs">
                                        Edit
                                    </a>

                                    <form id="delForm-{{ $perm->id }}" action="{{ route('permissions.destroy',$perm) }}" method="POST" class="hidden">
                                        @csrf @method('DELETE')
                                    </form>
                                    <button
                                        @click.prevent="openDelete({ id: {{ $perm->id }}, name: @js($perm->name) })"
                                        class="px-2.5 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-8">
                                <div class="text-center text-gray-500">
                                    <svg class="h-10 w-10 mx-auto mb-2 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h6" />
                                    </svg>
                                    No permissions found.
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
                    Showing <span class="font-medium">{{ $permissions->firstItem() ?? 0 }}</span> to
                    <span class="font-medium">{{ $permissions->lastItem() ?? 0 }}</span> of
                    <span class="font-medium">{{ $permissions->total() }}</span> results
                </div>
                <div>{{ $permissions->withQueryString()->links() }}</div>
            </div>
        </div>

        {{-- Delete modal --}}
        <div x-cloak x-show="modal.open" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
            <div x-cloak x-show="modal.open" x-transition class="w-full max-w-md rounded-xl bg-white shadow-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900">Delete permission</h3>
                </div>
                <div class="px-5 py-4 text-sm text-gray-700">
                    Are you sure you want to delete
                    <span class="font-semibold text-gray-900" x-text="modal.name"></span>? This action cannot be undone.
                </div>
                <div class="px-5 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-2">
                    <button @click="modal.open=false"
                        class="px-3 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50">
                        Cancel
                    </button>
                    <button @click="submitDelete()"
                        class="px-3 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </div>
        </div>

    </div>

    {{-- Alpine helpers --}}
    <script>
        function permissionsPage() {
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
