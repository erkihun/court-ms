<x-applicant-layout title="{{ __('auth.sessions_title') }}">
    <div class="mx-auto max-w-6xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[.18em] text-blue-600">{{ __('auth.security_section') }}</p>
                <h1 class="mt-1 text-xl font-semibold text-slate-900 md:text-2xl">{{ __('auth.sessions_title') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('auth.sessions_hint') }}</p>
            </div>
            <a href="{{ route('applicant.dashboard') }}"
                class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                {{ __('auth.back') }}
            </a>
        </div>

        <nav class="mb-6 flex flex-wrap gap-2 border-b border-slate-200 pb-4" aria-label="{{ __('auth.profile_section') }}">
            <a href="{{ route('applicant.profile.edit') }}#profile"
                class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-blue-300">
                {{ __('auth.profile_section') }}
            </a>
            <a href="{{ route('applicant.profile.edit') }}#security"
                class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-blue-300">
                {{ __('auth.security_section') }}
            </a>
            <a href="{{ route('applicant.profile.sessions.index') }}"
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                {{ __('auth.sessions_title') }}
            </a>
        </nav>

        @if(session('status'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if(config('session.driver') !== 'database')
            <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                {{ __('auth.sessions_file_driver_hint') }}
            </div>
        @endif

        <section class="overflow-hidden rounded-xl border border-slate-200">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">{{ __('auth.active_sessions') }}</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ __('auth.sessions_hint') }}</p>
                </div>
                @if($sessions->contains(fn (object $session): bool => ! $session->is_current))
                    <form method="POST" action="{{ route('applicant.profile.sessions.destroyOthers') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">
                            {{ __('auth.revoke_other_sessions') }}
                        </button>
                    </form>
                @endif
            </div>

            <div class="divide-y divide-slate-200">
                @forelse($sessions as $session)
                    <article class="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-start gap-3">
                            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-4M9 3v4h4M9 3h6a2 2 0 0 1 2 2v2m-4 4 3 3m0 0 3-3m-3 3V7"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="truncate text-sm font-semibold text-slate-900">{{ $session->device_name }}</h3>
                                    @if($session->is_current)
                                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">{{ __('auth.current_session') }}</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-xs text-slate-500">{{ $session->browser_name }} · {{ $session->ip_address ?: __('auth.unknown_location') }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ __('auth.last_active') }}: {{ $session->last_active_at->diffForHumans() }}</p>
                            </div>
                        </div>

                        @if(! $session->is_current)
                            <form method="POST" action="{{ route('applicant.profile.sessions.destroy', $session->id) }}" class="shrink-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:border-red-200 hover:bg-red-50 hover:text-red-700">
                                    {{ __('auth.revoke_session') }}
                                </button>
                            </form>
                        @endif
                    </article>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-slate-500">{{ __('auth.no_active_sessions') }}</div>
                @endforelse
            </div>
        </section>
    </div>
</x-applicant-layout>
