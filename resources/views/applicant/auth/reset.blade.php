<x-applicant-auth-layout
    :title="__('auth.reset_password_title')"
    :subtitle="__('auth.reset_password_subtitle')"
    portal="applicant"
    accent="orange"
    login-route="applicant.login">

    @if ($errors->any())
    <div class="auth-alert auth-alert-error mb-4">
        <ul class="list-disc ml-5">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('applicant.password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label class="auth-label" for="email">{{ __('auth.email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required class="auth-input">
            @error('email')
            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="auth-label" for="password">{{ __('auth.new_password') }}</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="auth-input">
            @error('password')
            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="auth-label" for="password_confirmation">{{ __('auth.confirm_new_password') }}</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="auth-input">
            @error('password_confirmation')
            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="auth-primary-btn">
            {{ __('auth.reset_password_button') }}
        </button>
    </form>
</x-applicant-auth-layout>
