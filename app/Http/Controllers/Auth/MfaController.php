<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ConfirmMfaRequest;
use App\Services\MfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MfaController extends Controller
{
    public function show(Request $request, MfaService $mfa): View
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $secret = $user->mfa_secret;
        $qrCode = $secret && ! $user->hasConfirmedMfa()
            ? QrCode::size(220)->margin(1)->generate($mfa->provisioningUri($user, $secret))
            : null;
        $recoveryCodes = collect($request->session()->get('mfa_recovery_codes', []))
            ->flatten()
            ->filter(fn (mixed $code): bool => is_scalar($code) && (string) $code !== '')
            ->map(fn (mixed $code): string => (string) $code)
            ->values()
            ->all();

        return view('admin.profile.mfa', compact('user', 'secret', 'qrCode', 'recoveryCodes'));
    }

    public function begin(Request $request, MfaService $mfa): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        if (! $user->hasConfirmedMfa()) {
            $user->mfa_secret = $mfa->generateSecret();
            $user->mfa_recovery_codes = null;
            $user->mfa_confirmed_at = null;
            $user->save();
        }

        return redirect()->route('mfa.setup.show');
    }

    public function confirm(ConfirmMfaRequest $request, MfaService $mfa): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null || blank($user->mfa_secret), 422);

        if (! $mfa->verify($user->mfa_secret, $request->string('code')->toString())) {
            return back()->withErrors(['code' => __('auth.mfa_invalid_code')]);
        }

        $recoveryCodes = $mfa->createRecoveryCodes();
        $user->mfa_recovery_codes = $mfa->hashRecoveryCodes($recoveryCodes);
        $user->mfa_confirmed_at = now();
        $user->save();
        $request->session()->put('mfa_verified_at', now()->timestamp);

        return redirect()->route('mfa.setup.show')->with('mfa_recovery_codes', $recoveryCodes);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);
        $user = $request->user();
        abort_if($user === null, 401);
        abort_if($user->requiresMfa(), 403, __('auth.mfa_required_by_role'));

        $user->forceFill([
            'mfa_secret' => null,
            'mfa_recovery_codes' => null,
            'mfa_confirmed_at' => null,
        ])->save();
        $request->session()->forget('mfa_verified_at');

        return redirect()->route('mfa.setup.show')->with('status', __('auth.mfa_disabled'));
    }
}
