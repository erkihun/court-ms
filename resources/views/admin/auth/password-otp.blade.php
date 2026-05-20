<x-guest-layout>
    <div class="mb-4">
        <h2 class="text-lg font-semibold text-gray-800">{{ __('auth.password_otp_title') }}</h2>
        <p class="mt-1 text-sm text-gray-600">{{ __('auth.password_otp_description') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.password.otp.verify') }}">
        @csrf

        <div>
            <x-input-label for="code" :value="__('auth.verification_code')" />
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
                class="mt-1 block w-full rounded-md border-gray-300 text-center text-2xl font-mono tracking-[0.5em]
                       shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <form method="POST" action="{{ route('admin.password.otp.resend') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800 underline">
                    {{ __('auth.resend_code') }}
                </button>
            </form>

            <x-primary-button>
                {{ __('auth.verify_code') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-4 text-sm text-center">
        <a href="{{ route('password.request') }}" class="text-gray-500 hover:text-gray-700">
            {{ __('auth.use_different_email') }}
        </a>
    </div>
</x-guest-layout>
