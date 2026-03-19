@php
$settings = null;
try {
    $settings = \App\Models\SystemSetting::query()->first();
} catch (\Throwable $e) {
    $settings = null;
}

$brandName = $settings?->app_name ?? config('app.name', 'Court MS');
$logoPath = $settings?->logo_path ?? null;
$faviconPath = $settings?->favicon_path ?? null;
$bannerPath = $settings?->banner_path ?? null;
@endphp

<x-guest-layout variant="split-auth">
    @if($faviconPath)
        @push('head')
            <link rel="icon" href="{{ asset('storage/'.$faviconPath) }}">
        @endpush
    @endif

    @if($bannerPath)
        @push('head')
            <style>
                .admin-auth-brand {
                    background-image:
                        linear-gradient(180deg, rgba(15, 23, 42, 0.88) 0%, rgba(15, 23, 42, 0.94) 100%),
                        url("{{ asset('storage/'.$bannerPath) }}");
                    background-position: center;
                    background-size: cover;
                }
            </style>
        @endpush
    @endif

    <div class="min-h-screen grid lg:grid-cols-2">
        <section class="admin-auth-brand relative hidden overflow-hidden lg:flex">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.24),_transparent_34%),radial-gradient(circle_at_bottom_right,_rgba(249,115,22,0.18),_transparent_26%)]"></div>
            <div class="relative flex w-full flex-col justify-between px-10 py-12 xl:px-14">
                <div class="flex flex-col items-center justify-center gap-4 text-center">
                    @if($logoPath)
                        <div class="flex items-center justify-center">
                            <img src="{{ asset('storage/'.$logoPath) }}" alt="{{ $brandName }}" class="h-20 w-auto object-contain">
                        </div>
                    @else
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-white/15 bg-white/10 text-lg font-bold text-white shadow-lg shadow-slate-950/20 backdrop-blur">
                            {{ \Illuminate\Support\Str::of($brandName)->substr(0, 2) }}
                        </div>
                    @endif

                    <div class="space-y-1 text-white">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-100/80">Administrative Access</p>
                        <h1 class="text-xl font-semibold">{{ $brandName }}</h1>
                    </div>
                </div>

                <div class="max-w-xl space-y-8 text-white">
                    <div class="space-y-4">
                        <p class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-medium uppercase tracking-[0.2em] text-blue-50/90 backdrop-blur">
                            Court Case Management System
                        </p>
                        <div class="space-y-3">
                            <h2 class="max-w-lg text-4xl font-semibold leading-tight tracking-tight">
                                Secure administrative access for court operations and judicial workflows.
                            </h2>
                            <p class="max-w-lg text-base leading-7 text-slate-200/88">
                                Manage cases, hearings, appeals, notifications, and system settings in a structured enterprise workspace designed for high-trust institutions.
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/12 bg-white/10 p-5 backdrop-blur">
                            <div class="text-sm font-semibold text-white">Operational clarity</div>
                            <p class="mt-2 text-sm leading-6 text-slate-200/82">
                                Review filings, assignments, and hearing schedules from one controlled admin surface.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-white/12 bg-white/10 p-5 backdrop-blur">
                            <div class="text-sm font-semibold text-white">Trusted access</div>
                            <p class="mt-2 text-sm leading-6 text-slate-200/82">
                                Sign in with your authorized administrator account to continue securely.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="text-sm text-slate-200/80">
                    {{ now()->format('Y') }} {{ $brandName }}
                </div>
            </div>
        </section>

        <section class="flex min-h-screen items-center bg-transparent px-4 py-8 sm:px-6 lg:px-10 xl:px-14">
            <div class="mx-auto w-full max-w-md">
                <div class="rounded-[1.75rem] border border-slate-200/80 bg-white/92 p-6 shadow-2xl shadow-slate-900/10 ring-1 ring-white/60 backdrop-blur dark:border-slate-800 dark:bg-slate-950/88 dark:ring-white/5 sm:p-8">
                    <div class="mb-8 flex flex-col items-center justify-center gap-4 text-center lg:hidden">
                        @if($logoPath)
                            <div class="flex items-center justify-center">
                                <img src="{{ asset('storage/'.$logoPath) }}" alt="{{ $brandName }}" class="h-16 w-auto object-contain">
                            </div>
                        @else
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-blue-50 text-sm font-bold text-blue-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-blue-300">
                                {{ \Illuminate\Support\Str::of($brandName)->substr(0, 2) }}
                            </div>
                        @endif

                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-300">Admin Portal</div>
                            <div class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $brandName }}</div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <h2 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">Sign in</h2>
                        <p class="text-sm leading-6 text-slate-600 dark:text-slate-400">
                            Use your administrator credentials to access the court management dashboard.
                        </p>
                    </div>

                    @if (session('status'))
                        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-200">
                            <ul class="space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                        @csrf

                        <div class="space-y-2">
                            <label for="email" class="block text-sm font-medium text-slate-600 dark:text-slate-300">Email</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="username"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 shadow-sm transition-fast placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-blue-400 dark:focus:ring-blue-500/20"
                            >
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-3">
                                <label for="password" class="block text-sm font-medium text-slate-600 dark:text-slate-300">Password</label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-700 hover:text-blue-800 dark:text-blue-300 dark:hover:text-blue-200">
                                        Forgot password?
                                    </a>
                                @endif
                            </div>

                            <div class="relative">
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    autocomplete="current-password"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 pr-11 text-sm text-slate-900 shadow-sm transition-fast placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-blue-400 dark:focus:ring-blue-500/20"
                                >
                                <button
                                    type="button"
                                    id="toggle-password"
                                    class="absolute right-3 top-1/2 inline-flex -translate-y-1/2 items-center justify-center rounded-md p-1 text-slate-500 transition-fast hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                    aria-label="Show password"
                                    aria-pressed="false"
                                >
                                    <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 3l18 18M10.477 10.48a3 3 0 004.243 4.243M9.88 5.08A9.953 9.953 0 0112 5c4.478 0 8.269 2.943 9.543 7a10.047 10.047 0 01-4.132 5.411M6.228 6.228A10.045 10.045 0 002.457 12c.738 2.344 2.327 4.286 4.45 5.41M12 19c-1.043 0-2.054-.147-3.01-.423" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <label for="remember_me" class="inline-flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                                <input
                                    id="remember_me"
                                    type="checkbox"
                                    name="remember"
                                    class="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900"
                                >
                                <span>Remember me</span>
                            </label>
                        </div>

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-blue-700 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-900/15 transition-fast hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-950"
                        >
                            Log in
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <script>
        (function() {
            const input = document.getElementById('password');
            const toggle = document.getElementById('toggle-password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');

            if (!input || !toggle || !eyeOpen || !eyeClosed) return;

            toggle.addEventListener('click', () => {
                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                eyeOpen.classList.toggle('hidden', !isHidden);
                eyeClosed.classList.toggle('hidden', isHidden);
                toggle.setAttribute('aria-pressed', String(isHidden));
                toggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            });
        })();
    </script>
</x-guest-layout>
