<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApplicantProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('applicant.profile.edit', ['user' => $request->user('applicant')]);
    }

    public function update(Request $request)
    {
        $user = $request->user('applicant');

        $validated = $request->validate([
            'first_name'         => ['required', 'string', 'max:100'],
            'middle_name'        => ['nullable', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'gender'             => ['nullable', 'in:male,female,other'],
            'position'           => ['required', 'string', 'max:150'],
            'organization_name'  => ['required', 'string', 'max:150'],
            'phone'              => ['required', 'string', 'max:30', 'unique:applicants,phone,' . $user->id],
            'email'              => ['required', 'email', 'max:255', 'unique:applicants,email,' . $user->id],
            'address'            => ['required', 'string', 'max:255'],
            'national_id_number' => ['required', 'string', 'max:100', 'unique:applicants,national_id_number,' . $user->id],

            'current_password'   => ['nullable', 'current_password:applicant'],
            'password'           => ['nullable', 'confirmed', 'min:6'],
        ]);

        if (!empty($validated['password'])) {
            if (empty($validated['current_password'])) {
                return back()->withErrors(['current_password' => 'Enter your current password to set a new one.']);
            }
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        unset($validated['current_password']);

        $user->fill($validated)->save();

        return back()->with('success', 'Profile updated.');
    }
}
