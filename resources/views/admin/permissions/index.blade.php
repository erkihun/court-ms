<x-admin-layout title="{{ __('permissions.index.title') }}">
    @section('page_header', __('permissions.index.title'))
    <style>[x-cloak]{display:none !important;}</style>

    <div x-data="permissionsPage()" class="enterprise-page">
        <x-ui.filter-bar class="enterprise-toolbar">
            <div class="enterprise-toolbar-block">
                <span class="text-sm text-slate-600">{{ __('permissions.index.showing') }}</span>
                <x-ui.badge type="neutral" class="enterprise-pill">
                    {{ number_format($permissions->total()) }} {{ __('permissions.index.total_permissions') }}
                </x-ui.badge>
            </div>

            <div class="enterprise-toolbar-block">
                <form method="GET" class="flex items-center gap-2">
                    <x-ui.input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('permissions.index.search_placeholder') }}"
                        class="w-64" />
                </form>
                <x-ui.button :href="route('permissions.create')">{{ __('permissions.index.new_permission') }}</x-ui.button>
            </div>
        </x-ui.filter-bar>

        @if(session('ok'))
        <x-ui.alert type="success">{{ session('ok') }}</x-ui.alert>
        @endif

        <x-ui.table>
            <x-slot name="head">
                <tr>
                    <th>{{ __('permissions.fields.name') }}</th>
                    <th>{{ __('permissions.fields.label') }}</th>
                    <th>{{ __('permissions.fields.description') }}</th>
                    <th>{{ __('permissions.index.roles') }}</th>
                    <th>{{ __('permissions.index.users') }}</th>
                    <th>{{ __('permissions.index.actions') }}</th>
                </tr>
            </x-slot>
            <x-slot name="body">
                        @forelse($permissions as $perm)
                        <tr>
                            <td>
                                <a href="{{ route('permissions.edit',$perm) }}" class="font-semibold text-slate-900 hover:underline">{{ $perm->name }}</a>
                            </td>
                            <td class="text-slate-700">{{ $perm->labelLocalized ?: '-' }}</td>
                            <td class="text-slate-600"><span class="line-clamp-2">{{ $perm->descriptionLocalized ?: '-' }}</span></td>
                            <td>
                                @php
                                $limit = 6;
                                $rs = $perm->roles;
                                $extra = max($rs->count() - $limit, 0);
                                @endphp
                                <div x-data="{open:false}" class="space-y-2">
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($rs->take($limit) as $r)
                                        <x-ui.badge type="neutral">{{ $r->name }}</x-ui.badge>
                                        @endforeach
                                        @if($extra > 0)
                                        <button type="button" @click="open = !open"
                                            class="enterprise-pill border-slate-300 bg-white text-slate-700 hover:bg-slate-50">
                                            +{{ $extra }} more
                                        </button>
                                        @endif
                                    </div>
                                    @if($extra > 0)
                                    <div x-show="open" x-transition class="flex flex-wrap gap-1.5">
                                        @foreach($rs->skip($limit) as $r)
                                        <x-ui.badge type="neutral">{{ $r->name }}</x-ui.badge>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500 mt-1">{{ $perm->roles_count }} {{ __('permissions.index.roles_count') }}</div>
                            </td>
                            <td><x-ui.badge type="info">{{ $perm->users_count }}</x-ui.badge></td>
                            <td>
                                <x-ui.actions>
                                    <x-ui.button :href="route('permissions.edit', $perm)" size="sm">{{ __('permissions.index.edit') }}</x-ui.button>
                                    <form id="delForm-{{ $perm->id }}" action="{{ route('permissions.destroy',$perm) }}" method="POST" class="hidden">
                                        @csrf @method('DELETE')
                                    </form>
                                    <x-ui.button @click.prevent="openDelete({ id: {{ $perm->id }}, name: @js($perm->name) })" variant="danger" size="sm">{{ __('permissions.index.delete') }}</x-ui.button>
                                </x-ui.actions>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">
                                <x-ui.empty :title="__('permissions.index.empty')" />
                            </td>
                        </tr>
                        @endforelse
            </x-slot>
            <x-slot name="footer">
                <div class="flex items-center justify-between">
                <div class="text-xs text-slate-600">
                    {{ __('Showing :from to :to of :total results', [
                        'from' => $permissions->firstItem() ?? 0,
                        'to' => $permissions->lastItem() ?? 0,
                        'total' => $permissions->total()
                    ]) }}
                </div>
                <div>{{ $permissions->withQueryString()->links() }}</div>
                </div>
            </x-slot>
        </x-ui.table>

        <div x-cloak x-show="modal.open" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
            <div x-cloak x-show="modal.open" x-transition class="enterprise-modal">
                <div class="enterprise-modal-header">
                    <h3 class="text-base font-semibold text-slate-900">{{ __('permissions.index.delete_title') }}</h3>
                </div>
                <div class="enterprise-modal-body">
                    {{ __('permissions.index.delete_confirm') }}
                    <span class="font-semibold text-slate-900" x-text="modal.name"></span>.
                </div>
                <div class="enterprise-modal-footer">
                    <x-ui.button @click="modal.open=false" variant="secondary">{{ __('permissions.index.cancel') }}</x-ui.button>
                    <x-ui.button @click="submitDelete()" variant="danger">{{ __('permissions.index.delete') }}</x-ui.button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function permissionsPage() {
        return {
            modal: { open: false, id: null, name: '' },
            openDelete({ id, name }) {
                this.modal = { open: true, id, name };
            },
            submitDelete() {
                if (!this.modal.id) return;
                document.getElementById('delForm-' + this.modal.id)?.submit();
            }
        }
    }
    </script>
</x-admin-layout>
