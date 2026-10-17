<?php

declare(strict_types=1);

use App\Models\HomeSetting;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;

/*
| The landing CMS edits the public home page. Only staff who can manage system
| settings may read or change it; any other signed-in staff account is refused.
*/

function landingStaff(array $permissions): User
{
    $role = Role::query()->create(['name' => 'landing-auth-'.Str::uuid()]);
    $role->permissions()->sync(collect($permissions)->map(
        fn (string $name): int => Permission::query()->firstOrCreate(['name' => $name], ['label' => Str::headline($name)])->id,
    ));

    $user = User::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
    $user->roles()->attach($role);

    return $user;
}

test('staff without settings.manage cannot open the landing manager', function (): void {
    $this->actingAs(landingStaff(['cases.view']))
        ->get(route('admin.landing.index'))
        ->assertRedirect(route('dashboard'));
});

test('staff without settings.manage cannot change public landing content', function (): void {
    HomeSetting::setJson('footer.settings', ['contact_phone' => 'original']);

    $this->actingAs(landingStaff(['cases.view']))
        ->put(route('admin.landing.footer.update'), ['contact_phone' => 'defaced'])
        ->assertRedirect(route('dashboard'));

    expect(HomeSetting::getJson('footer.settings')['contact_phone'] ?? null)->toBe('original');
});

test('staff with settings.manage can open the landing manager', function (): void {
    $this->actingAs(landingStaff(['settings.manage']))
        ->get(route('admin.landing.index'))
        ->assertOk();
});
