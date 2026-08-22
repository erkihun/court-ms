<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;

function roleSwitcherRole(string $name, array $permissions): Role
{
    $role = Role::query()->create(['name' => $name]);
    $permissionIds = collect($permissions)
        ->map(fn (string $permission): int => Permission::query()->create([
            'name' => $permission,
            'label' => Str::headline($permission),
        ])->id);
    $role->permissions()->sync($permissionIds);

    return $role;
}

function roleSwitcherUser(Role ...$roles): User
{
    $user = User::factory()->create(['status' => 'active']);
    $user->roles()->sync(collect($roles)->pluck('id'));

    return $user;
}

test('a multi-role admin user can switch the active permission context', function (): void {
    $caseRole = roleSwitcherRole('case-clerk', ['cases.view']);
    $roleManager = roleSwitcherRole('role-manager', ['roles.manage']);
    $unassignedRole = roleSwitcherRole('unassigned-role', []);
    $user = roleSwitcherUser($roleManager, $caseRole);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSessionHas(User::ACTIVE_ROLE_SESSION_KEY, $caseRole->id)
        ->assertSee(route('admin.roles.switch'), false)
        ->assertSeeText('Case Clerk')
        ->assertSeeText('Role Manager');

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertRedirect(route('dashboard'));

    $this->actingAs($user)
        ->post(route('admin.roles.switch'), ['role_id' => $roleManager->id])
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas(User::ACTIVE_ROLE_SESSION_KEY, $roleManager->id);

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('cases.index'))
        ->assertRedirect(route('dashboard'));

    $this->actingAs($user)
        ->post(route('admin.roles.switch'), ['role_id' => $unassignedRole->id])
        ->assertSessionHasErrors('role_id')
        ->assertSessionHas(User::ACTIVE_ROLE_SESSION_KEY, $roleManager->id);
});

test('the role switcher is hidden for a user with one role', function (): void {
    $role = roleSwitcherRole('single-role', ['cases.view']);
    $user = roleSwitcherUser($role);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSessionHas(User::ACTIVE_ROLE_SESSION_KEY, $role->id)
        ->assertDontSee(route('admin.roles.switch'), false);
});

test('admin remains the default role before switching to a restricted role', function (): void {
    $adminRole = roleSwitcherRole('admin', []);
    $caseRole = roleSwitcherRole('case-clerk', ['cases.view']);
    $user = roleSwitcherUser($caseRole, $adminRole);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSessionHas(User::ACTIVE_ROLE_SESSION_KEY, $adminRole->id);

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertOk();

    $this->actingAs($user)
        ->post(route('admin.roles.switch'), ['role_id' => $caseRole->id])
        ->assertRedirect(route('admin.dashboard'));

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertRedirect(route('dashboard'));

    expect($user->roles()->count())->toBe(2);
});
