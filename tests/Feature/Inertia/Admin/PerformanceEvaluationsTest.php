<?php

declare(strict_types=1);

use App\Models\PerformanceCriterion;
use App\Models\PerformanceEvaluation;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    // Migrations seed default criteria; keep only this test's two active.
    PerformanceCriterion::query()->update(['active' => false]);
    $this->quality = PerformanceCriterion::create(['name' => 'Quality', 'category' => 'quality', 'weight' => 60, 'active' => true, 'sort_order' => 1]);
    $this->speed = PerformanceCriterion::create(['name' => 'Speed', 'category' => 'efficiency', 'weight' => 40, 'active' => true, 'sort_order' => 2]);
    $this->member = User::factory()->create(['name' => 'Abebe Member', 'status' => 'active']);
    $this->payload = fn (string $action, array $scores = [8, 5]): array => [
        'evaluated_user_id' => $this->member->id,
        'period_type' => 'quarterly',
        'period_start' => '2026-01-01',
        'period_end' => '2026-03-31',
        'notes' => 'Solid quarter',
        'scores' => [
            ['criterion_id' => $this->quality->id, 'score' => $scores[0], 'comment' => 'Careful'],
            ['criterion_id' => $this->speed->id, 'score' => $scores[1], 'comment' => ''],
        ],
        'action' => $action,
    ];
});

test('the evaluation form lists active criteria with default scores', function (): void {
    $this->actingAs(inertiaStaff(['performance-evaluations.create']))
        ->get(route('performance-evaluations.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/PerformanceEvaluations/Form')
            ->where('evaluation', null)
            ->has('criteria', 2)
            ->where('criteria.0.name', 'Quality')
            ->where('criteria.0.category', __('performance.categories.quality'))
            ->where('criteria.0.score', 5)
            ->has('periodTypes', 3));
});

test('an evaluation is submitted, scored by weight and listed', function (): void {
    $user = inertiaStaff(['performance-evaluations.create', 'performance-evaluations.view', 'performance-evaluations.update']);

    $this->actingAs($user)->post(route('performance-evaluations.store'), ($this->payload)('submitted'))
        ->assertRedirect(route('performance-evaluations.index'));

    $evaluation = PerformanceEvaluation::firstOrFail();
    // (0.8 * 60 + 0.5 * 40) / 100 = 68%
    expect($evaluation->status)->toBe('submitted')
        ->and($evaluation->overall_score)->toBe(68.0);

    $this->actingAs($user)->get(route('performance-evaluations.index', ['status' => 'submitted']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/PerformanceEvaluations/Index')
            ->where('evaluations.data.0.member', 'Abebe Member')
            ->where('evaluations.data.0.score', 68)
            ->where('stats.submitted', 1)
            ->where('filters.status', 'submitted')
            ->where('can.create', true));

    $this->actingAs($user)->get(route('performance-evaluations.edit', $evaluation))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/PerformanceEvaluations/Form')
            ->where('evaluation.period_start', '2026-01-01')
            ->where('criteria.0.score', 8)
            ->where('criteria.0.comment', 'Careful'));
});

test('score validation errors come back keyed by row', function (): void {
    $this->actingAs(inertiaStaff(['performance-evaluations.create']))
        ->post(route('performance-evaluations.store'), ($this->payload)('draft', [11, 5]))
        ->assertSessionHasErrors('scores.0.score');
});

test('reviewers approve a submitted evaluation, which then locks', function (): void {
    $user = inertiaStaff(['performance-evaluations.create', 'performance-evaluations.view', 'performance-evaluations.review', 'performance-evaluations.update', 'performance-evaluations.delete']);
    $this->actingAs($user)->post(route('performance-evaluations.store'), ($this->payload)('submitted'));
    $evaluation = PerformanceEvaluation::firstOrFail();

    $this->actingAs($user)->get(route('performance-evaluations.show', $evaluation))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/PerformanceEvaluations/Show')
            ->has('evaluation.scores', 2)
            ->where('evaluation.scores.0.criterion', 'Quality')
            ->where('can.review', true)
            ->where('can.update', true)
            ->where('can.delete', true));

    $this->actingAs($user)->post(route('performance-evaluations.review', $evaluation), ['reviewer_notes' => 'Agreed'])
        ->assertRedirect(route('performance-evaluations.show', $evaluation));

    $this->actingAs($user)->get(route('performance-evaluations.show', $evaluation))
        ->assertInertia(fn (Assert $page) => $page
            ->where('evaluation.status', 'reviewed')
            ->where('evaluation.reviewerNotes', 'Agreed')
            ->where('can.review', false)
            ->where('can.update', false)
            ->where('can.delete', false));

    $this->actingAs($user)->get(route('performance-evaluations.edit', $evaluation))->assertForbidden();
});

test('settings managers get the quick-add categories on the list', function (): void {
    $this->actingAs(inertiaStaff(['performance-evaluations.view', 'settings.manage']))
        ->get(route('performance-evaluations.index'))
        ->assertInertia(fn (Assert $page) => $page->where('can.manageSettings', true)->has('categories'));
});
