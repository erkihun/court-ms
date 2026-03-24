<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ApplicantProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('applicant.profile.edit', ['user' => $request->user('applicant')]);
    }

    public function update(Request $request)
    {
        $user = $request->user('applicant');

        $request->merge([
            // Applicants store a digits-only National ID in the DB.
            'national_id_number' => preg_replace('/\D/', '', (string) $request->input('national_id_number', '')),
            // The applicants table now requires a non-null middle name, but an empty string is allowed.
            'middle_name' => (string) $request->input('middle_name', ''),
        ]);

        $validated = $request->validate([
            'first_name'         => ['required', 'string', 'max:100'],
            'middle_name'        => ['nullable', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'gender'             => ['required', Rule::in(['male', 'female'])],
            'position'           => ['required', 'string', 'max:150'],
            'organization_name'  => ['required', 'string', 'max:150'],
            'phone'              => ['required', 'string', 'max:30', 'unique:applicants,phone,' . $user->id],
            'email'              => ['required', 'email', 'max:255', 'unique:applicants,email,' . $user->id],
            'address'            => ['required', 'string', 'max:255'],
            'national_id_number' => ['required', 'digits:16', 'unique:applicants,national_id_number,' . $user->id],

            'current_password'   => ['nullable', 'current_password:applicant'],
            'password'           => ['nullable', 'confirmed', 'min:6'],
        ], [
            'national_id_number.digits' => 'National ID must be exactly 16 digits.',
        ]);

        if (!empty($validated['password'])) {
            if (empty($validated['current_password'])) {
                return back()->withErrors(['current_password' => 'Enter your current password to set a new one.']);
            }
            $currentPassword = $validated['current_password'];
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        unset($validated['current_password']);

        $user->fill($validated);
        if (!empty($currentPassword)) {
            $user->forceFill(['remember_token' => Str::random(60)]);
        }
        $user->save();

        if (!empty($currentPassword)) {
            Auth::guard('applicant')->logoutOtherDevices($currentPassword);
            $request->session()->regenerate();
        }

        return back()->with('success', 'Profile updated.');
    }
}
