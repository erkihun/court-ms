<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class NewPasswordController extends Controller
{
    // ── Step 5: show new password form ────────────────────────────────────────

    public function create()
    {
        if (!session('admin_pwd_verified_email')) {
            return redirect()->route('password.request');
        }

        return response()
            ->view('admin.auth.reset-password')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    // ── Step 6: save new password ─────────────────────────────────────────────

    public function store(Request $request)
    {
        $email = session('admin_pwd_verified_email');

        if (!$email) {
            return redirect()->route('password.request')
                ->withErrors(['email' => __('auth.password_reset_session_expired')]);
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('password.request')
                ->withErrors(['email' => __('auth.account_not_found_or_deactivated')]);
        }

        $user->forceFill([
            'password'       => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        session()->forget('admin_pwd_verified_email');

        return redirect()->route('login')
            ->with('status', 'Password reset successfully. Please sign in.');
    }
}
