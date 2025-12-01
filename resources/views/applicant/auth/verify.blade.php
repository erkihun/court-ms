<x-applicant-layout title="{{ __('auth.verify_email_title') }}">
    <div class="mx-auto max-w-md">
        <div class="rounded-xl border bg-white p-6">


            <h1 class="text-lg font-semibold">{{ __('auth.verify_email_title') }}</h1>
            <p class="mt-2 text-sm text-slate-600">
                {{ __('auth.verify_email_subtitle') }}
            </p>

            @if (session('success'))
            <div class="mt-3 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
            @endif

            @if (session('status') == 'verification-link-sent')
            <div class="mt-3 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                {{ __('auth.verification_link_sent') }}
            </div>
            @endif

            <form method="POST" action="{{ route('applicant.verification.send') }}" class="mt-4">
                @csrf
                <button class="w-full rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 transition-colors">
                    {{ __('auth.resend_verification_email') }}
                </button>
            </form>

            <div class="mt-4 text-sm flex items-center justify-between">
                <a href="{{ route('applicant.dashboard') }}" class="text-slate-600 hover:text-slate-800">
                    {{ __('auth.back_to_dashboard') }}
                </a>
                <form method="POST" action="{{ route('applicant.logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-600 hover:text-slate-800">
                        {{ __('auth.log_out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-applicant-layout>