<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('teams list, detail and edit pages render', function (): void {
    $staff = inertiaStaff(['teams.manage']);
    $leader = User::factory()->create(['name' => 'Team Lead', 'status' => 'active']);
    $parent = Team::query()->create(['name' => 'Bench A']);
    $team = Team::query()->create(['name' => 'Registry', 'parent_id' => $parent->id, 'team_leader_id' => $leader->id]);
    $team->users()->attach($leader);

    $this->actingAs($staff)
        ->get(route('teams.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Teams/Index')
            ->where('teams', fn ($teams) => collect($teams)->firstWhere('name', 'Registry')['membersCount'] === 1
                && collect($teams)->firstWhere('name', 'Registry')['parentName'] === 'Bench A'));

    $this->actingAs($staff)
        ->get(route('teams.show', $team))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Teams/Show')
            ->where('team.leaderName', 'Team Lead')
            ->where('team.members.0.name', 'Team Lead'));

    // The edit page never offers the team itself, or one of its sub-teams, as a parent.
    $this->actingAs($staff)
        ->get(route('teams.edit', $parent))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Teams/Form')
            ->where('parents', []));
});

test('a team cannot be moved under itself or its sub-team', function (): void {
    $staff = inertiaStaff(['teams.manage']);
    $parent = Team::query()->create(['name' => 'Parent']);
    $child = Team::query()->create(['name' => 'Child', 'parent_id' => $parent->id]);

    $this->actingAs($staff)
        ->patch(route('teams.update', $parent), ['name' => 'Parent', 'parent_id' => $child->id])
        ->assertSessionHasErrors('parent_id');
    $this->actingAs($staff)
        ->patch(route('teams.update', $parent), ['name' => 'Parent', 'parent_id' => $parent->id])
        ->assertSessionHasErrors('parent_id');

    expect($parent->fresh()->parent_id)->toBeNull();
});

test('team membership is saved and the leader always stays a member', function (): void {
    $staff = inertiaStaff(['teams.manage']);
    $leader = User::factory()->create(['status' => 'active']);
    $member = User::factory()->create(['status' => 'active']);
    $team = Team::query()->create(['name' => 'Registry', 'team_leader_id' => $leader->id]);

    $this->actingAs($staff)
        ->patch(route('teams.users.update', $team), ['users' => [$member->id]])
        ->assertRedirect(route('teams.edit', $team));

    expect($team->users()->pluck('users.id')->sort()->values()->all())->toBe(collect([$leader->id, $member->id])->sort()->values()->all());
});

test('applicants are listed and can be deactivated by staff who manage them', function (): void {
    $applicant = partyApplicant('admin-list');

    $this->actingAs(inertiaStaff(['applicants.view']))
        ->get(route('applicants.index', ['q' => 'party-admin-list']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Applicants/Index')
            ->where('canManage', false)
            ->where('applicants.total', 1)
            ->where('applicants.data.0.email', 'party-admin-list@example.test')
            ->where('applicants.data.0.active', true)
            ->missing('applicants.data.0.password'));

    $this->actingAs(inertiaStaff(['applicants.view']))
        ->patch(route('applicants.status.update', $applicant), ['is_active' => false])
        ->assertRedirect(route('dashboard'));
    expect($applicant->fresh()->is_active)->toBeTrue();

    $this->actingAs(inertiaStaff(['applicants.view', 'applicants.manage']))
        ->patch(route('applicants.status.update', $applicant), ['is_active' => false])
        ->assertRedirect();
    expect($applicant->fresh()->is_active)->toBeFalse();
});
