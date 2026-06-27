<x-admin-layout title="{{ __('settings.title') }}">
<style>
/* ── Settings design tokens ─────────────────────────────── */
.ss-wrap { max-width:100%; }
.ss-header { padding:1.5rem 0 1.125rem; border-bottom:1px solid var(--border); margin-bottom:1.5rem; }
.ss-title { font-size:1.25rem; font-weight:700; color:var(--text); letter-spacing:-.02em; }
.ss-subtitle { font-size:.8125rem; color:var(--text-subtle); margin-top:.2rem; }

/* Tab strip */
.ss-tabs { display:flex; gap:0; border-bottom:1px solid var(--border); margin-bottom:1.5rem; overflow-x:auto; scrollbar-width:none; }
.ss-tabs::-webkit-scrollbar { display:none; }
.ss-tab { display:inline-flex; align-items:center; gap:.4rem; padding:.6rem .9rem; font-size:.8rem; font-weight:500;
          color:var(--text-subtle); border-bottom:2px solid transparent; margin-bottom:-1px;
          cursor:pointer; white-space:nowrap; transition:color .15s,border-color .15s;
          background:none; border-top:none; border-left:none; border-right:none; }
.ss-tab:hover { color:var(--text); }
.ss-tab.active { color:rgb(var(--ac)); border-bottom-color:rgb(var(--ac)); font-weight:600; }
.ss-tab svg { width:.9rem; height:.9rem; flex-shrink:0; }

/* Cards */
.ss-card { background:var(--surface-strong); border:1px solid var(--border); border-radius:.875rem; padding:1.375rem; margin-bottom:1rem; }
.ss-card-title { font-size:.8125rem; font-weight:700; color:var(--text); margin-bottom:1rem; display:flex; align-items:center; gap:.5rem; }
.ss-card-icon { display:grid; place-items:center; width:1.625rem; height:1.625rem; border-radius:.4rem; background:rgb(var(--ac)/.1); color:rgb(var(--ac)); flex-shrink:0; }
.ss-card-icon svg { width:.8rem; height:.8rem; }
.ss-divider { border:none; border-top:1px solid var(--border); margin:1rem 0; }

/* Field label + hint */
.ss-label { display:block; font-size:.775rem; font-weight:600; color:var(--text-muted); margin-bottom:.3rem; }
.ss-hint  { font-size:.7rem; color:var(--text-subtle); margin-top:.25rem; line-height:1.4; }

/* Inputs */
.ss-input, .ss-select, .ss-textarea {
    width:100%; padding:.45rem .7rem; border-radius:.5rem;
    border:1px solid var(--border); background:var(--surface-soft);
    color:var(--text); font-size:.8125rem;
    transition:border-color .15s, box-shadow .15s;
}
.ss-input:focus, .ss-select:focus, .ss-textarea:focus {
    outline:none; border-color:rgb(var(--ac)/.55);
    box-shadow:0 0 0 3px rgb(var(--ac)/.1);
}
.ss-textarea { resize:vertical; }

/* Input with addon unit */
.ss-input-group { display:flex; border:1px solid var(--border); border-radius:.5rem; overflow:hidden; background:var(--surface-soft); }
.ss-input-group:focus-within { border-color:rgb(var(--ac)/.55); box-shadow:0 0 0 3px rgb(var(--ac)/.1); }
.ss-input-group input { flex:1; padding:.45rem .7rem; border:none; background:transparent; color:var(--text); font-size:.8125rem; min-width:0; outline:none; }
.ss-input-group-addon { padding:.45rem .7rem; font-size:.73rem; font-weight:600; color:var(--text-subtle);
                        background:var(--surface-strong); border-left:1px solid var(--border);
                        white-space:nowrap; display:flex; align-items:center; }
.ss-input-group-prefix { border-left:none; border-right:1px solid var(--border); }

/* Password reveal */
.ss-pw-wrap { position:relative; }
.ss-pw-wrap .ss-input { padding-right:2.5rem; }
.ss-pw-toggle { position:absolute; right:.6rem; top:50%; transform:translateY(-50%);
                background:none; border:none; cursor:pointer; color:var(--text-subtle);
                padding:.15rem; line-height:0; }
.ss-pw-toggle:hover { color:var(--text); }

/* Toggle rows */
.ss-toggle-row { display:flex; align-items:center; justify-content:space-between; gap:1rem;
                 padding:.7rem .875rem; border-radius:.5rem;
                 border:1px solid var(--border); background:var(--surface-soft); }
.ss-toggle-row + .ss-toggle-row { margin-top:.5rem; }
.ss-toggle-info { flex:1; min-width:0; }
.ss-toggle-name { font-size:.8125rem; font-weight:600; color:var(--text); display:block; }
.ss-toggle-desc { font-size:.7rem; color:var(--text-subtle); margin-top:.1rem; }
.ss-sw { position:relative; width:2.5rem; height:1.3rem; flex-shrink:0; }
.ss-sw input { opacity:0; width:0; height:0; position:absolute; }
.ss-sw-track { position:absolute; inset:0; border-radius:999px; background:var(--border-strong); transition:background .2s; cursor:pointer; }
.ss-sw input:checked ~ .ss-sw-track { background:rgb(var(--ac)); }
.ss-sw-thumb { position:absolute; top:.17rem; left:.17rem; width:.96rem; height:.96rem; border-radius:50%;
               background:#fff; transition:transform .2s; pointer-events:none; box-shadow:0 1px 3px rgba(0,0,0,.25); }
.ss-sw input:checked ~ .ss-sw-track .ss-sw-thumb { transform:translateX(1.2rem); }

/* Status badge */
.ss-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.2rem .55rem; border-radius:999px; font-size:.68rem; font-weight:700; }
.ss-badge-ok   { background:rgb(16 185 129/.1); color:rgb(4 120 87); border:1px solid rgb(16 185 129/.2); }
.ss-badge-warn { background:rgb(245 158 11/.1); color:rgb(146 64 14); border:1px solid rgb(245 158 11/.2); }

/* Info grid (system info) */
.ss-info-row { display:flex; align-items:center; justify-content:space-between;
               padding:.45rem .75rem; border-radius:.4rem;
               background:var(--surface-soft); border:1px solid var(--border); }
.ss-info-key { font-size:.75rem; color:var(--text-subtle); }
.ss-info-val { font-size:.75rem; font-weight:600; color:var(--text); font-family:monospace; }

/* Palette swatches */
.ss-palette { display:flex; flex-wrap:wrap; gap:.5rem; }
.ss-swatch { position:relative; }
.ss-swatch input { position:absolute; opacity:0; width:0; height:0; }
.ss-swatch-box { display:flex; align-items:center; gap:.4rem; padding:.35rem .65rem; border-radius:.4rem;
                 border:2px solid transparent; cursor:pointer; font-size:.75rem; font-weight:600;
                 transition:border-color .15s,box-shadow .15s; user-select:none; }
.ss-swatch input:checked ~ .ss-swatch-box { border-color:currentColor; box-shadow:0 0 0 3px rgba(0,0,0,.07); }
.ss-swatch-dot { width:.625rem; height:.625rem; border-radius:50%; }

/* Theme cards */
.ss-theme-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:.625rem; }
.ss-theme-card { position:relative; }
.ss-theme-card input { position:absolute; opacity:0; width:0; height:0; }
.ss-theme-box { border:2px solid var(--border); border-radius:.625rem; padding:.75rem; cursor:pointer;
                transition:border-color .15s,box-shadow .15s; text-align:center; }
.ss-theme-card input:checked ~ .ss-theme-box { border-color:rgb(var(--ac)); box-shadow:0 0 0 3px rgb(var(--ac)/.12); }
.ss-theme-preview { height:2.5rem; border-radius:.375rem; margin-bottom:.5rem; }
.ss-theme-label { font-size:.75rem; font-weight:600; color:var(--text-muted); }

/* Upload zones */
.ss-upload { border:1.5px dashed var(--border); border-radius:.625rem; padding:1rem; text-align:center;
             cursor:pointer; background:var(--surface-soft); position:relative;
             transition:border-color .15s,background .15s; }
.ss-upload:hover { border-color:rgb(var(--ac)/.45); background:rgb(var(--ac)/.04); }
.ss-upload input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
.ss-upload-icon { width:1.75rem; height:1.75rem; margin:0 auto .375rem; color:var(--text-subtle); }
.ss-upload-text { font-size:.73rem; color:var(--text-muted); }
.ss-upload-hint { font-size:.67rem; color:var(--text-subtle); margin-top:.2rem; }

/* Sticky save bar */
.ss-bar { position:sticky; bottom:0; z-index:40; margin-top:1.5rem;
          background:var(--surface-strong); border-top:1px solid var(--border);
          padding:.75rem 0; display:flex; align-items:center; justify-content:space-between;
          gap:1rem; backdrop-filter:blur(10px); }
.ss-bar-hint { font-size:.73rem; color:var(--text-subtle); display:flex; align-items:center; gap:.35rem; }
</style>

@php $s = $settings; @endphp

<div class="ss-wrap px-5 pb-20"
     x-data="{ tab: '{{ request('tab','general') }}', saving: false, telegramEnabled: @js((bool) old('telegram_enabled', $s->telegram_enabled ?? false)) }">

    {{-- Page header --}}
    <div class="ss-header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="ss-title">{{ __('settings.title') }}</h1>
                <p class="ss-subtitle">{{ __('settings.subtitle') }}</p>
            </div>
            @if($s->maintenance_mode)
                <span class="ss-badge ss-badge-warn">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    {{ __('settings.maintenance_on') }}
                </span>
            @else
                <span class="ss-badge ss-badge-ok">
                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                    {{ __('settings.system_online') }}
                </span>
            @endif
        </div>
    </div>

    @if($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700 space-y-0.5">
        @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
    </div>
    @endif

    {{-- Tab strip --}}
    <div class="ss-tabs">
        @php
        $tabs = [
            ['key'=>'general',      'label'=>__('settings.tab_general'),       'icon'=>'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
            ['key'=>'branding',     'label'=>__('settings.tab_branding'),      'icon'=>'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['key'=>'localization', 'label'=>__('settings.tab_localization'),  'icon'=>'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129'],
            ['key'=>'security',     'label'=>__('settings.tab_security'),      'icon'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
            ['key'=>'appearance',   'label'=>__('settings.tab_appearance'),    'icon'=>'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'],
            ['key'=>'notifications','label'=>__('settings.tab_notifications'), 'icon'=>'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
            ['key'=>'data',         'label'=>__('settings.tab_data_management'),'icon'=>'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4'],
            ['key'=>'api',          'label'=>__('settings.tab_api'),           'icon'=>'M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ];
        @endphp
        @foreach($tabs as $t)
        <button type="button" @click="tab='{{ $t['key'] }}'"
                :class="tab==='{{ $t['key'] }}' && 'active'" class="ss-tab">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $t['icon'] }}"/>
            </svg>
            {{ $t['label'] }}
        </button>
        @endforeach
    </div>

    {{-- ════════════════════════════════════════════════════════ --}}
    <form method="POST" action="{{ route('settings.system.update') }}"
          enctype="multipart/form-data" @submit="saving=true">
    @csrf

    {{-- ── TAB: GENERAL ────────────────────────────────────── --}}
    <div x-show="tab==='general'" x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">

        <div class="ss-card">
            <div class="ss-card-title">
                <div class="ss-card-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div>
                {{ __('settings.platform_identity') }}
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="ss-label">{{ __('settings.app_name') }} <span class="text-rose-500">*</span></label>
                    <input name="app_name" value="{{ old('app_name', $s->app_name) }}" required class="ss-input">
                    <p class="ss-hint">{{ __('settings.app_name_hint') }}</p>
                </div>
                <div>
                    <label class="ss-label">{{ __('settings.short_name') }}</label>
                    <input name="short_name" value="{{ old('short_name', $s->short_name) }}" class="ss-input" placeholder="e.g. CMS">
                </div>
                <div>
                    <label class="ss-label">{{ __('settings.welcome_message') }}</label>
                    <input name="welcome_message" value="{{ old('welcome_message', $s->welcome_message) }}" class="ss-input" placeholder="Welcome to the court portal">
                </div>
                <div>
                    <label class="ss-label">{{ __('settings.website_url') }}</label>
                    <input type="url" name="website_url" value="{{ old('website_url', $s->website_url) }}" class="ss-input" placeholder="https://court.gov.et">
                </div>
                <div class="md:col-span-2">
                    <label class="ss-label">{{ __('settings.address') }}</label>
                    <input name="address" value="{{ old('address', $s->address) }}" class="ss-input" placeholder="Bole Sub-city, Addis Ababa, Ethiopia">
                </div>
                <div class="md:col-span-2">
                    <label class="ss-label">{{ __('settings.about') }}</label>
                    <textarea name="about" rows="3" class="ss-textarea">{{ old('about', $s->about) }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="ss-label">{{ __('settings.footer_text') }}</label>
                    <input name="footer_text" value="{{ old('footer_text', $s->footer_text) }}" class="ss-input" placeholder="© {{ date('Y') }} Federal Court. All rights reserved.">
                </div>
            </div>
        </div>

        <div class="ss-card">
            <div class="ss-card-title">
                <div class="ss-card-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></div>
                {{ __('settings.contact_information') }}
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="ss-label">{{ __('settings.contact_email') }}</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $s->contact_email) }}" class="ss-input" placeholder="info@court.gov.et">
                </div>
                <div>
                    <label class="ss-label">{{ __('settings.contact_phone') }}</label>
                    <input name="contact_phone" value="{{ old('contact_phone', $s->contact_phone) }}" class="ss-input" placeholder="+251 11 ...">
                </div>
            </div>
        </div>
    </div>

    {{-- ── TAB: BRANDING ───────────────────────────────────── --}}
    <div x-show="tab==='branding'" x-cloak x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">

        <div class="ss-card">
            <div class="ss-card-title">
                <div class="ss-card-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                {{ __('settings.visual_assets') }}
                <span class="ml-auto text-[.68rem] text-[var(--text-subtle)] font-normal">{{ __('settings.keep_current_file') }}</span>
            </div>

            <div class="mb-4">
                <label class="ss-label mb-1.5">{{ __('settings.header_banner') }}</label>
                <div class="ss-upload">
                    <input type="file" name="banner" accept=".png,.jpg,.jpeg,.webp">
                    <svg class="ss-upload-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="ss-upload-text">{{ __('settings.upload_banner') }}</p>
                    <p class="ss-upload-hint">{{ __('settings.banner_hint') }}</p>
                </div>
                @if($s->banner_path)
                <div class="mt-2 rounded-lg border border-[var(--border)] overflow-hidden">
                    <img src="{{ asset('storage/'.$s->banner_path) }}" class="w-full max-h-28 object-cover">
                </div>
                @endif
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                @php
                $assets = [
                    ['name'=>'logo',    'label'=>__('settings.logo'),          'hint'=>__('settings.logo_hint'),    'accept'=>'.png,.jpg,.jpeg,.svg,.webp', 'path'=>$s->logo_path,    'h'=>'h-12'],
                    ['name'=>'favicon', 'label'=>__('settings.favicon'),       'hint'=>__('settings.favicon_hint'), 'accept'=>'.png,.ico',                  'path'=>$s->favicon_path, 'h'=>'h-8 w-8'],
                    ['name'=>'seal',    'label'=>__('settings.official_seal'), 'hint'=>__('settings.seal_hint'),    'accept'=>'.png',                        'path'=>$s->seal_path,    'h'=>'h-14 w-14'],
                ];
                @endphp
                @foreach($assets as $asset)
                <div>
                    <label class="ss-label mb-1.5">{{ $asset['label'] }}</label>
                    <div class="ss-upload">
                        <input type="file" name="{{ $asset['name'] }}" accept="{{ $asset['accept'] }}">
                        <svg class="ss-upload-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                        <p class="ss-upload-text">{{ __('settings.upload_label', ['name' => strtolower($asset['label'])]) }}</p>
                        <p class="ss-upload-hint">{{ $asset['hint'] }}</p>
                    </div>
                    @if($asset['path'])
                    <div class="mt-2 flex items-center justify-center h-14 rounded-lg border border-[var(--border)] bg-[var(--surface-soft)]">
                        <img src="{{ asset('storage/'.$asset['path']) }}" class="{{ $asset['h'] }} object-contain">
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── TAB: LOCALIZATION ───────────────────────────────── --}}
    <div x-show="tab==='localization'" x-cloak x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">

        <div class="ss-card">
            <div class="ss-card-title">
                <div class="ss-card-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg></div>
                {{ __('settings.default_language') }}
            </div>
            @php
            $currentLocale = old('default_locale', $s->default_locale ?? 'en');
            $localeFlags   = ['en'=>'🇬🇧','am'=>'🇪🇹'];
            $localeSamples = ['en'=>'Hello, welcome to the portal.','am'=>'ወደ ፖርታሉ እንኳን ደህና መጡ።'];
            @endphp
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
                @foreach($locales as $code)
                <label class="relative cursor-pointer select-none">
                    <input type="radio" name="default_locale" value="{{ $code }}" @checked($currentLocale===$code) class="sr-only peer">
                    <div class="rounded-lg border-2 p-3.5 transition-all duration-150
                                peer-checked:border-[rgb(var(--ac))] peer-checked:bg-[rgb(var(--ac)/0.05)] peer-checked:shadow-sm
                                border-[var(--border)] bg-[var(--surface-soft)] hover:border-[rgb(var(--ac)/0.35)]">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="text-lg leading-none">{{ $localeFlags[$code] ?? '🌐' }}</span>
                            <span class="font-bold text-xs text-[var(--text)]">{{ $localeNames[$code] ?? $code }}</span>
                            @if($currentLocale===$code)
                            <span class="ml-auto text-[.65rem] font-bold px-1.5 py-0.5 rounded-full bg-[rgb(var(--ac))] text-white">{{ __('settings.active') }}</span>
                            @endif
                        </div>
                        <p class="text-[.68rem] text-[var(--text-subtle)] leading-relaxed">{{ $localeSamples[$code] ?? '' }}</p>
                    </div>
                </label>
                @endforeach
            </div>
            <hr class="ss-divider">
            <div class="ss-toggle-row">
                <div class="ss-toggle-info">
                    <span class="ss-toggle-name">{{ __('settings.show_lang_switcher') }}</span>
                    <span class="ss-toggle-desc">{{ __('settings.show_lang_switcher_desc') }}</span>
                </div>
                <label class="ss-sw">
                    <input type="checkbox" name="show_language_switcher" value="1" @checked(old('show_language_switcher',$s->show_language_switcher??true))>
                    <div class="ss-sw-track"><div class="ss-sw-thumb"></div></div>
                </label>
            </div>
        </div>

        <div class="ss-card">
            <div class="ss-card-title">
                <div class="ss-card-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg></div>
                {{ __('settings.region_formats') }}
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="ss-label">{{ __('settings.timezone') }}</label>
                    <select name="timezone" class="ss-select">
                        @foreach($timezones as $tz)
                        <option value="{{ $tz }}" @selected(old('timezone',$s->timezone??'Africa/Addis_Ababa')===$tz)>{{ $tz }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ss-label">{{ __('settings.date_format') }}</label>
                    <select name="date_format" class="ss-select">
                        @foreach(['Y-m-d'=>'2026-06-11 (ISO)','d/m/Y'=>'11/06/2026','m/d/Y'=>'06/11/2026','d-m-Y'=>'11-06-2026','F j, Y'=>'June 11, 2026'] as $v=>$l)
                        <option value="{{ $v }}" @selected(old('date_format',$s->date_format??'Y-m-d')===$v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ss-label">{{ __('settings.time_format') }}</label>
                    <select name="time_format" class="ss-select">
                        <option value="H:i"   @selected(old('time_format',$s->time_format??'H:i')==='H:i')>{{ __('settings.time_24') }}</option>
                        <option value="h:i A" @selected(old('time_format',$s->time_format)==='h:i A')>{{ __('settings.time_12') }}</option>
                    </select>
                </div>
            </div>
            <hr class="ss-divider">
            <div class="ss-toggle-row">
                <div class="ss-toggle-info">
                    <span class="ss-toggle-name">{{ __('settings.ethiopian_calendar') }}</span>
                    <span class="ss-toggle-desc">{{ __('settings.ethiopian_calendar_desc') }}</span>
                </div>
                <label class="ss-sw">
                    <input type="checkbox" name="use_ethiopian_calendar" value="1" @checked(old('use_ethiopian_calendar',$s->use_ethiopian_calendar??false))>
                    <div class="ss-sw-track"><div class="ss-sw-thumb"></div></div>
                </label>
            </div>
        </div>
    </div>

    {{-- ── TAB: SECURITY ───────────────────────────────────── --}}
    <div x-show="tab==='security'" x-cloak x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">

        <div class="ss-card">
            <div class="ss-card-title">
                <div class="ss-card-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></div>
                {{ __('settings.access_controls') }}
            </div>
            <div class="space-y-2">
                @php
                $accessToggles = [
                    ['name'=>'maintenance_mode',  'checked'=>$s->maintenance_mode??false,   'label'=>__('settings.maintenance_mode'),   'desc'=>__('settings.maintenance_mode_desc')],
                    ['name'=>'registration_open', 'checked'=>$s->registration_open??true,   'label'=>__('settings.registration_open'),  'desc'=>__('settings.registration_open_desc')],
                    ['name'=>'force_https',       'checked'=>$s->force_https??false,         'label'=>__('settings.force_https'),        'desc'=>__('settings.force_https_desc')],
                ];
                @endphp
                @foreach($accessToggles as $tg)
                <div class="ss-toggle-row">
                    <div class="ss-toggle-info">
                        <span class="ss-toggle-name">{{ $tg['label'] }}</span>
                        <span class="ss-toggle-desc">{{ $tg['desc'] }}</span>
                    </div>
                    <label class="ss-sw">
                        <input type="checkbox" name="{{ $tg['name'] }}" value="1" @checked(old($tg['name'],$tg['checked']))>
                        <div class="ss-sw-track"><div class="ss-sw-thumb"></div></div>
                    </label>
                </div>
                @endforeach
            </div>
        </div>

        <div class="ss-card">
            <div class="ss-card-title">
                <div class="ss-card-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                {{ __('settings.session_lockout') }}
            </div>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="ss-label">{{ __('settings.session_lifetime') }}</label>
                    <div class="ss-input-group">
                        <input type="number" name="session_lifetime" min="5" max="43200" value="{{ old('session_lifetime',$s->session_lifetime??120) }}">
                        <span class="ss-input-group-addon">{{ __('settings.unit_min') }}</span>
                    </div>
                    <p class="ss-hint">{{ __('settings.session_lifetime_hint') }}</p>
                </div>
                <div>
                    <label class="ss-label">{{ __('settings.max_login_attempts') }}</label>
                    <div class="ss-input-group">
                        <input type="number" name="login_max_attempts" min="1" max="100" value="{{ old('login_max_attempts',$s->login_max_attempts??5) }}">
                        <span class="ss-input-group-addon">{{ __('settings.unit_tries') }}</span>
                    </div>
                </div>
                <div>
                    <label class="ss-label">{{ __('settings.lockout_duration') }}</label>
                    <div class="ss-input-group">
                        <input type="number" name="lockout_minutes" min="1" max="1440" value="{{ old('lockout_minutes',$s->lockout_minutes??15) }}">
                        <span class="ss-input-group-addon">{{ __('settings.unit_min') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="ss-card">
            <div class="ss-card-title">
                <div class="ss-card-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg></div>
                {{ __('settings.password_policy') }}
            </div>
            <div class="mb-4">
                <label class="ss-label">{{ __('settings.password_min_length') }}</label>
                <div class="ss-input-group" style="max-width:180px;">
                    <input type="number" name="password_min_length" min="6" max="128" value="{{ old('password_min_length',$s->password_min_length??8) }}">
                    <span class="ss-input-group-addon">{{ __('settings.unit_characters') }}</span>
                </div>
            </div>
            <div class="space-y-2">
                @php
                $pwToggles = [
                    ['name'=>'password_require_uppercase','checked'=>$s->password_require_uppercase??true, 'label'=>__('settings.require_uppercase'),'desc'=>__('settings.require_uppercase_desc')],
                    ['name'=>'password_require_number',   'checked'=>$s->password_require_number??true,   'label'=>__('settings.require_number'),   'desc'=>__('settings.require_number_desc')],
                    ['name'=>'password_require_symbol',   'checked'=>$s->password_require_symbol??false,  'label'=>__('settings.require_symbol'),   'desc'=>__('settings.require_symbol_desc')],
                ];
                @endphp
                @foreach($pwToggles as $tg)
                <div class="ss-toggle-row">
                    <div class="ss-toggle-info">
                        <span class="ss-toggle-name">{{ $tg['label'] }}</span>
                        <span class="ss-toggle-desc">{{ $tg['desc'] }}</span>
                    </div>
                    <label class="ss-sw">
                        <input type="checkbox" name="{{ $tg['name'] }}" value="1" @checked(old($tg['name'],$tg['checked']))>
                        <div class="ss-sw-track"><div class="ss-sw-thumb"></div></div>
                    </label>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── TAB: APPEARANCE ─────────────────────────────────── --}}
    <div x-show="tab==='appearance'" x-cloak x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">

        <div class="ss-card">
            <div class="ss-card-title">
                <div class="ss-card-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg></div>
                {{ __('settings.accent_palette') }}
            </div>
            <p class="ss-hint mb-3">{{ __('settings.accent_palette_hint') }}</p>
            @php
            $palettes=[
                'blue'    =>['label'=>__('settings.palette_blue'),    'hex'=>'#3b82f6'],
                'teal'    =>['label'=>__('settings.palette_teal'),    'hex'=>'#14b8a6'],
                'violet'  =>['label'=>__('settings.palette_violet'),  'hex'=>'#8b5cf6'],
                'emerald' =>['label'=>__('settings.palette_emerald'), 'hex'=>'#10b981'],
                'rose'    =>['label'=>__('settings.palette_rose'),    'hex'=>'#f43f5e'],
            ];
            $cp=old('accent_palette',$s->accent_palette??'blue');
            @endphp
            <div class="ss-palette">
                @foreach($palettes as $key=>$p)
                <label class="ss-swatch">
                    <input type="radio" name="accent_palette" value="{{ $key }}" @checked($cp===$key)>
                    <div class="ss-swatch-box" style="color:{{ $p['hex'] }};background:{{ $p['hex'] }}18;">
                        <span class="ss-swatch-dot" style="background:{{ $p['hex'] }};"></span>{{ $p['label'] }}
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <div class="ss-card">
            <div class="ss-card-title">
                <div class="ss-card-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg></div>
                {{ __('settings.default_theme') }}
            </div>
            @php $ct=old('default_theme',$s->default_theme??'system'); @endphp
            <div class="ss-theme-grid">
                @foreach([
                    'light'  =>['label'=>__('settings.theme_light'),  'bg'=>'linear-gradient(135deg,#f8fafc 50%,#e2e8f0 100%)','brd'=>'1px solid #e2e8f0'],
                    'dark'   =>['label'=>__('settings.theme_dark'),   'bg'=>'linear-gradient(135deg,#1e293b 50%,#0f172a 100%)', 'brd'=>'none'],
                    'system' =>['label'=>__('settings.theme_system'), 'bg'=>'linear-gradient(135deg,#f8fafc 0%,#f8fafc 50%,#1e293b 50%,#0f172a 100%)','brd'=>'1px solid #e2e8f0'],
                ] as $v=>$th)
                <label class="ss-theme-card">
                    <input type="radio" name="default_theme" value="{{ $v }}" @checked($ct===$v)>
                    <div class="ss-theme-box">
                        <div class="ss-theme-preview" style="background:{{ $th['bg'] }};border:{{ $th['brd'] }};"></div>
                        <p class="ss-theme-label">{{ $th['label'] }}</p>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <div class="ss-card">
            <div class="ss-card-title">
                <div class="ss-card-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></div>
                {{ __('settings.display_options') }}
            </div>
            <div class="ss-toggle-row">
                <div class="ss-toggle-info">
                    <span class="ss-toggle-name">{{ __('settings.show_banner_login') }}</span>
                    <span class="ss-toggle-desc">{{ __('settings.show_banner_login_desc') }}</span>
                </div>
                <label class="ss-sw">
                    <input type="checkbox" name="show_banner_on_login" value="1" @checked(old('show_banner_on_login',$s->show_banner_on_login??true))>
                    <div class="ss-sw-track"><div class="ss-sw-thumb"></div></div>
                </label>
            </div>
        </div>

        <div class="ss-card">
            <div class="ss-card-title">
                <div class="ss-card-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg></div>
                {{ __('settings.custom_css') }}
                <span class="ml-auto text-[.66rem] font-normal px-2 py-0.5 rounded-full border border-[var(--border)] text-[var(--text-subtle)]">{{ __('settings.custom_css_badge') }}</span>
            </div>
            <p class="ss-hint mb-2">{{ __('settings.custom_css_hint') }}</p>
            <textarea name="custom_css" rows="7" class="ss-textarea" style="font-family:monospace;font-size:.78rem;"
                      placeholder=":root { --ac: 37 99 235; }">{{ old('custom_css',$s->custom_css) }}</textarea>
        </div>
    </div>

    {{-- ── TAB: NOTIFICATIONS ──────────────────────────────── --}}
    <div x-show="tab==='notifications'" x-cloak x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">

        {{-- ── Email (SMTP) ── --}}
        <div class="ss-card">
            <div class="ss-card-title">
                <div class="ss-card-icon" style="background:rgb(59 130 246/.1);color:rgb(59 130 246);">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                {{ __('settings.email_smtp') }}
                <div class="ml-auto">
                    <label class="ss-sw">
                        <input type="checkbox" name="mail_enabled" value="1" @checked(old('mail_enabled',$s->mail_enabled??false))>
                        <div class="ss-sw-track"><div class="ss-sw-thumb"></div></div>
                    </label>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="ss-label">{{ __('settings.smtp_host') }}</label>
                    <input name="mail_host" value="{{ old('mail_host',$s->mail_host??'smtp.gmail.com') }}" class="ss-input" placeholder="smtp.gmail.com">
                </div>
                <div>
                    <label class="ss-label">{{ __('settings.smtp_port') }}</label>
                    <div class="ss-input-group">
                        <input type="number" name="mail_port" min="1" max="65535" value="{{ old('mail_port',$s->mail_port??587) }}">
                        <span class="ss-input-group-addon">TCP</span>
                    </div>
                </div>
                <div>
                    <label class="ss-label">{{ __('settings.encryption') }}</label>
                    <select name="mail_encryption" class="ss-select">
                        @foreach(['tls'=>'TLS (587)','ssl'=>'SSL (465)','starttls'=>'STARTTLS','none'=>'None (plain)'] as $v=>$l)
                        <option value="{{ $v }}" @selected(old('mail_encryption',$s->mail_encryption??'tls')===$v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ss-label">{{ __('settings.mailer_driver') }}</label>
                    <select name="mail_mailer" class="ss-select">
                        <option value="smtp"     @selected(old('mail_mailer',$s->mail_mailer??'smtp')==='smtp')>SMTP</option>
                        <option value="sendmail" @selected(old('mail_mailer',$s->mail_mailer)==='sendmail')>Sendmail</option>
                        <option value="log"      @selected(old('mail_mailer',$s->mail_mailer)==='log')>{{ __('settings.log_debug') }}</option>
                    </select>
                </div>
                <div>
                    <label class="ss-label">{{ __('settings.mail_username') }}</label>
                    <input name="mail_username" value="{{ old('mail_username',$s->mail_username) }}" class="ss-input" placeholder="your@gmail.com" autocomplete="off">
                </div>
                <div>
                    <label class="ss-label">{{ __('settings.mail_password') }}</label>
                    <div class="ss-pw-wrap" x-data="{show:false}">
                        <input :type="show?'text':'password'" name="mail_password"
                               value="{{ old('mail_password',$s->mail_password) }}"
                               class="ss-input" placeholder="••••••••••••" autocomplete="new-password">
                        <button type="button" class="ss-pw-toggle" @click="show=!show" :title="show?'Hide':'Show'">
                            <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="show" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    <p class="ss-hint">{{ __('settings.mail_password_hint') }}</p>
                </div>
                <div>
                    <label class="ss-label">{{ __('settings.from_address') }}</label>
                    <input type="email" name="mail_from_address" value="{{ old('mail_from_address',$s->mail_from_address??$s->contact_email) }}" class="ss-input" placeholder="noreply@court.gov.et">
                </div>
                <div>
                    <label class="ss-label">{{ __('settings.from_name') }}</label>
                    <input name="mail_from_name" value="{{ old('mail_from_name',$s->mail_from_name??$s->app_name) }}" class="ss-input" placeholder="{{ $s->app_name ?? config('app.name') }}">
                </div>
            </div>
        </div>

        {{-- ── Telegram ── --}}
        <div class="ss-card">
            <div class="ss-card-title">
                <div class="ss-card-icon" style="background:rgb(0 136 204/.12);color:rgb(0 136 204);">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12l-6.871 4.326-2.962-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.194 1.006.131.833.941z"/></svg>
                </div>
                {{ __('settings.telegram_bot') }}
            </div>
            <p class="ss-hint mb-4">{{ __('settings.telegram_hint') }}</p>

            <div class="ss-toggle-row mb-4">
                <div class="ss-toggle-info">
                    <span class="ss-toggle-name">{{ __('settings.enable_telegram') }}</span>
                    <span class="ss-toggle-desc">{{ __('settings.enable_telegram_desc') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[.7rem] font-bold uppercase"
                          :class="telegramEnabled ? 'text-emerald-600' : 'text-[var(--text-subtle)]'"
                          x-text="telegramEnabled ? '{{ __('settings.on') }}' : '{{ __('settings.off') }}'"></span>
                    <label class="ss-sw">
                        <input type="checkbox" name="telegram_enabled" value="1" x-model="telegramEnabled">
                        <div class="ss-sw-track"><div class="ss-sw-thumb"></div></div>
                    </label>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="ss-label">{{ __('settings.bot_token') }}</label>
                    <div class="ss-pw-wrap" x-data="{show:false}">
                        <input :type="show?'text':'password'" name="telegram_bot_token"
                               value="{{ old('telegram_bot_token',$s->telegram_bot_token) }}"
                               class="ss-input" placeholder="123456789:AABBccDDee..." autocomplete="off">
                        <button type="button" class="ss-pw-toggle" @click="show=!show">
                            <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="show" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    <p class="ss-hint">{{ __('settings.bot_token_hint') }}</p>
                </div>
                <div>
                    <label class="ss-label">{{ __('settings.default_chat_id') }}</label>
                    <input name="telegram_default_chat_id" value="{{ old('telegram_default_chat_id',$s->telegram_default_chat_id) }}" class="ss-input" placeholder="-100xxxxxxxxxx or @channelname">
                    <p class="ss-hint">{{ __('settings.default_chat_id_hint') }}</p>
                </div>
            </div>
        </div>

        {{-- ── SMS ── --}}
        <div class="ss-card">
            <div class="ss-card-title">
                <div class="ss-card-icon" style="background:rgb(16 185 129/.12);color:rgb(5 150 105);">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                {{ __('settings.sms_gateway') }}
                <div class="ml-auto">
                    <label class="ss-sw">
                        <input type="checkbox" name="sms_enabled" value="1" @checked(old('sms_enabled',$s->sms_enabled??false))>
                        <div class="ss-sw-track"><div class="ss-sw-thumb"></div></div>
                    </label>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="ss-label">{{ __('settings.sms_provider') }}</label>
                    <input name="sms_provider"
                           value="{{ old('sms_provider',$s->sms_provider) }}"
                           class="ss-input" placeholder="e.g. Infobip, Twilio, EthioTelecom, AfricasTalking…">
                    <p class="ss-hint">{{ __('settings.sms_provider_hint') }}</p>
                </div>
                <div>
                    <label class="ss-label">{{ __('settings.sms_base_url') }}</label>
                    <input type="url" name="sms_base_url"
                           value="{{ old('sms_base_url',$s->sms_base_url) }}"
                           class="ss-input" placeholder="https://api.yourprovider.com/v1">
                    <p class="ss-hint">{{ __('settings.sms_base_url_hint') }}</p>
                </div>
                <div>
                    <label class="ss-label">{{ __('settings.sms_api_key') }}</label>
                    <div class="ss-pw-wrap" x-data="{show:false}">
                        <input :type="show?'text':'password'" name="sms_api_key"
                               value="{{ old('sms_api_key',$s->sms_api_key) }}"
                               class="ss-input" placeholder="API key or Account SID" autocomplete="off">
                        <button type="button" class="ss-pw-toggle" @click="show=!show">
                            <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="show" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="ss-label">{{ __('settings.sms_api_secret') }}</label>
                    <div class="ss-pw-wrap" x-data="{show:false}">
                        <input :type="show?'text':'password'" name="sms_api_secret"
                               value="{{ old('sms_api_secret',$s->sms_api_secret) }}"
                               class="ss-input" placeholder="API secret or auth token" autocomplete="off">
                        <button type="button" class="ss-pw-toggle" @click="show=!show">
                            <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="show" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="ss-label">{{ __('settings.sms_sender_id') }}</label>
                    <input name="sms_sender_id" value="{{ old('sms_sender_id',$s->sms_sender_id) }}" class="ss-input" placeholder="CourtMS or +251912345678">
                    <p class="ss-hint">{{ __('settings.sms_sender_id_hint') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TAB: DATA MANAGEMENT ───────────────────────────────── --}}
    <div x-show="tab==='data'" x-cloak x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">

        <div class="grid lg:grid-cols-2 gap-4">
            <div class="ss-card">
                <div class="ss-card-title">
                    <div class="ss-card-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg></div>
                    {{ __('settings.data_overview') }}
                </div>
                <p class="ss-hint mb-3">{{ __('settings.data_overview_hint') }}</p>
                <div class="grid sm:grid-cols-2 gap-2">
                    @foreach([
                        __('settings.data_driver')          => strtoupper((string) ($databaseMetrics['driver'] ?? 'unknown')),
                        __('settings.data_connection')      => $databaseMetrics['connection'] ?? '-',
                        __('settings.data_database')        => $databaseMetrics['database'] ?? '-',
                        __('settings.data_tables')          => number_format((int) ($databaseMetrics['table_count'] ?? 0)),
                        __('settings.data_size')            => $databaseMetrics['size'] ?? '-',
                        __('settings.data_migration_batch') => $databaseMetrics['migration_batch'] ?? '-',
                    ] as $k=>$v)
                    <div class="ss-info-row">
                        <span class="ss-info-key">{{ $k }}</span>
                        <span class="ss-info-val">{{ $v }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="ss-card">
                <div class="ss-card-title">
                    <div class="ss-card-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg></div>
                    {{ __('settings.backup_database') }}
                    <span class="ml-auto ss-badge {{ ($databaseMetrics['backup_supported'] ?? false) ? 'ss-badge-ok' : 'ss-badge-warn' }}">
                        {{ ($databaseMetrics['backup_supported'] ?? false) ? __('settings.backup_supported') : __('settings.backup_not_supported') }}
                    </span>
                </div>
                <p class="ss-hint mb-4">{{ __('settings.backup_database_hint') }}</p>

                @if($databaseMetrics['backup_supported'] ?? false)
                    <button type="submit" form="ss-database-backup-form"
                            onclick="return confirm('{{ __('settings.download_backup_confirm') }}')"
                            class="cs-btn-primary text-xs px-4 py-2">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
                        {{ __('settings.download_backup') }}
                    </button>
                @else
                    <button type="button" class="cs-btn-secondary text-xs px-4 py-2 opacity-60 cursor-not-allowed" disabled>
                        {{ __('settings.download_backup') }}
                    </button>
                    <p class="ss-hint mt-3">{{ __('settings.backup_unsupported') }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ── TAB: API & ACCESS ───────────────────────────────── --}}
    <div x-show="tab==='api'" x-cloak x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">

        <div class="ss-card">
            <div class="ss-card-title">
                <div class="ss-card-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                {{ __('settings.rest_api') }}
            </div>
            <div class="ss-toggle-row mb-4">
                <div class="ss-toggle-info">
                    <span class="ss-toggle-name">{{ __('settings.enable_api') }}</span>
                    <span class="ss-toggle-desc">{{ __('settings.enable_api_desc') }}</span>
                </div>
                <label class="ss-sw">
                    <input type="checkbox" name="api_enabled" value="1" @checked(old('api_enabled',$s->api_enabled??false))>
                    <div class="ss-sw-track"><div class="ss-sw-thumb"></div></div>
                </label>
            </div>
            <div style="max-width:220px;">
                <label class="ss-label">{{ __('settings.rate_limit') }}</label>
                <div class="ss-input-group">
                    <input type="number" name="api_rate_limit" min="1" max="10000" value="{{ old('api_rate_limit',$s->api_rate_limit??60) }}">
                    <span class="ss-input-group-addon">{{ __('settings.unit_req_min') }}</span>
                </div>
                <p class="ss-hint">{{ __('settings.rate_limit_hint') }}</p>
            </div>
        </div>

        <div class="ss-card">
            <div class="ss-card-title">
                <div class="ss-card-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                {{ __('settings.system_information') }}
            </div>
            <div class="grid sm:grid-cols-2 gap-2">
                @foreach([
                    __('settings.info_laravel')    => app()->version(),
                    __('settings.info_php')        => PHP_VERSION,
                    __('settings.info_environment')=> app()->environment(),
                    __('settings.info_debug')      => config('app.debug') ? __('settings.info_enabled') : __('settings.info_disabled'),
                    __('settings.info_cache')      => config('cache.default'),
                    __('settings.info_session')    => config('session.driver'),
                    __('settings.info_queue')      => config('queue.default'),
                    __('settings.info_db')         => config('database.default'),
                    __('settings.info_timezone')   => config('app.timezone'),
                    __('settings.info_server_time')=> now()->format('Y-m-d H:i:s T'),
                ] as $k=>$v)
                <div class="ss-info-row">
                    <span class="ss-info-key">{{ $k }}</span>
                    <span class="ss-info-val">{{ $v }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="ss-card">
            <div class="ss-card-title">
                <div class="ss-card-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg></div>
                {{ __('settings.cache_management') }}
            </div>
            <p class="ss-hint mb-3">{{ __('settings.cache_hint') }}</p>
            <button type="submit" form="ss-clear-cache-form"
                    onclick="return confirm('{{ __('settings.clear_caches_confirm') }}')"
                    class="cs-btn-secondary text-xs px-4 py-2">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                {{ __('settings.clear_caches') }}
            </button>
        </div>
    </div>

    {{-- ── Sticky save bar ─────────────────────────────────── --}}
    <div class="ss-bar">
        <span class="ss-bar-hint">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ __('settings.save_hint') }}
        </span>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('settings.system.edit') }}" class="cs-btn-secondary text-xs px-4 py-2"
               :class="saving && 'pointer-events-none opacity-50'">{{ __('settings.discard') }}</a>
            <button type="submit" :disabled="saving"
                    class="cs-btn-primary text-xs px-5 py-2 min-w-[130px] justify-center disabled:opacity-60 disabled:cursor-not-allowed"
                    style="background:linear-gradient(135deg,rgb(var(--ac)) 0%,rgb(var(--ac-light,var(--ac))) 100%);">
                <span x-show="!saving" class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    {{ __('settings.save_settings') }}
                </span>
                <span x-show="saving" x-cloak class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    {{ __('settings.saving') }}
                </span>
            </button>
        </div>
    </div>

    </form>

    {{-- Standalone clear-cache form (outside main form — no nesting) --}}
    <form id="ss-clear-cache-form" method="POST" action="{{ route('settings.system.clearCache') }}">@csrf</form>
    <form id="ss-database-backup-form" method="POST" action="{{ route('settings.system.databaseBackup') }}">@csrf</form>

</div>
</x-admin-layout>
