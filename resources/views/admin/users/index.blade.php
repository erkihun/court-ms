<x-admin-layout title="{{ __('app.Users') }}">
    @section('page_header', __('app.Users'))

        <div class="enterprise-page">
            <div class="enterprise-header">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h1 class="enterprise-title">{{ __('users.administration') }}</h1>
                        <p class="enterprise-subtitle">{{ __('users.management_subtitle') }}</p>
                    </div>
                    <x-ui.button :href="route('users.create')">{{ __('users.new_user') }}</x-ui.button>
                </div>
            </div>

        <x-ui.filter-bar as="form" method="GET" class="enterprise-toolbar">
            <div class="enterprise-toolbar-block">
                <x-ui.input name="q" value="{{ request('q','') }}" placeholder="{{ __('app.Search') }}..."
                    class="w-full md:w-80" />
                <x-ui.select name="status" class="w-full md:w-52">
                    <option value="">{{ __('app.All statuses') }}</option>
                    <option value="active" @selected(request('status')==='active' )>{{ __('app.Active') }}</option>
                    <option value="inactive" @selected(request('status')==='inactive' )>{{ __('app.Inactive') }}</option>
                </x-ui.select>
            </div>
            <x-ui.actions>
                <x-ui.button type="submit">{{ __('users.filter') }}</x-ui.button>
                @if(request()->hasAny(['q','status']) && (request('q')!=='' || request('status')!==''))
                <x-ui.button :href="route('users.index')" variant="secondary">{{ __('users.reset') }}</x-ui.button>
                @endif
            </x-ui.actions>
        </x-ui.filter-bar>

        <x-ui.table>
            <x-slot name="head">
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th>Status</th>
                    <th>Verified</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </x-slot>
            <x-slot name="body">
                        @forelse($users as $u)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($u->avatar_url)
                                    <img src="{{ $u->avatar_url }}" class="h-9 w-9 rounded-full object-cover border border-slate-200" alt="{{ __('app.Avatar') }}">
                                    @else
                                    <div class="h-9 w-9 rounded-full bg-blue-100 text-blue-700 grid place-items-center text-xs font-semibold border border-blue-200">
                                        {{ strtoupper(substr($u->name ?? 'U',0,1)) }}
                                    </div>
                                    @endif
                                    <div>
                                        <div class="font-semibold text-slate-900">{{ $u->name }}</div>
                                        @if($u->signature_path)
                                        <span class="enterprise-pill border-emerald-200 bg-emerald-50 text-emerald-700 mt-1">{{ __('app.signature') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-slate-600">{{ $u->email }}</td>
                            <td class="text-slate-600">
                                @php $roleNames = $u->roles?->pluck('name')->all() ?? []; @endphp
                                {{ empty($roleNames) ? '-' : implode(', ', $roleNames) }}
                            </td>
                            <td>
                                <x-ui.badge :type="$u->status === 'active' ? 'success' : 'neutral'">
                                    {{ ucfirst($u->status) }}
                                </x-ui.badge>
                            </td>
                            <td>
                                <x-ui.badge :type="$u->hasVerifiedEmail() ? 'success' : 'warning'">
                                    {{ $u->hasVerifiedEmail() ? __('users.verified') : __('users.unverified') }}
                                </x-ui.badge>
                            </td>
                            <td class="text-slate-600">{{ \App\Support\EthiopianDate::format($u->created_at) }}</td>
                            <td>
                                <x-ui.actions>
                                    <x-ui.button :href="route('users.show', $u)" variant="secondary" size="sm">{{ __('app.View') }}</x-ui.button>
                                    <x-ui.button :href="route('users.edit', $u)" size="sm">{{ __('permissions.index.edit') }}</x-ui.button>
                                    <form method="POST" action="{{ route('users.destroy',$u) }}"
                                        onsubmit="return confirm(@js(__('users.delete_user_confirm')))">
                                        @csrf @method('DELETE')
                                        <x-ui.button type="submit" variant="danger" size="sm">{{ __('permissions.index.delete') }}</x-ui.button>
                                    </form>
                                </x-ui.actions>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <x-ui.empty :title="__('users.no_users_found')" />
                            </td>
                        </tr>
                        @endforelse
            </x-slot>
        </x-ui.table>

        <div>{{ $users->links() }}</div>
    </div>
</x-admin-layout>
