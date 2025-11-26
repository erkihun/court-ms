<x-public-layout title="{{ __('auth.login_title') }}">
    <div class="max-w-md mx-auto bg-white rounded-xl border border-slate-200 shadow-sm p-6 md:p-8">

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6">
            <div class="h-9 w-9 rounded-full bg-blue-50 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 text-blue-600"
                    viewBox="0 0 24 24"
                    fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                        d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm0 2c-4 0-7 2-7 4v1h14v-1c0-2-3-4-7-4z" />
                </svg>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-semibold text-slate-900">
                    {{ __('auth.login_title') }}
                </h1>
                <p class="text-xs md:text-sm text-slate-600">
                    {{ __('auth.login_subtitle') }}
                </p>
            </div>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-xs md:text-sm text-red-800">
            <ul class="list-disc ml-5 space-y-0.5">
                @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('applicant.login.submit') }}" class="space-y-6">
            @csrf

            {{-- Email --}}
            <div>
                <label class="block text-sm font-medium text-slate-700" for="email">
                    {{ __('auth.email') }}
                </label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                           focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
            </div>

            {{-- Password (with show/hide) --}}
            <div>
                <label class="block text-sm font-medium text-slate-700" for="password">
                    {{ __('auth.password') }}
                </label>
                <div class="mt-1 relative">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        class="w-full pr-10 px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                               focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">

                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
                        data-toggle-password="password"
                        aria-label="{{ __('auth.show_password') }}"
                        aria-pressed="false">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                d="M2.5 12.5S5.5 6 12 6s9.5 6.5 9.5 6.5S18.5 19 12 19 2.5 12.5 2.5 12.5z" />
                            <circle cx="12" cy="12.5" r="2.5" stroke-width="1.6" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Remember / Register link --}}
            <div class="flex items-center justify-between text-sm">
                <label class="inline-flex items-center gap-2 text-slate-700">
                    <input type="checkbox" name="remember"
                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-600">
                    <span>{{ __('auth.remember_me') }}</span>
                </label>
                <a href="{{ route('applicant.register') }}"
                    class="text-blue-700 hover:underline">
                    {{ __('auth.create_account') }}
                </a>
            </div>

            {{-- Forgot password --}}
            <div class="mt-1 text-right text-sm">
                <a href="{{ route('applicant.password.request') }}"
                    class="text-slate-600 hover:text-slate-800">
                    {{ __('auth.forgot_password') }}
                </a>
            </div>

            {{-- Submit --}}
            <button
                class="mt-3 w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg bg-orange-500 text-white text-sm font-semibold
                       hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-1">
                {{ __('auth.sign_in') }}
            </button>
        </form>
    </div>

    {{-- Show / hide password script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
                const targetId = btn.getAttribute('data-toggle-password');
                const input = document.getElementById(targetId);
                if (!input) return;

                btn.addEventListener('click', () => {
                    const isHidden = input.type === 'password';
                    input.type = isHidden ? 'text' : 'password';
                    btn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                });
            });
        });
    </script>
</x-public-layout>