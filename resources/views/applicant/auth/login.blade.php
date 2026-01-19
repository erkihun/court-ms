@php
    $brandName   = $settings?->app_name ?? config('app.name', 'Court MS');
    $logoPath    = $settings?->logo_path ?? null;
    $faviconPath = $settings?->favicon_path ?? null;
    $bannerPath  = $settings?->banner_path ?? null;

    $loginAs = old('login_as', request('login_as', 'applicant'));
    $isRespondent = $loginAs === 'respondent';

    $applicantPanelLabel  = __('auth.applicant_panel_label');
    $respondentPanelLabel = __('auth.respondent_panel_label');
    $panelLabel = $isRespondent ? $respondentPanelLabel : $applicantPanelLabel;

    session()->forget('acting_as_respondent');
@endphp
@php
$settings = null;
try {
$settings = \App\Models\SystemSetting::query()->first();
} catch (\Throwable $e) {
$settings = null;
}
$bannerPath = $settings?->banner_path ?? null;
@endphp

<x-applicant-layout title="{{ __('auth.login_title') }}" :hide-footer="true" :as-respondent-nav="false">

    {{-- Favicon --}}
    @if($faviconPath)
        @push('head')
            <link rel="icon" href="{{ asset('storage/'.$faviconPath) }}">
        @endpush
    @endif

    {{-- Banner background --}}
    @if($bannerPath)
        @push('head')
            <style>
                body {
                    background:
                        url("{{ asset('storage/'.$bannerPath) }}")
                        center / cover no-repeat,
                        #e5e7eb;
                }
                .applicant-login-card {
                    backdrop-filter: blur(6px);
                    background-color: rgba(255,255,255,.94);
                }
            </style>
        @endpush
    @endif

    <div class="min-h-screen bg-transparent flex items-center">
        <div class="w-full  mx-auto grid grid-cols-1 lg:grid-cols-2 gap-0 lg:gap-8 px-4 py-10">

            {{-- RIGHT PANEL --}}
            <section class="space-y-5 flex justify-center">
                <div
                    class="applicant-login-card w-full max-w-md bg-white border border-slate-200
                           shadow-lg px-6 py-7 flex flex-col justify-between rounded-3xl">

                {{-- HEADER --}}
                <div class="flex items-center justify-center gap-3">
                    @if($logoPath)
                        <div class="h-16 w-16 rounded-xl overflow-hidden">
                            <img
                                src="{{ asset('storage/'.$logoPath) }}"
                                alt="{{ $brandName }}"
                                class="h-full w-full object-contain">
                        </div>
                    @else
                        <div
                            class="h-16 w-16 rounded-xl bg-blue-100 border border-blue-200
                                   text-blue-700 flex items-center justify-center
                                   font-bold text-xl uppercase">
                            {{ \Illuminate\Support\Str::of($brandName)->substr(0,2) }}
                        </div>
                    @endif

                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $brandName }}</h1>
                        <p
                            class="text-xs font-semibold uppercase tracking-wider text-indigo-600"
                            data-panel-label>
                            {{ $panelLabel }}
                        </p>
                    </div>
                </div>

                {{-- FLASH --}}
                @if(session('success'))
                    <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- ERRORS --}}
                @if($errors->any())
                    <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- FORM --}}
                <form method="POST" action="{{ route('applicant.login.submit') }}" class="space-y-6 mt-6">
                    @csrf

                    {{-- ROLE SWITCH --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-800">
                            {{ __('auth.sign_in_as') }}
                        </label>

                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <button type="button"
                                data-role="applicant"
                                class="role-btn {{ $loginAs === 'applicant' ? 'active' : '' }}">
                                {{ __('auth.role_applicant') }}
                            </button>

                            <button type="button"
                                data-role="respondent"
                                class="role-btn {{ $loginAs === 'respondent' ? 'active' : '' }}">
                                {{ __('auth.role_respondent') }}
                            </button>
                        </div>

                        <input type="hidden" name="login_as" id="login_as" value="{{ $loginAs }}">
                    </div>

                    {{-- EMAIL --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-800">
                            {{ __('auth.email') }}
                        </label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300
                                   focus:ring-4 focus:ring-blue-500/20 focus:border-blue-600 transition">
                    </div>

                    {{-- PASSWORD --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-800">
                            {{ __('auth.password') }}
                        </label>

                        <div class="mt-1 relative">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                class="w-full pr-10 px-3 py-2.5 rounded-lg border border-slate-300
                                       focus:ring-4 focus:ring-blue-500/20 focus:border-blue-600 transition">

                            <button
                                type="button"
                                data-toggle-password="password"
                                class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600">
                                👁
                            </button>
                        </div>

                        <p class="mt-1 text-xs text-slate-500">
                            {{ __('auth.password_hint') }}
                        </p>
                    </div>

                    {{-- REMEMBER --}}
                    <div class="flex items-center justify-between text-sm">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="remember"
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-600">
                            {{ __('auth.remember_me') }}
                        </label>

                        <a href="{{ route('applicant.register') }}"
                           class="text-blue-700 font-medium hover:underline">
                            {{ __('auth.create_account') }}
                        </a>
                    </div>

                    <div class="text-right text-sm">
                        <a href="{{ route('applicant.password.request') }}"
                           class="text-slate-600 hover:underline">
                            {{ __('auth.forgot_password') }}
                        </a>
                    </div>

                    {{-- SUBMIT --}}
                    <button
                        class="w-full rounded-xl py-3 bg-gradient-to-r from-orange-500 to-orange-600
                               text-white font-semibold tracking-wide shadow-lg
                               hover:from-orange-600 hover:to-orange-700
                               focus:ring-4 focus:ring-orange-400/40 transition">
                        {{ __('auth.sign_in') }}
                    </button>
                </form>
            </section>

            
            {{-- LEFT PANEL --}}
            <section
                class=" bg-gradient-to-br from-slate-900 via-slate-900/80 to-slate-900/60
                       p-10 mt-3 text-white shadow-2xl space-y-8 flex flex-col justify-between animate-fade-in">

                <div class="space-y-6">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-slate-400">
                            {{ __('auth.powered_by', ['brand' => $brandName]) }}
                        </p>
                        <h1 class="text-3xl font-bold">{{ $brandName }}</h1>
                    </div>

                    <p class="text-sm text-slate-200 leading-relaxed">
                        Court Management System keeps case files, hearings, and bench notes
                        in one secure portal with full audit trails and PDF-ready records.
                    </p>

                    <div class="space-y-3">
                        <div class="flex items-center gap-3 text-sm">
                            <span class="h-3.5 w-3.5 rounded-full bg-emerald-300"></span>
                            Track cases, hearings, and decisions in real time.
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <span class="h-3.5 w-3.5 rounded-full bg-cyan-300"></span>
                            Secure notifications and official PDF downloads.
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <span class="h-3.5 w-3.5 rounded-full bg-amber-300"></span>
                            Full audit trail for every action.
                        </div>
                    </div>
                </div>

                <div class="text-sm text-slate-300">
                    <p>{{ __('auth.need_help') }}</p>
                    <p class="font-semibold text-white">
                        {{ $settings?->support_email ?? 'support@courtms.local' }}
                    </p>
                </div>
            </section>

        </div>
    </div>

    {{-- SCRIPTS --}}
    <script>
        document.querySelectorAll('[data-toggle-password]').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = document.getElementById(btn.dataset.togglePassword);
                input.type = input.type === 'password' ? 'text' : 'password';
            });
        });

        const panelLabel = document.querySelector('[data-panel-label]');
        const loginAs = document.getElementById('login_as');

        document.querySelectorAll('.role-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                loginAs.value = btn.dataset.role;

                panelLabel.textContent =
                    btn.dataset.role === 'respondent'
                        ? @json($respondentPanelLabel)
                        : @json($applicantPanelLabel);

                document.querySelectorAll('.role-btn')
                    .forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });
    </script>

    <style>
        .role-btn {
            padding: .65rem;
            border-radius: .75rem;
            font-weight: 600;
            background: #f1f5f9;
            transition: all .2s ease;
        }
        .role-btn.active {
            background: #2563eb;
            color: white;
        }
        @keyframes fade-in {
            from { opacity:0; transform: translateY(12px); }
            to { opacity:1; transform:none; }
        }
        .animate-fade-in {
            animation: fade-in .6s ease-out;
        }
    </style>

</x-applicant-layout>
