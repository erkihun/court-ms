<?php

declare(strict_types=1);

use App\Models\CaseType;
use App\Models\CourtCase;
use App\Models\Decision;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

function decisionUiOrderRole(): Role
{
    $role = Role::query()->create(['name' => 'decision-ui-order-'.Str::uuid()]);
    $permissionIds = collect([
        'decision.view',
        'decision.view.all',
        'decision.create',
    ])->map(fn (string $name): int => Permission::query()->create([
        'name' => $name,
        'label' => Str::headline($name),
    ])->id);

    $role->permissions()->sync($permissionIds);

    return $role;
}

function decisionUiOrderUser(Role $role, string $name): User
{
    $user = User::factory()->create(['name' => $name, 'status' => 'active']);
    $user->roles()->attach($role);

    return $user;
}

function decisionUiOrderCase(): CourtCase
{
    $caseType = CaseType::query()->create(['name' => 'Judge UI order test']);

    return CourtCase::query()->create([
        'case_number' => 'JU-'.Str::upper(Str::random(10)),
        'title' => 'Judge UI order case',
        'case_type_id' => $caseType->id,
        'filing_date' => now()->toDateString(),
    ]);
}

test('create form puts the session judge in the presiding seat', function (): void {
    $role = decisionUiOrderRole();
    $creator = decisionUiOrderUser($role, 'Session Judge');

    // The form shows seat 1 (the presiding seat) first and fills it with the
    // current user; the server only accepts that user there.
    $this->actingAs($creator)
        ->get(route('decisions.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Decisions/Form')
            ->where('decision', null)
            ->where('currentUser.id', $creator->id)
            ->where('currentUser.name', 'Session Judge'));
});

test('decision view always displays the decision author first without changing stored panel order', function (): void {
    Storage::fake('public');
    $role = decisionUiOrderRole();
    $author = decisionUiOrderUser($role, 'Decision Author');
    $author->update(['signature_path' => 'signatures/author-ui-hidden.png']);
    Storage::disk('public')->put($author->signature_path, 'signature');
    $viewer = decisionUiOrderUser($role, 'Different Viewer');
    $firstStoredJudge = decisionUiOrderUser($role, 'First Stored Judge');
    $thirdJudge = decisionUiOrderUser($role, 'Third Judge');
    $courtCase = decisionUiOrderCase();
    $storedPanel = [
        ['order' => 1, 'admin_user_id' => $firstStoredJudge->id, 'admin_user_name' => $firstStoredJudge->name],
        ['order' => 2, 'admin_user_id' => $author->id, 'admin_user_name' => $author->name],
        ['order' => 3, 'admin_user_id' => $thirdJudge->id, 'admin_user_name' => $thirdJudge->name],
    ];
    $decision = Decision::query()->create([
        'court_case_id' => $courtCase->id,
        'case_number' => $courtCase->case_number,
        'decision_date' => now()->toDateString(),
        'panel_judges' => $storedPanel,
        'reviewing_admin_user_id' => $author->id,
        'reviewing_admin_user_name' => $author->name,
        'name' => 'UI order ruling',
        'decision_content' => 'Decision content.',
        'status' => 'published',
    ]);

    $response = $this->actingAs($viewer)
        ->get(route('decisions.show', $decision))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Decisions/Show')
            ->where('decision.panel.0.name', 'Decision Author')
            ->where('decision.panel.1.name', 'First Stored Judge')
            ->where('decision.panel.2.name', 'Third Judge'));

    expect($response->getContent())->not->toContain(Storage::disk('public')->url($author->signature_path))
        ->and($decision->fresh()->panel_judges)->toBe($storedPanel);
});
