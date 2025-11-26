<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;

use App\Models\Applicant;
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
}
