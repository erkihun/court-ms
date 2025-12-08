<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Respondent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApplicantRoleSwitchController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        // Mark session as acting respondent; use applicant guard/session
        $request->session()->put('acting_as_respondent', true);

        return redirect()
            ->route('respondent.dashboard')
            ->with('success', __('app.switch_to_respondent_success'));
    }
}
