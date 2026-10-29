<?php

declare(strict_types=1);

use App\Models\CaseType;
use App\Models\CourtCase;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function feedCase(string $status = 'pending'): CourtCase
{
    $caseType = CaseType::query()->create(['name' => 'Feed '.Str::uuid()]);

    return CourtCase::query()->create([
        'case_number' => 'NF-'.Str::upper(Str::random(10)),
        'title' => 'Feed case',
        'case_type_id' => $caseType->id,
        'filing_date' => now()->toDateString(),
        'status' => $status,
    ]);
}

function feedAdmin(): User
{
    $user = User::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
    $role = Role::query()->firstOrCreate(['name' => 'admin']);
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

test('the header bell feed lists an admin\'s unseen notifications and marks them read', function (): void {
    $user = feedAdmin();
    $case = feedCase();

    $feed = $this->actingAs($user)
        ->getJson(route('notifications.feed'))
        ->assertOk()
        ->assertJsonStructure(['unread_count', 'items', 'page', 'last_page'])
        ->json();

    $notice = collect($feed['items'])->firstWhere('id', 'case:'.$case->id);
    expect($notice)->not->toBeNull()
        ->and($notice['url'])->toBe(route('cases.show', $case->id))
        ->and($notice['read'])->toBeFalse()
        ->and($feed['unread_count'])->toBeGreaterThanOrEqual(1);

    $this->actingAs($user)->postJson(route('notifications.read', $notice['id']))->assertOk();
    expect(DB::table('admin_notification_reads')->where(['user_id' => $user->id, 'type' => 'case', 'source_id' => $case->id])->exists())->toBeTrue();

    $after = $this->actingAs($user)->getJson(route('notifications.feed'))->assertOk()->json();
    expect(collect($after['items'])->firstWhere('id', $notice['id']))->toBeNull();
});

test('read-all marks every unseen notification and a malformed notice id is not found', function (): void {
    $user = feedAdmin();
    $case = feedCase();

    $this->actingAs($user)->postJson(route('notifications.read-all'))->assertOk();

    expect(DB::table('admin_notification_reads')->where(['user_id' => $user->id, 'type' => 'case', 'source_id' => $case->id])->exists())->toBeTrue()
        ->and($this->actingAs($user)->getJson(route('notifications.feed'))->json('unread_count'))->toBe(0);

    $this->actingAs($user)->postJson(route('notifications.read', 'bogus'))->assertNotFound();
});

test('an admin without cases.view gets an empty feed', function (): void {
    $user = User::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
    $role = Role::query()->create(['name' => 'Feed viewer '.Str::random(6)]);
    $role->permissions()->sync(Permission::query()->whereIn('name', ['reports.view'])->pluck('id'));
    $user->roles()->sync([$role->id]);
    feedCase();

    $this->actingAs($user)
        ->getJson(route('notifications.feed'))
        ->assertOk()
        ->assertJsonPath('unread_count', 0)
        ->assertJsonPath('items', []);
});
