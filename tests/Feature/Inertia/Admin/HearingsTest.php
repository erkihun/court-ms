<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

test('the hearing list renders through Inertia and filters by case number and date', function (): void {
    $caseId = partyCase(partyApplicant('hear'), 'HEAR/00001/26');
    $otherId = partyCase(partyApplicant('hear2'), 'OTHR/00002/26');
    DB::table('case_hearings')->insert([
        ['case_id' => $caseId, 'hearing_at' => '2026-03-10 09:30:00', 'type' => 'Preliminary', 'location' => 'Room 2', 'notes' => 'Bring files', 'created_at' => now(), 'updated_at' => now()],
        ['case_id' => $otherId, 'hearing_at' => '2026-03-11 09:30:00', 'type' => null, 'location' => null, 'notes' => null, 'created_at' => now(), 'updated_at' => now()],
    ]);
    $user = inertiaStaff(['cases.view']);

    $this->actingAs($user)->get(route('admin.hearings.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Hearings/Index')
            ->has('hearings.data', 2)
            ->where('filters.case_number', ''));

    $this->actingAs($user)->get(route('admin.hearings.index', ['case_number' => 'HEAR', 'hearing_date' => '2026-03-10']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('hearings.data', 1)
            ->where('hearings.data.0.caseNumber', 'HEAR/00001/26')
            ->where('hearings.data.0.location', 'Room 2')
            ->where('filters.hearing_date', '2026-03-10'));

    // A malformed date is ignored rather than passed to the query.
    $this->actingAs($user)->get(route('admin.hearings.index', ['hearing_date' => 'not-a-date']))
        ->assertInertia(fn (Assert $page) => $page->has('hearings.data', 2)->where('filters.hearing_date', ''));
});

test('the hearing list needs cases.view', function (): void {
    $this->actingAs(inertiaStaff(['reports.view']))
        ->get(route('admin.hearings.index'))
        ->assertRedirect(route('dashboard'));
});
