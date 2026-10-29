<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password (the profile's Security tab, and the forced
     * change). Errors go to the default bag, where the React forms read them.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();
        $wasForced = (bool) ($user?->must_change_password);

        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
            'remember_token' => Str::random(60),
        ]);

        Auth::logoutOtherDevices($validated['password']);
        $request->session()->regenerate();

        // If they were forced to change password, log them out and send to login.
        if ($wasForced) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', __('auth.password_updated_sign_in_again'));
        }

        return back()->with('status', 'password-updated');
    }
}
