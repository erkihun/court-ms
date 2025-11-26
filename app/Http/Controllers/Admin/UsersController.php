<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UsersController extends Controller
{
    /** List users (requires users.manage via routes) */
    public function index(Request $request)
    {
        $q      = trim($request->get('q', ''));
        $status = $request->get('status');

        $users = User::query()
            ->with('roles')
            ->when(
                $q !== '',
                fn($b) =>
                $b->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                })
            )
            ->when(in_array($status, ['active', 'inactive']), fn($b) => $b->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /** Show a single profile (policy: view) */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        $user->load('roles');

        return view('admin.users.show', compact('user'));
    }

    /** Create form (requires users.manage via routes) */
    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.create', compact('roles'));
    }

    /** Store (requires users.manage via routes) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'confirmed', 'min:6'],
            'status'                => ['required', Rule::in(['active', 'inactive'])],
            'roles'                 => ['nullable', 'array'],
            'roles.*'               => ['integer', 'exists:roles,id'],

            'avatar'                => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'signature'             => ['nullable', 'image', 'mimes:png,webp,jpg,jpeg', 'max:2048'],
        ]);

        $avatarPath = $request->hasFile('avatar')
            ? $request->file('avatar')->store('avatars', 'public')
            : null;

        $signaturePath = $request->hasFile('signature')
            ? $request->file('signature')->store('signatures', 'public')
            : null;

        $user = User::create([
            'name'           => $data['name'],
            'email'          => $data['email'],
            'password'       => Hash::make($data['password']),
            'status'         => $data['status'],
            'avatar_path'    => $avatarPath,
            'signature_path' => $signaturePath,
        ]);

        if (!empty($data['roles'])) {
            $user->roles()->sync($data['roles']);
        }

        return redirect()->route('users.index')->with('success', 'User created.');
    }

    /** Edit form (policy: update) */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = Role::orderBy('name')->get();
        $user->load('roles');

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /** Update (policy: update) */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'status'                => ['required', Rule::in(['active', 'inactive'])],

            'password'              => ['nullable', 'confirmed', 'min:6'],

            'roles'                 => ['nullable', 'array'],
            'roles.*'               => ['integer', 'exists:roles,id'],

            'avatar'                => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'signature'             => ['nullable', 'image', 'mimes:png,webp,jpg,jpeg', 'max:2048'],

            'remove_avatar'         => ['nullable', 'boolean'],
            'remove_signature'      => ['nullable', 'boolean'],
        ]);

        // Update password if provided
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        // Avatar remove / replace
        if ($request->boolean('remove_avatar')) {
            if ($user->avatar_path) Storage::disk('public')->delete($user->avatar_path);
            $data['avatar_path'] = null;
        } elseif ($request->hasFile('avatar')) {
            if ($user->avatar_path) Storage::disk('public')->delete($user->avatar_path);
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Signature remove / replace
        if ($request->boolean('remove_signature')) {
            if ($user->signature_path) Storage::disk('public')->delete($user->signature_path);
            $data['signature_path'] = null;
        } elseif ($request->hasFile('signature')) {
            if ($user->signature_path) Storage::disk('public')->delete($user->signature_path);
            $data['signature_path'] = $request->file('signature')->store('signatures', 'public');
        }

        // Fill basic fields
        $user->fill([
            'name'           => $data['name'],
            'email'          => $data['email'],
            'status'         => $data['status'],
            'avatar_path'    => $data['avatar_path']    ?? $user->avatar_path,
            'signature_path' => $data['signature_path'] ?? $user->signature_path,
        ])->save();

        // Sync roles if provided
        if (array_key_exists('roles', $data)) {
            $user->roles()->sync($data['roles'] ?? []);
        }

        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    /** Destroy (policy: delete) */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Clean up files
        if ($user->avatar_path)    Storage::disk('public')->delete($user->avatar_path);
        if ($user->signature_path) Storage::disk('public')->delete($user->signature_path);

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted.');
    }
}
