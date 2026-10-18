<?php

declare(strict_types=1);

use App\Models\CaseType;
use App\Models\CourtCase;
use App\Models\Decision;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

function dashboardResolvedCase(string $status, User $assignee): CourtCase
{
    $caseType = CaseType::query()->create([
        'name' => 'Dashboard resolved '.Str::uuid(),
    ]);

    return CourtCase::query()->create([
        'case_number' => 'DR-'.Str::upper(Str::random(10)),
        'title' => 'Dashboard resolved case',
        'case_type_id' => $caseType->id,
        'filing_date' => now()->toDateString(),
        'status' => $status,
        'assigned_member_user_id' => $assignee->id,
    ]);
}

function dashboardDecision(CourtCase $courtCase, string $status): Decision
{
    return Decision::query()->create([
        'court_case_id' => $courtCase->id,
        'case_number' => $courtCase->case_number,
        'decision_date' => now()->toDateString(),
        'name' => 'Dashboard decision',
        'decision_content' => 'Decision content.',
        'status' => $status,
    ]);
}

test('published decisions define resolved cases across the admin dashboard', function (): void {
    Cache::flush();

    $user = inertiaStaff(['cases.view']);
    $team = Team::query()->create(['name' => 'Dashboard team']);
    $team->users()->attach($user);

    $activeWithPublishedDecision = dashboardResolvedCase('active', $user);
    dashboardDecision($activeWithPublishedDecision, 'published');
    dashboardDecision($activeWithPublishedDecision, 'published');

    $pendingWithPublishedDecision = dashboardResolvedCase('pending', $user);
    dashboardDecision($pendingWithPublishedDecision, 'published');

    $closedWithDraftDecision = dashboardResolvedCase('closed', $user);
    dashboardDecision($closedWithDraftDecision, 'draft');

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->where('cases.kpis.resolved', 2)
            ->where('cases.members', fn ($members): bool => collect($members)->firstWhere('id', $user->id)['resolved'] === 2));

    $this->actingAs($user)
        ->getJson(route('admin.dashboard.stats', [
            'start' => now()->subDay()->toDateString(),
            'end' => now()->addDay()->toDateString(),
        ]))
        ->assertOk()
        ->assertJsonPath('kpis.resolvedCases', 2)
        ->assertJsonPath('memberCaseCounts.0.resolved_cases_count', 2);
});

test('case statistics and system panels follow permissions', function (): void {
    Cache::flush();

    $this->actingAs(inertiaStaff([]))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Dashboard')->where('cases', null)->where('system', null));

    $this->actingAs(inertiaStaff([]))
        ->getJson(route('admin.dashboard.stats'))
        ->assertForbidden();

    $this->actingAs(inertiaStaff(['cases.view', 'settings.manage']))
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('cases.kpis')
            ->has('system.health')
            ->has('system.storage'));
});
