<?php

declare(strict_types=1);

use App\Models\CaseType;
use App\Models\CourtCase;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;

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

    // The page is React now: with the case closed and the viewer allowed to edit statuses,
    // Court/Cases/Show shows the lock notice (cases.status.closed_locked_title / closed_locked)
    // in place of the status form, and the response never carries the status route.
    $this->actingAs($user)
        ->get(route('cases.show', $case->id))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Court/Cases/Show')
            ->where('case.status', 'closed')
            ->where('can.editStatus', true)
            ->etc())
        ->assertDontSee(route('cases.status.update', $case->id), false);
});
