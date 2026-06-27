<x-applicant-auth-layout
    :title="__('auth.verify_email_title')"
    :subtitle="__('auth.verify_email_otp_description')"
    portal="applicant"
    accent="orange"
    login-route="applicant.login">

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

    <form method="POST" action="{{ route('applicant.verification.verify-otp') }}" class="space-y-5">
        @csrf

        <div>
            <label class="auth-label text-center" for="code">
                {{ __('auth.verification_code') }}
            </label>
            <x-ui.otp-input name="code" :length="6" autofocus />
        </div>

        <button type="submit" class="auth-primary-btn">
            {{ __('auth.verify_email_button') }}
        </button>
    </form>

    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-sm">
        <span class="text-slate-500">{{ __('auth.didnt_receive_code') }}</span>
        <form method="POST" action="{{ route('applicant.verification.send') }}">
            @csrf
            <button type="submit" class="auth-accent-link">
                {{ __('auth.resend_code') }}
            </button>
        </form>
    </div>

</x-applicant-auth-layout>
