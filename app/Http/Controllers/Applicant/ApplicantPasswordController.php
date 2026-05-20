<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Notifications\PasswordResetOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class ApplicantPasswordController extends Controller
{
    // ── Step 1: show email form ────────────────────────────────────────────────

    public function showLinkRequestForm()
    {
        return view('applicant.auth.forgot');
    }

    // ── Step 2: send OTP to email ──────────────────────────────────────────────

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        // Always respond the same way to avoid email enumeration
        $applicant = Applicant::where('email', $request->email)->where('is_active', true)->first();

        if ($applicant) {
            $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            session([
                'applicant_pwd_otp_email'   => $request->email,
                'applicant_pwd_otp_code'    => hash('sha256', $otp),
                'applicant_pwd_otp_expires' => now()->addMinutes(10)->timestamp,
            ]);

            $applicant->notify(new PasswordResetOtp($otp));
        }

        return redirect()->route('applicant.password.otp.show')
            ->with('info', 'If that email exists, a 6-digit reset code has been sent.');
    }

    // ── Step 3: show OTP entry form ────────────────────────────────────────────

    public function showOtpForm(Request $request)
    {
        if (!session('applicant_pwd_otp_email')) {
            return redirect()->route('applicant.password.request');
        }

        return view('applicant.auth.password-otp');
    }

    // ── Step 4: verify OTP ─────────────────────────────────────────────────────

    public function verifyOtp(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        $email      = session('applicant_pwd_otp_email');
        $storedHash = session('applicant_pwd_otp_code');
        $expiresAt  = session('applicant_pwd_otp_expires');

        if (!$email || !$storedHash || !$expiresAt) {
            return redirect()->route('applicant.password.request')
                ->withErrors(['code' => 'Session expired. Please start again.']);
        }

        if (now()->timestamp > $expiresAt) {
            return back()->withErrors(['code' => 'This code has expired. Please request a new one.']);
        }

        if (!hash_equals($storedHash, hash('sha256', $request->input('code')))) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        // OTP verified — store the email for the next step, clear OTP data
        session()->forget(['applicant_pwd_otp_code', 'applicant_pwd_otp_expires']);
        session(['applicant_pwd_verified_email' => $email]);
        session()->forget('applicant_pwd_otp_email');

        return redirect()->route('applicant.password.new.show');
    }

    // ── Step 4b: resend OTP ────────────────────────────────────────────────────

    public function resendOtp(Request $request)
    {
        $email = session('applicant_pwd_otp_email');

        if (!$email) {
            return redirect()->route('applicant.password.request');
        }

        $applicant = Applicant::where('email', $email)->first();

        if ($applicant) {
            $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            session([
                'applicant_pwd_otp_code'    => hash('sha256', $otp),
                'applicant_pwd_otp_expires' => now()->addMinutes(10)->timestamp,
            ]);

            $applicant->notify(new PasswordResetOtp($otp));
        }

        return back()->with('success', 'A new code has been sent to your email.');
    }

    // ── Step 5: show new password form ────────────────────────────────────────

    public function showNewPasswordForm(Request $request)
    {
        if (!session('applicant_pwd_verified_email')) {
            return redirect()->route('applicant.password.request');
        }

        return view('applicant.auth.reset-otp');
    }

    // ── Step 6: save new password ─────────────────────────────────────────────

    public function updatePassword(Request $request)
    {
        $email = session('applicant_pwd_verified_email');

        if (!$email) {
            return redirect()->route('applicant.password.request')
                ->withErrors(['email' => 'Session expired. Please start again.']);
        }

        $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $applicant = Applicant::where('email', $email)->where('is_active', true)->first();

        if (!$applicant) {
            session()->forget('applicant_pwd_verified_email');
            return redirect()->route('applicant.password.request')
                ->withErrors(['email' => 'Account not found or has been deactivated.']);
        }

        $applicant->forceFill([
            'password'       => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($applicant));

        session()->forget('applicant_pwd_verified_email');

        Auth::guard('applicant')->login($applicant);
        $request->session()->regenerate();

        return redirect()->route('applicant.dashboard')
            ->with('success', 'Password reset successfully. You are now signed in.');
    }
}
