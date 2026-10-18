<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('the roles list and form render grouped permissions', function (): void {
    $staff = inertiaStaff(['roles.manage', 'cases.view']);
    $role = Role::query()->where('name', '!=', '')->firstOrFail();

    $this->actingAs($staff)
        ->get(route('roles.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Roles/Index')
            ->has('roles.data', 1)
            ->where('roles.data.0.permissions', ['cases.view', 'roles.manage'])
            ->where('roles.data.0.usersCount', 1));

    $this->actingAs($staff)
        ->get(route('roles.edit', $role))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Roles/Form')
            ->where('role.id', $role->id)
            ->where('role.usersCount', 1)
            ->where('groups', fn ($groups) => collect($groups)->pluck('key')->sort()->values()->all() === ['cases', 'roles']));
});

test('roles are created and updated with their permissions, including clearing them', function (): void {
    $staff = inertiaStaff(['roles.manage']);
    $view = Permission::query()->create(['name' => 'cases.view']);

    $this->actingAs($staff)
        ->post(route('roles.store'), ['name' => 'auditor', 'description' => 'Reads cases', 'permissions' => [$view->id]])
        ->assertRedirect(route('roles.index'));

    $auditor = Role::query()->where('name', 'auditor')->firstOrFail();
    expect($auditor->permissions()->pluck('name')->all())->toBe(['cases.view']);

    $this->actingAs($staff)
        ->patch(route('roles.update', $auditor), ['name' => 'auditor', 'description' => '', 'permissions' => []])
        ->assertRedirect(route('roles.index'));

    expect($auditor->permissions()->count())->toBe(0);
});

test('staff without roles.manage cannot reach the roles pages', function (): void {
    $this->actingAs(inertiaStaff(['cases.view']))
        ->get(route('roles.index'))
        ->assertRedirect(route('dashboard'));
});

test('the permissions list is searchable and caps the page size', function (): void {
    $staff = inertiaStaff(['permissions.manage']);
    Permission::query()->create(['name' => 'letters.approve', 'label' => 'Approve letters']);

    $this->actingAs($staff)
        ->get(route('permissions.index', ['q' => 'letters', 'perPage' => 100000]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Permissions/Index')
            ->where('filters.q', 'letters')
            ->where('permissions.per_page', 100)
            ->where('permissions.data.0.name', 'letters.approve')
            ->where('permissions.data.0.label', 'Approve letters'));
});

test('permissions keep a label and description per locale', function (): void {
    $staff = inertiaStaff(['permissions.manage']);

    $this->actingAs($staff)
        ->post(route('permissions.store'), [
            'name' => 'reports.export',
            'label_translations' => ['en' => 'Export reports', 'am' => 'ሪፖርቶችን ላክ'],
            'description_translations' => ['en' => 'Download reports', 'am' => ''],
        ])
        ->assertRedirect(route('permissions.index'));

    $permission = Permission::query()->where('name', 'reports.export')->firstOrFail();
    expect($permission->label)->toBe('Export reports')
        ->and($permission->label_translations)->toEqual(['en' => 'Export reports', 'am' => 'ሪፖርቶችን ላክ'])
        ->and($permission->description_translations)->toBe(['en' => 'Download reports']);

    $this->actingAs($staff)
        ->get(route('permissions.edit', $permission))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Permissions/Form')
            ->where('locales', ['en', 'am'])
            ->where('labels', ['en' => 'Export reports', 'am' => 'ሪፖርቶችን ላክ'])
            ->where('descriptions', ['en' => 'Download reports', 'am' => '']));

    $this->actingAs($staff)
        ->post(route('permissions.store'), ['name' => 'bad name!'])
        ->assertSessionHasErrors('name');
});

test('the admin role and roles still held by staff cannot be deleted', function (): void {
    $manager = inertiaStaff(['roles.manage']);
    $admin = Role::query()->firstOrCreate(['name' => 'admin']);
    $held = Role::query()->create(['name' => 'clerk']);
    User::factory()->create(['status' => 'active'])->roles()->attach($held);
    $unused = Role::query()->create(['name' => 'unused']);

    $this->actingAs($manager)->from(route('roles.index'))->delete(route('roles.destroy', $admin))
        ->assertRedirect(route('roles.index'))
        ->assertSessionHas('error', 'roles.index.cannot_delete_admin');
    $this->actingAs($manager)->from(route('roles.index'))->delete(route('roles.destroy', $held))
        ->assertSessionHas('error', ['key' => 'roles.index.cannot_delete_assigned', 'replace' => ['count' => 1]]);
    $this->actingAs($manager)->from(route('roles.index'))->delete(route('roles.destroy', $unused))
        ->assertSessionHas('ok');

    expect(Role::query()->whereKey([$admin->id, $held->id])->count())->toBe(2)
        ->and(Role::query()->whereKey($unused->id)->exists())->toBeFalse();
});
