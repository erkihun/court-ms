<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PerformanceCriterion;
use App\Models\PerformanceEvaluation;
use App\Models\PerformanceEvaluationCategory;
use App\Models\PerformanceEvaluationScore;
use App\Models\User;
use App\Support\EthiopianDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Inertia\Inertia;
use Inertia\Response;

class PerformanceEvaluationController extends Controller
{
    private const PERIOD_TYPES = ['monthly', 'quarterly', 'annual'];

    public function index(Request $request): Response
    {
        $query = PerformanceEvaluation::with(['evaluatedUser', 'evaluator'])
            ->orderByDesc('period_start');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('user_id')) {
            $query->where('evaluated_user_id', $request->user_id);
        }
        if ($request->filled('period_type')) {
            $query->where('period_type', $request->period_type);
        }

        $evaluations = $query->paginate(15)->withQueryString()
            ->through(fn (PerformanceEvaluation $evaluation): array => $this->presentRow($evaluation));
        $user = $request->user();
        $canManageSettings = $user?->hasPermission('settings.manage') ?? false;

        // Summary stats
        $stats = [
            'total'     => PerformanceEvaluation::count(),
            'draft'     => PerformanceEvaluation::where('status', 'draft')->count(),
            'submitted' => PerformanceEvaluation::where('status', 'submitted')->count(),
            'reviewed'  => PerformanceEvaluation::where('status', 'reviewed')->count(),
            'avg_score' => round(PerformanceEvaluation::where('status', 'reviewed')->avg('overall_score') ?? 0, 1),
        ];

        return Inertia::render('Admin/PerformanceEvaluations/Index', [
            'evaluations' => $evaluations,
            'users' => $this->userOptions(),
            'stats' => $stats,
            'filters' => [
                'status' => (string) $request->query('status', ''),
                'user_id' => (string) $request->query('user_id', ''),
                'period_type' => (string) $request->query('period_type', ''),
            ],
            'periodTypes' => self::PERIOD_TYPES,
            'categories' => $canManageSettings
                ? PerformanceEvaluationCategory::query()
                    ->where('active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get()
                    ->map(fn (PerformanceEvaluationCategory $category): array => ['slug' => (string) $category->slug, 'name' => (string) $category->local_name])
                    ->values()
                : [],
            'can' => [
                'create' => $user?->hasPermission('performance-evaluations.create') ?? false,
                'update' => $user?->hasPermission('performance-evaluations.update') ?? false,
                'manageSettings' => $canManageSettings,
            ],
        ]);
    }

    public function create(): Response
    {
        return $this->form(null);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'evaluated_user_id' => ['required', 'exists:users,id'],
            'period_type'       => ['required', 'in:monthly,quarterly,annual'],
            'period_start'      => ['required', 'date'],
            'period_end'        => ['required', 'date', 'after_or_equal:period_start'],
            'notes'             => ['nullable', 'string', 'max:2000'],
            'scores'            => ['required', 'array'],
            'scores.*.criterion_id' => ['required', 'exists:performance_evaluation_criteria,id'],
            'scores.*.score'        => ['required', 'integer', 'min:0', 'max:10'],
            'scores.*.comment'      => ['nullable', 'string', 'max:500'],
            'action'            => ['required', 'in:draft,submitted'],
        ], [], __('performance.validation.attributes'));

        DB::transaction(function () use ($data, $request) {
            $evaluation = PerformanceEvaluation::create([
                'evaluated_user_id' => $data['evaluated_user_id'],
                'evaluator_id'      => auth()->id(),
                'period_type'       => $data['period_type'],
                'period_start'      => $data['period_start'],
                'period_end'        => $data['period_end'],
                'notes'             => $data['notes'] ?? null,
                'status'            => $data['action'],
            ]);

            foreach ($data['scores'] as $scoreData) {
                PerformanceEvaluationScore::create([
                    'evaluation_id' => $evaluation->id,
                    'criterion_id'  => $scoreData['criterion_id'],
                    'score'         => $scoreData['score'],
                    'comment'       => $scoreData['comment'] ?? null,
                ]);
            }

            $evaluation->recalculateScore();
        });

        $msg = $data['action'] === 'submitted'
            ? __('performance.messages.submitted')
            : __('performance.messages.draft');

        return redirect()->route('performance-evaluations.index')->with('success', $msg);
    }

    public function show(Request $request, PerformanceEvaluation $performanceEvaluation): Response
    {
        $performanceEvaluation->load([
            'evaluatedUser', 'evaluator', 'reviewer',
            'scores.criterion',
        ]);
        $user = $request->user();
        $na = __('performance.not_available');

        return Inertia::render('Admin/PerformanceEvaluations/Show', [
            'evaluation' => [
                ...$this->presentRow($performanceEvaluation),
                'periodStart' => EthiopianDate::smartFormat($performanceEvaluation->period_start, false, $na, 'h:i A', 'M d, Y'),
                'scoreLabel' => $performanceEvaluation->score_label,
                'reviewer' => $performanceEvaluation->reviewer?->name,
                'reviewedAt' => $performanceEvaluation->reviewed_at ? EthiopianDate::smartFormat($performanceEvaluation->reviewed_at, false, $na, 'h:i A', 'M d, Y') : null,
                'notes' => $performanceEvaluation->notes,
                'reviewerNotes' => $performanceEvaluation->reviewer_notes,
                'scores' => $performanceEvaluation->scores
                    ->sortBy(fn (PerformanceEvaluationScore $score): int => (int) ($score->criterion?->sort_order ?? 0))
                    ->map(fn (PerformanceEvaluationScore $score): array => [
                        'id' => (int) $score->id,
                        'criterion' => $score->criterion?->local_name,
                        'category' => $this->categoryLabel($score->criterion?->category),
                        'weight' => $score->criterion?->weight,
                        'score' => (int) $score->score,
                        'comment' => $score->comment ?: null,
                    ])
                    ->values(),
            ],
            'can' => [
                'update' => $performanceEvaluation->status !== 'reviewed' && ($user?->hasPermission('performance-evaluations.update') ?? false),
                'review' => $performanceEvaluation->status === 'submitted' && ($user?->hasPermission('performance-evaluations.review') ?? false),
                'delete' => $performanceEvaluation->status !== 'reviewed' && ($user?->hasPermission('performance-evaluations.delete') ?? false),
            ],
        ]);
    }

    public function edit(PerformanceEvaluation $performanceEvaluation): Response
    {
        abort_if($performanceEvaluation->status === 'reviewed', 403, __('performance.messages.reviewed_cannot_edit'));

        $performanceEvaluation->load(['scores', 'evaluatedUser']);

        return $this->form($performanceEvaluation);
    }

    public function update(Request $request, PerformanceEvaluation $performanceEvaluation): RedirectResponse
    {
        abort_if($performanceEvaluation->status === 'reviewed', 403, __('performance.messages.reviewed_cannot_edit'));

        $data = $request->validate([
            'evaluated_user_id' => ['required', 'exists:users,id'],
            'period_type'       => ['required', 'in:monthly,quarterly,annual'],
            'period_start'      => ['required', 'date'],
            'period_end'        => ['required', 'date', 'after_or_equal:period_start'],
            'notes'             => ['nullable', 'string', 'max:2000'],
            'scores'            => ['required', 'array'],
            'scores.*.criterion_id' => ['required', 'exists:performance_evaluation_criteria,id'],
            'scores.*.score'        => ['required', 'integer', 'min:0', 'max:10'],
            'scores.*.comment'      => ['nullable', 'string', 'max:500'],
            'action'            => ['required', 'in:draft,submitted'],
        ], [], __('performance.validation.attributes'));

        DB::transaction(function () use ($data, $performanceEvaluation) {
            $performanceEvaluation->update([
                'evaluated_user_id' => $data['evaluated_user_id'],
                'period_type'       => $data['period_type'],
                'period_start'      => $data['period_start'],
                'period_end'        => $data['period_end'],
                'notes'             => $data['notes'] ?? null,
                'status'            => $data['action'],
            ]);

            // Replace all scores
            $performanceEvaluation->scores()->delete();
            foreach ($data['scores'] as $scoreData) {
                PerformanceEvaluationScore::create([
                    'evaluation_id' => $performanceEvaluation->id,
                    'criterion_id'  => $scoreData['criterion_id'],
                    'score'         => $scoreData['score'],
                    'comment'       => $scoreData['comment'] ?? null,
                ]);
            }

            $performanceEvaluation->recalculateScore();
        });

        return redirect()->route('performance-evaluations.show', $performanceEvaluation)
            ->with('success', __('performance.messages.updated'));
    }

    public function destroy(PerformanceEvaluation $performanceEvaluation): RedirectResponse
    {
        abort_if($performanceEvaluation->status === 'reviewed', 403, __('performance.messages.reviewed_cannot_delete'));

        $performanceEvaluation->delete();

        return redirect()->route('performance-evaluations.index')
            ->with('success', __('performance.messages.deleted'));
    }

    public function review(Request $request, PerformanceEvaluation $performanceEvaluation): RedirectResponse
    {
        abort_if($performanceEvaluation->status !== 'submitted', 422, __('performance.messages.submitted_only_review'));

        $data = $request->validate([
            'reviewer_notes' => ['nullable', 'string', 'max:2000'],
        ], [], __('performance.validation.attributes'));

        $performanceEvaluation->update([
            'status'         => 'reviewed',
            'reviewer_notes' => $data['reviewer_notes'] ?? null,
            'reviewed_by'    => auth()->id(),
            'reviewed_at'    => now(),
        ]);

        return redirect()->route('performance-evaluations.show', $performanceEvaluation)
            ->with('success', __('performance.messages.reviewed'));
    }

    private function form(?PerformanceEvaluation $evaluation): Response
    {
        $existing = $evaluation?->scores->keyBy('criterion_id') ?? collect();
        $na = __('performance.not_available');

        return Inertia::render('Admin/PerformanceEvaluations/Form', [
            'evaluation' => $evaluation ? [
                'id' => (int) $evaluation->id,
                'memberName' => $evaluation->evaluatedUser?->name,
                'period' => EthiopianDate::smartFormat($evaluation->period_start, false, $na, 'h:i A', 'M d')
                    .' - '.EthiopianDate::smartFormat($evaluation->period_end, false, $na, 'h:i A', 'M d, Y'),
                'evaluated_user_id' => (string) $evaluation->evaluated_user_id,
                'period_type' => (string) $evaluation->period_type,
                'period_start' => $evaluation->period_start?->format('Y-m-d') ?? '',
                'period_end' => $evaluation->period_end?->format('Y-m-d') ?? '',
                'notes' => (string) ($evaluation->notes ?? ''),
            ] : null,
            'users' => $this->userOptions(),
            'periodTypes' => self::PERIOD_TYPES,
            'criteria' => PerformanceCriterion::active()->get()->map(fn (PerformanceCriterion $criterion): array => [
                'id' => (int) $criterion->id,
                'name' => $criterion->local_name,
                'description' => $criterion->description ?: null,
                'category' => $this->categoryLabel($criterion->category),
                'weight' => (int) $criterion->weight,
                'score' => (int) ($existing->get($criterion->id)?->score ?? 5),
                'comment' => (string) ($existing->get($criterion->id)?->comment ?? ''),
            ])->values(),
        ]);
    }

    /** @return array<string, mixed> */
    private function presentRow(PerformanceEvaluation $evaluation): array
    {
        $na = __('performance.not_available');

        return [
            'id' => (int) $evaluation->id,
            'member' => $evaluation->evaluatedUser?->name,
            'memberAvatarUrl' => $evaluation->evaluatedUser?->avatar_url,
            'period' => EthiopianDate::smartFormat($evaluation->period_start, false, $na, 'h:i A', 'M d')
                .' - '.EthiopianDate::smartFormat($evaluation->period_end, false, $na, 'h:i A', 'M d, Y'),
            'periodEnd' => EthiopianDate::smartFormat($evaluation->period_end, false, $na, 'h:i A', 'M d, Y'),
            'periodType' => (string) $evaluation->period_type,
            'score' => round((float) $evaluation->overall_score, 1),
            'status' => (string) $evaluation->status,
            'evaluator' => $evaluation->evaluator?->name,
            'created' => EthiopianDate::smartFormat($evaluation->created_at, false, $na, 'h:i A', 'M d, Y'),
        ];
    }

    /** @return Collection<int, array{id: int, name: string}> */
    private function userOptions(): Collection
    {
        return User::where('status', 'active')->orderBy('name')->get(['id', 'name'])
            ->map(fn (User $user): array => ['id' => (int) $user->id, 'name' => (string) $user->name])
            ->values();
    }

    private function categoryLabel(?string $category): ?string
    {
        if ($category === null || $category === '') {
            return null;
        }

        return Lang::has("performance.categories.{$category}") ? __("performance.categories.{$category}") : $category;
    }
}
