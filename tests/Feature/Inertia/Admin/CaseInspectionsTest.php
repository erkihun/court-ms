<?php

declare(strict_types=1);

use App\Models\CaseInspectionFinding;
use App\Models\CaseInspectionRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->caseId = partyCase(partyApplicant('insp'), 'INSP/00001/26');
    $this->makeRequest = fn (array $overrides = []): CaseInspectionRequest => CaseInspectionRequest::create([
        'court_case_id' => $this->caseId,
        'request_date' => now()->toDateString(),
        'subject' => 'Inspect the site',
        'status' => 'pending',
        ...$overrides,
    ]);
});

test('request pages render through Inertia', function (): void {
    $user = inertiaStaff(['inspection-requests.manage']);
    $record = ($this->makeRequest)();

    $this->actingAs($user)->get(route('case-inspection-requests.index', ['status' => 'pending']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/CaseInspections/Requests/Index')
            ->where('requests.data.0.id', $record->id)
            ->where('requests.data.0.caseNumber', 'INSP/00001/26')
            ->where('filters.status', 'pending')
            ->has('cases', 1));

    $this->actingAs($user)->get(route('case-inspection-requests.create', ['case_id' => $this->caseId]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/CaseInspections/Requests/Form')
            ->where('record', null)
            ->where('defaults.court_case_id', (string) $this->caseId)
            ->where('canAssign', false)
            ->where('inspectors', []));

    $this->actingAs($user)->get(route('case-inspection-requests.show', $record))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/CaseInspections/Requests/Show')
            ->where('request.subject', 'Inspect the site')
            ->has('request.findings', 0));
});

test('request pages need the inspection-requests.manage permission', function (): void {
    $this->actingAs(inertiaStaff(['cases.view']))
        ->get(route('case-inspection-requests.index'))
        ->assertRedirect(route('dashboard'));
});

test('a request can be created, updated and deleted', function (): void {
    $user = inertiaStaff(['inspection-requests.manage']);

    $this->actingAs($user)->post(route('case-inspection-requests.store'), [
        'court_case_id' => $this->caseId,
        'request_date' => now()->toDateString(),
        'subject' => 'Visit',
        'status' => 'pending',
    ])->assertRedirect(route('case-inspection-requests.index'));

    $record = CaseInspectionRequest::firstOrFail();
    // Without assign.inspections the requester becomes the inspector.
    expect($record->assigned_inspector_user_id)->toBe($user->id);

    $this->actingAs($user)->patch(route('case-inspection-requests.update', $record), [
        'court_case_id' => $this->caseId,
        'request_date' => now()->toDateString(),
        'subject' => 'Visit again',
        'status' => 'in_progress',
    ])->assertRedirect(route('case-inspection-requests.index'));
    expect($record->fresh()->subject)->toBe('Visit again');

    $this->actingAs($user)->delete(route('case-inspection-requests.destroy', $record))
        ->assertRedirect(route('case-inspection-requests.index'));
    expect(CaseInspectionRequest::count())->toBe(0);
});

test('completed requests cannot be edited', function (): void {
    $record = ($this->makeRequest)(['status' => 'completed']);

    $this->actingAs(inertiaStaff(['inspection-requests.manage']))
        ->get(route('case-inspection-requests.edit', $record))
        ->assertRedirect(route('case-inspection-requests.show', $record));
});

test('inspectors only see findings for requests assigned to them', function (): void {
    $inspector = inertiaStaff(['inspection-findings.manage']);
    $other = inertiaStaff(['inspection-findings.manage']);
    $mine = ($this->makeRequest)(['assigned_inspector_user_id' => $inspector->id]);
    $theirs = ($this->makeRequest)(['assigned_inspector_user_id' => $other->id]);
    CaseInspectionFinding::create(['case_inspection_request_id' => $mine->id, 'finding_date' => now(), 'title' => 'Mine', 'details' => 'x', 'severity' => 'low']);
    $foreign = CaseInspectionFinding::create(['case_inspection_request_id' => $theirs->id, 'finding_date' => now(), 'title' => 'Theirs', 'details' => 'x', 'severity' => 'low']);

    $this->actingAs($inspector)->get(route('case-inspection-findings.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/CaseInspections/Findings/Index')
            ->has('findings.data', 1)
            ->where('findings.data.0.title', 'Mine')
            ->has('requests', 1));

    $this->actingAs($inspector)->get(route('case-inspection-findings.show', $foreign))->assertForbidden();
});

test('an inspector records a finding with a PDF and the request completes', function (): void {
    Storage::fake('local');
    $inspector = inertiaStaff(['inspection-findings.manage']);
    $record = ($this->makeRequest)(['assigned_inspector_user_id' => $inspector->id]);

    $this->actingAs($inspector)->get(route('case-inspection-findings.create', ['request_id' => $record->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/CaseInspections/Findings/Form')
            ->where('defaults.case_inspection_request_id', (string) $record->id)
            ->has('severities', 4));

    $this->actingAs($inspector)->post(route('case-inspection-findings.store'), [
        'case_inspection_request_id' => $record->id,
        'finding_date' => now()->toDateString(),
        'title' => 'Broken seal',
        'details' => 'Seal was broken.',
        'severity' => 'high',
        'attachment_pdf' => UploadedFile::fake()->create('report.pdf', 20, 'application/pdf'),
    ])->assertRedirect(route('case-inspection-findings.index'));

    $finding = CaseInspectionFinding::firstOrFail();
    expect($finding->attachment_original_name)->toBe('report.pdf')
        ->and($record->fresh()->status)->toBe('completed');

    $this->actingAs($inspector)->get(route('case-inspection-findings.show', $finding))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/CaseInspections/Findings/Show')
            ->where('finding.details', 'Seal was broken.')
            ->where('finding.accepted', false)
            ->where('canAccept', false));
});

test('administrators accept findings, which then lock', function (): void {
    $admin = inertiaStaff(['inspection-findings.manage'], 'admin');
    $finding = CaseInspectionFinding::create(['case_inspection_request_id' => ($this->makeRequest)()->id, 'finding_date' => now(), 'title' => 'T', 'details' => 'x', 'severity' => 'low']);

    $this->actingAs($admin)->get(route('case-inspection-findings.show', $finding))
        ->assertInertia(fn (Assert $page) => $page->where('canAccept', true));

    $this->actingAs($admin)->post(route('case-inspection-findings.accept', $finding))->assertRedirect();
    expect($finding->fresh()->accepted_at)->not->toBeNull();

    $this->actingAs($admin)->get(route('case-inspection-findings.edit', $finding))
        ->assertRedirect(route('case-inspection-findings.show', $finding));
});
