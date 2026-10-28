<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ConfirmMfaRequest;
use App\Services\MfaService;
use App\Support\Ui\SharedSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MfaController extends Controller
{
    /**
     * The second-factor setup (React, Auth/MfaSetup from EUISIS): the QR
     * code and key for an app, then the code to confirm. Opening it starts
     * the enrolment (a fresh secret) when none is under way; once confirmed
     * it shows the recovery codes one time, then sends the person to the
     * profile's Security tab.
     */
    public function show(Request $request, MfaService $mfa, SharedSettings $settings): Response|RedirectResponse
    {
        $user = Auth::guard('web')->user();
        abort_if($user === null, 401);

        $recoveryCodes = collect($request->session()->get('mfa_recovery_codes', []))
            ->flatten()
            ->filter(fn (mixed $code): bool => is_scalar($code) && (string) $code !== '')
            ->map(fn (mixed $code): string => (string) $code)
            ->values()
            ->all();

        if ($user->hasConfirmedMfa() && $recoveryCodes === []) {
            return redirect()->to(route('profile.edit').'#security');
        }

        if (! $user->hasConfirmedMfa() && blank($user->mfa_secret)) {
            $this->startEnrolment($user, $mfa);
        }

        $issuer = (string) $settings->branding()['brandNameEn'];
        // Once confirmed the page only shows the recovery codes: the secret never leaves the server again.
        $confirmed = $user->hasConfirmedMfa();
        $svg = $confirmed ? '' : (string) QrCode::format('svg')->size(220)->margin(1)->generate($mfa->provisioningUri($user, (string) $user->mfa_secret));

        return Inertia::render('Auth/MfaSetup', [
            'qrCodeUri' => $confirmed ? '' : 'data:image/svg+xml;base64,'.base64_encode($svg),
            'secretKey' => $confirmed ? '' : (string) $user->mfa_secret,
            'user' => ['name' => $user->name, 'email' => $user->email],
            'issuer' => $issuer,
        ]);
    }

    public function begin(Request $request, MfaService $mfa): RedirectResponse
    {
        $user = Auth::guard('web')->user();
        abort_if($user === null, 401);

        if (! $user->hasConfirmedMfa()) {
            $this->startEnrolment($user, $mfa);
        }

        return redirect()->route('mfa.setup.show');
    }

    public function confirm(ConfirmMfaRequest $request, MfaService $mfa): RedirectResponse
    {
        $user = Auth::guard('web')->user();
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
        $user = Auth::guard('web')->user();
        abort_if($user === null, 401);
        abort_if($user->requiresMfa(), 403, __('auth.mfa_required_by_role'));

        $user->forceFill([
            'mfa_secret' => null,
            'mfa_recovery_codes' => null,
            'mfa_confirmed_at' => null,
        ])->save();
        $request->session()->forget('mfa_verified_at');

        return redirect()
            ->to(route('profile.edit').'#security')
            ->with('mfa_status', __('auth.mfa_disabled'));
    }

    private function startEnrolment(\App\Models\User $user, MfaService $mfa): void
    {
        $user->mfa_secret = $mfa->generateSecret();
        $user->mfa_recovery_codes = null;
        $user->mfa_confirmed_at = null;
        $user->save();
    }
}
