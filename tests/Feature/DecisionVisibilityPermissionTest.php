<?php

declare(strict_types=1);

use App\Models\CaseType;
use App\Models\CourtCase;
use App\Models\Decision;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Str;

function decisionVisibilityUser(array $permissions, ?Team $team = null): User
{
    $role = Role::query()->create([
        'name' => 'decision-visibility-'.Str::uuid(),
    ]);

    $permissionIds = collect($permissions)
        ->map(fn (string $name): int => Permission::query()->create([
            'name' => $name,
            'label' => Str::headline($name),
        ])->id);

    $role->permissions()->sync($permissionIds);

    $user = User::factory()->create(['status' => 'active']);
    $user->roles()->attach($role);

    if ($team !== null) {
        $team->users()->attach($user);
    }

    return $user;
}

function decisionVisibilityDecision(?Team $team = null): Decision
{
    $caseType = CaseType::query()->create([
        'name' => 'Decision visibility test',
    ]);
    $courtCase = CourtCase::query()->create([
        'case_number' => 'DV-'.Str::upper(Str::random(10)),
        'title' => 'Decision visibility case',
        'case_type_id' => $caseType->id,
        'filing_date' => now()->toDateString(),
        'assigned_team_id' => $team?->id,
    ]);

    return Decision::query()->create([
        'court_case_id' => $courtCase->id,
        'case_number' => $courtCase->case_number,
        'decision_date' => now()->toDateString(),
        'name' => 'Decision visibility ruling',
        'decision_content' => 'Decision content.',
        'status' => 'draft',
    ]);
}

test('decision view remains team scoped', function (): void {
    $assignedTeam = Team::query()->create(['name' => 'Assigned team']);
    $otherTeam = Team::query()->create(['name' => 'Other team']);
    $visibleDecision = decisionVisibilityDecision($assignedTeam);
    $hiddenDecision = decisionVisibilityDecision($otherTeam);
    $user = decisionVisibilityUser(['decision.view'], $assignedTeam);

    $this->actingAs($user)
        ->get(route('decisions.index'))
        ->assertOk()
        ->assertViewHas('decisions', function ($decisions) use ($visibleDecision, $hiddenDecision): bool {
            $ids = $decisions->getCollection()->pluck('id');

            return $ids->contains($visibleDecision->id)
                && ! $ids->contains($hiddenDecision->id);
        });

    $this->actingAs($user)
        ->get(route('decisions.show', $hiddenDecision))
        ->assertForbidden();
});

test('decision view alone does not bypass team scope for a user without a team', function (): void {
    $decision = decisionVisibilityDecision();
    $user = decisionVisibilityUser(['decision.view']);

    $this->actingAs($user)
        ->get(route('decisions.index'))
        ->assertOk()
        ->assertViewHas('decisions', fn ($decisions): bool => $decisions->isEmpty());

    $this->actingAs($user)
        ->get(route('decisions.show', $decision))
        ->assertForbidden();
});

test('decision view all bypasses team scope for a permitted user without a team', function (): void {
    $decision = decisionVisibilityDecision();
    $user = decisionVisibilityUser(['decision.view', 'decision.view.all']);

    $this->actingAs($user)
        ->get(route('decisions.index'))
        ->assertOk()
        ->assertViewHas(
            'decisions',
            fn ($decisions): bool => $decisions->getCollection()->contains('id', $decision->id),
        );

    $this->actingAs($user)
        ->get(route('decisions.show', $decision))
        ->assertOk()
        ->assertViewIs('admin.decisions.show');
});
