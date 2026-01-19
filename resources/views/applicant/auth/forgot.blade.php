

<x-applicant-layout title="{{ __('auth.forgot_password_title') }}">
    <div class="mx-auto max-w-md">
        <div class="rounded-xl border border-slate-200 bg-white p-6 md:p-7 shadow-sm">

            {{-- Header --}}
            <div class="flex items-center gap-3 mb-4">
                <div class="h-9 w-9 rounded-full bg-blue-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 text-blue-600"
                        viewBox="0 0 24 24"
                        fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                            d="M16 12a4 4 0 1 0-8 0 4 4 0 0 0 8 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                            d="M12 2v2m0 16v2m10-10h-2M4 12H2m15.07-7.07L17 6m-10 12-1.07 1.07M17 18l1.07 1.07M6 6 4.93 4.93" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg md:text-xl font-semibold text-slate-900">
                        {{ __('auth.forgot_password_title') }}
                    </h1>
                    <p class="mt-0.5 text-xs md:text-sm text-slate-600">
                        {{ __('auth.forgot_password_subtitle') }}
                    </p>
                </div>
            </div>

            {{-- Success / status --}}
            @if(session('success'))
            <div class="mt-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs md:text-sm text-emerald-800">
                {{ session('success') }}
            </div>
            @endif

            @if(session('status'))
            <div class="mt-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs md:text-sm text-emerald-800">
                {{ session('status') }}
            </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('applicant.password.email') }}" class="mt-5 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="email">
                        {{ __('auth.email') }}
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900
                               focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                    @error('email')
                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button
                    class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white
                           hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-1">
                    {{ __('auth.send_reset_link') }}
                </button>
            </form>

            <div class="mt-4 text-sm">
                <a href="{{ route('applicant.login') }}"
                    class="inline-flex items-center gap-1 text-slate-600 hover:text-slate-800">
                    {{ __('auth.back_to_login') }}
                </a>
            </div>
        </div>
    </div>
</x-applicant-layout>