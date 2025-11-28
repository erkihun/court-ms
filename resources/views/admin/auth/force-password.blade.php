<x-guest-layout>
    <div class="max-w-xl mx-auto w-full space-y-6">
        <div class="text-center space-y-2">
            <h1 class="text-2xl font-bold text-gray-900">{{ __('Change Password') }}</h1>
            <p class="text-sm text-gray-600">
                {{ __('Your account requires a password update before you can continue.') }}
            </p>
        </div>

        @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm">
            {{ session('error') }}
        </div>
        @endif

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-6">
            @include('admin.profile.partials.update-password-form')
        </div>
    </div>
</x-guest-layout>
