<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ApplicantAuthController extends Controller
{
    public function showRegister()
    {
        return response()
            ->view('applicant.auth.register')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function register(Request $request)
    {
        // Normalize National ID early: keep only digits
        if ($request->filled('national_id_number')) {
            $request->merge([
                'national_id_number' => preg_replace('/\D/', '', $request->input('national_id_number')),
            ]);
        }

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'position' => ['required', 'string', 'max:150'],
            'organization_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30', 'unique:applicants,phone'],
            'email' => ['required', 'email', 'max:255', 'unique:applicants,email'],
            'address' => ['required', 'string', 'max:255'],
            'is_lawyer' => ['required', 'boolean'],
            'lawyer_document' => ['nullable', 'required_if:is_lawyer,1', 'file', 'mimes:pdf', 'max:1024'],

            // normalized (digits-only) National ID
            'national_id_number' => ['required', 'string', 'bail', 'regex:/^\d{16}$/', 'unique:applicants,national_id_number'],

            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'national_id_number.regex' => __('auth.national_id_must_be_16'),
            'lawyer_document.required_if' => __('auth.lawyer_document_required'),
        ]);

        $isLawyer = (bool) $data['is_lawyer'];

        $lawyerDocumentPath = null;
        if ($isLawyer && $request->hasFile('lawyer_document')) {
            $lawyerDocumentPath = $request->file('lawyer_document')->store('lawyer_documents', 'private');
        }

        unset($data['lawyer_document']);

        // Registration is not email-OTP gated: the account is created straight
        // away. OTP verification is only used for the password-reset flow.
        $applicant = Applicant::create([
            ...$data,
            'is_lawyer' => $isLawyer,
            'lawyer_document_path' => $lawyerDocumentPath,
            'is_active' => true,
        ]);

        // Not mass-assignable — set explicitly.
        $applicant->email_verified_at = now();
        $applicant->save();

        Auth::guard('applicant')->login($applicant);
        $request->session()->regenerate();

        return redirect()->route('applicant.dashboard')
            ->with('success', __('auth.email_verified_welcome'));
    }

    public function showLogin()
    {
        // Always reset acting-as flag on login screen
        session()->forget('acting_as_respondent');
        $asRespondentNav = request('login_as') === 'respondent';

        return response()
            ->view('applicant.auth.login', compact('asRespondentNav'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $existing = Applicant::where('email', $credentials['email'])->first();
        if ($existing && ! $existing->is_active) {
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
        $loginSettings = SystemSetting::cached();
        $maxAttempts = $loginSettings?->login_max_attempts ?? 5;
        $lockoutSecs = ($loginSettings?->lockout_minutes ?? 15) * 60;

        $throttleKey = Str::lower($credentials['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $message = trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]);
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
            if (! $applicant) {
                $applicant = Applicant::create([
                    'first_name' => $existing?->first_name ?? '',
                    'middle_name' => $existing?->middle_name ?? '',
                    'last_name' => $existing?->last_name ?? '',
                    'gender' => $existing?->gender ?? null,
                    'position' => $existing?->position ?? '',
                    'organization_name' => $existing?->organization_name ?? '',
                    'phone' => $existing?->phone ?? ('resp_'.uniqid()),
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

                return $this->loginResponse($request, $target);
            }
        }

        // 2) Normal applicant login
        if (Auth::guard('applicant')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $request->session()->forget('acting_as_respondent');
            RateLimiter::clear($throttleKey);
            $target = route('applicant.dashboard');

            return $this->loginResponse($request, $target);
        }

        RateLimiter::hit($throttleKey, $lockoutSecs);

        if ($request->expectsJson()) {
            return response()->json([
                'errors' => ['email' => ['Invalid credentials.']],
            ], 422);
        }

        return back()
            ->withErrors(['email' => 'Invalid credentials.'])
            ->withInput($request->only('email', 'login_as'));
    }

    private function loginResponse(Request $request, string $target)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'redirect' => $target,
                'message' => 'Signed in.',
            ]);
        }

        return redirect($target)->with('success', 'Signed in.');
    }

    public function logout(Request $request)
    {
        Auth::guard('applicant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('applicant.login');
    }
}
