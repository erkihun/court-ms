<x-applicant-auth-layout
    :title="__('auth.forgot_password_title')"
    :subtitle="__('auth.forgot_password_subtitle')"
    portal="applicant"
    accent="orange"
    login-route="applicant.login">

    @if(session('success'))
    <div class="auth-alert auth-alert-success mb-4">
        {{ session('success') }}
    </div>
    @endif

    @if(session('status'))
    <div class="auth-alert auth-alert-success mb-4">
        {{ session('status') }}
    </div>
    @endif

    @if(session('error'))
    <div class="auth-alert auth-alert-error mb-4">
        {{ session('error') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="auth-alert auth-alert-error mb-4">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('applicant.password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label class="auth-label" for="email">{{ __('auth.email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="auth-input">
            @error('email')
            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="auth-primary-btn">
            {{ __('auth.send_reset_link') }}
        </button>
    </form>
</x-applicant-auth-layout>
