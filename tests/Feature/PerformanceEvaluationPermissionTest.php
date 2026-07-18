<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

function performancePermissionUser(array $permissions = [], string $roleName = 'performance-test-role'): User
{
    $user = User::factory()->create(['email_verified_at' => now()]);
    $roleId = DB::table('roles')->insertGetId([
        'name' => $roleName,
        'description' => 'Performance permission test role',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('role_user')->insert([
        'user_id' => $user->id,
        'role_id' => $roleId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach ($permissions as $permission) {
        $permissionId = DB::table('permissions')->where('name', $permission)->value('id');
        DB::table('permission_role')->insert([
            'permission_id' => $permissionId,
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return $user;
}

beforeEach(function (): void {
    $this->seed(PermissionSeeder::class);
});

test('performance evaluation routes declare independent backend permissions', function (): void {
    $expected = [
        'performance-evaluations.index' => 'perm:performance-evaluations.view',
        'performance-evaluations.show' => 'perm:performance-evaluations.view',
        'performance-evaluations.create' => 'perm:performance-evaluations.create',
        'performance-evaluations.store' => 'perm:performance-evaluations.create',
        'performance-evaluations.edit' => 'perm:performance-evaluations.update',
        'performance-evaluations.update' => 'perm:performance-evaluations.update',
        'performance-evaluations.destroy' => 'perm:performance-evaluations.delete',
        'performance-evaluations.review' => 'perm:performance-evaluations.review',
    ];

    foreach ($expected as $routeName => $middleware) {
        expect(Route::getRoutes()->getByName($routeName)?->gatherMiddleware())
            ->toContain($middleware);
    }
});

test('user without view permission cannot access the module by direct url', function (): void {
    $user = performancePermissionUser();

    $this->actingAs($user)
        ->get(route('performance-evaluations.index'))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('error');
});

test('view permission grants read access but not create access', function (): void {
    $user = performancePermissionUser(['performance-evaluations.view']);

    $this->actingAs($user)
        ->get(route('performance-evaluations.index'))
        ->assertOk()
        ->assertDontSee(route('performance-evaluations.create'));

    $this->actingAs($user)
        ->get(route('performance-evaluations.create'))
        ->assertRedirect(route('dashboard'));
});

test('admin role retains the global permission bypass', function (): void {
    $admin = performancePermissionUser([], 'admin');

    $this->actingAs($admin)
        ->get(route('performance-evaluations.index'))
        ->assertOk();
});
