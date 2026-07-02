<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Notifications\ApplicantEmailOtp;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ApplicantVerificationController extends Controller
{
    /** Generate a fresh OTP, store hash in session, return the plain code. */
    private function generateOtp(int $ttlMinutes = 10): string
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        session([
            'email_otp' => hash('sha256', $otp),
            'email_otp_expires_at' => now()->addMinutes($ttlMinutes)->timestamp,
        ]);

        return $otp;
    }

    /** Show the OTP form, auto-sending a code if none is already pending. */
    public function notice(Request $request)
    {
        /** @var Applicant|null $user */
        $user = $request->user('applicant');

        if (! $user) {
            return redirect()->route('applicant.login');
        }
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('applicant.dashboard')->with('success', __('auth.email_already_verified'));
        }

        // Only send a new OTP when there is no unexpired one already in session
        if (! session('email_otp') || now()->timestamp > session('email_otp_expires_at', 0)) {
            try {
                $otp = $this->generateOtp();
                $user->notify(new ApplicantEmailOtp($otp));
            } catch (\Throwable $e) {
                // The code was never delivered — clear it so the next visit retries
                // instead of silently skipping for the whole TTL window.
                session()->forget(['email_otp', 'email_otp_expires_at']);
                Log::error('[VerifyEmail] OTP send failed: '.$e->getMessage());
            }
        }

        return view('applicant.auth.email-verify-otp');
    }

    /** Resend a fresh OTP code to the authenticated applicant. */
    public function send(Request $request)
    {
        /** @var Applicant|null $user */
        $user = $request->user('applicant');

        if (! $user) {
            return redirect()->route('applicant.login');
        }
        if ($user->hasVerifiedEmail()) {
            return back()->with('success', __('auth.email_already_verified'));
        }

        try {
            $otp = $this->generateOtp();
            $user->notify(new ApplicantEmailOtp($otp));

            return back()->with('success', __('auth.verification_code_sent_to_email'));
        } catch (\Throwable $e) {
            session()->forget(['email_otp', 'email_otp_expires_at']);
            Log::error('[VerifyEmail] OTP resend failed: '.$e->getMessage());

            return back()->withErrors(['code' => __('auth.verification_code_send_failed')]);
        }
    }

    /** Verify the OTP submitted from the email-verify form. */
    public function verifyEmailOtp(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        /** @var Applicant|null $user */
        $user = $request->user('applicant');

        if (! $user) {
            return redirect()->route('applicant.login');
        }
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('applicant.dashboard');
        }

        $storedHash = session('email_otp');
        $expiresAt = session('email_otp_expires_at');

        if (! $storedHash || ! $expiresAt) {
            return redirect()->route('applicant.verification.notice')
                ->withErrors(['code' => __('auth.verification_code_missing')]);
        }

        if (now()->timestamp > $expiresAt) {
            return back()->withErrors(['code' => __('auth.verification_code_expired')]);
        }

        if (! hash_equals($storedHash, hash('sha256', $request->input('code')))) {
            return back()->withErrors(['code' => __('auth.verification_code_invalid')]);
        }

        $user->email_verified_at = now();
        $user->save();

        session()->forget(['email_otp', 'email_otp_expires_at']);

        return redirect()->route('applicant.dashboard')->with('success', __('auth.email_verified_successfully'));
    }

    /** Handle the signed verification link (kept for backward compatibility). */
    public function verify(EmailVerificationRequest $request)
    {
        Auth::shouldUse('applicant');

        if ($request->user() && ! $request->user()->hasVerifiedEmail()) {
            $request->fulfill();
        }

        return redirect()->route('applicant.dashboard')->with('success', __('auth.email_verified_successfully'));
    }

    /** Show the OTP entry form (for new registrations). */
    public function showOtp(Request $request)
    {
        if (! session('pending_registration')) {
            return redirect()->route('applicant.register');
        }

        return view('applicant.auth.verify-otp');
    }

    /** Verify the submitted OTP code and create the account. */
    public function verifyOtp(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        $pending = session('pending_registration');
        $storedHash = session('otp_code');
        $expiresAt = session('otp_expires_at');

        if (! $pending || ! $storedHash || ! $expiresAt) {
            return redirect()->route('applicant.register')
                ->withErrors(['code' => __('auth.verification_session_expired_register_again')]);
        }

        if (now()->timestamp > $expiresAt) {
            return back()->withErrors(['code' => __('auth.verification_code_expired')]);
        }

        if (! hash_equals($storedHash, hash('sha256', $request->input('code')))) {
            return back()->withErrors(['code' => __('auth.verification_code_invalid')]);
        }

        // Email proven real — only now is the account saved to the database.
        // Guard against a duplicate created between registration and verification.
        $applicant = Applicant::where('email', $pending['email'])->first();

        if (! $applicant) {
            $applicant = Applicant::create([
                ...$pending,
                'is_active' => true,
            ]);
            // Not mass-assignable — set explicitly.
            $applicant->email_verified_at = now();
            $applicant->save();
        } elseif (! $applicant->email_verified_at) {
            $applicant->email_verified_at = now();
            $applicant->save();
        }

        session()->forget(['pending_registration', 'otp_code', 'otp_expires_at']);

        Auth::guard('applicant')->login($applicant);
        $request->session()->regenerate();

        return redirect()->route('applicant.dashboard')
            ->with('success', __('auth.email_verified_welcome'));
    }

    /** Resend a fresh OTP code. */
    public function resendOtp(Request $request)
    {
        $pending = session('pending_registration');
        if (! $pending || empty($pending['email'])) {
            return redirect()->route('applicant.register');
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        session([
            'otp_code' => hash('sha256', $otp),
            'otp_expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        try {
            Notification::route('mail', $pending['email'])
                ->notify(new ApplicantEmailOtp($otp));
        } catch (\Throwable $e) {
            Log::error('[Register] OTP resend failed: '.$e->getMessage());

            return back()->withErrors(['code' => __('auth.reset_code_send_failed')]);
        }

        return back()->with('success', __('auth.verification_code_sent_to_email'));
    }
}
