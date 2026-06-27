<x-applicant-auth-layout
    :title="__('auth.password_otp_title')"
    :subtitle="__('auth.password_otp_description')"
    portal="applicant"
    accent="orange"
    login-route="applicant.login">

    @if (session('info'))
    <div class="auth-alert auth-alert-info mb-4">
        {{ session('info') }}
    </div>
    @endif

    @if (session('success'))
    <div class="auth-alert auth-alert-success mb-4">
        {{ session('success') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="auth-alert auth-alert-error mb-4">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('applicant.password.otp.verify') }}" class="space-y-5">
        @csrf

        <div>
            <label class="auth-label text-center" for="code">
                {{ __('auth.verification_code') }}
            </label>
            <x-ui.otp-input name="code" :length="6" autofocus />
        </div>

        <button type="submit" class="auth-primary-btn">
            {{ __('auth.verify_code') }}
        </button>
    </form>

    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-sm">
        <a href="{{ route('applicant.password.request') }}" class="auth-link">
            {{ __('auth.use_different_email') }}
        </a>
        <form method="POST" action="{{ route('applicant.password.otp.resend') }}">
            @csrf
            <button type="submit" class="auth-accent-link">
                {{ __('auth.resend_code') }}
            </button>
        </form>
    </div>

</x-applicant-auth-layout>
