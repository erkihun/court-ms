<x-respondant-layout title="{{ __('Respondent Login') }}">
    {{-- FULLSCREEN wrapper – prevents scrolling --}}
    <div class="min-h-screen w-full flex items-center justify-center overflow-hidden px-4">

        <div class="w-full max-w-md bg-white rounded-2xl border border-slate-200 shadow-lg px-7 py-8">

            {{-- Header --}}
            <div class="mb-6">
                <h1 class="text-xl md:text-2xl font-semibold text-slate-900">
                    {{ __('Respondent Login') }}
                </h1>
                <p class="text-xs md:text-sm text-slate-600 mt-1">
                    {{ __('Sign in to your respondent account.') }}
                </p>
            </div>

            {{-- Success message --}}
            @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
            @endif

            {{-- Errors --}}
            @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc ml-5 space-y-0.5">
                    @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Login form --}}
            <form method="POST" action="{{ route('respondent.login.submit') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Email') }}</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm
                               focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Password') }}</label>
                    <input
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm
                               focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>

                {{-- Remember + Register --}}
                <div class="flex items-center justify-between text-sm">
                    <label class="inline-flex items-center gap-2 text-slate-700">
                        <input type="checkbox" name="remember" value="1"
                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-600">
                        <span>{{ __('Remember me') }}</span>
                    </label>

                    <a href="{{ route('respondent.register') }}"
                        class="text-blue-700 hover:text-blue-800 font-medium">
                        {{ __('Register') }}
                    </a>
                </div>

                {{-- Login button --}}
                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-lg bg-blue-700 text-white text-sm font-semibold
                           hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    {{ __('Login') }}
                </button>
            </form>

        </div>
    </div>
</x-respondant-layout>