<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\MfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MfaChallengeController extends Controller
{
    /** The second-factor prompt (React, Auth/MfaChallenge from EUISIS). */
    public function show(): Response
    {
        $user = Auth::guard('web')->user();
        abort_if($user === null, 401);

        return Inertia::render('Auth/MfaChallenge', [
            'user' => ['name' => $user->name, 'email' => $user->email],
        ]);
    }

    /** The page sends either a six-digit `code` or a `recovery_code`. */
    public function store(Request $request, MfaService $mfa): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:20'],
            'recovery_code' => ['nullable', 'string', 'max:40'],
        ]);
        $user = Auth::guard('web')->user();
        abort_if($user === null || ! $user->hasConfirmedMfa(), 403);

        $code = trim((string) ($data['code'] ?? ''));
        $recovery = trim((string) ($data['recovery_code'] ?? ''));
        if ($code === '' && $recovery === '') {
            return back()->withErrors(['code' => __('validation.required', ['attribute' => __('auth.mfa_code')])]);
        }

        $valid = $code !== ''
            ? (preg_match('/^\d{6}$/', $code) === 1 ? $mfa->verify($user->mfa_secret, $code) : $mfa->consumeRecoveryCode($user, $code))
            : $mfa->consumeRecoveryCode($user, $recovery);

        if (! $valid) {
            return back()->withErrors([$code !== '' ? 'code' : 'recovery_code' => __('auth.mfa_invalid_code')]);
        }

        $request->session()->put('mfa_verified_at', now()->timestamp);

        return redirect()->intended(route('dashboard'));
    }
}
