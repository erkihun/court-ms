<?php

declare(strict_types=1);

use App\Models\PerformanceCriterion;
use App\Models\PerformanceEvaluationCategory;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->staff = inertiaStaff(['settings.manage']);
    $this->category = PerformanceEvaluationCategory::query()->create([
        'slug' => 'quality_test', 'name' => 'Quality', 'name_am' => 'ጥራት', 'active' => true, 'sort_order' => 0,
    ]);
});

test('the settings page lists criteria with category labels and the active weight', function (): void {
    $criterion = PerformanceCriterion::query()->create([
        'name' => 'Accuracy', 'category' => 'quality_test', 'weight' => 40, 'active' => true, 'sort_order' => 1,
    ]);

    $this->actingAs($this->staff)->get(route('settings.performance-evaluation.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/PerformanceSettings/Index')
            ->where('categories', fn ($categories) => collect($categories)->contains(fn (array $option): bool => $option['value'] === 'quality_test'))
            ->where('criteria', fn ($criteria) => collect($criteria)->contains(fn (array $row): bool => $row['id'] === $criterion->id
                && $row['categoryLabel'] === $this->category->local_name
                && $row['scoresCount'] === 0))
            ->where('totalActiveWeight', (int) PerformanceCriterion::query()->where('active', true)->sum('weight')));
});

test('the standalone create pages render', function (): void {
    $this->actingAs($this->staff)->get(route('settings.performance-evaluation.criteria.create'))
        ->assertInertia(fn (Assert $page) => $page->component('Admin/PerformanceSettings/CreateCriterion')->has('categories'));
    $this->actingAs($this->staff)->get(route('settings.performance-evaluation.categories.create'))
        ->assertInertia(fn (Assert $page) => $page->component('Admin/PerformanceSettings/CreateCategory'));
});

test('criteria and categories can be created, updated and removed', function (): void {
    $this->actingAs($this->staff)->post(route('settings.performance-evaluation.criteria.store'), [
        'name' => 'Timeliness', 'category' => 'quality_test', 'weight' => 20, 'sort_order' => 3, 'active' => true,
    ])->assertRedirect(route('settings.performance-evaluation.index'));

    $criterion = PerformanceCriterion::query()->where('name', 'Timeliness')->sole();

    $this->actingAs($this->staff)->patch(route('settings.performance-evaluation.criteria.update', $criterion), [
        'name' => 'Timeliness', 'category' => 'quality_test', 'weight' => 25, 'active' => false,
    ])->assertRedirect(route('settings.performance-evaluation.index'));
    expect($criterion->fresh())->weight->toBe(25)->active->toBeFalse();

    $this->actingAs($this->staff)->post(route('settings.performance-evaluation.categories.store'), ['name' => 'Team Work'])
        ->assertRedirect(route('settings.performance-evaluation.index'));
    expect(PerformanceEvaluationCategory::query()->where('slug', 'team_work')->exists())->toBeTrue();

    $this->actingAs($this->staff)->delete(route('settings.performance-evaluation.criteria.destroy', $criterion))
        ->assertRedirect(route('settings.performance-evaluation.index'));
    expect(PerformanceCriterion::query()->whereKey($criterion->id)->exists())->toBeFalse();
});

test('redirect_to only returns to this application', function (string $target, bool $followed): void {
    $response = $this->actingAs($this->staff)->post(route('settings.performance-evaluation.categories.store'), [
        'name' => 'Redirect '.md5($target),
        'redirect_to' => $target,
    ]);

    $response->assertRedirect($followed ? $target : route('settings.performance-evaluation.index'));
})->with([
    'same app' => [fn () => url('/admin/performance-evaluations'), true],
    'lookalike host' => [fn () => rtrim(url('/'), '/').'.evil.test/phish', false],
    'other host' => ['https://evil.test/phish', false],
    'credentials in url' => [fn () => str_replace('://', '://user@', url('/x')), false],
]);

test('the settings pages need settings.manage', function (): void {
    $this->actingAs(inertiaStaff(['performance-evaluations.view']))
        ->get(route('settings.performance-evaluation.index'))
        ->assertRedirect(route('dashboard'));
});
