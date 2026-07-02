@php
$settings = null;
try {
$settings = \App\Models\SystemSetting::query()->first();
} catch (\Throwable $e) {
$settings = null;
}

$brandName = $settings?->app_name ?? config('app.name', 'Court MS');
$logoPath  = $settings?->logo_path ?? null;

$lawyerQuestionText = __('auth.lawyer_question');
$yesText            = __('auth.yes');
$noText             = __('auth.no');
@endphp

@extends('layouts.fullscreen-layout')

@section('content')
<div class="ar-shell">

    {{-- ── Top strip ──────────────────────────────────────────────── --}}
    <div class="ar-topstrip">
        <a href="/" class="ar-brand">
            @if ($logoPath)
                <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ $brandName }}" class="ar-brand-logo">
            @else
                <span class="ar-brand-mark">{{ mb_strtoupper(mb_substr(strip_tags($brandName), 0, 2)) }}</span>
            @endif
            <span class="ar-brand-name">{{ $brandName }}</span>
        </a>

        @if(Route::has('language.switch') && ($settings?->show_language_switcher ?? true))
        <div class="ar-lang-switch" aria-label="{{ __('app.Language') }}">
            <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
               class="ar-lang-opt {{ app()->getLocale() === 'en' ? 'active' : '' }}">
                <span class="fi fi-us text-sm"></span>
                <span>EN</span>
            </a>
            <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
               class="ar-lang-opt {{ app()->getLocale() === 'am' ? 'active' : '' }}">
                <span class="fi fi-et text-sm"></span>
                <span>አማ</span>
            </a>
        </div>
        @endif
    </div>

    {{-- ── Body ────────────────────────────────────────────────────── --}}
    <div class="ar-body">
        <div class="ar-card">

            {{-- Heading --}}
            <div class="ar-heading">
                <h1>{{ __('auth.sign_up_title') }}</h1>
                <p>{{ __('auth.sign_up_subtitle') }}</p>
            </div>

            {{-- Errors --}}
            @if ($errors->any())
            <div class="ar-alert">
                <svg xmlns="http://www.w3.org/2000/svg" class="ar-alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <span>{{ __('auth.please_fix_errors') }}</span>
            </div>
            @endif

            <form method="POST" action="{{ route('applicant.register.submit') }}" class="ar-form"
                enctype="multipart/form-data" x-data="{ isLawyer: '{{ old('is_lawyer', '0') }}' }">
                @csrf

                {{-- ── Section: Role ──────────────────────────────────── --}}
                <div class="ar-section">
                    <div class="ar-section-label">{{ __('auth.lawyer_question') }}</div>
                    <div class="ar-radio-group">
                        <label class="ar-radio-label">
                            <input type="radio" name="is_lawyer" value="1" class="ar-radio" x-model="isLawyer"
                                @checked(old('is_lawyer') === '1')>
                            <span class="ar-radio-dot"></span>
                            {{ $yesText }}
                        </label>
                        <label class="ar-radio-label">
                            <input type="radio" name="is_lawyer" value="0" class="ar-radio" x-model="isLawyer"
                                @checked(old('is_lawyer', '0') === '0')>
                            <span class="ar-radio-dot"></span>
                            {{ $noText }}
                        </label>
                    </div>
                    @error('is_lawyer')
                    <p class="ar-error">{{ $message }}</p>
                    @enderror

                    <div x-show="isLawyer === '1'" x-cloak class="ar-field" style="margin-top: 1rem;">
                        <label class="ar-label">{{ __('auth.lawyer_document') }}</label>
                        <input type="file" name="lawyer_document" accept=".pdf"
                            class="ar-input @error('lawyer_document') ar-input-error @enderror" x-bind:required="isLawyer === '1'">
                        @error('lawyer_document')
                        <p class="ar-error">{{ $message }}</p>
                        @else
                        <p class="ar-hint">{{ __('auth.lawyer_document_hint') }}</p>
                        @enderror
                    </div>
                </div>

                {{-- ── Section: Personal ──────────────────────────────── --}}
                <div class="ar-section">
                    <div class="ar-section-label">{{ __('auth.personal_info') }}</div>

                    <div class="ar-grid-3">
                        <div class="ar-field">
                            <label class="ar-label">{{ __('auth.first_name') }}</label>
                            <input name="first_name" value="{{ old('first_name') }}" required class="ar-input @error('first_name') ar-input-error @enderror">
                            @error('first_name')<p class="ar-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="ar-field">
                            <label class="ar-label">{{ __('auth.middle_name') }}</label>
                            <input name="middle_name" value="{{ old('middle_name') }}" required class="ar-input @error('middle_name') ar-input-error @enderror">
                            @error('middle_name')<p class="ar-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="ar-field">
                            <label class="ar-label">{{ __('auth.last_name') }}</label>
                            <input name="last_name" value="{{ old('last_name') }}" required class="ar-input @error('last_name') ar-input-error @enderror">
                            @error('last_name')<p class="ar-error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="ar-grid-3">
                        <div class="ar-field">
                            <label class="ar-label">{{ __('auth.gender') }}</label>
                            <select name="gender" class="ar-input ar-select @error('gender') ar-input-error @enderror">
                                <option value="">{{ __('auth.select_option') }}</option>
                                <option value="male"   @selected(old('gender') === 'male')>{{ __('auth.male') }}</option>
                                <option value="female" @selected(old('gender') === 'female')>{{ __('auth.female') }}</option>
                            </select>
                            @error('gender')<p class="ar-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="ar-field">
                            <label class="ar-label">{{ __('auth.phone') }}</label>
                            <div class="ar-phone-wrap @error('phone') ar-input-error @enderror">
                                <span class="ar-phone-prefix">
                                    <span class="fi fi-et"></span>
                                    +251
                                </span>
                                <input id="phone_display" type="tel" inputmode="numeric"
                                    placeholder="9XXXXXXXXX"
                                    minlength="9" maxlength="10"
                                    pattern="[0-9]{9,10}"
                                    value="{{ old('phone') ? ltrim(preg_replace('/^\+251/', '', old('phone')), '0') : '' }}"
                                    class="ar-phone-input"
                                    autocomplete="tel-national">
                                <input type="hidden" name="phone" id="phone_hidden"
                                    value="{{ old('phone') }}">
                            </div>
                            @error('phone')
                            <p class="ar-error">{{ $message }}</p>
                            @else
                            <p class="ar-hint">{{ __('auth.phone_hint', ['min' => 9, 'max' => 10]) }}</p>
                            @enderror
                        </div>
                        <div class="ar-field">
                            <label class="ar-label">{{ __('auth.national_id') }}</label>
                            <input name="national_id_number" value="{{ old('national_id_number') }}" required
                                inputmode="numeric" autocomplete="off" maxlength="19"
                                pattern="\d{4}\s\d{4}\s\d{4}\s\d{4}"
                                title="{{ __('auth.national_id_hint') }}"
                                placeholder="{{ __('auth.national_id_placeholder') }}"
                                class="ar-input @error('national_id_number') ar-input-error @enderror">
                            @error('national_id_number')
                            <p class="ar-error">{{ $message }}</p>
                            @else
                            <p class="ar-hint">{{ __('auth.national_id_hint') }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- ── Section: Organization ───────────────────────────── --}}
                <div class="ar-section">
                    <div class="ar-section-label">{{ __('auth.organization_info') }}</div>
                    <div class="ar-grid-2">
                        <div class="ar-field">
                            <label class="ar-label">{{ __('auth.position') }}</label>
                            <div class="ar-input-wrap">
                                <svg class="ar-input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <input name="position" value="{{ old('position') }}" required class="ar-input ar-input-with-icon @error('position') ar-input-error @enderror">
                            </div>
                            @error('position')<p class="ar-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="ar-field">
                            <label class="ar-label">{{ __('auth.organization_name') }}</label>
                            <div class="ar-input-wrap">
                                <svg class="ar-input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <input name="organization_name" value="{{ old('organization_name') }}" required class="ar-input ar-input-with-icon @error('organization_name') ar-input-error @enderror">
                            </div>
                            @error('organization_name')<p class="ar-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- ── Section: Account Details ─────────────────────────── --}}
                <div class="ar-section">
                    <div class="ar-section-label">{{ __('auth.account_details') }}</div>

                    <div class="ar-grid-2">
                        <div class="ar-field">
                            <label class="ar-label">{{ __('auth.email') }}</label>
                            <div class="ar-input-wrap">
                                <svg class="ar-input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <input type="email" name="email" value="{{ old('email') }}" required class="ar-input ar-input-with-icon @error('email') ar-input-error @enderror">
                            </div>
                            @error('email')<p class="ar-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="ar-field">
                            <label class="ar-label">{{ __('auth.address') }}</label>
                            <div class="ar-input-wrap">
                                <svg class="ar-input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <input name="address" value="{{ old('address') }}" required class="ar-input ar-input-with-icon @error('address') ar-input-error @enderror">
                            </div>
                            @error('address')<p class="ar-error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="ar-grid-2">
                        <div class="ar-field">
                            <label class="ar-label" for="password">{{ __('auth.password') }}</label>
                            <div class="ar-input-wrap">
                                <svg class="ar-input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <input id="password" type="password" name="password" required
                                    class="ar-input ar-input-with-icon ar-input-pr @error('password') ar-input-error @enderror">
                                <button type="button" class="ar-eye-btn" data-toggle-password="password"
                                    aria-label="{{ __('auth.show_password') }}" aria-pressed="false">
                                    <svg class="ar-eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                            d="M2.5 12.5S5.5 6 12 6s9.5 6.5 9.5 6.5S18.5 19 12 19 2.5 12.5 2.5 12.5z"/>
                                        <circle cx="12" cy="12.5" r="2.5" stroke-width="1.6"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')<p class="ar-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="ar-field">
                            <label class="ar-label" for="password_confirmation">{{ __('auth.confirm_password') }}</label>
                            <div class="ar-input-wrap">
                                <svg class="ar-input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <input id="password_confirmation" type="password" name="password_confirmation" required
                                    class="ar-input ar-input-with-icon ar-input-pr @error('password_confirmation') ar-input-error @enderror">
                                <button type="button" class="ar-eye-btn" data-toggle-password="password_confirmation"
                                    aria-label="{{ __('auth.show_password') }}" aria-pressed="false">
                                    <svg class="ar-eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                            d="M2.5 12.5S5.5 6 12 6s9.5 6.5 9.5 6.5S18.5 19 12 19 2.5 12.5 2.5 12.5z"/>
                                        <circle cx="12" cy="12.5" r="2.5" stroke-width="1.6"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password_confirmation')<p class="ar-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- ── Actions ───────────────────────────────────────────── --}}
                <div class="ar-actions">
                    <a href="{{ route('applicant.login') }}" class="ar-login-link">
                        {{ __('auth.already_have_account') }}
                    </a>
                    <button type="submit" class="ar-submit">
                        {{ __('auth.create_account_button') }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Footer ───────────────────────────────────────────────────── --}}
    <div class="ar-footer">
        &copy; {{ date('Y') }} {{ strip_tags($brandName) }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        localStorage.setItem('theme', 'light');
        document.documentElement.classList.remove('dark');
        document.documentElement.dataset.theme = 'light';
    })();

    document.addEventListener('DOMContentLoaded', function () {
        /* National ID formatter */
        const idInput = document.querySelector('input[name="national_id_number"]');
        if (idInput) {
            const fmt = v => (v || '').replace(/[^0-9]/g, '').slice(0, 16).match(/.{1,4}/g)?.join(' ') ?? '';
            idInput.addEventListener('input', e => e.target.value = fmt(e.target.value));
            idInput.addEventListener('blur',  () => idInput.value = fmt(idInput.value));
        }

        /* Phone +251 prefix logic */
        const phoneDisplay = document.getElementById('phone_display');
        const phoneHidden  = document.getElementById('phone_hidden');
        if (phoneDisplay && phoneHidden) {
            const sanitize = v => v.replace(/[^0-9]/g, '').slice(0, 10);
            phoneDisplay.addEventListener('input', () => {
                phoneDisplay.value = sanitize(phoneDisplay.value);
                phoneHidden.value  = '+251' + phoneDisplay.value;
            });
            /* Sync once on load for old() repopulation */
            if (phoneDisplay.value) phoneHidden.value = '+251' + sanitize(phoneDisplay.value);
            /* Sync before submit */
            phoneDisplay.closest('form').addEventListener('submit', () => {
                phoneHidden.value = '+251' + sanitize(phoneDisplay.value);
            });
        }

        /* Password toggles */
        document.querySelectorAll('[data-toggle-password]').forEach(btn => {
            const input = document.getElementById(btn.dataset.togglePassword);
            if (!input) return;
            btn.addEventListener('click', () => {
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            });
        });
    });
</script>
<style>
    /* ── Shell ──────────────────────────────────────────────────────── */
    .ar-shell {
        min-height: 100vh;
        background: #ffffff;
        display: flex;
        flex-direction: column;
    }

    /* ── Top strip ──────────────────────────────────────────────────── */
    .ar-topstrip {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        flex-shrink: 0;
    }
    .ar-brand {
        display: inline-flex;
        align-items: center;
        gap: 0.625rem;
        text-decoration: none;
    }
    .ar-brand-logo {
        height: 2rem;
        width: auto;
        border-radius: 0.5rem;
    }
    .ar-brand-mark {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 2rem;
        width: 2rem;
        border-radius: 0.5rem;
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: #fff;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        flex-shrink: 0;
    }
    .ar-brand-name {
        font-size: 0.9375rem;
        font-weight: 700;
        color: #0f172a;
        max-width: 18rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .ar-lang-switch {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.1875rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.625rem;
        background: #f8fafc;
    }
    .ar-lang-opt {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        min-width: 3.25rem;
        justify-content: center;
        padding: 0.3125rem 0.5rem;
        border-radius: 0.4375rem;
        border: 1px solid transparent;
        color: #475569;
        cursor: pointer;
        text-decoration: none;
        font-size: 0.75rem;
        font-weight: 700;
        transition: background 120ms, border-color 120ms, color 120ms;
    }
    .ar-lang-opt:hover { background: #fff; color: #0f172a; }
    .ar-lang-opt.active {
        background: #fff;
        border-color: #fed7aa;
        color: #c2410c;
        box-shadow: 0 1px 3px rgb(15 23 42 / 0.08);
    }

    /* ── Body / Card ────────────────────────────────────────────────── */
    .ar-body {
        flex: 1;
        display: flex;
        justify-content: center;
        padding: 2rem 2rem 1rem;
    }
    @media (min-width: 768px) {
        .ar-body { padding: 2.5rem 3rem 1.5rem; }
    }
    .ar-card {
        width: 100%;
        max-width: 1100px;
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 1.25rem;
        padding: 2rem 2rem 1.5rem;
        box-shadow: 0 4px 24px rgb(15 23 42 / 0.05);
    }
    @media (min-width: 768px) {
        .ar-card { padding: 2.5rem 3rem 2rem; }
    }
    @media (max-width: 540px) {
        .ar-card { padding: 1.25rem 1rem 1rem; border: none; box-shadow: none; }
    }

    /* ── Heading ────────────────────────────────────────────────────── */
    .ar-heading {
        margin-bottom: 2rem;
    }
    .ar-heading h1 {
        font-size: 1.75rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.02em;
        line-height: 1.2;
        margin: 0 0 0.375rem;
    }
    .ar-heading p {
        font-size: 0.9rem;
        color: #64748b;
        margin: 0;
    }

    /* ── Alert ──────────────────────────────────────────────────────── */
    .ar-alert {
        display: flex;
        align-items: flex-start;
        gap: 0.625rem;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 0.75rem;
        padding: 0.875rem 1rem;
        margin-bottom: 1.5rem;
        color: #b91c1c;
        font-size: 0.875rem;
    }
    .ar-alert-icon {
        flex-shrink: 0;
        width: 1rem;
        height: 1rem;
        margin-top: 0.1rem;
    }
    .ar-alert-list {
        margin: 0;
        padding: 0 0 0 1.125rem;
    }
    .ar-alert-list li { margin-bottom: 0.125rem; }

    /* ── Form sections ──────────────────────────────────────────────── */
    .ar-form { display: flex; flex-direction: column; gap: 0; }
    .ar-section {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        padding-bottom: 1.75rem;
        margin-bottom: 1.75rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .ar-section:last-of-type { border-bottom: none; }
    .ar-section-label {
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #94a3b8;
    }

    /* ── Grids ──────────────────────────────────────────────────────── */
    .ar-grid-2 {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    .ar-grid-3 {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    @media (min-width: 540px) {
        .ar-grid-2 { grid-template-columns: 1fr 1fr; }
    }
    @media (min-width: 640px) {
        .ar-grid-3 { grid-template-columns: 1fr 1fr 1fr; }
    }

    /* ── Fields & Inputs ────────────────────────────────────────────── */
    .ar-field { display: flex; flex-direction: column; gap: 0.375rem; }
    .ar-label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #374151;
    }
    .ar-input-wrap { position: relative; }
    .ar-input-icon {
        position: absolute;
        left: 0.875rem;
        top: 50%;
        transform: translateY(-50%);
        width: 0.9375rem;
        height: 0.9375rem;
        color: #9ca3af;
        pointer-events: none;
    }
    .ar-input {
        height: 2.625rem;
        width: 100%;
        border-radius: 0.625rem;
        border: 1.5px solid #e2e8f0;
        background: #ffffff;
        padding: 0 0.875rem;
        font-size: 0.875rem;
        color: #1e293b;
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
        box-sizing: border-box;
    }
    .ar-input::placeholder { color: #cbd5e1; }
    .ar-input:focus {
        border-color: #f97316;
        box-shadow: 0 0 0 3px rgb(249 115 22 / 0.12);
    }
    .ar-input-with-icon { padding-left: 2.5rem; }
    .ar-input-pr { padding-right: 2.75rem; }
    .ar-select { appearance: none; cursor: pointer; }
    .ar-hint { font-size: 0.75rem; color: #94a3b8; margin: 0; }
    .ar-error { font-size: 0.75rem; color: #dc2626; margin: 0; font-weight: 500; }
    .ar-input-error, .ar-phone-wrap.ar-input-error { border-color: #ef4444; }
    .ar-input-error:focus, .ar-phone-wrap.ar-input-error:focus-within {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgb(239 68 68 / 0.12);
    }
    [x-cloak] { display: none !important; }

    /* ── Phone prefix widget ─────────────────────────────────────────── */
    .ar-phone-wrap {
        display: flex;
        align-items: stretch;
        border: 1.5px solid #e2e8f0;
        border-radius: 0.625rem;
        overflow: hidden;
        background: #fff;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .ar-phone-wrap:focus-within {
        border-color: #f97316;
        box-shadow: 0 0 0 3px rgb(249 115 22 / 0.12);
    }
    .ar-phone-prefix {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0 0.75rem;
        background: #f8fafc;
        border-right: 1.5px solid #e2e8f0;
        font-size: 0.8125rem;
        font-weight: 700;
        color: #374151;
        white-space: nowrap;
        flex-shrink: 0;
        user-select: none;
    }
    .ar-phone-input {
        flex: 1;
        height: 2.625rem;
        border: none;
        outline: none;
        background: transparent;
        padding: 0 0.875rem;
        font-size: 0.875rem;
        color: #1e293b;
        min-width: 0;
        font-variant-numeric: tabular-nums;
    }
    .ar-phone-input::placeholder { color: #cbd5e1; }

    /* ── Eye toggle ─────────────────────────────────────────────────── */
    .ar-eye-btn {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        color: #94a3b8;
        display: flex;
        align-items: center;
    }
    .ar-eye-btn:hover { color: #64748b; }
    .ar-eye { width: 1rem; height: 1rem; }

    /* ── Radio group ────────────────────────────────────────────────── */
    .ar-radio-group {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }
    .ar-radio-label {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: #374151;
        cursor: pointer;
        font-weight: 500;
    }
    .ar-radio {
        width: 1rem;
        height: 1rem;
        accent-color: #ea580c;
        cursor: pointer;
    }

    /* ── Actions ────────────────────────────────────────────────────── */
    .ar-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        padding-top: 0.5rem;
    }
    .ar-login-link {
        font-size: 0.875rem;
        color: #ea580c;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.15s;
    }
    .ar-login-link:hover { color: #c2410c; }
    .ar-submit {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.75rem;
        border-radius: 0.625rem;
        border: none;
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: #fff;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.15s, transform 0.1s;
        box-shadow: 0 2px 8px rgb(234 88 12 / 0.3);
    }
    .ar-submit:hover { opacity: 0.92; transform: translateY(-1px); }
    .ar-submit:active { transform: none; opacity: 1; }

    /* ── Footer ─────────────────────────────────────────────────────── */
    .ar-footer {
        text-align: center;
        padding: 1.25rem;
        font-size: 0.8rem;
        color: #94a3b8;
        border-top: 1px solid #f1f5f9;
        margin-top: 2rem;
        flex-shrink: 0;
    }
</style>
@endpush
