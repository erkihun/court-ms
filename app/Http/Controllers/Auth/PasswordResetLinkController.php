<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PasswordResetOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PasswordResetLinkController extends Controller
{
    // ── Step 1: show email form ────────────────────────────────────────────────

    public function create()
    {
        return response()
            ->view('admin.auth.forgot-password')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    // ── Step 2: send OTP ───────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('auth.email_not_found')]);
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        session([
            'admin_pwd_otp_email' => $request->email,
            'admin_pwd_otp_code' => hash('sha256', $otp),
            'admin_pwd_otp_expires' => now()->addMinutes(10)->timestamp,
        ]);

        try {
            $user->notify(new PasswordResetOtp($otp));
        } catch (\Throwable $e) {
            session()->forget(['admin_pwd_otp_email', 'admin_pwd_otp_code', 'admin_pwd_otp_expires']);

            Log::warning('Admin password reset OTP email failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('auth.reset_code_send_failed')]);
        }

        return redirect()->route('admin.password.otp.show')
            ->with('info', __('auth.reset_code_sent'));
    }

    // ── Step 3: show OTP entry form ────────────────────────────────────────────

    public function showOtp()
    {
        if (! session('admin_pwd_otp_email')) {
            return redirect()->route('password.request');
        }

        return response()
            ->view('admin.auth.password-otp')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    // ── Step 4: verify OTP ─────────────────────────────────────────────────────

    public function verifyOtp(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        $email = session('admin_pwd_otp_email');
        $storedHash = session('admin_pwd_otp_code');
        $expiresAt = session('admin_pwd_otp_expires');

        if (! $email || ! $storedHash || ! $expiresAt) {
            return redirect()->route('password.request')
                ->withErrors(['code' => 'Session expired. Please start again.']);
        }

        if (now()->timestamp > $expiresAt) {
            return back()->withErrors(['code' => 'This code has expired. Please request a new one.']);
        }

        if (! hash_equals($storedHash, hash('sha256', $request->input('code')))) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        session()->forget(['admin_pwd_otp_code', 'admin_pwd_otp_expires']);
        session(['admin_pwd_verified_email' => $email]);
        session()->forget('admin_pwd_otp_email');

        return redirect()->route('password.reset.new');
    }

    // ── Step 4b: resend OTP ────────────────────────────────────────────────────

    public function resendOtp()
    {
        $email = session('admin_pwd_otp_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            session([
                'admin_pwd_otp_code' => hash('sha256', $otp),
                'admin_pwd_otp_expires' => now()->addMinutes(10)->timestamp,
            ]);

            try {
                $user->notify(new PasswordResetOtp($otp));
            } catch (\Throwable $e) {
                Log::warning('Admin password reset OTP resend failed', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);

                return back()->withErrors(['code' => __('auth.reset_code_send_failed')]);
            }
        }

        return back()->with('success', 'A new code has been sent to your email.');
    }
}
