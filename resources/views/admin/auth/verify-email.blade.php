<x-applicant-auth-layout
    :title="__('auth.email_verification_label')"
    :subtitle="__('auth.email_verification_description')"
    portal="admin"
    accent="blue"
    login-route="login">

    @if (session('status') === 'verification-link-sent')
    <div class="auth-alert auth-alert-success mb-4">
        {{ __('auth.verification_link_sent') }}
    </div>
    @endif

    <div class="auth-alert auth-alert-error mb-4">
        {{ __('auth.email_not_verified') }}
    </div>

    <form method="POST" action="{{ route('verification.send') }}" class="space-y-3">
        @csrf
        <button type="submit" class="auth-primary-btn">
            {{ __('auth.resend_verification_email') }}
        </button>
    </form>

    <p class="mt-4 text-xs leading-relaxed text-slate-500">
        {{ __('auth.verify_email_hint') }}
    </p>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="auth-secondary-btn">
            {{ __('auth.log_out') }}
        </button>
    </form>
</x-applicant-auth-layout>
