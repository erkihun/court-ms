<x-applicant-layout title="Verify Your Email">
    <div class="mx-auto max-w-md">
        <div class="rounded-xl border border-slate-200 bg-white p-6 md:p-7 shadow-sm">

            <div class="flex items-center gap-3 mb-5">
                <div class="h-10 w-10 rounded-full bg-orange-50 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg md:text-xl font-semibold text-slate-900">Verify Your Email</h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-0.5">
                        We sent a 6-digit code to your email address.
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

            <form method="POST" action="{{ route('applicant.verify-otp.submit') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5" for="code">
                        Verification Code
                    </label>
                    <input
                        id="code"
                        type="text"
                        name="code"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        maxlength="6"
                        pattern="\d{6}"
                        placeholder="000000"
                        autofocus
                        class="w-full rounded-lg border border-slate-300 px-4 py-3 text-center text-2xl font-mono tracking-[0.5em] text-slate-900
                               focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400
                               @error('code') border-red-400 @enderror">
                    <p class="mt-1.5 text-xs text-slate-500">Enter the 6-digit code from your email. It expires in 10 minutes.</p>
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white
                           hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-1 transition-colors">
                    Verify Email
                </button>
            </form>

            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-sm">
                <span class="text-slate-500">Didn't receive a code?</span>
                <form method="POST" action="{{ route('applicant.verify-otp.resend') }}">
                    @csrf
                    <button type="submit"
                        class="text-orange-500 hover:text-orange-600 font-medium focus:outline-none">
                        Resend Code
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-applicant-layout>
