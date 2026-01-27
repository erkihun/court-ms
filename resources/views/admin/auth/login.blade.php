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

<x-guest-layout>
    @if($faviconPath)
    @push('head')
    <link rel="icon" href="{{ asset('storage/'.$faviconPath) }}">
    @endpush
    @endif
    @if($bannerPath)
    @push('head')
    <style>
        body {
            background: url("{{ asset('storage/'.$bannerPath) }}") center / cover no-repeat,
            #e5e7eb;

            backdrop-filter: blur(4px);

        }

        .guest-container {
            background: transparent;

        }

        .auth-card {
            backdrop-filter: blur(4px);
            background-color: rgba(255, 255, 255, 0.92);

        }
    </style>
    @endpush
    @endif

    <div class="max-w-md mx-auto w-full space-y-8">





        {{-- LOGIN CARD --}}
        <div class="auth-card rounded-2xl p-6 space-y-6">

            {{-- BRAND HEADER --}}
            <div class="flex items-center justify-center gap-3">
                @if($logoPath)
                <div class="h-16 w-16 rounded-xl overflow-hidden">
                    <img src="{{ asset('storage/'.$faviconPath) }}"
                        alt="{{ $brandName }}"
                        class="h-full w-full object-contain">
                </div>
                @else
                <div class="h-16 w-16 rounded-xl bg-indigo-100 text-indigo-700 border border-indigo-300 flex items-center justify-center font-bold uppercase tracking-wide">
                    {{ \Illuminate\Support\Str::of($brandName)->substr(0,2) }}
                </div>
                @endif

                <div class="text-left">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $brandName }}</h1>
                    <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Admin Login Panel</p>
                </div>
            </div>
            {{-- STATUS MESSAGE --}}
            @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
                {{ session('status') }}
            </div>
            @endif

            {{-- ERROR BLOCK --}}
            @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-sm">
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            {{-- LOGIN FORM --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                {{-- EMAIL --}}
                <div class="space-y-1">
                    <label class="block text-md text-indigo-600 font-medium for=" email">Email</label>
                    <input id="email" name="email" type="email"
                        value="{{ old('email') }}" required autofocus
                        class="w-full rounded-lg border border-indigo-300 px-3 py-2.5 text-sm bg-white
                                  focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600">
                </div>

                {{-- PASSWORD --}}
                <div class="space-y-1">
                    <label class="block text-md text-indigo-600 font-medium" for="password">Password</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required
                            class="w-full rounded-lg border border-indigo-300 px-3 py-2.5 pr-10 text-sm bg-white
                                      focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600">
                        <button type="button" id="toggle-password"
                            class="absolute right-3 top-1/2 -translate-y-1/2 inline-flex items-center text-indigo-600 hover:text-indigo-800 focus:outline-none"
                            aria-label="Show password" aria-pressed="false">
                            <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3l18 18M10.477 10.48a3 3 0 004.243 4.243M9.88 5.08A9.953 9.953 0 0112 5c4.478 0 8.269 2.943 9.543 7a10.047 10.047 0 01-4.132 5.411M6.228 6.228A10.045 10.045 0 002.457 12c.738 2.344 2.327 4.286 4.45 5.41M12 19c-1.043 0-2.054-.147-3.01-.423" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- REMEMBER + FORGOT --}}
                <div class="flex items-center justify-between text-md">
                    <label class="inline-flex items-center gap-2">
                        <input id="remember_me" type="checkbox" name="remember"
                            class="rounded border-slate-500 text-indigo-600 focus:ring-indigo-600">
                        <span class="text-slate-900">Remember me</span>
                    </label>

                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                        class="text-indigo-600 font-medium hover:text-indigo-800">
                        Forgot password?
                    </a>
                    @endif
                </div>

                {{-- SUBMIT BUTTON --}}
                <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5
                               rounded-lg bg-indigo-600 text-white text-sm font-semibold shadow
                               hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    Log in
                </button>
            </form>
        </div>

    </div>
    <script>
        (function () {
            const input = document.getElementById('password');
            const toggle = document.getElementById('toggle-password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');
            if (!input || !toggle) return;

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
