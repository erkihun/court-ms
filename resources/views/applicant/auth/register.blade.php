@php
$settings = null;
try {
$settings = \App\Models\SystemSetting::query()->first();
} catch (\Throwable $e) {
$settings = null;
}

$brandName = $settings?->app_name ?? config('app.name', 'Court MS');
$logoPath = $settings?->logo_path ?? null;

$lawyerQuestionText = __('auth.lawyer_question');
if ($lawyerQuestionText === 'auth.lawyer_question') {
    $lawyerQuestionText = 'Are you a lawyer?';
}
$yesText = __('auth.yes');
if ($yesText === 'auth.yes') {
    $yesText = 'Yes';
}
$noText = __('auth.no');
if ($noText === 'auth.no') {
    $noText = 'No';
}
@endphp

@extends('layouts.fullscreen-layout')

@section('content')
<div class="relative z-1 bg-white p-6 sm:p-0">
        <div class="relative flex min-h-screen w-full flex-col sm:p-0 lg:flex-row lg:items-stretch">
        <div class="absolute right-6 top-6 z-20">
            <div x-data="{ open: false }" class="relative">
                <button type="button" @click.stop="open = !open"
                    class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                    <span class="fi fi-{{ app()->getLocale() == 'am' ? 'et' : 'us' }}"></span>
                    <span>{{ __('app.Language') }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 9l6 6 6-6" />
                    </svg>
                </button>

                <div x-cloak x-show="open" @click.outside="open = false"
                    class="absolute right-0 mt-2 w-36 rounded-md border border-slate-200 bg-white shadow-lg">
                    <div class="p-2 space-y-1 text-slate-700 text-sm">
                        <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
                            class="flex items-center gap-2 w-full px-3 py-2 rounded hover:bg-slate-50 {{ app()->getLocale() == 'en' ? 'bg-blue-50 text-blue-700' : '' }}">
                            <span class="fi fi-us"></span>
                            {{ __('app.English') }}
                        </a>
                        <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
                            class="flex items-center gap-2 w-full px-3 py-2 rounded hover:bg-slate-50 {{ app()->getLocale() == 'am' ? 'bg-orange-50 text-orange-700' : '' }}">
                            <span class="fi fi-et"></span>
                            {{ __('app.Amharic') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

            <div class="flex w-full flex-1 flex-col lg:w-1/2 lg:px-12 xl:px-16">
                <div class="mx-auto w-full max-w-md pt-10 sm:max-w-lg lg:max-w-none"></div>

                <div class="mx-auto flex w-full max-w-md flex-1 flex-col lg:justify-center sm:max-w-lg lg:max-w-none">
                    <div class="rounded-2xl bg-white p-5 shadow-lg ring-1 ring-gray-100 sm:p-8">
                        <div class="mb-6">
                            <h1 class="text-3xl font-semibold text-gray-900 sm:text-4xl">
                                {{ __('auth.sign_up_title') }}
                            </h1>
                            <p class="mt-2 text-sm text-gray-500">
                                {{ __('auth.sign_up_subtitle') }}
                            </p>
                        </div>

                    @if ($errors->any())
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                        <form method="POST" action="{{ route('applicant.register.submit') }}" class="space-y-6">
                        @csrf

                        <div class="flex flex-wrap items-center gap-4">
                            <span class="text-sm font-medium text-gray-700">{{ $lawyerQuestionText }}</span>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="radio" name="is_lawyer" value="1"
                                    class="h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500/20"
                                    @checked(old('is_lawyer')==='1' )>
                                {{ $yesText }}
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="radio" name="is_lawyer" value="0"
                                    class="h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500/20"
                                    @checked(old('is_lawyer', '0' )==='0' )>
                                {{ $noText }}
                            </label>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    {{ __('auth.first_name') }}
                                </label>
                                <input name="first_name" value="{{ old('first_name') }}" required
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    {{ __('auth.middle_name') }}
                                </label>
                                <input name="middle_name" value="{{ old('middle_name') }}" required
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    {{ __('auth.last_name') }}
                                </label>
                                <input name="last_name" value="{{ old('last_name') }}" required
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10">
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    {{ __('auth.position') }}
                                </label>
                                <input name="position" value="{{ old('position') }}" required
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    {{ __('auth.organization_name') }}
                                </label>
                                <input name="organization_name" value="{{ old('organization_name') }}" required
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10">
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    {{ __('auth.gender') }}
                                </label>
                                <select name="gender"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10">
                                    <option value="">{{ __('auth.select_option') }}</option>
                                    <option value="male" @selected(old('gender')==='male' )>{{ __('auth.male') }}</option>
                                    <option value="female" @selected(old('gender')==='female' )>{{ __('auth.female') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    {{ __('auth.phone') }}
                                </label>
                                <input name="phone" value="{{ old('phone') }}" required
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    {{ __('auth.national_id') }}
                                </label>
                                <input name="national_id_number" value="{{ old('national_id_number') }}" required
                                    inputmode="numeric" autocomplete="off" maxlength="19" pattern="\d{4}\s\d{4}\s\d{4}\s\d{4}"
                                    title="Format: XXXX XXXX XXXX XXXX (only numbers allowed)"
                                    placeholder="1234 5678 9012 3456"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10">
                                <p class="mt-1 text-xs text-gray-500">
                                    Format: XXXX XXXX XXXX XXXX (only numbers allowed)
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    {{ __('auth.email') }}
                                </label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    {{ __('auth.address') }}
                                </label>
                                <input name="address" value="{{ old('address') }}" required
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10">
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700" for="password">
                                    {{ __('auth.password') }}
                                </label>
                                <div class="relative">
                                    <input id="password" type="password" name="password" required
                                        class="h-11 w-full rounded-lg border border-gray-300 bg-white py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10">
                                    <button type="button"
                                        class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600"
                                        data-toggle-password="password" aria-label="{{ __('auth.show_password') }}"
                                        aria-pressed="false">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                                d="M2.5 12.5S5.5 6 12 6s9.5 6.5 9.5 6.5S18.5 19 12 19 2.5 12.5 2.5 12.5z" />
                                            <circle cx="12" cy="12.5" r="2.5" stroke-width="1.6" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700"
                                    for="password_confirmation">
                                    {{ __('auth.confirm_password') }}
                                </label>
                                <div class="relative">
                                    <input id="password_confirmation" type="password" name="password_confirmation" required
                                        class="h-11 w-full rounded-lg border border-gray-300 bg-white py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10">
                                    <button type="button"
                                        class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600"
                                        data-toggle-password="password_confirmation" aria-label="{{ __('auth.show_password') }}"
                                        aria-pressed="false">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                                d="M2.5 12.5S5.5 6 12 6s9.5 6.5 9.5 6.5S18.5 19 12 19 2.5 12.5 2.5 12.5z" />
                                            <circle cx="12" cy="12.5" r="2.5" stroke-width="1.6" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col items-start justify-between gap-3 pb-6 md:flex-row md:items-center">
                            <a href="{{ route('applicant.login') }}"
                                class="inline-flex items-center text-sm text-brand-500 hover:text-brand-600">
                                {{ __('auth.already_have_account') }}
                            </a>

                            <button
                                class="w-full rounded-lg bg-orange-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-orange-600 md:w-auto">
                                {{ __('auth.create_account_button') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="auth-visual relative hidden min-h-screen w-full items-center lg:grid lg:w-1/2">
            <div class="auth-grid pointer-events-none absolute inset-0"></div>
            <div class="relative z-10 flex items-center justify-center">
                <div class="flex max-w-xs flex-col items-center text-center">
                    <a href="/" class="mb-4 inline-flex items-center gap-3">
                        @if ($logoPath)
                        <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ $brandName }}"
                            class="h-12 w-auto rounded-lg bg-white/10 p-2" />
                        @else
                        <span class="auth-brand-mark">CM</span>
                        @endif
                        <span class="text-2xl font-semibold text-white">{{ $brandName }}</span>
                    </a>
                    <p class="text-sm text-white/70">
                        Free and Open-Source Tailwind CSS Admin Dashboard Template
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const applyLightTheme = () => {
        localStorage.setItem('theme', 'light');
        document.documentElement.classList.remove('dark');
        if (document.body) {
            document.body.classList.remove('dark', 'bg-gray-900');
        }
    };

    if (document.body) {
        applyLightTheme();
    } else {
        document.addEventListener('DOMContentLoaded', applyLightTheme);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const idInput = document.querySelector('input[name="national_id_number"]');
        if (idInput) {
            const format = (val) => {
                const cleaned = (val || '')
                    .replace(/[^0-9]/g, '')
                    .slice(0, 16);
                const parts = cleaned.match(/.{1,4}/g) || [];
                return parts.join(' ');
            };

            idInput.addEventListener('input', (e) => {
                e.target.value = format(e.target.value);
            });

            idInput.addEventListener('blur', () => {
                idInput.value = format(idInput.value);
            });
        }

        document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
            const targetId = btn.getAttribute('data-toggle-password');
            const input = document.getElementById(targetId);
            if (!input) return;

            btn.addEventListener('click', () => {
                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                btn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
            });
        });
    });
</script>
<style>
    .auth-visual {
        background: radial-gradient(circle at 20% 20%, rgba(67, 56, 202, 0.25), transparent 55%),
            radial-gradient(circle at 80% 80%, rgba(59, 130, 246, 0.2), transparent 50%),
            #0b1340;
    }

    .auth-grid {
        background-image:
            linear-gradient(rgba(255, 255, 255, 0.06) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.06) 1px, transparent 1px);
        background-size: 48px 48px;
        opacity: 0.8;
    }

    .auth-brand-mark {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 48px;
        width: 48px;
        border-radius: 12px;
        background: rgba(99, 102, 241, 0.9);
        color: #ffffff;
        font-weight: 700;
        letter-spacing: 0.05em;
    }
</style>
@endpush
