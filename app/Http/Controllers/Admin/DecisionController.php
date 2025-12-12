<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourtCase;
use App\Models\Decision;
use App\Models\User;
use App\Models\DecisionReview;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class DecisionController extends Controller
{
    private const STATUS_OPTIONS = [
        'draft',
        'active',
        'archived',
    ];

    private const CASE_LIMIT = 250;

    public function index(Request $request)
    {
        $search = trim($request->string('q')->toString());
        $decisions = Decision::with(['courtCase', 'reviewer'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('case_number', 'like', "%{$search}%")
                        ->orWhere('case_file_number', 'like', "%{$search}%")
                        ->orWhere('decision_content', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('decision_date')
            ->paginate(12)
            ->withQueryString();

        return view('admin.decisions.index', compact('decisions', 'search'));
    }

    public function create()
    {
        $cases = $this->loadCases(); // allow all cases
        $adminUsers = $this->loadAdminUsers();
        $judgeUsers = $this->loadJudgeUsers();
        return view('admin.decisions.create', compact('cases', 'adminUsers', 'judgeUsers'));
    }

    public function show(Decision $decision)
    {
        $decision->loadMissing(['courtCase', 'reviews.reviewer']);
        return view('admin.decisions.show', compact('decision'));
    }

    public function store(Request $request)
    {
        $payload = $this->preparePayload($request->validate($this->rules()));
        $case = CourtCase::with('applicant')->findOrFail($payload['case_id']);
        $data = $this->hydrateCaseData($payload, $case);

        Decision::create($data);

        return redirect()->route('decisions.index')->with('success', 'Decision created.');
    }

    public function edit(Decision $decision)
    {
        $this->ensureMiddleJudge($decision);
        $cases = $this->loadCases($decision);
        $adminUsers = $this->loadAdminUsers();
        $judgeUsers = $this->loadJudgeUsers();
        return view('admin.decisions.edit', compact('decision', 'cases', 'adminUsers', 'judgeUsers'));
    }

    public function update(Request $request, Decision $decision)
    {
        $this->ensureMiddleJudge($decision);
        $payload = $this->preparePayload($request->validate($this->rules()));
        $case = CourtCase::with('applicant')->findOrFail($payload['case_id']);
        $decision->update($this->hydrateCaseData($payload, $case));

        return redirect()->route('decisions.index')->with('success', 'Decision updated.');
    }

    public function storeReview(Request $request, Decision $decision)
    {
        $this->ensureReviewable($decision);
        $data = $request->validate([
            'outcome' => ['required', Rule::in(['approve', 'reject', 'improve'])],
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);

        DecisionReview::create([
            'decision_id' => $decision->id,
            'case_number' => $decision->case_number,
            'reviewer_id' => auth()->id(),
            'review_note' => $data['review_note'] ?? null,
            'outcome' => $data['outcome'],
        ]);

        return back()->with('success', 'Review submitted.');
    }

    public function updateReview(Request $request, Decision $decision, DecisionReview $review)
    {
        $this->ensureReviewable($decision);
        abort_if($review->decision_id !== $decision->id, 404);
        abort_if(auth()->id() !== $review->reviewer_id, 403);

        $data = $request->validate([
            'outcome' => ['required', Rule::in(['approve', 'reject', 'improve'])],
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $review->update([
            'outcome' => $data['outcome'],
            'review_note' => $data['review_note'] ?? null,
        ]);

        return redirect()->route('decisions.show', $decision)->with('success', 'Review updated.');
    }

    public function destroyReview(Decision $decision, DecisionReview $review)
    {
        $this->ensureReviewable($decision);
        abort_if($review->decision_id !== $decision->id, 404);
        abort_if(auth()->id() !== $review->reviewer_id, 403);

        $review->delete();

        return back()->with('success', 'Review deleted.');
    }

    public function editReview(Decision $decision, DecisionReview $review)
    {
        abort_if($review->decision_id !== $decision->id, 404);
        abort_if(auth()->id() !== $review->reviewer_id, 403);

        return view('admin.decisions.reviews.edit', compact('decision', 'review'));
    }

    public function destroy(Decision $decision)
    {
        $this->ensureMiddleJudge($decision);
        $decision->delete();
        return back()->with('success', 'Decision deleted.');
    }

    private function rules(): array
    {
        return [
            'case_id' => ['required', 'exists:court_cases,id'],
            'case_file_number' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'decision_content' => ['required', 'string'],
            'decision_date' => ['required', 'date'],
            'status' => ['required', 'string', 'max:32', Rule::in(self::STATUS_OPTIONS)],
            'reviewing_admin_user_names' => ['nullable', 'array'],
            'reviewing_admin_user_names.*' => ['string', 'max:255'],
            'judges_comments' => ['nullable', 'string', 'max:5000'],
            'judges' => ['nullable', 'array', 'size:3'],
            'judges.*.admin_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    private function preparePayload(array $payload): array
    {
        $payload['name'] = trim((string) ($payload['name'] ?? ''));
        $payload['case_file_number'] = trim((string) ($payload['case_file_number'] ?? ''));
        $payload['description'] = isset($payload['description']) ? strip_tags(trim($payload['description'])) : null;
        $payload['decision_content'] = trim($payload['decision_content'] ?? '');
        $payload['reviewing_admin_user_names'] = $payload['reviewing_admin_user_names'] ?? [];
        $payload['judges'] = $payload['judges'] ?? [];

        if ($payload['name'] === '') {
            $payload['name'] = $payload['case_file_number'] !== '' ? $payload['case_file_number'] : 'Decision';
        }

        return $payload;
    }

    private function hydrateCaseData(array $payload, CourtCase $case): array
    {
        $panelJudges = $this->normalizePanelJudges($payload['judges']);

        $reviewerName = auth()->user()?->name ?? 'Admin';
        $reviewers = $this->normalizeReviewerNames($payload['reviewing_admin_user_names'] ?? []);
        if (empty($reviewers)) {
            $reviewers = [$reviewerName];
        }

        return array_merge($payload, [
            'court_case_id' => $case->id,
            'case_number' => $case->case_number,
            'case_file_number' => $payload['case_file_number'] ?: $case->case_number,
            'applicant_full_name' => trim((string) ($case->applicant?->full_name ?? '')),
            'respondent_full_name' => $case->respondent_name,
            'case_filed_date' => $case->filing_date?->toDateString() ?? $case->created_at?->toDateString(),
            'panel_judges' => $panelJudges,
            'panel_decision' => 'pending',
            'reviewing_admin_user_id' => auth()->id(),
            'reviewing_admin_user_name' => $reviewers[0] ?? $reviewerName,
            'reviewing_admin_user_names' => $reviewers,
            'judges_comments' => $payload['judges_comments'] ?? null,
        ]);
    }

    private function normalizeReviewerNames(array|string|null $raw): array
    {
        if (is_array($raw)) {
            return collect($raw)
                ->map(fn($value) => trim((string) $value))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        if (trim($raw ?? '') === '') {
            return [];
        }

        return collect(preg_split('/[\r\n,]+/', $raw))
            ->map(fn($value) => trim($value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function normalizePanelJudges(array $panel): array
    {
        $ordered = collect($panel)->pad(3, [])->take(3)->values();
        $ids = $ordered->pluck('admin_user_id')->filter()->unique();
        $namesById = User::whereIn('id', $ids)->pluck('name', 'id');

        return $ordered->map(function ($row, $index) use ($namesById) {
            $id = (int) Arr::get($row, 'admin_user_id');
            return [
                'order' => $index + 1,
                'admin_user_id' => $id ?: null,
                'admin_user_name' => $id && isset($namesById[$id]) ? $namesById[$id] : null,
                'vote' => null,
            ];
        })->all();
    }

    private function ensureMiddleJudge(Decision $decision): void
    {
        $middleJudgeId = $decision->panel_judges[1]['admin_user_id'] ?? null;
        if (!$middleJudgeId || $middleJudgeId !== auth()->id()) {
            abort(403, 'Only the middle judge can modify this decision.');
        }
    }

    private function ensureReviewable(Decision $decision): void
    {
        if (in_array($decision->status, ['active', 'archived'], true)) {
            abort(403, 'Reviews are locked for active or archived decisions.');
        }
    }

    private function loadCases(Decision $decision = null, bool $excludeClosed = false)
    {
        $cases = CourtCase::with('applicant')
            ->orderByDesc('filing_date')
            ->limit(self::CASE_LIMIT)
            ->when($excludeClosed, fn($q) => $q->where('status', '!=', 'closed'))
            ->get(['id', 'case_number', 'title', 'filing_date', 'applicant_id', 'respondent_name', 'status']);

        if ($decision && $decision->court_case_id && !$cases->contains('id', $decision->court_case_id)) {
            $current = CourtCase::find($decision->court_case_id, ['id', 'case_number', 'title', 'filing_date', 'status']);
            if ($current) {
                $cases->push($current);
            }
        }

        return $cases;
    }

    private function loadAdminUsers()
    {
        return User::query()
            ->whereHas('roles', fn($query) => $query->where('name', 'admin'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function loadJudgeUsers()
    {
        $user = auth()->user();
        $teamIds = $user?->teams()->pluck('teams.id') ?? collect();

        $query = User::query()->orderBy('name');

        if ($teamIds->isNotEmpty()) {
            $query->whereHas('teams', fn($q) => $q->whereIn('teams.id', $teamIds));
        }

        $judges = $query->get(['id', 'name']);

        // Ensure current user is present
        if ($user && !$judges->contains('id', $user->id)) {
            $judges->push($user);
        }

        return $judges->unique('id')->sortBy('name')->values();
    }
}
