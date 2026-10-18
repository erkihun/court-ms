<?php

declare(strict_types=1);

use App\Models\Decision;
use App\Models\DecisionReview;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->caseId = partyCase(partyApplicant('decisions'), 'DEC/00001/26', [
        'status' => 'closed',
        'title' => 'Applicant Name',
        'respondent_name' => 'Respondent Org',
    ]);
    $this->judge = inertiaStaff(['decision.view', 'decision.view.all', 'decision.create', 'decision.update', 'decision.delete', 'decision.approve']);
    $this->judge->update(['name' => 'Presiding Judge']);
    $this->second = User::factory()->create(['name' => 'Second Judge']);
    $this->third = User::factory()->create(['name' => 'Third Judge']);
    $this->makeDecision = fn (array $overrides = []): Decision => Decision::query()->create([
        'court_case_id' => $this->caseId,
        'case_number' => 'DEC/00001/26',
        'decision_date' => '2026-03-01',
        'name' => 'Final ruling',
        'decision_content' => '<p>Ruling</p><script>alert(1)</script>',
        'status' => 'draft',
        'panel_judges' => [
            ['order' => 1, 'admin_user_id' => $this->second->id, 'admin_user_name' => 'Second Judge'],
            ['order' => 2, 'admin_user_id' => $this->judge->id, 'admin_user_name' => 'Presiding Judge'],
            ['order' => 3, 'admin_user_id' => $this->third->id, 'admin_user_name' => 'Third Judge'],
        ],
        'reviewing_admin_user_id' => $this->judge->id,
        'reviewing_admin_user_name' => 'Presiding Judge',
        ...$overrides,
    ]);
});

test('the decision list marks which rows the presiding judge may change', function (): void {
    $draft = ($this->makeDecision)();
    $published = ($this->makeDecision)(['status' => 'published', 'name' => 'Published ruling']);

    $this->actingAs($this->judge)
        ->get(route('decisions.index', ['q' => 'ruling']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Decisions/Index')
            ->where('filters.q', 'ruling')
            ->has('decisions.data', 2)
            ->where('decisions.data', function ($rows) use ($draft, $published): bool {
                $rows = collect($rows)->keyBy('id');

                return $rows[$draft->id]['canEdit'] === true
                    && $rows[$draft->id]['canDelete'] === true
                    && $rows[$draft->id]['judge'] === 'Presiding Judge'
                    && $rows[$draft->id]['applicant'] === 'Applicant Name'
                    && $rows[$published->id]['canEdit'] === false
                    && $rows[$published->id]['canDelete'] === false;
            }));

    // Another judge can view but not change it.
    $this->actingAs(inertiaStaff(['decision.view', 'decision.view.all', 'decision.update']))
        ->get(route('decisions.index'))
        ->assertInertia(fn (Assert $page) => $page->where('decisions.data', fn ($rows): bool => collect($rows)->every(fn (array $row): bool => $row['canEdit'] === false)));
});

test('decision pages keep their route permissions', function (): void {
    $decision = ($this->makeDecision)();
    $viewer = inertiaStaff(['decision.view', 'decision.view.all']);

    $this->actingAs($viewer)->get(route('decisions.create'))->assertRedirect(route('dashboard'));
    $this->actingAs($viewer)->get(route('decisions.edit', $decision))->assertRedirect(route('dashboard'));
    $this->actingAs($viewer)->patch(route('decisions.approve', $decision))->assertRedirect(route('dashboard'));
    $this->actingAs(inertiaStaff([]))->get(route('decisions.index'))->assertRedirect(route('dashboard'));
});

test('the create form offers closed cases with their parties and preselects the requested case', function (): void {
    partyCase(partyApplicant('decisions-open'), 'DEC/00002/26', ['status' => 'active']);

    $this->actingAs($this->judge)
        ->get(route('decisions.create', ['case_id' => $this->caseId]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Decisions/Form')
            ->where('decision', null)
            ->where('defaultCaseId', $this->caseId)
            ->has('cases', 1)
            ->where('cases.0.caseNumber', 'DEC/00001/26')
            ->where('cases.0.applicant', 'Applicant Name')
            ->where('cases.0.respondent', 'Respondent Org')
            ->where('currentUser.id', $this->judge->id));
});

test('a decision is created from the form payload', function (): void {
    $this->actingAs($this->judge)
        ->post(route('decisions.store'), [
            'case_id' => $this->caseId,
            'case_file_number' => 'FILE-9',
            'decision_date' => '2026-04-02',
            'decision_content' => '<p>Decided</p>',
            'judges' => [
                ['admin_user_id' => $this->second->id],
                ['admin_user_id' => $this->judge->id],
                ['admin_user_id' => $this->third->id],
            ],
        ])
        ->assertRedirect(route('decisions.index'));

    $decision = Decision::query()->where('case_file_number', 'FILE-9')->sole();
    expect($decision->status)->toBe('draft')
        ->and($decision->presidingJudgeId())->toBe($this->judge->id)
        ->and($decision->applicant_full_name)->toBe('Applicant Name');
});

test('the edit form carries the stored panel and content', function (): void {
    $decision = ($this->makeDecision)(['case_file_number' => 'CUSTOM-1']);

    $this->actingAs($this->judge)
        ->get(route('decisions.edit', $decision))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Decisions/Form')
            ->where('decision.id', $decision->id)
            ->where('decision.caseFileNumber', 'CUSTOM-1')
            ->where('decision.decisionDate', '2026-03-01')
            ->where('decision.judgeIds', [$this->second->id, $this->judge->id, $this->third->id]));
});

test('the decision page sanitises content and lists reviews with ownership', function (): void {
    $decision = ($this->makeDecision)();
    $other = User::factory()->create(['name' => 'Other Reviewer']);
    DecisionReview::create(['decision_id' => $decision->id, 'case_number' => 'DEC/00001/26', 'reviewer_id' => $this->judge->id, 'outcome' => 'approve']);
    DecisionReview::create(['decision_id' => $decision->id, 'case_number' => 'DEC/00001/26', 'reviewer_id' => $other->id, 'outcome' => 'improve', 'review_note' => 'Tighten it']);

    $this->actingAs($this->judge)
        ->get(route('decisions.show', $decision))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Decisions/Show')
            ->where('decision.status', 'draft')
            ->where('decision.isPresidingJudge', true)
            ->where('decision.contentHtml', fn (string $html): bool => str_contains($html, 'Ruling') && ! str_contains($html, '<script'))
            ->has('reviews', 2)
            ->where('reviews', fn ($reviews): bool => collect($reviews)->firstWhere('reviewer', 'Presiding Judge')['isOwner'] === true
                && collect($reviews)->firstWhere('reviewer', 'Other Reviewer')['isOwner'] === false)
            ->has('templates'));
});

test('reviews can be added, edited on their own page and removed by their author', function (): void {
    $decision = ($this->makeDecision)();

    $this->actingAs($this->judge)
        ->post(route('decisions.reviews.store', $decision), ['outcome' => 'reject'])
        ->assertSessionHasErrors('review_note');

    $this->actingAs($this->judge)
        ->post(route('decisions.reviews.store', $decision), ['outcome' => 'reject', 'review_note' => 'Disagree'])
        ->assertRedirect();

    $review = DecisionReview::query()->where('decision_id', $decision->id)->sole();

    $this->actingAs($this->judge)
        ->get(route('decisions.reviews.edit', [$decision, $review]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Decisions/ReviewEdit')
            ->where('review.outcome', 'reject')
            ->where('review.note', 'Disagree')
            ->where('decision.caseNumber', 'DEC/00001/26'));

    $this->actingAs($this->judge)
        ->patch(route('decisions.reviews.update', [$decision, $review]), ['outcome' => 'approve'])
        ->assertRedirect(route('decisions.show', $decision));

    expect($review->fresh()->outcome)->toBe('approve');

    $this->actingAs($this->judge)->delete(route('decisions.reviews.destroy', [$decision, $review]))->assertRedirect();

    expect(DecisionReview::query()->find($review->id))->toBeNull();
});

test('publishing then approving a decision locks it', function (): void {
    $decision = ($this->makeDecision)();

    $this->actingAs($this->judge)
        ->patch(route('decisions.approve', $decision))
        ->assertSessionHasErrors('approve');

    $this->actingAs($this->judge)
        ->patch(route('decisions.status', $decision), ['status' => 'published'])
        ->assertRedirect(route('decisions.show', $decision));

    $this->actingAs($this->judge)
        ->patch(route('decisions.approve', $decision))
        ->assertRedirect(route('decisions.show', $decision));

    expect($decision->fresh())
        ->status->toBe('published')
        ->approved_by->toBe($this->judge->id);

    $this->actingAs($this->judge)->get(route('decisions.edit', $decision))->assertForbidden();
    expect(DB::table('decisions')->where('id', $decision->id)->exists())->toBeTrue();
});
