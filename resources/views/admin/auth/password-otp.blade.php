<x-guest-layout>
    <div class="mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Enter Reset Code</h2>
        <p class="mt-1 text-sm text-gray-600">We sent a 6-digit code to your email address. It expires in 10 minutes.</p>
    </div>

    @if (session('info'))
    <div class="mb-4 rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-800">
        {{ session('info') }}
    </div>
    @endif

    @if (session('success'))
    <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800">
        {{ session('success') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('admin.password.otp.verify') }}">
        @csrf

        <div>
            <x-input-label for="code" value="Verification Code" />
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
                    Resend Code
                </button>
            </form>

            <x-primary-button>
                Verify Code
            </x-primary-button>
        </div>
    </form>

    <div class="mt-4 text-sm text-center">
        <a href="{{ route('password.request') }}" class="text-gray-500 hover:text-gray-700">
            Use a different email
        </a>
    </div>
</x-guest-layout>
