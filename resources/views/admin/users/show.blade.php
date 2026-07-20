<x-admin-layout title="{{ __('users.profile_title') }}">
    @section('page_header', __('users.profile_title'))

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
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <aside class="enterprise-panel">
                <div class="enterprise-panel-body text-center">
                    <div class="w-28 h-28 rounded-full overflow-hidden mx-auto bg-slate-100 border-4 border-white shadow">
                        @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" class="w-full h-full object-cover" alt="{{ __('users.avatar') }}">
                        @else
                        <div class="w-full h-full flex items-center justify-center bg-blue-50 text-blue-600 text-4xl font-bold">
                            {{ strtoupper(substr($user->name ?? 'U',0,1)) }}
                        </div>
                        @endif
                    </div>

                    <h2 class="mt-5 text-xl font-semibold tracking-tight text-slate-900">{{ $user->name }}</h2>
                    <p class="text-sm text-slate-600 mt-1">{{ $user->email }}</p>
                    <div class="mt-4">
                        <span class="enterprise-pill {{ $user->status === 'active' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-100 text-slate-700' }}">
                            {{ $user->status === 'active' ? __('users.active') : __('users.inactive') }}
                        </span>
                    </div>
                </div>
                <div class="enterprise-panel-header">
                    <a href="{{ route('users.edit',$user) }}" class="btn btn-primary">{{ __('users.edit_profile') }}</a>
                    <a href="{{ route('users.index') }}" class="btn btn-outline">{{ __('users.back') }}</a>
                </div>
            </aside>

            <section class="enterprise-panel lg:col-span-2">
                <div class="enterprise-panel-header">
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('users.details') }}</h3>
                </div>
                <div class="enterprise-panel-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.16em] text-slate-500">{{ __('users.first_name') }}</p>
                                <p class="mt-1 font-medium text-slate-900">{{ $firstName }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.16em] text-slate-500">{{ __('users.middle_name') }}</p>
                                <p class="mt-1 font-medium text-slate-900">{{ $middleName ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.16em] text-slate-500">{{ __('users.last_name') }}</p>
                                <p class="mt-1 font-medium text-slate-900">{{ $lastName ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.16em] text-slate-500">{{ __('users.full_name') }}</p>
                                <p class="mt-1 font-medium text-slate-900">{{ $user->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.16em] text-slate-500">{{ __('users.email_address') }}</p>
                                <p class="mt-1 font-medium text-slate-900">{{ $user->email }}</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.16em] text-slate-500">{{ __('users.created_at') }}</p>
                                <p class="mt-1 text-slate-700">{{ \App\Support\EthiopianDate::format($user->created_at, withTime: true, timeFormat: 'h:i A') }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.16em] text-slate-500">{{ __('users.updated_at') }}</p>
                                <p class="mt-1 text-slate-700">{{ \App\Support\EthiopianDate::format($user->updated_at, withTime: true, timeFormat: 'h:i A') }}</p>
                            </div>
                        </div>

                        <div class="md:col-span-2 grid gap-4 md:grid-cols-2">
                            <div class="admin-panel-muted">
                                <p class="text-xs uppercase tracking-[0.16em] text-slate-500 mb-2">{{ __('users.signature') }}</p>
                                @if($user->signature_url)
                                <img src="{{ $user->signature_url }}" class="max-h-20 mx-auto object-contain" alt="{{ __('users.signature') }}">
                                @else
                                <p class="text-sm text-slate-500 italic">{{ __('users.no_signature') }}</p>
                                @endif
                            </div>

                            <div class="admin-panel-muted">
                                <p class="text-xs uppercase tracking-[0.16em] text-slate-500 mb-2">{{ __('users.stamp') }}</p>
                                @if($user->stamp_url)
                                <img src="{{ $user->stamp_url }}" class="max-h-20 mx-auto object-contain" alt="{{ __('users.stamp') }}">
                                @else
                                <p class="text-sm text-slate-500 italic">{{ __('users.no_stamp') }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="md:col-span-2 border-t border-slate-200 pt-4">
                            <p class="text-xs uppercase tracking-[0.16em] text-slate-500 mb-2">{{ __('users.assigned_roles') }}</p>
                            <div class="flex flex-wrap gap-2">
                                @php $roles = $user->roles?->pluck('name')->all() ?? []; @endphp
                                @if(!empty($roles))
                                @foreach($roles as $role)
                                <span class="enterprise-pill border-blue-200 bg-blue-50 text-blue-700">{{ $role }}</span>
                                @endforeach
                                @else
                                <span class="text-sm text-slate-500">{{ __('users.no_roles_assigned') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-admin-layout>
