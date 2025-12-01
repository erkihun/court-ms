<x-guest-layout>
    <div class="mx-auto w-full max-w-lg">
        <div class="rounded-2xl border border-gray-200 bg-white/80 p-8 shadow-xl
            backdrop-blur-xl shadow-indigo-500/10">
            <div class="mb-4 flex items-center gap-3">
                <div
                    class="h-12 w-12 rounded-full bg-indigo-50 text-indigo-600 grid place-items-center text-lg font-semibold">
                    ✉️
                </div>
                <div>
                    <p class="text-sm uppercase tracking-wider text-indigo-600">Email verification</p>

                </div>
            </div>

            <p class="text-sm text-gray-700">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
            </p>
            <p class="mt-3 text-sm font-medium text-red-600">
                {{ __('auth.email_not_verified') }}
            </p>

            @if (session('status') == 'verification-link-sent')
            <div
                class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
            @endif

            <div class="mt-6 space-y-3">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf

                    <x-primary-button class="w-full justify-center">
                        {{ __('auth.resend_verification_email') }}
                    </x-primary-button>
                </form>

                <p class="text-xs text-gray-500">
                    {{ __('auth.verify_email_hint') }}
                </p>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit"
                        class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>