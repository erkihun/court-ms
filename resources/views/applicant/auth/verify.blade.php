<x-applicant-auth-layout
    :title="__('auth.verify_email_title')"
    :subtitle="__('auth.verify_email_subtitle')"
    portal="applicant"
    accent="orange"
    :login-route="false">

    @if (session('success'))
    <div class="auth-alert auth-alert-success mb-4">
        {{ session('success') }}
    </div>
    @endif

    @if (session('status') == 'verification-link-sent')
    <div class="auth-alert auth-alert-success mb-4">
        {{ __('auth.verification_link_sent') }}
    </div>
    @endif

    <form method="POST" action="{{ route('applicant.verification.send') }}" class="space-y-3">
        @csrf
        <button type="submit" class="auth-primary-btn">
            {{ __('auth.resend_verification_email') }}
        </button>
    </form>

    <div class="mt-4 grid gap-3 sm:grid-cols-2">
        <a href="{{ route('applicant.dashboard') }}" class="auth-secondary-btn">
            {{ __('auth.back_to_dashboard') }}
        </a>
        <form method="POST" action="{{ route('applicant.logout') }}">
            @csrf
            <button type="submit" class="auth-secondary-btn">
                {{ __('auth.log_out') }}
            </button>
        </form>
    </div>
</x-applicant-auth-layout>
