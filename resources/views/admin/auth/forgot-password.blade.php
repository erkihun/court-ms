<x-applicant-auth-layout
    :title="__('auth.forgot_password_title')"
    :subtitle="__('auth.forgot_password_description')"
    portal="admin"
    accent="blue"
    login-route="login">

    @if (session('status'))
    <div class="auth-alert auth-alert-success mb-4">
        {{ session('status') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="auth-alert auth-alert-error mb-4">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="auth-label">{{ __('auth.email') }}</label>
            <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email')
            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="auth-primary-btn">
            {{ __('auth.send_reset_link') }}
        </button>
    </form>
</x-applicant-auth-layout>
