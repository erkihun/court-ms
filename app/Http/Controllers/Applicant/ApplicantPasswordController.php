<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class ApplicantPasswordController extends Controller
{
    // Show "forgot password" form
    public function showLinkRequestForm()
    {
        // matches: resources/views/applicant/auth/forgot.blade.php
        return view('applicant.auth.forgot');
    }

    // Send reset link email via applicants broker
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::broker('applicants')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    // Show the reset form (token + email)
    public function showResetForm(Request $request, string $token)
    {
        // matches: resources/views/applicant/auth/reset.blade.php
        return view('applicant.auth.reset', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    // Handle reset submit
    public function reset(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::broker('applicants')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($applicant, $password) {
                // If Applicant model uses 'password' => 'hashed' cast, don't double-hash:
                $applicant->forceFill([
                    'password'       => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($applicant));

                Auth::guard('applicant')->login($applicant);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('applicant.dashboard')->with('success', 'Password updated. You are signed in.')
            : back()->withErrors(['email' => __($status)]);
    }
}
