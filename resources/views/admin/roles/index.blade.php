<x-admin-layout title="{{ __('roles.index.title') }}">
    @section('page_header', __('roles.index.title'))
    <style>[x-cloak]{display:none !important;}</style>

    <div x-data="rolesPage()" class="enterprise-page">
        <div class="enterprise-toolbar">
            <div class="enterprise-toolbar-block">
                <span class="text-sm text-slate-600">{{ __('roles.index.showing') }}</span>
                <span class="enterprise-pill border-slate-200 bg-slate-100 text-slate-700">
                    {{ number_format($roles->total()) }} {{ __('roles.index.total_roles') }}
                </span>
            </div>
            <a href="{{ route('roles.create') }}" class="btn btn-primary">{{ __('roles.index.new_role') }}</a>
        </div>

        @if(session('ok'))
        <div class="ui-alert ui-alert-success">{{ session('ok') }}</div>
        @endif

        <div class="ui-table-wrap">
            <div class="ui-table-scroll">
                <table class="ui-table">
                    <thead class="sticky top-0 z-10">
                        <tr>
                            <th>{{ __('roles.fields.name') }}</th>
                            <th>{{ __('roles.fields.description') }}</th>
                            <th>{{ __('roles.fields.permissions') }}</th>
                            <th>{{ __('roles.fields.users') }}</th>
                            <th>{{ __('roles.index.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $r)
                        <tr>
                            <td>
                                <a href="{{ route('roles.edit',$r) }}" class="font-semibold text-slate-900 hover:underline">{{ $r->name }}</a>
                            </td>
                            <td class="text-slate-600">
                                <span class="line-clamp-2">{{ $r->description ?? '-' }}</span>
                            </td>
                            <td>
                                @php
                                $limit = 6;
                                $perm = $r->permissions;
                                $extra = max($perm->count() - $limit, 0);
                                @endphp
                                <div x-data="{open:false}" class="space-y-2">
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($perm->take($limit) as $p)
                                        <span class="enterprise-pill border-slate-200 bg-slate-100 text-slate-700">{{ $p->name }}</span>
                                        @endforeach
                                        @if($extra > 0)
                                        <button type="button" @click="open = !open"
                                            class="enterprise-pill border-slate-300 bg-white text-slate-700 hover:bg-slate-50">
                                            +{{ $extra }} {{ __('roles.index.more') }}
                                        </button>
                                        @endif
                                    </div>
                                    @if($extra > 0)
                                    <div x-show="open" x-transition class="flex flex-wrap gap-1.5">
                                        @foreach($perm->skip($limit) as $p)
                                        <span class="enterprise-pill border-slate-200 bg-slate-100 text-slate-700">{{ $p->name }}</span>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="enterprise-pill border-blue-200 bg-blue-50 text-blue-700">{{ $r->users_count }}</span>
                            </td>
                            <td>
                                <div class="enterprise-actions">
                                    <a href="{{ route('roles.edit',$r) }}" class="btn btn-primary !px-3 !py-1.5 !text-xs">Edit</a>
                                    <form id="delForm-{{ $r->id }}" action="{{ route('roles.destroy',$r) }}" method="POST" class="hidden">
                                        @csrf @method('DELETE')
                                    </form>
                                    <button @click.prevent="openDelete({ id: {{ $r->id }}, name: @js($r->name) })"
                                        class="btn btn-danger !px-3 !py-1.5 !text-xs">
                                        {{ __('roles.index.delete') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5"><div class="enterprise-empty">No roles found.</div></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                <div class="text-xs text-slate-600">
                    Showing <span class="font-medium">{{ $roles->firstItem() ?? 0 }}</span> to
                    <span class="font-medium">{{ $roles->lastItem() ?? 0 }}</span> of
                    <span class="font-medium">{{ $roles->total() }}</span> results
                </div>
                <div>{{ $roles->withQueryString()->links() }}</div>
            </div>
        </div>

        <div x-cloak x-show="modal.open" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
            <div x-cloak x-show="modal.open" x-transition class="enterprise-modal">
                <div class="enterprise-modal-header">
                    <h3 class="text-base font-semibold text-slate-900">{{ __('roles.index.delete_title') }}</h3>
                </div>
                <div class="enterprise-modal-body">
                    {{ __('roles.index.delete_confirm') }}
                    <span class="font-semibold text-slate-900" x-text="modal.name"></span>.
                </div>
                <div class="enterprise-modal-footer">
                    <button @click="modal.open=false" class="btn btn-outline">{{ __('roles.index.cancel') }}</button>
                    <button @click="submitDelete()" class="btn btn-danger">{{ __('roles.index.delete') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function rolesPage() {
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
