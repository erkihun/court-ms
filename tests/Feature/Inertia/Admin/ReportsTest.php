<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

test('the system report renders through Inertia with filtered counts', function (): void {
    $applicant = partyApplicant('rep');
    partyCase($applicant, 'REP/00001/26', ['status' => 'active']);
    partyCase($applicant, 'REP/00002/26', ['status' => 'pending']);
    $user = inertiaStaff(['reports.view']);

    $this->actingAs($user)->get(route('reports.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Reports/Index')
            ->where('filtered.count', 2)
            ->has('summaryCards', 14)
            ->where('summaryCards.0.key', 'cases_total')
            ->where('summaryCards.0.value', 2)
            ->has('breakdowns.cases_status', 2)
            ->has('recentCases', 2)
            ->has('caseStatuses', 5));

    // Filtering by status used to fail: the per-judge query joins users, which also has a status column.
    $this->actingAs($user)->get(route('reports.index', ['status' => 'active', 'start_date' => 'bogus']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('filtered.count', 1)
            ->where('filtered.byStatus.0.label', __('cases.status.active'))
            ->where('filters.status', 'active')
            ->where('filters.start_date', ''));
});

test('the system report needs reports.view', function (): void {
    $this->actingAs(inertiaStaff(['cases.view']))
        ->get(route('reports.index'))
        ->assertRedirect(route('dashboard'));
});
