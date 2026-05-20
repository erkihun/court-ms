<x-applicant-layout title="{{ __('auth.password_otp_title') }}">
    <div class="mx-auto max-w-md">
        <div class="rounded-xl border border-slate-200 bg-white p-6 md:p-7 shadow-sm">

            <div class="flex items-center gap-3 mb-5">
                <div class="h-10 w-10 rounded-full bg-orange-50 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg md:text-xl font-semibold text-slate-900">{{ __('auth.password_otp_title') }}</h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-0.5">
                        {{ __('auth.password_otp_description') }}
                    </p>
                </div>
            </div>

            @if (session('info'))
            <div class="mb-4 rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-800">
                {{ session('info') }}
            </div>
            @endif

            @if (session('success'))
            <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('applicant.password.otp.verify') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5" for="code">
                        {{ __('auth.verification_code') }}
                    </label>
                    <input
                        id="code"
                        type="text"
                        name="code"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        maxlength="6"
                        pattern="\d{6}"
                        placeholder="{{ __('auth.otp_placeholder') }}"
                        autofocus
                        class="w-full rounded-lg border border-slate-300 px-4 py-3 text-center text-2xl font-mono tracking-[0.5em] text-slate-900
                               focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400
                               @error('code') border-red-400 @enderror">
                    <p class="mt-1.5 text-xs text-slate-500">{{ __('auth.otp_code_hint') }}</p>
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white
                           hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-1 transition-colors">
                    {{ __('auth.verify_code') }}
                </button>
            </form>

            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-sm">
                <a href="{{ route('applicant.password.request') }}" class="text-slate-500 hover:text-slate-700">
                    {{ __('auth.use_different_email') }}
                </a>
                <form method="POST" action="{{ route('applicant.password.otp.resend') }}">
                    @csrf
                    <button type="submit" class="text-orange-500 hover:text-orange-600 font-medium">
                        {{ __('auth.resend_code') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-applicant-layout>
