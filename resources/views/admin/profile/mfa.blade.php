<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('auth.mfa_title') }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { color-scheme: light; }
        body { margin:0; min-height:100vh; background:#f8fafc; color:#172033; font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; }
        .mfa-shell { min-height:100vh; display:grid; place-items:center; padding:2rem 1rem; background:radial-gradient(circle at 15% 10%,#dbeafe 0,transparent 32rem),#f8fafc; }
        .mfa-page { width:min(100%,720px); }
        .mfa-brand { text-align:center; margin-bottom:1.5rem; }
        .mfa-mark { width:3rem; height:3rem; display:grid; place-items:center; margin:0 auto .75rem; border-radius:1rem; color:#fff; background:linear-gradient(135deg,#2563eb,#4f46e5); font-weight:800; box-shadow:0 10px 24px rgb(37 99 235/.25); }
        .mfa-brand h1 { margin:0; font-size:1.35rem; letter-spacing:-.025em; }
        .mfa-brand p { margin:.35rem 0 0; color:#64748b; font-size:.875rem; }
        .mfa-card { background:#fff; border:1px solid #e2e8f0; border-radius:1.25rem; box-shadow:0 18px 50px rgb(15 23 42/.08); padding:clamp(1.25rem,4vw,2rem); }
        .mfa-card h2 { margin:0; font-size:1.15rem; }
        .mfa-card .hint { color:#64748b; font-size:.875rem; line-height:1.55; margin:.45rem 0 1.25rem; }
        .mfa-qr { display:grid; place-items:center; padding:1rem; border:1px solid #e2e8f0; border-radius:.875rem; background:#fff; margin-bottom:1.25rem; }
        .mfa-secret { display:block; overflow-wrap:anywhere; padding:.7rem .8rem; border-radius:.55rem; background:#f1f5f9; color:#334155; font:600 .76rem/1.4 ui-monospace,SFMono-Regular,Menlo,monospace; }
        .mfa-label { display:block; color:#334155; font-size:.8rem; font-weight:700; margin:1rem 0 .4rem; }
        .mfa-input { width:100%; box-sizing:border-box; padding:.75rem .85rem; border:1px solid #cbd5e1; border-radius:.6rem; font-size:1rem; letter-spacing:.35em; outline:none; }
        .mfa-input:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgb(37 99 235/.12); }
        .mfa-btn { margin-top:1rem; border:0; border-radius:.6rem; padding:.7rem 1rem; color:#fff; background:#2563eb; font-weight:700; cursor:pointer; }
        .mfa-btn:hover { background:#1d4ed8; }
        .mfa-btn-danger { background:#dc2626; }
        .mfa-btn-danger:hover { background:#b91c1c; }
        .mfa-alert { padding:1rem; border-radius:.75rem; margin-bottom:1rem; font-size:.85rem; }
        .mfa-alert-warning { background:#fffbeb; border:1px solid #fde68a; color:#92400e; }
        .mfa-alert-success { background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; }
        .mfa-codes { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.5rem; margin-top:.9rem; }
        .mfa-codes code { padding:.55rem; border:1px solid #fde68a; border-radius:.45rem; background:#fff; font-size:.8rem; text-align:center; }
        .mfa-back { display:inline-block; margin-top:1.25rem; color:#475569; font-size:.82rem; text-decoration:none; }
        .mfa-back:hover { color:#1d4ed8; }
    </style>
</head>
<body>
<main class="mfa-shell">
    <section class="mfa-page">
        <header class="mfa-brand">
            <div class="mfa-mark">🔐</div>
            <h1>{{ __('auth.mfa_title') }}</h1>
            <p>{{ config('app.name') }}</p>
        </header>

        @if(session('status')) <div class="mfa-alert mfa-alert-success">{{ session('status') }}</div> @endif
        @if(session('warning')) <div class="mfa-alert mfa-alert-warning">{{ session('warning') }}</div> @endif

        <div class="mfa-card">
            <p class="hint">{{ __('auth.mfa_setup_hint') }}</p>

            @if($recoveryCodes)
                <div class="mfa-alert mfa-alert-warning">
                    <strong>{{ __('auth.mfa_recovery_codes') }}</strong>
                    <p>{{ __('auth.mfa_recovery_warning') }}</p>
                    <div class="mfa-codes">
                        @foreach($recoveryCodes as $code)<code>{{ $code }}</code>@endforeach
                    </div>
                </div>
            @endif

            @if($user->hasConfirmedMfa())
                <div class="mfa-alert mfa-alert-success"><strong>{{ __('auth.mfa_active') }}</strong><br>{{ __('auth.mfa_active_hint') }}</div>
            @elseif($secret)
                <div class="mfa-qr">{!! $qrCode !!}</div>
                <h2>{{ __('auth.mfa_scan_qr') }}</h2>
                <p class="hint">{{ __('auth.mfa_scan_qr_hint') }}</p>
                <span class="mfa-secret">{{ $secret }}</span>
                <form method="POST" action="{{ route('mfa.setup.confirm') }}">
                    @csrf
                    <label class="mfa-label" for="code">{{ __('auth.mfa_authentication_code') }}</label>
                    <input class="mfa-input" id="code" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>
                    @error('code')<div class="mfa-alert mfa-alert-warning">{{ $message }}</div>@enderror
                    <button class="mfa-btn" type="submit">{{ __('auth.mfa_confirm_enable') }}</button>
                </form>
            @else
                <h2>{{ __('auth.mfa_not_configured') }}</h2>
                <p class="hint">{{ __('auth.mfa_begin_hint') }}</p>
                <form method="POST" action="{{ route('mfa.setup.begin') }}">@csrf<button class="mfa-btn" type="submit">{{ __('auth.mfa_begin') }}</button></form>
            @endif
        </div>
        <a class="mfa-btn mfa-back" href="{{ route('profile.edit') }}">← {{ __('auth.mfa_back_profile') }}</a>
    </section>
</main>
</body>
</html>
