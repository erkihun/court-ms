<x-applicant-auth-layout
    :title="__('auth.confirm_password')"
    :subtitle="__('auth.confirm_password_description')"
    portal="admin"
    accent="blue"
    login-route="login">

    @if ($errors->any())
    <div class="auth-alert auth-alert-error mb-4">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div>
            <label for="password" class="auth-label">{{ __('auth.password') }}</label>
            <input id="password" class="auth-input" type="password" name="password" required autocomplete="current-password" autofocus>
            @error('password')
            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="auth-primary-btn">
            {{ __('auth.confirm') }}
        </button>
    </form>
</x-applicant-auth-layout>
