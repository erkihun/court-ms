<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Throwable;

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
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $validated = $request->safe()->except(['avatar', 'signature', 'remove_signature']);
        $oldAvatarPath = $user->avatar_path;
        $newAvatarPath = $request->file('avatar')?->store('avatars', 'public');

        if ($newAvatarPath) {
            $validated['avatar_path'] = $newAvatarPath;
        }

        // Reset verification if email changes
        if (isset($validated['email']) && $validated['email'] !== $user->email) {
            $user->email_verified_at = null;
        }

        try {
            $user->fill($validated)->save();
        } catch (Throwable $exception) {
            Storage::disk('public')->delete(array_filter([$newAvatarPath]));
            throw $exception;
        }

        if ($newAvatarPath && $oldAvatarPath) {
            Storage::disk('public')->delete($oldAvatarPath);
        }

        return redirect()->route('profile.edit')->with('success', [
            'key' => 'messages.success.updated',
            'replace' => ['resource' => __('messages.resources.profile')],
        ]);
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

        return redirect('/')->with('success', 'messages.success.account_deleted');
    }
}
