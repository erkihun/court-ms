<?php

declare(strict_types=1);

use App\Models\CaseType;
use App\Models\CourtCase;
use App\Models\Decision;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;

function panelIntegrityUser(): User
{
    $role = Role::query()->create(['name' => 'panel-integrity-'.Str::uuid()]);
    $permissionIds = collect([
        'decision.view',
        'decision.view.all',
        'decision.create',
        'decision.update',
        'decision.approve',
    ])->map(fn (string $name): int => Permission::query()->firstOrCreate(
        ['name' => $name],
        ['label' => Str::headline($name)],
    )->id);
    $role->permissions()->sync($permissionIds);

    $user = User::factory()->create(['status' => 'active']);
    $user->roles()->attach($role);

    return $user;
}

function panelIntegrityClosedCase(): CourtCase
{
    $caseType = CaseType::query()->create(['name' => 'Panel integrity '.Str::random(6)]);

    return CourtCase::query()->create([
        'case_number' => 'PI-'.Str::upper(Str::random(10)),
        'title' => 'Panel integrity case',
        'case_type_id' => $caseType->id,
        'filing_date' => now()->toDateString(),
        'status' => 'closed',
    ]);
}

/** @return array<int, array{order: int, admin_user_id: ?int, admin_user_name: ?string, vote: null}> */
function panelOf(?User $first, ?User $presiding, ?User $third): array
{
    return collect([$first, $presiding, $third])
        ->map(fn (?User $judge, int $index): array => [
            'order' => $index + 1,
            'admin_user_id' => $judge?->id,
            'admin_user_name' => $judge?->name,
            'vote' => null,
        ])
        ->all();
}

function panelIntegrityDecision(CourtCase $case, array $panel, string $status = 'draft'): Decision
{
    return Decision::query()->create([
        'court_case_id' => $case->id,
        'case_number' => $case->case_number,
        'name' => 'Panel integrity decision',
        'decision_content' => '<p>Judgment.</p>',
        'decision_date' => now()->toDateString(),
        'panel_judges' => $panel,
        'status' => $status,
    ]);
}

/** @return array<string, mixed> */
function decisionPayload(CourtCase $case, array $judgeIds): array
{
    return [
        'case_id' => $case->id,
        'decision_date' => now()->toDateString(),
        'decision_content' => '<p>Judgment for the applicant.</p>',
        'judges' => array_map(fn (?int $id): array => ['admin_user_id' => $id], $judgeIds),
    ];
}

test('a decision cannot be created without all three judges', function (): void {
    $presiding = panelIntegrityUser();
    $wing = User::factory()->create();
    $case = panelIntegrityClosedCase();

    $this->actingAs($presiding)
        ->post(route('decisions.store'), decisionPayload($case, [$wing->id, $presiding->id, null]))
        ->assertSessionHasErrors('judges.2.admin_user_id');

    $payload = decisionPayload($case, []);
    unset($payload['judges']);
    $this->actingAs($presiding)
        ->post(route('decisions.store'), $payload)
        ->assertSessionHasErrors('judges');

    expect(Decision::query()->count())->toBe(0);
});

test('the same judge cannot fill two seats on a panel', function (): void {
    $presiding = panelIntegrityUser();
    $wing = User::factory()->create();
    $case = panelIntegrityClosedCase();

    $this->actingAs($presiding)
        ->post(route('decisions.store'), decisionPayload($case, [$wing->id, $presiding->id, $wing->id]))
        ->assertSessionHasErrors('judges.0.admin_user_id');

    expect(Decision::query()->count())->toBe(0);
});

test('the presiding seat must be held by the judge submitting the decision', function (): void {
    $submitter = panelIntegrityUser();
    [$a, $b, $c] = User::factory()->count(3)->create()->all();
    $case = panelIntegrityClosedCase();

    $this->actingAs($submitter)
        ->post(route('decisions.store'), decisionPayload($case, [$a->id, $b->id, $c->id]))
        ->assertSessionHasErrors('judges.1.admin_user_id');

    expect(Decision::query()->count())->toBe(0);
});

test('a complete panel with the submitter presiding is accepted', function (): void {
    $presiding = panelIntegrityUser();
    [$first, $third] = User::factory()->count(2)->create()->all();
    $case = panelIntegrityClosedCase();

    $this->actingAs($presiding)
        ->post(route('decisions.store'), decisionPayload($case, [$first->id, $presiding->id, $third->id]))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('decisions.index'));

    $decision = Decision::query()->sole();
    expect($decision->hasCompletePanel())->toBeTrue()
        ->and($decision->presidingJudgeId())->toBe($presiding->id);
});

test('a decision with an incomplete panel cannot be published', function (): void {
    $presiding = panelIntegrityUser();
    $decision = panelIntegrityDecision(panelIntegrityClosedCase(), panelOf(null, $presiding, null));

    $this->actingAs($presiding)
        ->patch(route('decisions.status', $decision), ['status' => 'published'])
        ->assertSessionHasErrors('status');

    expect($decision->fresh()->status)->toBe('draft');
});

test('a decision with a complete panel can be published', function (): void {
    $presiding = panelIntegrityUser();
    [$first, $third] = User::factory()->count(2)->create()->all();
    $decision = panelIntegrityDecision(panelIntegrityClosedCase(), panelOf($first, $presiding, $third));

    $this->actingAs($presiding)
        ->patch(route('decisions.status', $decision), ['status' => 'published'])
        ->assertSessionHasNoErrors();

    expect($decision->fresh()->status)->toBe('published');
});

test('a published decision without a complete panel cannot be sealed by approval', function (): void {
    $approver = panelIntegrityUser();
    $decision = panelIntegrityDecision(panelIntegrityClosedCase(), panelOf(null, null, null), 'published');

    $this->actingAs($approver)
        ->patch(route('decisions.approve', $decision))
        ->assertSessionHasErrors('approve');

    expect($decision->fresh()->approved_at)->toBeNull()
        ->and($decision->fresh()->isDownloadableByParties())->toBeFalse();
});

test('a legacy draft with no presiding judge can be repaired by an authorised editor', function (): void {
    $editor = panelIntegrityUser();
    [$first, $third] = User::factory()->count(2)->create()->all();
    $case = panelIntegrityClosedCase();
    $decision = panelIntegrityDecision($case, panelOf(null, null, null));

    $this->actingAs($editor)
        ->get(route('decisions.edit', $decision))
        ->assertOk();

    $this->actingAs($editor)
        ->patch(route('decisions.update', $decision), decisionPayload($case, [$first->id, $editor->id, $third->id]))
        ->assertSessionHasNoErrors();

    $decision->refresh();
    expect($decision->hasCompletePanel())->toBeTrue()
        ->and($decision->presidingJudgeId())->toBe($editor->id);
});

test('only the presiding judge may edit a decision that has one', function (): void {
    $presiding = panelIntegrityUser();
    $otherEditor = panelIntegrityUser();
    [$first, $third] = User::factory()->count(2)->create()->all();
    $decision = panelIntegrityDecision(panelIntegrityClosedCase(), panelOf($first, $presiding, $third));

    $this->actingAs($otherEditor)
        ->get(route('decisions.edit', $decision))
        ->assertForbidden();

    $this->actingAs($presiding)
        ->get(route('decisions.edit', $decision))
        ->assertOk();
});
