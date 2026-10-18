<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePerformanceCategoryRequest;
use App\Http\Requests\Admin\StorePerformanceCriterionRequest;
use App\Http\Requests\Admin\UpdatePerformanceCriterionRequest;
use App\Models\PerformanceCriterion;
use App\Models\PerformanceEvaluationCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PerformanceEvaluationSettingController extends Controller
{
    public function index(): Response
    {
        $categories = $this->categories();
        $labels = $categories->pluck('label', 'value');

        $criteria = PerformanceCriterion::query()
            ->withCount('scores')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (PerformanceCriterion $criterion): array => [
                'id' => $criterion->id,
                'name' => (string) $criterion->name,
                'nameAm' => $criterion->name_am,
                'category' => (string) $criterion->category,
                'categoryLabel' => (string) ($labels[$criterion->category] ?? $criterion->category),
                'weight' => (int) $criterion->weight,
                'sortOrder' => (int) $criterion->sort_order,
                'active' => (bool) $criterion->active,
                'description' => $criterion->description,
                'scoresCount' => (int) $criterion->scores_count,
            ]);

        return Inertia::render('Admin/PerformanceSettings/Index', [
            'criteria' => $criteria,
            'categories' => $categories,
            'totalActiveWeight' => (int) PerformanceCriterion::query()->where('active', true)->sum('weight'),
        ]);
    }

    public function createCriterion(): Response
    {
        return Inertia::render('Admin/PerformanceSettings/CreateCriterion', [
            'categories' => $this->categories(),
        ]);
    }

    public function createCategory(): Response
    {
        return Inertia::render('Admin/PerformanceSettings/CreateCategory');
    }

    public function store(StorePerformanceCriterionRequest $request): RedirectResponse
    {
        PerformanceCriterion::query()->create($this->payload($request->validated(), $request->boolean('active', true)));

        return redirect()
            ->to($this->redirectTarget($request->input('redirect_to')))
            ->with('success', __('performance.settings.messages.created'));
    }

    public function storeCategory(StorePerformanceCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $slug = Str::slug((string) ($data['slug'] ?? $data['name']), '_');
        if ($slug === '') {
            $slug = Str::random(8);
        }

        if (PerformanceEvaluationCategory::query()->where('slug', $slug)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['slug' => __('performance.settings.messages.category_slug_exists')]);
        }

        PerformanceEvaluationCategory::query()->create([
            'slug' => $slug,
            'name' => $data['name'],
            'name_am' => $data['name_am'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'active' => $request->boolean('active', true),
        ]);

        return redirect()
            ->to($this->redirectTarget($request->input('redirect_to')))
            ->with('success', __('performance.settings.messages.category_created'));
    }

    public function update(UpdatePerformanceCriterionRequest $request, PerformanceCriterion $criterion): RedirectResponse
    {
        $criterion->update($this->payload($request->validated(), $request->boolean('active')));

        return redirect()
            ->route('settings.performance-evaluation.index')
            ->with('success', __('performance.settings.messages.updated'));
    }

    public function destroy(PerformanceCriterion $criterion): RedirectResponse
    {
        if ($criterion->scores()->exists()) {
            $criterion->update(['active' => false]);

            return redirect()
                ->route('settings.performance-evaluation.index')
                ->with('success', __('performance.settings.messages.deactivated_in_use'));
        }

        $criterion->delete();

        return redirect()
            ->route('settings.performance-evaluation.index')
            ->with('success', __('performance.settings.messages.deleted'));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function payload(array $data, bool $active): array
    {
        return [
            'name' => $data['name'],
            'name_am' => $data['name_am'] ?? null,
            'category' => $data['category'],
            'weight' => (int) $data['weight'],
            'description' => $data['description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'active' => $active,
        ];
    }

    /**
     * Category options for the criterion forms: the configured categories, or the
     * distinct values already used by criteria when none are configured.
     *
     * @return Collection<int, array{value: string, label: string}>
     */
    private function categories(): Collection
    {
        $configured = PerformanceEvaluationCategory::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        if ($configured->isNotEmpty()) {
            return $configured
                ->map(fn (PerformanceEvaluationCategory $category): array => ['value' => (string) $category->slug, 'label' => $category->local_name])
                ->values();
        }

        return PerformanceCriterion::query()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->filter()
            ->map(fn (string $category): array => ['value' => $category, 'label' => $category])
            ->values();
    }

    /** Only follow `redirect_to` back into this application (same scheme, host and port). */
    private function redirectTarget(mixed $target): string
    {
        $app = parse_url(url('/'));
        $parts = is_string($target) ? parse_url($target) : false;

        if (
            is_array($parts) && is_array($app)
            && ($parts['scheme'] ?? null) === ($app['scheme'] ?? null)
            && ($parts['host'] ?? null) === ($app['host'] ?? null)
            && ($parts['port'] ?? null) === ($app['port'] ?? null)
            && ! isset($parts['user'])
        ) {
            return (string) $target;
        }

        return route('settings.performance-evaluation.index');
    }
}
