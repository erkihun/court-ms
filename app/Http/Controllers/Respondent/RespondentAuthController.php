<?php

namespace App\Http\Controllers\Respondent;

use App\Http\Controllers\Controller;
use App\Models\Respondent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RespondentAuthController extends Controller
{
    public function showRegister()
    {
        return view('respondant.auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name'         => ['required', 'string', 'max:100'],
            'middle_name'        => ['required', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'gender'             => ['nullable', Rule::in(['male', 'female', 'other'])],
            'position'           => ['required', 'string', 'max:150'],
            'organization_name'  => ['required', 'string', 'max:150'],
            'address'            => ['required', 'string', 'max:255'],
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
            'phone'             => $data['phone'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
        ]);

        return redirect()->route('respondent.register')
            ->with('success', 'Registration submitted successfully.');
    }

    public function showLogin()
    {
        return view('respondant.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('respondent')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('respondent.dashboard'))
                ->with('success', 'Logged in successfully.');
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('respondent')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('respondent.login')->with('success', 'Logged out.');
    }
}
