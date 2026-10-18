<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

test('the case list shows only the applicant\'s own cases', function (): void {
    $owner = partyApplicant('cases-owner');
    partyCase($owner, 'AC-OWN-1');
    partyCase(partyApplicant('cases-other'), 'AC-OTHER-1');

    $this->actingAs($owner, 'applicant')
        ->get(route('applicant.cases.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Applicant/Cases/Index')
            ->has('cases.data', 1)
            ->where('cases.data.0.caseNumber', 'AC-OWN-1')
            ->where('cases.data.0.canDelete', true));
});

test('a case page sends sanitised rich text and keeps other applicants out', function (): void {
    $owner = partyApplicant('show-owner');
    $caseId = partyCase($owner, 'AC-SHOW-1', [
        'description' => '<p>Facts</p><script>alert(1)</script><a href="javascript:alert(2)">x</a>',
        'relief_requested' => '<p>Relief</p><img src=x onerror=alert(3)>',
    ]);

    $this->actingAs($owner, 'applicant')
        ->get(route('applicant.cases.show', $caseId))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Applicant/Cases/Show')
            ->where('case.caseNumber', 'AC-SHOW-1')
            ->where('case.canEdit', true)
            ->where('case.descriptionHtml', fn (string $html) => str_contains($html, 'Facts') && ! str_contains($html, '<script') && ! str_contains($html, 'javascript:'))
            ->where('case.reliefHtml', fn (string $html) => ! str_contains($html, 'onerror')));

    $this->actingAs(partyApplicant('show-intruder'), 'applicant')
        ->get(route('applicant.cases.show', $caseId))
        ->assertNotFound();
});

test('the filing form lists case types and the active terms', function (): void {
    $this->actingAs(partyApplicant('create'), 'applicant')
        ->get(route('applicant.cases.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Applicant/Cases/Create')
            ->has('types')
            ->where('applicant.isLawyer', false)
            ->where('today', now()->toDateString()));
});

test('accepted cases cannot be edited or have evidence and witnesses removed', function (): void {
    $owner = partyApplicant('accepted');
    $caseId = partyCase($owner, 'AC-ACC-1', ['review_status' => 'accepted']);
    $evidenceId = DB::table('case_evidences')->insertGetId(['case_id' => $caseId, 'type' => 'document', 'title' => 'Doc', 'created_at' => now(), 'updated_at' => now()]);
    $witnessId = DB::table('case_witnesses')->insertGetId(['case_id' => $caseId, 'full_name' => 'Witness', 'created_at' => now(), 'updated_at' => now()]);

    $this->actingAs($owner, 'applicant')
        ->get(route('applicant.cases.edit', $caseId))
        ->assertRedirect(route('applicant.cases.show', $caseId));

    $this->delete(route('applicant.cases.evidences.delete', ['id' => $caseId, 'evidenceId' => $evidenceId]))->assertRedirect();
    $this->delete(route('applicant.cases.witnesses.delete', ['id' => $caseId, 'witnessId' => $witnessId]))->assertRedirect();

    $this->assertDatabaseHas('case_evidences', ['id' => $evidenceId]);
    $this->assertDatabaseHas('case_witnesses', ['id' => $witnessId]);
    $this->delete(route('applicant.cases.destroy', $caseId))->assertForbidden();
});

test('a returned case opens in the edit form with its documents and witnesses', function (): void {
    $owner = partyApplicant('returned');
    $caseId = partyCase($owner, 'AC-RET-1', ['review_status' => 'returned', 'description' => '<p>Draft</p>']);
    DB::table('case_witnesses')->insert(['case_id' => $caseId, 'full_name' => 'Abebe', 'phone' => '0911', 'created_at' => now(), 'updated_at' => now()]);

    $this->actingAs($owner, 'applicant')
        ->get(route('applicant.cases.edit', $caseId))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Applicant/Cases/Edit')
            ->where('case.description', '<p>Draft</p>')
            ->where('case.filingDate', now()->toDateString())
            ->where('witnesses.0.fullName', 'Abebe'));
});

test('applicants can message the court on their own case', function (): void {
    $owner = partyApplicant('message');
    $caseId = partyCase($owner, 'AC-MSG-1');

    $this->actingAs($owner, 'applicant')
        ->from(route('applicant.cases.show', $caseId))
        ->post(route('applicant.cases.messages.post', $caseId), ['body' => 'When is the hearing?'])
        ->assertRedirect(route('applicant.cases.show', $caseId));

    $this->get(route('applicant.cases.show', $caseId))
        ->assertInertia(fn (Assert $page) => $page->where('messages.0.body', 'When is the hearing?')->where('messages.0.mine', true));
});
