<?php

namespace App\Http\Controllers\Respondent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RespondentAuthController extends Controller
{
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
