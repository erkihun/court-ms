@php
$settings = null;
try {
    $settings = \App\Models\SystemSetting::query()->first();
} catch (\Throwable $e) { $settings = null; }

$brandName  = $settings?->app_name ?? config('app.name', 'Court MS');
$logoPath   = $settings?->logo_path ?? null;
$loginAs    = old('login_as', request('login_as', 'applicant'));

$applicantPanelLabel  = __('auth.applicant_panel_label');
$respondentPanelLabel = __('auth.respondent_panel_label');
$panelLabel           = $loginAs === 'respondent' ? $respondentPanelLabel : $applicantPanelLabel;

try {
    $aboutPage = \App\Models\AboutPage::query()
        ->where('is_published', true)
        ->orderByDesc('updated_at')
        ->first();
} catch (\Throwable $e) { $aboutPage = null; }

session()->forget('acting_as_respondent');
@endphp

@extends('layouts.fullscreen-layout')

@section('content')
<div class="al-shell" x-data="{ aboutOpen: false }">

    {{-- ══════════════════════════════════════════
         LEFT — pure white, no card box
         ══════════════════════════════════════════ --}}
    <div class="al-left">

        {{-- Top strip --}}
        <div class="al-topstrip">
            <a href="/" class="al-logo-link">
                @if($logoPath)
                    <img src="{{ asset('storage/'.$logoPath) }}" alt="{{ $brandName }}" class="al-logo-img">
                @else
                    <span class="al-logo-mark">{{ strtoupper(substr($brandName,0,2)) }}</span>
                @endif
                <span class="al-logo-name">{{ $brandName }}</span>
            </a>

            <div class="al-top-actions">
                <button type="button" class="al-mobile-about-btn al-mobile-about-btn-top" @click="aboutOpen = true">
                    <span>{{ __('app.View details') }}</span>
                </button>

                @if(Route::has('language.switch'))
                <div class="al-lang-switch" aria-label="{{ __('app.Language') }}">
                    <a href="{{ route('language.switch',['locale'=>'en','return'=>url()->current()]) }}"
                        class="al-lang-btn {{ app()->getLocale()==='en' ? 'active' : '' }}">
                        <span class="fi fi-us text-sm"></span>
                        <span>EN</span>
                    </a>
                    <a href="{{ route('language.switch',['locale'=>'am','return'=>url()->current()]) }}"
                        class="al-lang-btn {{ app()->getLocale()==='am' ? 'active' : '' }}">
                        <span class="fi fi-et text-sm"></span>
                        <span>አማ</span>
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Centred form area --}}
        <div class="al-form-area">
            <div class="al-form-inner">

                {{-- Heading --}}
                <div class="al-heading">
                    <h1>{{ __('auth.sign_in_title') }}</h1>
                    <p>{{ __('auth.sign_in_subtitle') }}</p>
                </div>

                {{-- Role tabs --}}
                <div class="al-role-tabs" role="group" aria-label="{{ __('auth.sign_in_as') }}">
                    <button type="button" data-role="applicant"
                        class="al-role-tab {{ $loginAs==='applicant' ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        {{ __('auth.role_applicant') }}
                    </button>
                    <button type="button" data-role="respondent"
                        class="al-role-tab {{ $loginAs==='respondent' ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ __('auth.role_respondent') }}
                    </button>
                    <span class="al-role-indicator" id="al-role-indicator"></span>
                </div>
                <p class="al-panel-label" data-panel-label>{{ $panelLabel }}</p>

                {{-- Alerts --}}
                @if(session('success'))
                <div class="al-alert al-alert-ok">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                @endif
                @if($errors->any())
                <div class="al-alert al-alert-err">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 mt-px" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
                @endif

                {{-- Form --}}
                <form method="POST" action="{{ route('applicant.login.submit') }}" class="al-form">
                    @csrf
                    <input type="hidden" name="login_as" id="login_as" value="{{ $loginAs }}">

                    {{-- Email --}}
                    <div class="al-field">
                        <label for="email" class="al-label">{{ __('auth.email') }}</label>
                        <div class="al-input-wrap">
                            <svg class="al-input-ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <input type="email" id="email" name="email"
                                value="{{ old('email') }}"
                                placeholder="{{ __('auth.email_placeholder') }}"
                                class="al-input"
                                required autofocus autocomplete="email"/>
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="al-field">
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="al-label" style="margin:0">{{ __('auth.password') }}</label>
                            <a href="{{ route('applicant.password.request') }}" class="al-forgot">{{ __('auth.forgot_password') }}</a>
                        </div>
                        <div x-data="{ show:false }" class="al-input-wrap">
                            <svg class="al-input-ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <input :type="show?'text':'password'" id="password" name="password"
                                placeholder="{{ __('auth.password_placeholder') }}"
                                class="al-input pr-11"
                                required autocomplete="current-password"/>
                            <button type="button" @click="show=!show" class="al-pw-eye" :aria-label="show?'Hide':'Show'">
                                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-[1.05rem] w-[1.05rem]" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="show"  xmlns="http://www.w3.org/2000/svg" class="h-[1.05rem] w-[1.05rem]" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Remember me --}}
                    <label class="al-remember">
                        <input type="checkbox" name="remember" class="al-checkbox"/>
                        <span>{{ __('auth.remember_me') }}</span>
                    </label>

                    {{-- Submit --}}
                    <button type="submit" class="al-submit">
                        {{ __('auth.sign_in') }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </button>
                </form>

                {{-- Register --}}
                <p class="al-register-line">
                    {{ __('auth.no_account') }}
                    <a href="{{ route('applicant.register') }}">{{ __('auth.create_account') }}</a>
                </p>

            </div>
        </div>

        {{-- Footer --}}
        <p class="al-footer">&copy; {{ date('Y') }} {{ $brandName }}</p>
    </div>

    {{-- ══════════════════════════════════════════
         RIGHT — about content on dark panel
         ══════════════════════════════════════════ --}}
    <div class="al-right" :class="{ 'open': aboutOpen }" @keydown.escape.window="aboutOpen = false">
        {{-- ambient glow orbs --}}
        <div class="al-orb al-orb-1" aria-hidden="true"></div>
        <div class="al-orb al-orb-2" aria-hidden="true"></div>
        <div class="al-orb al-orb-3" aria-hidden="true"></div>

        <button type="button" class="al-about-close" @click="aboutOpen = false" aria-label="{{ __('app.Close details') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>

        @if($aboutPage)
        <div class="al-about">
            {{-- title --}}
            <h2 class="al-about-title">{{ $aboutPage->title }}</h2>

            {{-- divider --}}
            <div class="al-about-divider" aria-hidden="true"></div>

            {{-- scrollable body --}}
            <div class="al-about-body">
                {!! clean($aboutPage->body ?? '', 'cases') !!}
            </div>
        </div>
        @else
        {{-- No about page: just show brand centered --}}
        <div class="al-about-empty">
            <div class="al-empty-mark">{{ strtoupper(substr($brandName,0,2)) }}</div>
            <p class="al-empty-name">{{ $brandName }}</p>
            <p class="al-empty-sub">{{ __('auth.sign_in_subtitle') }}</p>
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
    localStorage.setItem('theme','light');
    document.documentElement.classList.remove('dark');
    document.body.classList.remove('dark','bg-gray-900');

    // Role tab switcher + sliding indicator
    const loginAsInput = document.getElementById('login_as');
    const panelLabel   = document.querySelector('[data-panel-label]');
    const indicator    = document.getElementById('al-role-indicator');
    const tabs         = document.querySelectorAll('.al-role-tab');

    function moveIndicator(tab) {
        if (!indicator || !tab) return;
        indicator.style.width  = tab.offsetWidth  + 'px';
        indicator.style.left   = tab.offsetLeft   + 'px';
    }

    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            tabs.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            loginAsInput.value = btn.dataset.role;
            if (panelLabel) {
                panelLabel.textContent = btn.dataset.role === 'respondent'
                    ? @json($respondentPanelLabel)
                    : @json($applicantPanelLabel);
            }
            moveIndicator(btn);
        });
    });

    // Init indicator position
    window.addEventListener('DOMContentLoaded', () => {
        moveIndicator(document.querySelector('.al-role-tab.active'));
    });
    window.addEventListener('resize', () => {
        moveIndicator(document.querySelector('.al-role-tab.active'));
    });
</script>

<style>
/* ── Reset body ──────────────────────────────── */
html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }

/* ── Shell ───────────────────────────────────── */
.al-shell {
    display: flex;
    height: 100vh;
    width: 100%;
    overflow: hidden;
    font-family: inherit;
}

/* ══════════════════════════════════════════════
   LEFT PANEL
══════════════════════════════════════════════ */
.al-left {
    display: flex;
    flex-direction: column;
    width: 100%;
    background: #ffffff;
    overflow-y: auto;
    position: relative;
}
@media (min-width: 1024px) {
    .al-left { width: 460px; flex-shrink: 0; }
}

/* top strip */
.al-topstrip {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.375rem 2rem;
    flex-shrink: 0;
    border-bottom: 1px solid #f1f5f9;
}
.al-top-actions {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.al-logo-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}
.al-logo-img {
    height: 2rem; width: 2rem;
    border-radius: 0.5rem; object-fit: contain;
}
.al-logo-mark {
    display: inline-flex; align-items: center; justify-content: center;
    height: 2rem; width: 2rem; border-radius: 0.5rem;
    background: linear-gradient(135deg,#ea580c,#9a3412);
    color: #fff; font-size: 0.6875rem; font-weight: 800;
    letter-spacing: 0.04em;
}
.al-logo-name {
    font-size: 0.9rem; font-weight: 700; color: #0f172a; letter-spacing: -0.01em;
}

/* language switch */
.al-lang-switch {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.1875rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.625rem;
    background: #f8fafc;
}
.al-lang-btn {
    display: inline-flex; align-items: center; gap: 0.375rem;
    min-width: 3.25rem;
    justify-content: center;
    padding: 0.3125rem 0.5rem;
    border-radius: 0.4375rem; border: 1px solid transparent;
    color: #475569; cursor: pointer; text-decoration: none;
    font-size: 0.75rem; font-weight: 700;
    transition: background 120ms, border-color 120ms, color 120ms;
}
.al-lang-btn:hover { background: #fff; color: #0f172a; }
.al-lang-btn.active {
    background: #fff;
    border-color: #fed7aa;
    color: #c2410c;
    box-shadow: 0 1px 3px rgb(15 23 42/0.08);
}

/* centered form area */
.al-form-area {
    flex: 1; display: flex; align-items: center; justify-content: center;
    padding: 2rem;
}
.al-form-inner { width: 100%; max-width: 22rem; }

/* heading */
.al-heading { margin-bottom: 1.75rem; }
.al-heading h1 {
    font-size: 1.75rem; font-weight: 800; color: #0f172a;
    letter-spacing: -0.03em; line-height: 1.2; margin: 0 0 0.375rem;
}
.al-heading p {
    font-size: 0.875rem; color: #64748b; margin: 0; line-height: 1.5;
}

/* role tabs */
.al-role-tabs {
    display: flex;
    position: relative;
    background: #f1f5f9;
    border-radius: 0.75rem;
    padding: 0.25rem;
    margin-bottom: 0.5rem;
    gap: 0;
}
.al-role-tab {
    flex: 1; display: flex; align-items: center; justify-content: center;
    gap: 0.375rem;
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.8125rem; font-weight: 600;
    color: #64748b; background: transparent; border: none; cursor: pointer;
    position: relative; z-index: 1;
    transition: color 180ms;
}
.al-role-tab.active { color: #0f172a; }
.al-role-indicator {
    position: absolute; top: 0.25rem; bottom: 0.25rem; left: 0.25rem;
    background: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 1px 4px rgb(0 0 0/0.12);
    transition: left 200ms cubic-bezier(0.4,0,0.2,1), width 200ms cubic-bezier(0.4,0,0.2,1);
    pointer-events: none;
}
.al-panel-label {
    font-size: 0.6875rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.12em;
    color: #ea580c; margin: 0 0 1.25rem 0.125rem;
}

/* alerts */
.al-alert {
    display: flex; align-items: flex-start; gap: 0.5rem;
    border-radius: 0.625rem; padding: 0.625rem 0.875rem;
    font-size: 0.8rem; line-height: 1.5; margin-bottom: 1rem;
}
.al-alert ul { margin: 0; padding: 0; list-style: none; }
.al-alert-ok { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
.al-alert-err { background: #fff1f2; border: 1px solid #fecdd3; color: #be123c; }

/* form */
.al-form { display: flex; flex-direction: column; gap: 1.125rem; }
.al-field { display: flex; flex-direction: column; }

.al-label {
    font-size: 0.8125rem; font-weight: 600; color: #334155;
    margin-bottom: 0.375rem; display: block;
}
.al-input-wrap { position: relative; }
.al-input-ico {
    position: absolute; top: 50%; left: 0.875rem;
    transform: translateY(-50%);
    width: 1rem; height: 1rem; color: #94a3b8; pointer-events: none;
}
.al-input {
    display: block; width: 100%; height: 2.75rem;
    padding: 0 0.875rem 0 2.625rem;
    border-radius: 0.625rem;
    border: 1.5px solid #e2e8f0;
    background: #fff;
    font-size: 0.875rem; color: #0f172a;
    transition: border-color 150ms, box-shadow 150ms;
    outline: none; box-sizing: border-box;
}
.al-input::placeholder { color: #94a3b8; }
.al-input:hover { border-color: #cbd5e1; }
.al-input:focus {
    border-color: #ea580c;
    box-shadow: 0 0 0 3px rgb(234 88 12/0.13);
}
.al-pw-eye {
    position: absolute; inset-y: 0; right: 0;
    display: flex; align-items: center; padding: 0 0.875rem;
    color: #94a3b8; background: none; border: none; cursor: pointer;
    transition: color 120ms;
}
.al-pw-eye:hover { color: #475569; }

.al-forgot {
    font-size: 0.8125rem; font-weight: 600;
    color: #ea580c; text-decoration: none; transition: color 120ms;
}
.al-forgot:hover { color: #c2410c; }

.al-remember {
    display: flex; align-items: center; gap: 0.625rem;
    cursor: pointer; user-select: none;
    font-size: 0.8125rem; color: #475569;
}
.al-checkbox {
    width: 1rem; height: 1rem;
    border-radius: 0.3125rem; border: 1.5px solid #cbd5e1;
    accent-color: #ea580c; cursor: pointer; flex-shrink: 0;
}

/* submit */
.al-submit {
    display: flex; align-items: center; justify-content: center; gap: 0.5rem;
    width: 100%; height: 2.875rem;
    border-radius: 0.75rem; border: none; cursor: pointer;
    background: linear-gradient(135deg,#ea580c 0%,#9a3412 100%);
    color: #fff; font-size: 0.9375rem; font-weight: 700;
    letter-spacing: 0.01em;
    box-shadow: 0 4px 16px rgb(234 88 12/0.38);
    transition: transform 150ms, box-shadow 150ms, opacity 150ms;
    margin-top: 0.25rem;
}
.al-submit:hover {
    opacity: 0.93;
    box-shadow: 0 6px 22px rgb(234 88 12/0.48);
    transform: translateY(-1px);
}
.al-submit:active { transform: scale(0.98); box-shadow: 0 2px 10px rgb(234 88 12/0.3); }

/* register link */
.al-register-line {
    margin-top: 1.25rem; text-align: center;
    font-size: 0.8125rem; color: #64748b;
}
.al-register-line a {
    font-weight: 700; color: #ea580c; text-decoration: none; transition: color 120ms;
}
.al-register-line a:hover { color: #c2410c; }

.al-mobile-about-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    min-height: 2.625rem;
    margin-top: 1rem;
    border-radius: 0.75rem;
    border: 1px solid #fed7aa;
    background: #fff7ed;
    color: #c2410c;
    font-size: 0.875rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 120ms, border-color 120ms, color 120ms;
}
.al-mobile-about-btn-top {
    width: auto;
    min-height: 2.125rem;
    margin-top: 0;
    padding: 0 0.75rem;
    white-space: nowrap;
    font-size: 0.75rem;
}
.al-mobile-about-btn:hover {
    background: #ffedd5;
    border-color: #fdba74;
    color: #9a3412;
}
@media (min-width: 1024px) {
    .al-mobile-about-btn { display: none; }
}

/* footer */
.al-footer {
    flex-shrink: 0; text-align: center;
    padding: 1rem 2rem 1.25rem;
    font-size: 0.6875rem; color: #94a3b8;
}

/* ══════════════════════════════════════════════
   RIGHT PANEL
══════════════════════════════════════════════ */
.al-right {
    display: none;
    flex: 1; position: relative; overflow: hidden;
    background: #080e27;
}
.al-right.open {
    position: fixed;
    inset: 0;
    z-index: 80;
    display: flex;
    align-items: stretch;
}
@media (min-width: 1024px) {
    .al-right {
        display: flex;
        align-items: stretch;
        position: relative;
        inset: auto;
        z-index: auto;
    }
}

/* ambient orbs */
.al-orb {
    position: absolute; border-radius: 9999px;
    filter: blur(72px); pointer-events: none;
}
.al-orb-1 {
    top: -15%; left: -10%; width: 65%; height: 65%;
    background: radial-gradient(circle, rgb(79 70 229/0.35) 0%, transparent 70%);
}
.al-orb-2 {
    bottom: -15%; right: -10%; width: 60%; height: 60%;
    background: radial-gradient(circle, rgb(234 88 12/0.28) 0%, transparent 70%);
}
.al-orb-3 {
    top: 40%; left: 30%; width: 40%; height: 40%;
    background: radial-gradient(circle, rgb(6 182 212/0.12) 0%, transparent 70%);
}

.al-about-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    z-index: 20;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 0.625rem;
    border: 1px solid rgb(255 255 255/0.16);
    background: rgb(255 255 255/0.08);
    color: rgb(255 255 255/0.82);
    cursor: pointer;
}
.al-about-close:hover {
    background: rgb(255 255 255/0.14);
    color: #fff;
}
@media (min-width: 1024px) {
    .al-about-close { display: none; }
}

/* about panel */
.al-about {
    position: relative; z-index: 10;
    display: flex; flex-direction: column;
    width: 100%; padding: 2.5rem;
}
@media (max-width: 1023px) {
    .al-about {
        padding: 4.5rem 1.5rem 1.5rem;
    }
}

.al-about-title {
    font-size: 1.5rem; font-weight: 800;
    color: #fff; line-height: 1.25;
    margin: 0 0 1rem; flex-shrink: 0;
    letter-spacing: -0.02em;
}

.al-about-divider {
    flex-shrink: 0; height: 1px;
    background: linear-gradient(90deg,rgb(255 255 255/0.12),transparent);
    margin-bottom: 1.25rem;
}

.al-about-body {
    flex: 1; min-height: 0;
    overflow-y: auto;
    font-size: 0.875rem; line-height: 1.8;
    color: rgb(255 255 255/0.6);
    word-break: break-word;
    padding-right: 0.25rem;
}
/* scrollbar */
.al-about-body::-webkit-scrollbar { width: 4px; }
.al-about-body::-webkit-scrollbar-track { background: transparent; }
.al-about-body::-webkit-scrollbar-thumb { background: rgb(255 255 255/0.15); border-radius: 9999px; }
.al-about-body::-webkit-scrollbar-thumb:hover { background: rgb(255 255 255/0.25); }

/* about body typography */
.al-about-body h1,.al-about-body h2,.al-about-body h3 {
    color: rgb(255 255 255/0.9); font-weight: 700; margin: 1em 0 0.4em;
}
.al-about-body p { margin: 0 0 0.75em; }
.al-about-body a { color: #fb923c; text-decoration: underline; }
.al-about-body ul,.al-about-body ol { padding-left: 1.25em; margin-bottom: 0.75em; }

/* empty state */
.al-about-empty {
    position: relative; z-index: 10;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    width: 100%; text-align: center; padding: 2rem;
}
.al-empty-mark {
    width: 5rem; height: 5rem; border-radius: 1.5rem;
    background: rgb(255 255 255/0.07);
    border: 1px solid rgb(255 255 255/0.15);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; font-weight: 800;
    color: rgb(255 255 255/0.7);
    margin-bottom: 1.25rem;
}
.al-empty-name {
    font-size: 1.375rem; font-weight: 700; color: #fff; margin: 0 0 0.5rem;
}
.al-empty-sub { font-size: 0.875rem; color: rgb(255 255 255/0.45); margin: 0; }
</style>
@endpush
