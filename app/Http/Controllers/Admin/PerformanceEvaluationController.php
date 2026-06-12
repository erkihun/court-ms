<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PerformanceCriterion;
use App\Models\PerformanceEvaluation;
use App\Models\PerformanceEvaluationCategory;
use App\Models\PerformanceEvaluationScore;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerformanceEvaluationController extends Controller
{
    public function index(Request $request)
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

        $evaluations = $query->paginate(15)->withQueryString();
        $users = User::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $categories = PerformanceEvaluationCategory::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Summary stats
        $stats = [
            'total'     => PerformanceEvaluation::count(),
            'draft'     => PerformanceEvaluation::where('status', 'draft')->count(),
            'submitted' => PerformanceEvaluation::where('status', 'submitted')->count(),
            'reviewed'  => PerformanceEvaluation::where('status', 'reviewed')->count(),
            'avg_score' => round(PerformanceEvaluation::where('status', 'reviewed')->avg('overall_score') ?? 0, 1),
        ];

        return view('admin.performance-evaluations.index', compact('evaluations', 'users', 'stats', 'categories'));
    }

    public function create()
    {
        $users    = User::where('status', 'active')->orderBy('name')->get(['id', 'name', 'avatar_path']);
        $criteria = PerformanceCriterion::active()->get();

        return view('admin.performance-evaluations.create', compact('users', 'criteria'));
    }

    public function store(Request $request)
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

    public function show(PerformanceEvaluation $performanceEvaluation)
    {
        $performanceEvaluation->load([
            'evaluatedUser', 'evaluator', 'reviewer',
            'scores.criterion',
        ]);

        return view('admin.performance-evaluations.show', [
            'evaluation' => $performanceEvaluation,
        ]);
    }

    public function edit(PerformanceEvaluation $performanceEvaluation)
    {
        abort_if($performanceEvaluation->status === 'reviewed', 403, __('performance.messages.reviewed_cannot_edit'));

        $performanceEvaluation->load('scores');

        $users    = User::where('status', 'active')->orderBy('name')->get(['id', 'name', 'avatar_path']);
        $criteria = PerformanceCriterion::active()->get();

        $existingScores = $performanceEvaluation->scores->keyBy('criterion_id');

        return view('admin.performance-evaluations.edit', [
            'evaluation'     => $performanceEvaluation,
            'users'          => $users,
            'criteria'       => $criteria,
            'existingScores' => $existingScores,
        ]);
    }

    public function update(Request $request, PerformanceEvaluation $performanceEvaluation)
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

    public function destroy(PerformanceEvaluation $performanceEvaluation)
    {
        abort_if($performanceEvaluation->status === 'reviewed', 403, __('performance.messages.reviewed_cannot_delete'));

        $performanceEvaluation->delete();

        return redirect()->route('performance-evaluations.index')
            ->with('success', __('performance.messages.deleted'));
    }

    public function review(Request $request, PerformanceEvaluation $performanceEvaluation)
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
}
