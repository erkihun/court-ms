<x-admin-layout title="{{ __('auth.sessions_title') }}">
    <div class="max-w-6xl mx-auto px-5 pb-16 space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-blue-700 to-indigo-700 p-6 text-white shadow-lg">
            <div class="flex items-center gap-4">
                <div class="grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-2xl bg-white/20 text-2xl font-bold ring-1 ring-white/30">
                    @if(auth()->user()->avatar_url)<img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="h-full w-full object-cover">@else{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}@endif
                </div>
                <div><p class="text-xs font-semibold uppercase tracking-[.18em] text-blue-100">{{ __('auth.profile_section') }}</p><h1 class="mt-1 text-2xl font-bold">{{ auth()->user()->name }}</h1><p class="mt-1 text-sm text-blue-100">{{ auth()->user()->email }}</p></div>
            </div>
        </div>

        <nav class="flex flex-wrap gap-2" aria-label="Profile sections">
            <a href="{{ route('profile.edit') }}#profile" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">{{ __('auth.profile_section') }}</a>
            <a href="{{ route('profile.edit') }}#security" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">{{ __('auth.security_section') }}</a>
            <a href="{{ route('profile.sessions.index') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">{{ __('auth.sessions_title') }}</a>
        </nav>

        @if(session('status'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>@endif
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4 mb-6 flex-wrap"><div><h2 class="text-lg font-bold text-slate-900">{{ __('auth.active_sessions') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('auth.sessions_hint') }}</p></div><form method="POST" action="{{ route('profile.sessions.destroyOthers') }}">@csrf @method('DELETE')<button class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">{{ __('auth.revoke_other_sessions') }}</button></form></div>
            <div class="space-y-3">
                @forelse($sessions as $session)
                    <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 p-3 hover:border-blue-200 hover:bg-blue-50/30">
                        <div class="flex min-w-0 items-center gap-4"><div class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-blue-50 text-blue-600"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5h16v11H4zM2 20h20M9 16v4m6-4v4"/></svg></div><div class="min-w-0"><p class="font-semibold text-slate-900 truncate">{{ $session->device_name }} - {{ $session->browser_name }}</p><p class="mt-1 text-sm text-slate-700">{{ $session->location }} • {{ $session->ip_address ?: '—' }}</p><p class="mt-1 text-xs text-slate-500">{{ __('auth.last_active') }}: {{ $session->last_active_at->diffForHumans() }} • {{ $session->last_active_at->format('n/j/Y H:i') }}</p></div></div>
                        @if($session->is_current)<span class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600">{{ __('auth.current_session') }}</span>@else<form method="POST" action="{{ route('profile.sessions.destroy', $session->id) }}">@csrf @method('DELETE')<button class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:border-red-200 hover:text-red-600">{{ __('auth.revoke_session') }}</button></form>@endif
                    </div>
                @empty<p class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">{{ __('auth.no_active_sessions') }}</p>@endforelse
            </div>
        </section>
    </div>
</x-admin-layout>
