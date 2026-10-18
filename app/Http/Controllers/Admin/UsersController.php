<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\EthiopianDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    /** List users (requires users.manage via routes) */
    public function index(Request $request): Response
    {
        $q = trim((string) $request->get('q', ''));
        $status = (string) $request->get('status', '');

        $users = User::query()
            ->with('roles')
            ->when(
                $q !== '',
                fn ($b) => $b->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                })
            )
            ->when(in_array($status, ['active', 'inactive']), fn ($b) => $b->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (User $user): array => [
                'id' => $user->id,
                'name' => (string) $user->name,
                'email' => (string) $user->email,
                'avatarUrl' => $user->avatar_url,
                'hasSignature' => (bool) $user->signature_path,
                'roles' => $user->roles->pluck('name')->values(),
                'active' => $user->status === 'active',
                'verified' => $user->hasVerifiedEmail(),
                'created' => EthiopianDate::format($user->created_at),
                'canEdit' => $request->user()->can('update', $user),
                'canDelete' => $request->user()->can('delete', $user),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => ['q' => $q, 'status' => in_array($status, ['active', 'inactive'], true) ? $status : ''],
        ]);
    }

    /** Show a single profile (policy: view) */
    public function show(Request $request, User $user): Response
    {
        $this->authorize('view', $user);
        $user->load('roles');
        [$first, $middle, $last] = $this->nameParts((string) $user->name);

        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => (string) $user->name,
                'firstName' => $first,
                'middleName' => $middle,
                'lastName' => $last,
                'email' => (string) $user->email,
                'active' => $user->status === 'active',
                'avatarUrl' => $user->avatar_url,
                'signatureUrl' => $user->signature_url,
                'stampUrl' => $user->stamp_url,
                'roles' => $user->roles->pluck('name')->values(),
                'created' => EthiopianDate::format($user->created_at, withTime: true, timeFormat: 'h:i A'),
                'updated' => EthiopianDate::format($user->updated_at, withTime: true, timeFormat: 'h:i A'),
                'canEdit' => $request->user()->can('update', $user),
            ],
        ]);
    }

    /** Create form (requires users.manage via routes) */
    public function create(Request $request): Response
    {
        return Inertia::render('Admin/Users/Form', [
            'user' => null,
            'roles' => $this->roleOptions($request->user()),
            'canManage' => true,
        ]);
    }

    /** Store (requires users.manage via routes) */
    public function store(Request $request): RedirectResponse
    {
        // Normalize national ID (strip spaces/non-digits before validation)
        $normalizedNationalId = preg_replace('/\D+/', '', (string) $request->input('national_id'));
        $request->merge(['national_id' => $normalizedNationalId ?: null]);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'date_of_birth' => ['nullable', 'date'],
            'national_id' => ['nullable', 'digits:16'],
            'position' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],

            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'signature' => ['nullable', 'image', 'mimes:png,webp,jpg,jpeg', 'max:2048'],
            'stamp' => ['nullable', 'image', 'mimes:png,webp,jpg,jpeg', 'max:2048'],
        ]);

        $this->ensureAssignableRoles($request->user(), $data['roles'] ?? []);

        // Use a random password and force reset via email.
        $rawPassword = Str::random(32);

        $avatarPath = $request->hasFile('avatar')
            ? $request->file('avatar')->store('avatars', 'public')
            : null;

        $signaturePath = $request->hasFile('signature')
            ? $request->file('signature')->store('signatures', 'public')
            : null;

        $stampPath = $request->hasFile('stamp')
            ? $request->file('stamp')->store('stamps', 'public')
            : null;

        $fullName = trim(implode(' ', array_filter([
            $data['first_name'] ?? null,
            $data['middle_name'] ?? null,
            $data['last_name'] ?? null,
        ])));

        $user = User::create([
            'name' => $fullName,
            'email' => $data['email'],
            'password' => Hash::make($rawPassword),
            'must_change_password' => true,
            'status' => $data['status'],
            'gender' => $data['gender'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'national_id' => $data['national_id'] ?? null,
            'position' => $data['position'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'avatar_path' => $avatarPath,
            'signature_path' => $signaturePath,
            'stamp_path' => $stampPath,
        ]);

        if (! empty($data['roles'])) {
            $user->roles()->sync($data['roles']);
        }

        try {
            Password::broker()->sendResetLink(['email' => $user->email]);
            $message = 'messages.success.user_created_reset_sent';
        } catch (\Throwable $e) {
            Log::warning('Failed sending user reset link', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            $message = 'messages.success.user_created_reset_manual';
        }

        return redirect()->route('users.index')->with('success', $message);
    }

    /** Edit form (policy: update) */
    public function edit(Request $request, User $user): Response
    {
        $this->authorize('update', $user);
        $user->load('roles');
        [$first, $middle, $last] = $this->nameParts((string) $user->name);

        return Inertia::render('Admin/Users/Form', [
            'user' => [
                'id' => $user->id,
                'firstName' => $first,
                'middleName' => $middle,
                'lastName' => $last,
                'email' => (string) $user->email,
                'status' => $user->status === 'active' ? 'active' : 'inactive',
                'gender' => in_array($user->gender, ['male', 'female'], true) ? $user->gender : '',
                'dateOfBirth' => $user->date_of_birth?->format('Y-m-d') ?? '',
                'nationalId' => (string) ($user->national_id ?? ''),
                'position' => (string) ($user->position ?? ''),
                'phone' => (string) ($user->phone ?? ''),
                'address' => (string) ($user->address ?? ''),
                'roleIds' => $user->roles->pluck('id')->map(fn ($id): int => (int) $id)->values(),
                'avatarUrl' => $user->avatar_url,
                'signatureUrl' => $user->signature_url,
                'stampUrl' => $user->stamp_url,
            ],
            'roles' => $this->roleOptions($request->user()),
            'canManage' => $this->canManage($request->user()),
        ]);
    }

    /** Update (policy: update) */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        // A user editing their own record without users.manage may only change
        // their contact details: roles, status, password and the signature/stamp
        // used on official documents stay with administrators.
        if (! $this->canManage($request->user())) {
            $request->request->remove('roles');
            $request->request->remove('status');
            $request->request->remove('password');
            $request->request->remove('password_confirmation');
            foreach (['signature', 'stamp'] as $asset) {
                $request->request->remove($asset);
                $request->request->remove("remove_{$asset}");
                $request->files->remove($asset);
            }
            $request->merge(['status' => $user->status === 'active' ? 'active' : 'inactive']);
        }

        // Normalize national ID (strip spaces/non-digits before validation)
        $normalizedNationalId = preg_replace('/\D+/', '', (string) $request->input('national_id'));
        $request->merge(['national_id' => $normalizedNationalId ?: null]);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'status' => ['required', Rule::in(['active', 'inactive'])],

            'password' => ['nullable', 'confirmed', PasswordRule::defaults()],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'date_of_birth' => ['nullable', 'date'],
            'national_id' => ['nullable', 'digits:16'],
            'position' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],

            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],

            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'signature' => ['nullable', 'image', 'mimes:png,webp,jpg,jpeg', 'max:2048'],
            'stamp' => ['nullable', 'image', 'mimes:png,webp,jpg,jpeg', 'max:2048'],

            'remove_avatar' => ['nullable', 'boolean'],
            'remove_signature' => ['nullable', 'boolean'],
            'remove_stamp' => ['nullable', 'boolean'],
        ]);

        if (array_key_exists('roles', $data)) {
            $this->ensureAssignableRoles($request->user(), $data['roles'] ?? [], $user);
        }

        // Update password if provided
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            $user->must_change_password = true;
        }

        // Avatar remove / replace
        if ($request->boolean('remove_avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $data['avatar_path'] = null;
        } elseif ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Signature remove / replace
        if ($request->boolean('remove_signature')) {
            if ($user->signature_path) {
                Storage::disk('public')->delete($user->signature_path);
            }
            $data['signature_path'] = null;
        } elseif ($request->hasFile('signature')) {
            if ($user->signature_path) {
                Storage::disk('public')->delete($user->signature_path);
            }
            $data['signature_path'] = $request->file('signature')->store('signatures', 'public');
        }

        // Stamp remove / replace
        if ($request->boolean('remove_stamp')) {
            if ($user->stamp_path) {
                Storage::disk('public')->delete($user->stamp_path);
            }
            $data['stamp_path'] = null;
        } elseif ($request->hasFile('stamp')) {
            if ($user->stamp_path) {
                Storage::disk('public')->delete($user->stamp_path);
            }
            $data['stamp_path'] = $request->file('stamp')->store('stamps', 'public');
        }

        $fullName = trim(implode(' ', array_filter([
            $data['first_name'] ?? null,
            $data['middle_name'] ?? null,
            $data['last_name'] ?? null,
        ])));

        // Fill basic fields
        $user->fill([
            'name' => $fullName,
            'email' => $data['email'],
            'status' => $data['status'],
            'gender' => $data['gender'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'national_id' => $data['national_id'] ?? null,
            'position' => $data['position'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'avatar_path' => $data['avatar_path'] ?? $user->avatar_path,
            'signature_path' => $data['signature_path'] ?? $user->signature_path,
            'stamp_path' => $data['stamp_path'] ?? $user->stamp_path,
        ])->save();

        // Roles are managed only through the full form (users.manage); an empty
        // selection clears them, which the old checkbox form could not express.
        if ($this->canManage($request->user())) {
            $user->roles()->sync($data['roles'] ?? []);
        }

        return redirect()->route('users.index')->with('success', [
            'key' => 'messages.success.updated',
            'replace' => ['resource' => __('messages.resources.user')],
        ]);
    }

    /** Destroy (policy: delete) */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        // Clean up files
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }
        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
        }
        if ($user->stamp_path) {
            Storage::disk('public')->delete($user->stamp_path);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', [
            'key' => 'messages.success.deleted',
            'replace' => ['resource' => __('messages.resources.user')],
        ]);
    }

    private function canManage(User $actor): bool
    {
        return $actor->hasRole('admin') || $actor->hasPermission('users.manage');
    }

    /**
     * Only administrators may hand out the admin role; anyone else with
     * users.manage could otherwise promote themselves or others.
     *
     * @param  array<int, int|string>  $roleIds
     */
    private function ensureAssignableRoles(User $actor, array $roleIds, ?User $target = null): void
    {
        if ($actor->hasRole('admin')) {
            return;
        }

        $adminRoleIds = Role::query()->whereRaw('LOWER(name) = ?', ['admin'])->pluck('id')->map(fn ($id): int => (int) $id);
        $requested = collect($roleIds)->map(fn ($id): int => (int) $id);
        $alreadyHeld = $target?->roles()->pluck('roles.id')->map(fn ($id): int => (int) $id) ?? collect();

        if ($requested->intersect($adminRoleIds)->diff($alreadyHeld)->isNotEmpty()) {
            throw ValidationException::withMessages(['roles' => __('users.admin_role_admin_only')]);
        }
    }

    /** @return list<array{id: int, name: string, disabled: bool}> */
    private function roleOptions(User $actor): array
    {
        $isAdmin = $actor->hasRole('admin');

        return Role::query()->orderBy('name')->get(['id', 'name'])
            ->map(fn (Role $role): array => [
                'id' => (int) $role->id,
                'name' => (string) $role->name,
                'disabled' => ! $isAdmin && mb_strtolower((string) $role->name) === 'admin',
            ])
            ->values()
            ->all();
    }

    /**
     * Users store one name column; the forms split it into first, middle and
     * last name (two words means no middle name).
     *
     * @return array{0: string, 1: string, 2: string}
     */
    private function nameParts(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 3) ?: [];

        return match (count($parts)) {
            0 => ['', '', ''],
            1 => [$parts[0], '', ''],
            2 => [$parts[0], '', $parts[1]],
            default => [$parts[0], $parts[1], $parts[2]],
        };
    }
}
