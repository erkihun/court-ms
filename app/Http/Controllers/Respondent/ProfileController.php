<?php

namespace App\Http\Controllers\Respondent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        $respondent = Auth::guard('applicant')->user();
        return view('applicant.respondent.profile.edit', compact('respondent'));
    }

    public function update(Request $request)
    {
        $respondent = Auth::guard('respondent')->user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['nullable', 'in:male,female,other'],
            'position' => ['nullable', 'string', 'max:150'],
            'organization_name' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'national_id' => ['nullable', 'digits:16', 'unique:respondents,national_id,' . $respondent->id],
            'phone' => ['required', 'string', 'max:30', 'unique:respondents,phone,' . $respondent->id],
            'email' => ['required', 'email', 'max:255', 'unique:respondents,email,' . $respondent->id],
        ]);

        $respondent->update($data);

        return back()->with('success', __('respondent.profile_updated'));
    }

    public function updatePassword(Request $request)
    {
        $respondent = Auth::guard('respondent')->user();

        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        if (!Hash::check($request->input('current_password'), $respondent->password)) {
            return back()->withErrors(['current_password' => __('respondent.incorrect_password')]);
        }

        $respondent->update(['password' => Hash::make($request->input('password'))]);

        return back()->with('success', __('respondent.password_updated'));
    }
}
