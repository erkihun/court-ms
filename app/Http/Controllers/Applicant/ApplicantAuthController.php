<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;

use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
            'middle_name'        => ['required', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'gender'             => ['nullable', Rule::in(['male', 'female', 'other'])],
            'position'           => ['required', 'string', 'max:150'],
            'organization_name'  => ['required', 'string', 'max:150'],
            'phone'              => ['required', 'string', 'max:30', 'unique:applicants,phone'],
            'email'              => ['required', 'email', 'max:255', 'unique:applicants,email'],
            'address'            => ['required', 'string', 'max:255'],

            // normalized (digits-only) National ID
            'national_id_number' => ['required', 'string', 'bail', 'regex:/^\d{16}$/', 'unique:applicants,national_id_number'],

            'password'           => ['required', 'confirmed', 'min:6'],
        ], [
            'national_id_number.regex' => 'National ID must be exactly 16 digits.',
        ]);

        $applicant = Applicant::create([
            ...$data,
            'password' => Hash::make($data['password']),
            'is_active' => true,
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
        // Always reset acting-as flag on login screen
        session()->forget('acting_as_respondent');
        $asRespondentNav = request('login_as') === 'respondent';
        return view('applicant.auth.login', compact('asRespondentNav'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $existing = Applicant::where('email', $credentials['email'])->first();
        if ($existing && !$existing->is_active) {
            if ($request->expectsJson()) {
                return response()->json([
                    'errors' => ['email' => ['Your account has been deactivated.']],
                ], 422);
            }

            return back()
                ->withErrors(['email' => 'This account has been deactivated.'])
                ->withInput($request->only('email', 'login_as'));
        }

        $remember = $request->boolean('remember');

        $throttleKey = Str::lower($credentials['email']) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $message = "Too many login attempts. Please try again in {$seconds} seconds.";
            if ($request->expectsJson()) {
                return response()->json([
                    'errors' => ['email' => [$message]],
                ], 429);
            }

            return back()
                ->withErrors(['email' => $message])
                ->withInput($request->only('email', 'login_as'));
        }

        $credentials['is_active'] = true;

        $isRespondentMode = $request->input('login_as') === 'respondent';

        // 1) If logging in as respondent, ensure an applicant record exists and log in
        if ($isRespondentMode) {
            $applicant = Applicant::where('email', $credentials['email'])->first();
            if (!$applicant) {
                $applicant = Applicant::create([
                    'first_name' => $existing?->first_name ?? '',
                    'middle_name' => $existing?->middle_name ?? '',
                    'last_name' => $existing?->last_name ?? '',
                    'gender' => $existing?->gender ?? null,
                    'position' => $existing?->position ?? '',
                    'organization_name' => $existing?->organization_name ?? '',
                    'phone' => $existing?->phone ?? ('resp_' . uniqid()),
                    'email' => $credentials['email'],
                    'address' => $existing?->address ?? '',
                    'national_id_number' => $existing?->national_id_number ?? '',
                    'password' => Hash::make($credentials['password']),
                    'is_active' => true,
                ]);
            }

            if (Hash::check($credentials['password'], $applicant->password) || Auth::guard('applicant')->attempt($credentials, $remember)) {
                Auth::guard('applicant')->login($applicant, $remember);
                $request->session()->regenerate();
                $request->session()->put('acting_as_respondent', true);
                RateLimiter::clear($throttleKey);
                $target = route('respondent.dashboard');
                return $request->expectsJson()
                    ? response()->json(['redirect' => $target, 'message' => 'Signed in.'])
                    : redirect($target)->with('success', 'Signed in.');
            }
        }

        // 2) Normal applicant login
        if (Auth::guard('applicant')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $request->session()->forget('acting_as_respondent');
            RateLimiter::clear($throttleKey);
            $target = route('applicant.dashboard');
            return $request->expectsJson()
                ? response()->json(['redirect' => $target, 'message' => 'Signed in.'])
                : redirect($target)->with('success', 'Signed in.');
        }

        RateLimiter::hit($throttleKey, 300);

        if ($request->expectsJson()) {
            return response()->json([
                'errors' => ['email' => ['Invalid credentials.']],
            ], 422);
        }

        return back()
            ->withErrors(['email' => 'Invalid credentials.'])
            ->withInput($request->only('email', 'login_as'));
    }

    public function logout(Request $request)
    {
        Auth::guard('applicant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('applicant.login');
    }
}
