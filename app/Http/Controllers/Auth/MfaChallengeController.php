<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\MfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MfaChallengeController extends Controller
{
    public function show(): View
    {
        return view('admin.auth.mfa-challenge');
    }

    public function store(Request $request, MfaService $mfa): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:20']]);
        $user = $request->user();
        abort_if($user === null || ! $user->hasConfirmedMfa(), 403);

        $valid = preg_match('/^\d{6}$/', $data['code']) === 1
            ? $mfa->verify($user->mfa_secret, $data['code'])
            : $mfa->consumeRecoveryCode($user, $data['code']);

        if (! $valid) {
            return back()->withErrors(['code' => __('auth.mfa_invalid_code')]);
        }

        $request->session()->put('mfa_verified_at', now()->timestamp);

        return redirect()->intended(route('dashboard'));
    }
}
