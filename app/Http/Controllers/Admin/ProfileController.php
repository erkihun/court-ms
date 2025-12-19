<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Show the user's profile form.
     */
    public function edit(Request $request)
    {
        return view('admin.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information, avatar, and signature.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'avatar'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'signature' => ['nullable', 'image', 'mimes:png,webp,jpg,jpeg', 'max:2048'],
            // Optional password change (uncomment in form when you need it)
            // 'current_password' => ['nullable', 'current_password'],
            // 'password' => ['nullable', Password::defaults(), 'confirmed'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $validated['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Handle signature upload
        if ($request->hasFile('signature')) {
            if ($user->signature_path) {
                Storage::disk('public')->delete($user->signature_path);
            }
            $validated['signature_path'] = $request->file('signature')->store('signatures', 'public');
        }

        // Optional password change
        // if (!empty($validated['password'] ?? null)) {
        //     $validated['password'] = Hash::make($validated['password']);
        // }

        // Reset verification if email changes
        if (isset($validated['email']) && $validated['email'] !== $user->email) {
            $user->email_verified_at = null;
        }

        // Persist
        $user->fill($validated)->save();

        return redirect()->route('profile.edit')->with('success', 'Profile updated.');
    }

    /**
     * Delete the user's account (Breeze style).
     */
    public function destroy(Request $request)
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Delete files before deleting the user
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }
        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
        }

        // Logout via facade (more reliable across guards)
        Auth::logout();

        // Delete user
        $user->delete();

        // Kill session and CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Your account has been deleted.');
    }
}
