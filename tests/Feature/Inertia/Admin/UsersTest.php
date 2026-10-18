<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('the user list renders with filters and per-row abilities', function (): void {
    $manager = inertiaStaff(['users.manage']);
    User::factory()->create(['name' => 'Abebe Kebede Alemu', 'email' => 'abebe@example.test', 'status' => 'inactive']);

    $this->actingAs($manager)
        ->get(route('users.index', ['q' => 'abebe', 'status' => 'inactive']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Users/Index')
            ->where('filters', ['q' => 'abebe', 'status' => 'inactive'])
            ->where('users.total', 1)
            ->where('users.data.0.name', 'Abebe Kebede Alemu')
            ->where('users.data.0.active', false)
            ->where('users.data.0.canEdit', true)
            ->where('users.data.0.canDelete', true)
            ->missing('users.data.0.password'));
});

test('staff without users.manage cannot open the user list', function (): void {
    $this->actingAs(inertiaStaff(['cases.view']))
        ->get(route('users.index'))
        ->assertRedirect(route('dashboard'));
});

test('creating a user assigns roles and emails a password setup link', function (): void {
    Notification::fake();
    $role = Role::query()->create(['name' => 'registrar']);

    $this->actingAs(inertiaStaff(['users.manage']))
        ->post(route('users.store'), [
            'first_name' => 'Hana',
            'middle_name' => 'Bekele',
            'last_name' => 'Tadesse',
            'email' => 'hana@example.test',
            'status' => 'active',
            'national_id' => '1234 5678 1234 5678',
            'roles' => [$role->id],
        ])
        ->assertRedirect(route('users.index'));

    $user = User::query()->where('email', 'hana@example.test')->firstOrFail();
    expect($user->name)->toBe('Hana Bekele Tadesse')
        ->and($user->national_id)->toBe('1234567812345678')
        ->and($user->must_change_password)->toBeTrue()
        ->and($user->roles()->pluck('name')->all())->toBe(['registrar']);
    Notification::assertSentTo($user, ResetPassword::class);
});

test('only administrators can hand out the admin role', function (): void {
    $adminRole = Role::query()->create(['name' => 'admin']);
    $target = User::factory()->create(['status' => 'active']);

    $this->actingAs(inertiaStaff(['users.manage']))
        ->get(route('users.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Users/Form')
            ->where('roles', fn ($roles) => collect($roles)->firstWhere('id', $adminRole->id)['disabled'] === true));

    $this->actingAs(inertiaStaff(['users.manage']))
        ->patch(route('users.update', $target), [
            'first_name' => 'Target',
            'last_name' => 'User',
            'email' => $target->email,
            'status' => 'active',
            'roles' => [$adminRole->id],
        ])
        ->assertSessionHasErrors('roles');

    expect($target->roles()->count())->toBe(0);

    $admin = User::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
    $admin->roles()->attach($adminRole);

    $this->actingAs($admin)
        ->patch(route('users.update', $target), [
            'first_name' => 'Target',
            'last_name' => 'User',
            'email' => $target->email,
            'status' => 'active',
            'roles' => [$adminRole->id],
        ])
        ->assertRedirect(route('users.index'))
        ->assertSessionHasNoErrors();

    expect($target->roles()->pluck('name')->all())->toBe(['admin']);
});

test('a user editing their own record cannot change roles, status, password or signature', function (): void {
    Storage::fake('public');
    $elevated = Role::query()->create(['name' => 'judge']);
    $self = inertiaStaff(['cases.view']);
    $originalRoles = $self->roles()->pluck('roles.id')->all();
    $originalPassword = $self->password;

    $this->actingAs($self)
        ->get(route('users.edit', $self))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Users/Form')->where('canManage', false));

    $this->actingAs($self)
        ->patch(route('users.update', $self), [
            'first_name' => 'Self',
            'last_name' => 'Edited',
            'email' => $self->email,
            'status' => 'inactive',
            'roles' => [$elevated->id],
            'password' => 'N3w-Passw0rd!x',
            'password_confirmation' => 'N3w-Passw0rd!x',
            'signature' => UploadedFile::fake()->image('signature.png'),
        ])
        ->assertRedirect(route('users.index'));

    $self->refresh();
    expect($self->name)->toBe('Self Edited')
        ->and($self->status)->toBe('active')
        ->and($self->password)->toBe($originalPassword)
        ->and($self->signature_path)->toBeNull()
        ->and($self->roles()->pluck('roles.id')->all())->toBe($originalRoles);
});

test('managers can clear every role, and cannot delete administrators', function (): void {
    $role = Role::query()->create(['name' => 'clerk']);
    $target = User::factory()->create(['status' => 'active']);
    $target->roles()->attach($role);
    $admin = inertiaStaff([], 'admin');
    $manager = inertiaStaff(['users.manage']);

    $this->actingAs($manager)
        ->patch(route('users.update', $target), ['first_name' => 'No', 'last_name' => 'Roles', 'email' => $target->email, 'status' => 'active'])
        ->assertRedirect(route('users.index'));
    expect($target->roles()->count())->toBe(0);

    $this->actingAs($manager)->delete(route('users.destroy', $admin))->assertForbidden();
    expect($admin->fresh())->not->toBeNull();
});

test('the profile page shows a user without exposing private fields', function (): void {
    $viewer = inertiaStaff(['users.manage']);
    $user = User::factory()->create(['name' => 'Meron Alemu', 'status' => 'active']);

    $this->actingAs($viewer)
        ->get(route('users.show', $user))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Users/Show')
            ->where('user.firstName', 'Meron')
            ->where('user.middleName', '')
            ->where('user.lastName', 'Alemu')
            ->missing('user.password')
            ->missing('user.mfa_secret'));
});
