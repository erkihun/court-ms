<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->applicant = partyApplicant('rec');
    $this->acceptedId = partyCase($this->applicant, 'REC/00001/26', [
        'review_status' => 'accepted',
        'status' => 'active',
        'description' => '<p>Facts</p><script>alert(1)</script>',
        'respondent_name' => 'Acme PLC',
    ]);
    $this->pendingId = partyCase($this->applicant, 'REC/00002/26');
});

test('the record list renders through Inertia and hides unaccepted cases from non-reviewers', function (): void {
    $this->actingAs(inertiaStaff(['cases.view']))
        ->get(route('recordes.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Records/Index')
            ->has('cases.data', 1)
            ->where('cases.data.0.caseNumber', 'REC/00001/26')
            ->where('cases.data.0.applicant', 'Hana B Party')
            ->where('cases.data.0.canAppealPdf', false)
            ->has('statuses', 5));

    $this->actingAs(inertiaStaff(['cases.view', 'cases.review']))
        ->get(route('recordes.index', ['q' => 'REC/00002']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('cases.data', 1)
            ->where('cases.data.0.reviewStatus', 'awaiting_review')
            ->where('filters.q', 'REC/00002'));
});

test('the record page shows the case file with sanitised rich text', function (): void {
    DB::table('case_hearings')->insert(['case_id' => $this->acceptedId, 'hearing_at' => '2026-02-01 10:00:00', 'location' => 'Room 1', 'created_at' => now(), 'updated_at' => now()]);

    $this->actingAs(inertiaStaff(['cases.view']))
        ->get(route('recordes.show', $this->acceptedId))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Records/Show')
            ->where('record.caseNumber', 'REC/00001/26')
            ->where('record.applicant.name', 'Hana B Party')
            ->where('record.respondent.name', 'Acme PLC')
            ->where('record.caseType', 'Type REC/00001/26')
            ->where('record.descriptionHtml', fn (string $html) => str_contains($html, 'Facts') && ! str_contains($html, 'script'))
            ->where('record.hearings.0.location', 'Room 1')
            ->where('record.appealPdfUrl', null)
            ->where('record.decision', null));
});

test('the record page and PDFs follow the case visibility rules', function (): void {
    $staff = inertiaStaff(['cases.view']);

    $this->actingAs($staff)->get(route('recordes.show', $this->pendingId))->assertForbidden();
    $this->actingAs($staff)->get(route('recordes.pdf', $this->pendingId))->assertForbidden();
    $this->actingAs($staff)->get(route('recordes.appeal-pdf', $this->pendingId))->assertForbidden();
    // The printable record stays a server-rendered page.
    $this->actingAs($staff)->get(route('recordes.pdf', $this->acceptedId))->assertOk()->assertSee('REC/00001/26');

    // Reviewers may open unaccepted cases.
    $this->actingAs(inertiaStaff(['cases.view', 'cases.review']))
        ->get(route('recordes.show', $this->pendingId))
        ->assertOk();
});

test('team members only open records of cases assigned to them', function (): void {
    $member = inertiaStaff(['cases.view']);
    $teamId = DB::table('teams')->insertGetId(['name' => 'Bench A', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('team_user')->insert(['team_id' => $teamId, 'user_id' => $member->id]);

    $this->actingAs($member)->get(route('recordes.show', $this->acceptedId))->assertForbidden();

    DB::table('court_cases')->where('id', $this->acceptedId)->update(['assigned_user_id' => $member->id]);
    $this->actingAs($member)->get(route('recordes.show', $this->acceptedId))->assertOk();
});
