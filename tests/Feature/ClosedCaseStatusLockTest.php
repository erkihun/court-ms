<?php

declare(strict_types=1);

use App\Models\CaseType;
use App\Models\CourtCase;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;

function closedCaseStatusUser(): User
{
    $role = Role::query()->create([
        'name' => 'closed-case-status-'.Str::uuid(),
    ]);
    $permissions = collect(['cases.view', 'cases.edit'])
        ->map(fn (string $name): int => Permission::query()->create([
            'name' => $name,
            'label' => Str::headline($name),
        ])->id);
    $role->permissions()->sync($permissions);

    $user = User::factory()->create(['status' => 'active']);
    $user->roles()->attach($role);

    return $user;
}

function closedCaseStatusRecord(): CourtCase
{
    $caseType = CaseType::query()->create([
        'name' => 'Closed status lock test',
    ]);

    return CourtCase::query()->create([
        'case_number' => 'CL-'.Str::upper(Str::random(10)),
        'title' => 'Terminal closed case',
        'case_type_id' => $caseType->id,
        'filing_date' => now()->toDateString(),
        'status' => 'closed',
    ]);
}

test('a closed case status cannot be changed by a permitted admin', function (): void {
    $user = closedCaseStatusUser();
    $case = closedCaseStatusRecord();

    $this->actingAs($user)
        ->from(route('cases.show', $case->id))
        ->patch(route('cases.status.update', $case->id), [
            'status' => 'active',
            'note' => 'Attempt to reopen the case.',
        ])
        ->assertRedirect(route('cases.show', $case->id))
        ->assertSessionHasErrors([
            'status' => __('cases.status.closed_locked'),
        ]);

    $this->assertDatabaseHas('court_cases', [
        'id' => $case->id,
        'status' => 'closed',
    ]);
    $this->assertDatabaseMissing('case_status_logs', [
        'case_id' => $case->id,
        'to_status' => 'active',
    ]);

    $this->actingAs($user)
        ->get(route('cases.show', $case->id))
        ->assertOk()
        ->assertSeeText(__('cases.status.closed_locked_title'))
        ->assertSeeText(__('cases.status.closed_locked'))
        ->assertDontSee(route('cases.status.update', $case->id), false);
});
