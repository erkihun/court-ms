<x-admin-layout title="{{ __('auth.profile_section') }}">
    <div class="max-w-6xl mx-auto px-5 pb-16 space-y-6" x-data="{ tab: @js($errors->updatePassword->any() || session('status') === 'password-updated') || window.location.hash === '#security' ? 'security' : 'profile' }" @hashchange.window="tab = window.location.hash === '#security' ? 'security' : 'profile'">
        <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-blue-700 to-indigo-700 p-6 text-white shadow-lg">
            <div class="flex items-center gap-4">
                <div class="grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-2xl bg-white/20 text-2xl font-bold ring-1 ring-white/30">
                    @if(auth()->user()->avatar_url)<img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="h-full w-full object-cover">@else{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}@endif
                </div>
                <div><p class="text-xs font-semibold uppercase tracking-[.18em] text-blue-100">{{ __('auth.profile_section') }}</p><h1 class="mt-1 text-2xl font-bold">{{ auth()->user()->name }}</h1><p class="mt-1 text-sm text-blue-100">{{ auth()->user()->email }}</p></div>
            </div>
        </div>

        <nav class="flex flex-wrap gap-2" aria-label="Profile sections">
            <a href="#profile" @click="tab = 'profile'" :class="tab === 'profile' ? 'bg-blue-600 text-white' : 'border border-slate-200 bg-white text-slate-700'" class="rounded-lg px-4 py-2 text-sm font-semibold">{{ __('auth.profile_section') }}</a>
            <a href="#security" @click="tab = 'security'" :class="tab === 'security' ? 'bg-blue-600 text-white' : 'border border-slate-200 bg-white text-slate-700'" class="rounded-lg px-4 py-2 text-sm font-semibold">{{ __('auth.security_section') }}</a>
            <a href="{{ route('profile.sessions.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">{{ __('auth.sessions_title') }}</a>
        </nav>

        <form id="profile" x-show="tab === 'profile'" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-[1.4fr_.8fr]">
            @csrf @method('PATCH')
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-6"><h2 class="text-lg font-bold text-slate-900">{{ __('auth.profile_section') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('auth.profile_basic_info') }}</p></div>
                <div class="grid gap-5 md:grid-cols-2">
                    <div><label class="mb-1.5 block text-sm font-semibold text-slate-700">{{ __('auth.name') }}</label><input name="name" value="{{ old('name', auth()->user()->name) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200"></div>
                    <div><label class="mb-1.5 block text-sm font-semibold text-slate-700">{{ __('auth.email') }}</label><input name="email" type="email" value="{{ old('email', auth()->user()->email) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200"></div>
                </div>
                <div class="mt-6 flex justify-end"><button class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">{{ __('auth.save') }}</button></div>
            </section>

            <aside class="space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 class="text-sm font-bold text-slate-900">{{ __('auth.avatar') }}</h2><p class="mt-1 text-xs text-slate-500">JPG, PNG or WebP · 2 MB maximum</p><input type="file" name="avatar" accept="image/*" class="mt-4 block w-full text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:font-semibold"><p class="mt-3 text-xs text-slate-500">{{ __('auth.profile_basic_info') }}</p></section>
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 class="text-sm font-bold text-slate-900">{{ __('auth.signature') }}</h2>@if(auth()->user()->signature_url)<img src="{{ auth()->user()->signature_url }}" alt="Signature" class="mt-3 max-h-16 border border-slate-200">@else<p class="mt-3 text-xs text-slate-500">No signature uploaded.</p>@endif<p class="mt-3 text-xs text-slate-500">Your signature can only be changed by an authorized administrator.</p></section>
            </aside>
        </form>

        <section id="security" x-show="tab === 'security'" x-cloak class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">@include('admin.profile.partials.update-password-form')</div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div class="flex items-start justify-between gap-4"><div><h2 class="text-lg font-bold text-slate-900">{{ __('auth.mfa_title') }}</h2><p class="mt-1 text-sm text-slate-500">{{ auth()->user()->hasConfirmedMfa() ? __('auth.mfa_active') : __('auth.mfa_not_configured') }}</p></div><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ auth()->user()->hasConfirmedMfa() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ auth()->user()->hasConfirmedMfa() ? __('auth.active') : __('auth.review_needed') }}</span></div><a href="{{ route('mfa.setup.show') }}" class="mt-6 inline-flex rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">{{ __('auth.mfa_manage') }}</a></div>
        </section>
    </div>
</x-admin-layout>
