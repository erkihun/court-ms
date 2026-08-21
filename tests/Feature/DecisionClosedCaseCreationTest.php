<?php

declare(strict_types=1);

use App\Models\CaseType;
use App\Models\CourtCase;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;

function closedCaseDecisionCreator(): User
{
    $permission = Permission::query()->create([
        'name' => 'decision.create',
        'label' => 'Create decisions',
    ]);
    $role = Role::query()->create([
        'name' => 'closed-case-decision-creator-'.Str::uuid(),
    ]);
    $role->permissions()->attach($permission);

    $user = User::factory()->create(['status' => 'active']);
    $user->roles()->attach($role);

    return $user;
}

function caseForDecisionCreation(CaseType $caseType, string $status): CourtCase
{
    return CourtCase::query()->create([
        'case_number' => 'DC-'.Str::upper(Str::random(10)),
        'title' => Str::headline($status).' decision case',
        'case_type_id' => $caseType->id,
        'filing_date' => now()->toDateString(),
        'status' => $status,
    ]);
}

test('a decision can only be created for a closed case', function (): void {
    $creator = closedCaseDecisionCreator();
    $caseType = CaseType::query()->create(['name' => 'Decision creation test']);
    $closedCase = caseForDecisionCreation($caseType, 'closed');
    $activeCase = caseForDecisionCreation($caseType, 'active');

    $this->actingAs($creator)
        ->get(route('decisions.create'))
        ->assertOk()
        ->assertSee($closedCase->case_number)
        ->assertDontSee($activeCase->case_number);

    $this->actingAs($creator)
        ->post(route('decisions.store'), [
            'case_id' => $activeCase->id,
            'decision_date' => now()->toDateString(),
            'decision_content' => 'Active cases must be rejected.',
        ])
        ->assertSessionHasErrors('case_id');

    $this->assertDatabaseMissing('decisions', [
        'court_case_id' => $activeCase->id,
    ]);

    $this->actingAs($creator)
        ->post(route('decisions.store'), [
            'case_id' => $closedCase->id,
            'decision_date' => now()->toDateString(),
            'decision_content' => 'Closed cases can receive decisions.',
        ])
        ->assertRedirect(route('decisions.index'));

    $this->assertDatabaseHas('decisions', [
        'court_case_id' => $closedCase->id,
        'status' => 'draft',
    ]);
});
