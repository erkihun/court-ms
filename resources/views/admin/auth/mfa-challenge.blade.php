<x-guest-layout>
    <div class="mb-5 text-center">
        <h1 class="text-xl font-bold text-gray-900">{{ __('auth.mfa_challenge_title') }}</h1>
        <p class="text-sm text-gray-600 mt-2">{{ __('auth.mfa_challenge_hint') }}</p>
    </div>
    <form method="POST" action="{{ route('mfa.challenge.store') }}">
        @csrf
        <label class="block text-sm font-semibold mb-2">{{ __('auth.mfa_code_or_recovery') }}</label>
        <input name="code" autofocus autocomplete="one-time-code" required class="w-full rounded-lg border px-3 py-2 text-center tracking-widest">
        @error('code')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
        <button class="mt-4 w-full rounded-lg bg-blue-600 px-4 py-2 text-white">{{ __('auth.mfa_verify') }}</button>
    </form>
    <form method="POST" action="{{ route('logout') }}" class="mt-3 text-center">@csrf<button class="text-sm text-gray-600 underline">{{ __('auth.logout') }}</button></form>
</x-guest-layout>
