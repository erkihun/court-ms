<?php

namespace App\Http\Controllers\Respondent;

use App\Http\Controllers\Controller;
use App\Models\Respondent;
use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RespondentAuthController extends Controller
{
    public function showRegister()
    {
        return view('applicant.auth.register', ['asRespondentNav' => true]);
    }

    public function register(Request $request)
    {
        $normalizedNationalId = preg_replace('/\D+/', '', (string) $request->input('national_id'));
        $request->merge(['national_id' => $normalizedNationalId ?: null]);

        $data = $request->validate([
            'first_name'         => ['required', 'string', 'max:100'],
            'middle_name'        => ['required', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'gender'             => ['nullable', Rule::in(['male', 'female'])],
            'position'           => ['required', 'string', 'max:150'],
            'organization_name'  => ['required', 'string', 'max:150'],
            'address'            => ['required', 'string', 'max:255'],
            'national_id'        => ['required', 'digits:16', 'unique:respondents,national_id'],
            'phone'              => ['required', 'string', 'max:30', 'unique:respondents,phone'],
            'email'              => ['required', 'email', 'max:255', 'unique:respondents,email', 'confirmed'],
            'email_confirmation' => ['required', 'email'],
            'password'           => ['required', 'confirmed', 'min:6'],
        ]);

        Respondent::create([
            'first_name'        => $data['first_name'],
            'middle_name'       => $data['middle_name'],
            'last_name'         => $data['last_name'],
            'gender'            => $data['gender'] ?? null,
            'position'          => $data['position'],
            'organization_name' => $data['organization_name'],
            'address'           => $data['address'],
            'national_id'       => $data['national_id'],
            'phone'             => $data['phone'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
        ]);

        return redirect()->route('respondent.register')
            ->with('success', __('respondent.registration_success'));
    }

    public function showLogin()
    {
        return view('applicant.auth.login', ['asRespondentNav' => true]);
    }

    public function login(Request $request)
    {
        // Deprecated: respondent login now handled by applicant login form/guard
        return redirect()->route('applicant.login', ['login_as' => 'respondent']);
    }

    public function logout(Request $request)
    {
        // Unified auth now uses the applicant guard; clear both to be safe.
        Auth::guard('respondent')->logout();
        Auth::guard('applicant')->logout();
        $request->session()->forget('acting_as_respondent');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('applicant.login');
    }

    /**
     * Switch from respondent to applicant by logging into the applicant guard.
     */
    public function switchToApplicant(Request $request)
    {
        // Stop acting as respondent but keep the applicant session alive
        $request->session()->forget('acting_as_respondent');

        return redirect()
            ->route('applicant.dashboard')
            ->with('success', __('app.switch_to_applicant_success'));
    }
}
