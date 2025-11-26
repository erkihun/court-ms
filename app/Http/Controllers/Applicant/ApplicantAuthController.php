<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;

use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ApplicantAuthController extends Controller
{
    public function showRegister()
    {
        return view('applicant.auth.register');
    }

    public function register(Request $request)
    {
        // Normalize National ID early: keep only digits
        if ($request->filled('national_id_number')) {
            $request->merge([
                'national_id_number' => preg_replace('/\D/', '', $request->input('national_id_number'))
            ]);
        }

        $data = $request->validate([
            'first_name'         => ['required', 'string', 'max:100'],
            'middle_name'        => ['nullable', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'gender'             => ['nullable', Rule::in(['male', 'female', 'other'])],
            'phone'              => ['required', 'string', 'max:30', 'unique:applicants,phone'],
            'email'              => ['required', 'email', 'max:255', 'unique:applicants,email'],
            'address'            => ['nullable', 'string', 'max:255'],

            // normalized (digits-only) National ID
            'national_id_number' => ['required', 'string', 'bail', 'regex:/^\d{16}$/', 'unique:applicants,national_id_number'],

            'password'           => ['required', 'confirmed', 'min:6'],
        ], [
            'national_id_number.regex' => 'National ID must be exactly 16 digits.',
        ]);

        $applicant = Applicant::create([
            ...$data,
            'password' => Hash::make($data['password']),
        ]);

        Auth::guard('applicant')->login($applicant);

        // Send verification immediately
        try {
            $applicant->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            Log::error('[VerifyEmail] send failed: ' . $e->getMessage());
        }

        /** @var Applicant $applicant */
        if (!$applicant->hasVerifiedEmail()) {
            return redirect()->route('applicant.verification.notice')
                ->with('success', 'Verification link sent.');
        }

        return redirect()->route('applicant.dashboard')->with('success', 'Welcome! Your account has been created.');
    }

    public function showLogin()
    {
        return view('applicant.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('applicant')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            /** @var Applicant|null $user */
            $user = Auth::guard('applicant')->user();

            if ($user && !$user->hasVerifiedEmail()) {
                try {
                    $user->sendEmailVerificationNotification();
                } catch (\Throwable $e) {
                    Log::error('[VerifyEmail] resend on login failed: ' . $e->getMessage());
                }

                return redirect()->route('applicant.verification.notice')
                    ->with('success', 'Verification link sent.');
            }

            return redirect()->intended(route('applicant.dashboard'))->with('success', 'Signed in.');
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('applicant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('applicant.login')->with('success', 'You have been logged out.');
    }
}
