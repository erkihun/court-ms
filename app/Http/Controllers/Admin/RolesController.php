<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RolesController extends Controller
{
    public function __construct()
    {
        // Enforce permission for all actions in this controller
        $this->middleware('perm:roles.manage');
    }

    public function index()
    {
        $roles = Role::withCount('users')
            ->with('permissions')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $perms = Permission::orderBy('name')->get();
        return view('admin.roles.create', compact('perms'));
    }

    public function store(Request $request)
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

        return redirect()->route('roles.index')->with('ok', 'Role created.');
    }

    public function edit(Role $role)
    {
        $role->load('permissions');
        $perms = Permission::orderBy('name')->get();

        return view('admin.roles.edit', compact('role', 'perms'));
    }

    public function update(Request $request, Role $role)
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

        return redirect()->route('roles.index')->with('ok', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return back()->with('ok', 'Role deleted.');
    }
}
