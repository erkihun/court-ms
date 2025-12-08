@php
$settings = null;
try {
$settings = \App\Models\SystemSetting::query()->first();
} catch (\Throwable $e) {
$settings = null;
}

$brandName = $settings?->app_name ?? config('app.name', 'Court MS');
$logoPath = $settings?->logo_path ?? null;
$faviconPath = $settings?->favicon_path ?? null;
$bannerPath = $settings?->banner_path ?? null;

$loginAs = old('login_as', request('login_as', 'applicant'));
$isRespondent = $loginAs === 'respondent';
$applicantPanelLabel = __('auth.applicant_panel_label');
$respondentPanelLabel = __('auth.respondent_panel_label');
$panelLabel = $isRespondent ? $respondentPanelLabel : $applicantPanelLabel;
@endphp

@php session()->forget('acting_as_respondent'); @endphp
<x-applicant-layout title="{{ __('auth.login_title') }}" :hide-footer="true" :as-respondent-nav="false">

    {{-- Favicon --}}
    @if($faviconPath)
    @push('head')
    <link rel="icon" href="{{ asset('storage/'.$faviconPath) }}">
    @endpush
    @endif

    @if($bannerPath)
    @push('head')
    <style>
        body {
            background: url("{{ asset('storage/'.$bannerPath) }}") center / cover no-repeat,
            #e5e7eb;
            backdrop-filter: blur(8px);
        }

        .guest-container {
            background: transparent;
        }

        .applicant-login-card {
            backdrop-filter: blur(4px);
            opacity: 0.9;
            background-color: rgba(255, 255, 255, 0.94);
        }
    </style>
    @endpush
    @endif

    <div class="max-w-md mx-auto w-full space-y-8 flex items-center justify-center min-h-[70vh]">
        <div class="applicant-login-card w-full max-w-md bg-white rounded-xl border border-slate-200 shadow-lg px-7 py-8">

            {{-- HEADER --}}
            <div class="flex items-center justify-center gap-3">
                @if($logoPath)
                <div class="h-16 w-16 rounded-xl flex items-center justify-center overflow-hidden">
                    <img src="{{ asset('storage/'.$faviconPath) }}" alt="{{ $brandName }}" class="h-full w-full object-contain">
                </div>
                @else
                <div class="h-16 w-16 rounded-xl bg-blue-100 border border-blue-200 text-blue-700 flex items-center justify-center font-bold text-xl uppercase">
                    {{ \Illuminate\Support\Str::of($brandName)->substr(0,2) }}
                </div>
                @endif

                <div class="text-left">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $brandName }}</h1>
                    <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600" data-panel-label>{{ $panelLabel }}</p>
                </div>
            </div>

            {{-- FLASH --}}
            @if (session('success'))
            <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
            @endif

            {{-- ERRORS --}}
            @if ($errors->any())
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- FORM --}}
            <form method="POST" action="{{ route('applicant.login.submit') }}" class="space-y-6 mt-6">
                @csrf

                {{-- Role selection --}}
                <div class="space-y-1">
                    <label for="login_as" class="block text-sm font-semibold text-slate-800">
                        {{ __('auth.sign_in_as') }}
                    </label>

                    <div class="relative">
                        <select id="login_as" name="login_as"
                            class="mt-1 w-full appearance-none px-3 py-2.5 rounded-lg border border-slate-300 bg-white text-sm
                   font-medium text-slate-700 cursor-pointer
                   focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600
                   hover:border-slate-400 transition">

                            <option value="applicant" {{ $loginAs === 'applicant' ? 'selected' : '' }}>{{ __('auth.role_applicant') }}</option>
                            <option value="respondent" {{ $loginAs === 'respondent' ? 'selected' : '' }}>{{ __('auth.role_respondent') }}</option>
                        </select>

                        {{-- Dropdown icon --}}
                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                            </svg>
                        </span>
                    </div>
                </div>


                <div>
                    <label class="block text-sm font-medium text-slate-800" for="email">
                        {{ __('auth.email') }}
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required autofocus
                        class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 bg-white text-sm 
                               focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-800" for="password">
                        {{ __('auth.password') }}
                    </label>

                    <div class="mt-1 relative">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            class="w-full pr-10 px-3 py-2.5 rounded-lg border border-slate-300 bg-white text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">

                        <button
                            type="button"
                            data-toggle-password="password"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
                            aria-label="{{ __('auth.show_password') }}"
                            aria-pressed="false">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.5 12.5S5.5 6 12 6s9.5 6.5 9.5 6.5S18.5 19 12 19 2.5 12.5 2.5 12.5z" />
                                <circle cx="12" cy="12.5" r="2.5" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="inline-flex items-center gap-2 text-slate-700">
                        <input type="checkbox" name="remember"
                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-600">
                        <span>{{ __('auth.remember_me') }}</span>
                    </label>

                    <a href="{{ route('applicant.register') }}"
                        class="text-blue-700 hover:text-blue-900 font-medium">
                        {{ __('auth.create_account') }}
                    </a>
                </div>

                <div class="text-right text-sm">
                    <a href="{{ route('applicant.password.request') }}"
                        class="text-slate-600 hover:text-slate-800 underline underline-offset-2">
                        {{ __('auth.forgot_password') }}
                    </a>
                </div>

                <button
                    class="mt-2 w-full rounded-lg px-4 py-2.5 bg-orange-500 text-white text-sm font-semibold shadow-sm
                           hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-1">
                    {{ __('auth.sign_in') }}
                </button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-toggle-password]').forEach(btn => {
                const id = btn.getAttribute('data-toggle-password');
                const input = document.getElementById(id);

                btn.addEventListener('click', () => {
                    const hidden = input.type === 'password';
                    input.type = hidden ? 'text' : 'password';
                    btn.setAttribute('aria-pressed', hidden ? 'true' : 'false');
                });
            });

            const roleSelect = document.getElementById('login_as');
            const panelLabel = document.querySelector('[data-panel-label]');
            if (roleSelect && panelLabel) {
                const updateLabel = () => {
                    panelLabel.textContent = roleSelect.value === 'respondent' ?
                        @json($respondentPanelLabel) :
                        @json($applicantPanelLabel);
                };
                roleSelect.addEventListener('change', updateLabel);
                updateLabel();
            }
        });
    </script>

</x-applicant-layout>