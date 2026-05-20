<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;

use App\Models\Applicant;
use App\Notifications\ApplicantEmailOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Log;

class ApplicantVerificationController extends Controller
{
    /** Show the “verify your email” notice. */
    public function notice(Request $request)
    {
        /** @var Applicant|null $user */
        $user = $request->user('applicant');

        if (!$user) {
            return redirect()->route('applicant.login');
        }
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('applicant.dashboard')->with('success', 'Email already verified.');
        }
        return view('applicant.auth.verify');
    }

    /** Resend verification link. */
    public function send(Request $request)
    {
        /** @var Applicant|null $user */
        $user = $request->user('applicant');

        if (!$user) {
            return redirect()->route('applicant.login');
        }
        if ($user->hasVerifiedEmail()) {
            return back()->with('success', 'Email already verified.');
        }

        try {
            $user->sendEmailVerificationNotification();
            return back()->with('success', 'Verification link sent.');
        } catch (\Throwable $e) {
            Log::error('[VerifyEmail] resend failed: ' . $e->getMessage());
            return back()->with('success', 'Could not send email. Please try again later.');
        }
    }

    /** Handle the signed verification link. */
    public function verify(EmailVerificationRequest $request)
    {
        // Ensure the applicant guard is in context for downstream usage
        Auth::shouldUse('applicant');

        if ($request->user() && !$request->user()->hasVerifiedEmail()) {
            $request->fulfill(); // sets email_verified_at + fires event
        }

        return redirect()->route('applicant.dashboard')->with('success', 'Email verified successfully.');
    }

    /** Show the OTP entry form (for new registrations). */
    public function showOtp(Request $request)
    {
        if (!session('pending_applicant_id')) {
            return redirect()->route('applicant.register');
        }

        return view('applicant.auth.verify-otp');
    }

    /** Verify the submitted OTP code. */
    public function verifyOtp(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        $pendingId  = session('pending_applicant_id');
        $storedHash = session('otp_code');
        $expiresAt  = session('otp_expires_at');

        if (!$pendingId || !$storedHash || !$expiresAt) {
            return redirect()->route('applicant.register')
                ->withErrors(['code' => 'Session expired. Please register again.']);
        }

        if (now()->timestamp > $expiresAt) {
            return back()->withErrors(['code' => 'This code has expired. Please request a new one.']);
        }

        if (!hash_equals($storedHash, hash('sha256', $request->input('code')))) {
            return back()->withErrors(['code' => 'Invalid verification code. Please try again.']);
        }

        $applicant = Applicant::find($pendingId);
        if (!$applicant) {
            session()->forget(['pending_applicant_id', 'otp_code', 'otp_expires_at']);
            return redirect()->route('applicant.register')
                ->withErrors(['code' => 'Account not found. Please register again.']);
        }

        $applicant->email_verified_at = now();
        $applicant->save();

        session()->forget(['pending_applicant_id', 'otp_code', 'otp_expires_at']);

        Auth::guard('applicant')->login($applicant);
        $request->session()->regenerate();

        return redirect()->route('applicant.dashboard')
            ->with('success', 'Email verified! Welcome to the applicant portal.');
    }

    /** Resend a fresh OTP code. */
    public function resendOtp(Request $request)
    {
        $pendingId = session('pending_applicant_id');
        if (!$pendingId) {
            return redirect()->route('applicant.register');
        }

        $applicant = Applicant::find($pendingId);
        if (!$applicant) {
            return redirect()->route('applicant.register');
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        session([
            'otp_code'       => hash('sha256', $otp),
            'otp_expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        $applicant->notify(new ApplicantEmailOtp($otp));

        return back()->with('success', 'A new verification code has been sent to your email.');
    }
}
