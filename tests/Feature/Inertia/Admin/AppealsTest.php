<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->caseId = partyCase(partyApplicant('appeals'), 'APL/00001/26');
    $this->makeAppeal = fn (array $overrides = []): int => DB::table('appeals')->insertGetId([
        'court_case_id' => $this->caseId,
        'appeal_number' => 'APL-T-'.uniqid(),
        'title' => 'Ground of appeal',
        'grounds' => "Line one\nLine two",
        'status' => 'draft',
        'created_at' => now(),
        'updated_at' => now(),
        ...$overrides,
    ]);
});

test('the appeals list renders through Inertia and filters by status', function (): void {
    ($this->makeAppeal)(['title' => 'Drafted']);
    ($this->makeAppeal)(['title' => 'Sent', 'status' => 'submitted']);

    $this->actingAs(inertiaStaff(['appeals.view']))
        ->get(route('appeals.index', ['status' => 'submitted']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Appeals/Index')
            ->where('filters.status', 'submitted')
            ->has('appeals.data', 1)
            ->where('appeals.data.0.title', 'Sent')
            ->where('appeals.data.0.caseNumber', 'APL/00001/26'));
});

test('appeal pages require their permissions', function (): void {
    $appealId = ($this->makeAppeal)();
    $user = inertiaStaff(['appeals.view']);

    // The perm: middleware sends unauthorised staff back to the dashboard.
    $this->actingAs($user)->get(route('appeals.create'))->assertRedirect(route('dashboard'));
    $this->actingAs($user)->get(route('appeals.edit', $appealId))->assertRedirect(route('dashboard'));
    $this->actingAs($user)->post(route('appeals.decide', $appealId), ['decision' => 'approved'])->assertRedirect(route('dashboard'));
    $this->actingAs(inertiaStaff([]))->get(route('appeals.index'))->assertRedirect(route('dashboard'));

    expect(DB::table('appeals')->find($appealId)->status)->toBe('draft');
});

test('an appeal can be created, edited and submitted', function (): void {
    $user = inertiaStaff(['appeals.view', 'appeals.create', 'appeals.edit']);

    $this->actingAs($user)
        ->get(route('appeals.create'))
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Appeals/Form')->where('appeal', null)->has('cases', 1));

    $this->actingAs($user)
        ->post(route('appeals.store'), ['court_case_id' => $this->caseId, 'title' => 'New appeal', 'grounds' => 'Because'])
        ->assertRedirect();

    $appeal = DB::table('appeals')->where('title', 'New appeal')->first();
    expect($appeal->status)->toBe('draft')
        ->and($appeal->appeal_number)->toBe('APL-'.now()->format('Y').'-'.str_pad((string) $appeal->id, 4, '0', STR_PAD_LEFT));

    $this->actingAs($user)
        ->get(route('appeals.edit', $appeal->id))
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Appeals/Form')->where('appeal.title', 'New appeal'));

    $this->actingAs($user)
        ->patch(route('appeals.update', $appeal->id), ['title' => 'Renamed', 'grounds' => 'More'])
        ->assertRedirect(route('appeals.show', $appeal->id));

    $this->actingAs($user)->post(route('appeals.submit', $appeal->id))->assertRedirect();

    expect(DB::table('appeals')->find($appeal->id))
        ->title->toBe('Renamed')
        ->status->toBe('submitted');
});

test('a decided appeal can no longer be edited', function (): void {
    $appealId = ($this->makeAppeal)(['status' => 'approved']);
    $user = inertiaStaff(['appeals.view', 'appeals.edit']);

    $this->actingAs($user)->get(route('appeals.edit', $appealId))->assertRedirect(route('appeals.show', $appealId));
    $this->actingAs($user)
        ->patch(route('appeals.update', $appealId), ['title' => 'Changed after decision'])
        ->assertRedirect(route('appeals.show', $appealId))
        ->assertSessionHas('error', 'messages.error.cannot_edit_stage');

    expect(DB::table('appeals')->find($appealId)->title)->toBe('Ground of appeal');
});

test('the appeal page shows details and records a decision', function (): void {
    $appealId = ($this->makeAppeal)(['status' => 'submitted']);
    $user = inertiaStaff(['appeals.view', 'appeals.decide']);

    $this->actingAs($user)
        ->get(route('appeals.show', $appealId))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Appeals/Show')
            ->where('appeal.id', $appealId)
            ->where('appeal.grounds', "Line one\nLine two")
            ->where('appeal.decidable', true)
            ->where('appeal.editable', true)
            ->has('documents', 0));

    $this->actingAs($user)
        ->post(route('appeals.decide', $appealId), ['decision' => 'rejected', 'decision_notes' => 'No merit'])
        ->assertRedirect();

    expect(DB::table('appeals')->find($appealId))
        ->status->toBe('rejected')
        ->decided_by_user_id->toBe($user->id)
        ->decision_notes->toBe('No merit');
});
