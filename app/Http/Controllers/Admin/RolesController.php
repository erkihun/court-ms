<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RolesController extends Controller
{
    public function __construct()
    {
        // Enforce permission for all actions in this controller
        $this->middleware('perm:roles.manage');
    }

    public function index(): Response
    {
        $roles = Role::withCount('users')
            ->with('permissions:id,name')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Role $role): array => [
                'id' => (int) $role->id,
                'name' => (string) $role->name,
                'description' => $role->description,
                'permissions' => $role->permissions->pluck('name')->sort()->values(),
                'usersCount' => (int) $role->users_count,
            ]);

        return Inertia::render('Admin/Roles/Index', ['roles' => $roles]);
    }

    public function create(): Response
    {
        return $this->form(null);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255', 'unique:roles,name'],
            'description'  => ['nullable', 'string', 'max:255'],
            'permissions'  => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = Role::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('ok', [
            'key' => 'messages.success.created',
            'replace' => ['resource' => __('messages.resources.role')],
        ]);
    }

    public function edit(Role $role): Response
    {
        return $this->form($role->load('permissions:id')->loadCount('users'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'description'  => ['nullable', 'string', 'max:255'],
            'permissions'  => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('ok', [
            'key' => 'messages.success.updated',
            'replace' => ['resource' => __('messages.resources.role')],
        ]);
    }

    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();
        return back()->with('ok', [
            'key' => 'messages.success.deleted',
            'replace' => ['resource' => __('messages.resources.role')],
        ]);
    }

    private function form(?Role $role): Response
    {
        $perms = Permission::orderBy('name')->get();

        return Inertia::render('Admin/Roles/Form', [
            'role' => $role === null ? null : [
                'id' => (int) $role->id,
                'name' => (string) $role->name,
                'description' => (string) ($role->description ?? ''),
                'permissionIds' => $role->permissions->pluck('id')->map(fn ($id): int => (int) $id)->values(),
                'usersCount' => (int) $role->users_count,
            ],
            'groups' => $this->groupPermissions($perms)->map(fn (array $group): array => [
                'key' => (string) $group['key'],
                'label' => (string) $group['label'],
                'permissions' => $group['permissions']->map(fn (Permission $permission): array => [
                    'id' => (int) $permission->id,
                    'name' => (string) $permission->name,
                    'label' => $permission->label_localized,
                ])->values(),
            ])->values(),
        ]);
    }

    private function groupPermissions(Collection $perms): Collection
    {
        $grouped = [];

        foreach ($perms as $perm) {
            $groupKey = $this->normalizePermissionGroupKey(Str::before($perm->name, '.'));
            $grouped[$groupKey][] = $perm;
        }

        $orderedLabels = collect($this->permissionGroupLabels())
            ->filter(fn ($_, $key) => isset($grouped[$key]));

        $ordered = $orderedLabels->map(function ($label, $key) use ($grouped) {
            return [
                'key' => $key,
                'label' => $label,
                'permissions' => collect($grouped[$key])->sortBy('name'),
            ];
        });

        $remaining = collect($grouped)
            ->except($ordered->pluck('key')->all())
            ->map(fn ($items, $key) => [
                'key' => $key,
                'label' => Str::headline(str_replace(['-', '_'], ' ', $key)),
                'permissions' => collect($items)->sortBy('name'),
            ]);

        return $ordered->concat($remaining);
    }

    private function normalizePermissionGroupKey(string $key): string
    {
        $aliases = [
            'decisions' => 'decision',
        ];

        return $aliases[$key] ?? $key;
    }

    private function permissionGroupLabels(): array
    {
        return [
            'cases' => __('roles.groups.cases'),
            'appeals' => __('roles.groups.appeals'),
            'decision' => __('roles.groups.decisions'),
            'bench-notes' => __('roles.groups.bench_notes'),
            'letters' => __('roles.groups.letters'),
            'hearing' => __('roles.groups.hearings'),
            'file' => __('roles.groups.files'),
            'message' => __('roles.groups.messaging'),
            'reports' => __('roles.groups.reports'),
            'settings' => __('roles.groups.system'),
            'teams' => __('roles.groups.teams'),
            'users' => __('roles.groups.users'),
            'roles' => __('roles.groups.roles'),
            'permissions' => __('roles.groups.permissions'),
            'templates' => __('roles.groups.templates'),
            'applicants' => __('roles.groups.applicants'),
            'notes' => __('roles.groups.notes'),
        ];
    }
}
