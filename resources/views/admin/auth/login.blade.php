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

            backdrop-filter: blur(8px);

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

        {{-- LOGIN CARD --}}
        <div class="auth-card rounded-2xl p-6 space-y-6">



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
                    <input id="password" name="password" type="password" required
                        class="w-full rounded-lg border border-indigo-300 px-3 py-2.5 text-sm bg-white
                                  focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600">
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
</x-guest-layout>