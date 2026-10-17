<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Concerns\RendersAuthPages;
use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Support\AuthScreen;
use App\Support\LoginThrottle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ApplicantAuthController extends Controller
{
    use RendersAuthPages;

    public function showRegister()
    {
        return $this->authPage('Auth/Party/Register');
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

        return $this->authPage('Auth/Party/Login', [
            'loginAs' => request('login_as') === 'respondent' ? 'respondent' : 'applicant',
            'bannerUrl' => AuthScreen::bannerUrl(respectLoginToggle: true),
            'about' => AuthScreen::aboutPage(),
        ]);
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
                    'errors' => ['email' => [__('auth.account_deactivated')]],
                ], 422);
            }

            return back()
                ->withErrors(['email' => __('auth.account_deactivated')])
                ->withInput($request->only('email', 'login_as'));
        }

        $remember = $request->boolean('remember');

        $throttle = LoginThrottle::for('applicant', $credentials['email'], $request->ip());
        if ($throttle->tooManyAttempts()) {
            $seconds = $throttle->availableIn();
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

        // Respondents sign in with an existing account. Accounts are created
        // only through registration (validation, national ID, the registration
        // switch); signing in never creates one.
        if (Auth::guard('applicant')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $throttle->clear();

            if ($isRespondentMode) {
                $request->session()->put('acting_as_respondent', true);

                return $this->loginResponse($request, route('respondent.dashboard'));
            }

            $request->session()->forget('acting_as_respondent');

            return $this->loginResponse($request, route('applicant.dashboard'));
        }

        $throttle->hit();

        if ($request->expectsJson()) {
            return response()->json([
                'errors' => ['email' => [__('auth.failed')]],
            ], 422);
        }

        return back()
            ->withErrors(['email' => __('auth.failed')])
            ->withInput($request->only('email', 'login_as'));
    }

    private function loginResponse(Request $request, string $target)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'redirect' => $target,
                'message' => __('auth.signed_in'),
            ]);
        }

        return redirect($target)->with('success', __('auth.signed_in'));
    }

    public function logout(Request $request)
    {
        Auth::guard('applicant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('applicant.login');
    }
}
