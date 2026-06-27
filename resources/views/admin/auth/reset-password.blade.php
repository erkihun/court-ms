<x-applicant-auth-layout
    :title="__('auth.reset_password_title')"
    :subtitle="__('auth.reset_password_subtitle')"
    portal="admin"
    accent="blue"
    login-route="login">

    @if ($errors->any())
    <div class="auth-alert auth-alert-error mb-4">
        <ul class="list-disc ml-5">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <div>
            <label for="password" class="auth-label">{{ __('auth.new_password') }}</label>
            <input id="password" class="auth-input" type="password" name="password" required autofocus autocomplete="new-password">
            @error('password')
            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="auth-label">{{ __('auth.confirm_password') }}</label>
            <input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required autocomplete="new-password">
            @error('password_confirmation')
            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="auth-primary-btn">
            {{ __('auth.reset_password_button') }}
        </button>
    </form>
</x-applicant-auth-layout>
