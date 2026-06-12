<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePerformanceCategoryRequest;
use App\Http\Requests\Admin\StorePerformanceCriterionRequest;
use App\Http\Requests\Admin\UpdatePerformanceCriterionRequest;
use App\Models\PerformanceEvaluationCategory;
use App\Models\PerformanceCriterion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PerformanceEvaluationSettingController extends Controller
{
    public function index(): View
    {
        $criteria = PerformanceCriterion::query()
            ->withCount('scores')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $categories = $this->categories();

        $totalActiveWeight = (int) PerformanceCriterion::query()
            ->where('active', true)
            ->sum('weight');

        return view('admin.settings.performance-evaluation', [
            'criteria' => $criteria,
            'categories' => $categories,
            'totalActiveWeight' => $totalActiveWeight,
        ]);
    }

    public function createCriterion(): View
    {
        return view('admin.settings.performance-criteria-create', [
            'categories' => $this->categories(),
        ]);
    }

    public function createCategory(): View
    {
        return view('admin.settings.performance-category-create');
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
     * @param array<string, mixed> $data
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

    private function categories()
    {
        $configured = PerformanceEvaluationCategory::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        if ($configured->isNotEmpty()) {
            return $configured;
        }

        return PerformanceCriterion::query()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->filter()
            ->map(fn (string $category): object => (object) [
                'slug' => $category,
                'local_name' => $category,
            ])
            ->values();
    }

    private function redirectTarget(mixed $target): string
    {
        if (is_string($target) && str_starts_with($target, url('/'))) {
            return $target;
        }

        return route('settings.performance-evaluation.index');
    }
}
