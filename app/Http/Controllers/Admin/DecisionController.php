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
use Mews\Purifier\Facades\Purifier;

class DecisionController extends Controller
{
    private const STATUS_OPTIONS = [
        'draft',
        'published',
    ];

    private const CASE_LIMIT = 250;

    public function index(Request $request)
    {
        $search = trim($request->string('q')->toString());
        $decisions = Decision::with(['courtCase.judge', 'reviewer'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('case_number', 'like', "%{$search}%")
                        ->orWhere('case_file_number', 'like', "%{$search}%")
                        ->orWhere('decision_content', 'like', "%{$search}%");
                });
            })
            ->tap(fn ($query) => $this->scopeToUserTeam($query))
            ->orderByDesc('decision_date')
            ->paginate(12)
            ->withQueryString();

        return view('admin.decisions.index', compact('decisions', 'search'));
    }

    /**
     * Restrict a decisions query to cases assigned to the current user's team(s).
     *
     * - Admins see everything.
     * - Team members see decisions for any case assigned to their team(s).
     * - Users with no team (and not admin) see nothing.
     */
    private function scopeToUserTeam($query): void
    {
        $user = auth()->user();

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return; // Admins: no scoping.
        }

        $teamIds = $user
            ? \App\Models\Team::query()
                ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
                ->pluck('id')
                ->all()
            : [];

        if (empty($teamIds)) {
            $query->whereRaw('1 = 0'); // No team => no decisions.
            return;
        }

        $query->whereHas('courtCase', fn ($q) => $q->whereIn('assigned_team_id', $teamIds));
    }

    /**
     * Whether the current user may access this decision (team-scoped).
     */
    private function canAccessDecision(Decision $decision): bool
    {
        $user = auth()->user();

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return true;
        }

        $teamId = $decision->courtCase?->assigned_team_id;
        if (! $teamId) {
            return false;
        }

        return $user
            ? \App\Models\Team::query()
                ->where('id', $teamId)
                ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
                ->exists()
            : false;
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
        abort_unless($this->canAccessDecision($decision), 403);

        $templates = \App\Models\DecisionTemplate::orderBy('title')->get(['id', 'title']);
        return view('admin.decisions.show', compact('decision', 'templates'));
    }

    /**
     * Render the final decision output for both applicant and respondent
     * using a chosen decision template, as a downloadable/streamed PDF.
     */
    public function output(Request $request, Decision $decision)
    {
        $decision->loadMissing('courtCase');
        abort_unless($this->canAccessDecision($decision), 403);

        $validated = $request->validate([
            'template_id' => ['nullable', 'integer', 'exists:decision_templates,id'],
            'mode'        => ['nullable', 'in:stream,download'],
        ]);

        $template = ! empty($validated['template_id'])
            ? \App\Models\DecisionTemplate::find($validated['template_id'])
            : null;

        $pdf = \App\Support\DecisionPdf::render($decision, $template);
        $filename = \App\Support\DecisionPdf::filename($decision);

        return ($validated['mode'] ?? 'stream') === 'download'
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }

    /**
     * Approve a decision (the gate to publishing). Stamps the seal on the PDF
     * and is a prerequisite for publishing and party downloads.
     */
    public function approve(Decision $decision)
    {
        $decision->loadMissing('courtCase');
        abort_unless($this->canAccessDecision($decision), 403);

        // A decision can only be approved after it has been published.
        if (! $decision->isPublished()) {
            return back()->withErrors(['approve' => __('decisions.approve_requires_published')]);
        }

        if ($decision->isApproved()) {
            return redirect()->route('decisions.show', $decision);
        }

        $decision->update([
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return redirect()->route('decisions.show', $decision)->with('success', [
            'key' => 'messages.success.updated',
            'replace' => ['resource' => __('messages.resources.decision')],
        ]);
    }

    /**
     * Replace {{placeholder}} tokens in a template body with decision values.
     */
    private function fillTemplatePlaceholders(string $body, Decision $decision): string
    {
        $panel = is_array($decision->panel_judges) ? array_values($decision->panel_judges) : [];

        $judgeName = static function (array $panel, int $index): string {
            return (string) ($panel[$index]['admin_user_name'] ?? '');
        };
        $judgeVote = static function (array $panel, int $index): string {
            return (string) ($panel[$index]['vote'] ?? '');
        };

        $replacements = [
            'applicant_name'     => (string) ($decision->applicant_full_name ?? ''),
            'respondent_name'    => (string) ($decision->respondent_full_name ?? ''),
            'case_number'        => (string) ($decision->case_number ?? ''),
            'case_file_number'   => (string) ($decision->case_file_number ?? ''),
            'judge_name'         => (string) ($decision->courtCase?->judge?->name ?? ''),
            'decision_date'      => \App\Support\EthiopianDate::format($decision->decision_date, fallback: ''),
            'decision_content'   => (string) ($decision->decision_content ?? ''),
            'decision_name'      => (string) ($decision->name ?? ''),

            // Panel judges (1 = presiding/first, 2 = middle, 3 = third)
            'judge_one'          => $judgeName($panel, 0),
            'judge_two'          => $judgeName($panel, 1),
            'judge_three'        => $judgeName($panel, 2),
            'judge_one_vote'     => $judgeVote($panel, 0),
            'judge_two_vote'     => $judgeVote($panel, 1),
            'judge_three_vote'   => $judgeVote($panel, 2),

            // Signature block for all panel judges (HTML)
            'judges_signatures'  => $this->buildJudgesSignatures($panel),
        ];

        foreach ($replacements as $key => $value) {
            // Allow optional spaces inside the braces: {{ key }} or {{key}}
            $body = preg_replace('/\{\{\s*' . preg_quote($key, '/') . '\s*\}\}/', $value, $body);
        }

        return $body;
    }

    /**
     * Build an HTML signature block for the panel judges, suitable for the PDF.
     */
    private function buildJudgesSignatures(array $panel): string
    {
        $labels = [__('decisions.judges.judge', ['number' => 1]), __('decisions.judges.judge', ['number' => 2]), __('decisions.judges.judge', ['number' => 3])];

        $cells = '';
        for ($i = 0; $i < 3; $i++) {
            $name = trim((string) ($panel[$i]['admin_user_name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $cells .= '<td style="width:33%;text-align:center;padding:8px;vertical-align:bottom;">'
                . '<div style="border-top:1px solid #111827;margin-top:36px;padding-top:4px;">'
                . '<div style="font-size:9pt;color:#6b7280;">' . e($labels[$i]) . '</div>'
                . '<div style="font-weight:600;">' . e($name) . '</div>'
                . '</div></td>';
        }

        if ($cells === '') {
            return '';
        }

        return '<table style="width:100%;border-collapse:collapse;margin-top:18px;"><tr>' . $cells . '</tr></table>';
    }

    public function store(Request $request)
    {
        $payload = $this->preparePayload($request->validate($this->rules()));
        $case = CourtCase::with('applicant')->findOrFail($payload['case_id']);
        $data = $this->hydrateCaseData($payload, $case);

        // New decisions always start as draft; status is changed later from the show page.
        $data['status'] = 'draft';

        Decision::create($data);

        return redirect()->route('decisions.index')->with('success', [
            'key' => 'messages.success.created',
            'replace' => ['resource' => __('messages.resources.decision')],
        ]);
    }

    public function edit(Decision $decision)
    {
        $this->ensureMiddleJudge($decision);
        $this->ensureNotPublished($decision);
        $cases = $this->loadCases($decision);
        $adminUsers = $this->loadAdminUsers();
        $judgeUsers = $this->loadJudgeUsers();
        return view('admin.decisions.edit', compact('decision', 'cases', 'adminUsers', 'judgeUsers'));
    }

    public function update(Request $request, Decision $decision)
    {
        $this->ensureMiddleJudge($decision);
        $this->ensureNotPublished($decision);
        $payload = $this->preparePayload($request->validate($this->rules()));
        $case = CourtCase::with('applicant')->findOrFail($payload['case_id']);
        $decision->update($this->hydrateCaseData($payload, $case));

        return redirect()->route('decisions.index')->with('success', [
            'key' => 'messages.success.updated',
            'replace' => ['resource' => __('messages.resources.decision')],
        ]);
    }

    /**
     * Change only the status of a decision (from the show page).
     */
    public function updateStatus(Request $request, Decision $decision)
    {
        $decision->loadMissing('courtCase');
        abort_unless($this->canAccessDecision($decision), 403);

        // Once published, the status is final and can no longer be changed.
        $this->ensureNotPublished($decision);

        $data = $request->validate([
            'status' => ['required', 'string', 'max:32', Rule::in(self::STATUS_OPTIONS)],
        ]);

        $decision->update(['status' => $data['status']]);

        return redirect()->route('decisions.show', $decision)->with('success', [
            'key' => 'messages.success.updated',
            'replace' => ['resource' => __('messages.resources.decision')],
        ]);
    }

    /**
     * Validation rules for a decision review.
     *
     * Outcomes (stored value => meaning):
     *   approve => Agree        (note optional)
     *   reject  => Have difference (note required: state the difference)
     *   improve => Improvement   (note required: the part to improve)
     */
    private function reviewRules(): array
    {
        return [
            'outcome' => ['required', Rule::in(['approve', 'reject', 'improve'])],
            'review_note' => ['nullable', 'string', 'max:2000', 'required_if:outcome,reject', 'required_if:outcome,improve'],
        ];
    }

    private function reviewMessages(): array
    {
        return [
            'review_note.required_if' => __('decisions.reviews.note_required'),
        ];
    }

    public function storeReview(Request $request, Decision $decision)
    {
        $this->ensureReviewable($decision);
        $data = $request->validate(
            $this->reviewRules(),
            $this->reviewMessages()
        );

        DecisionReview::create([
            'decision_id' => $decision->id,
            'case_number' => $decision->case_number,
            'reviewer_id' => auth()->id(),
            'review_note' => $data['review_note'] ?? null,
            'outcome' => $data['outcome'],
        ]);

        return back()->with('success', [
            'key' => 'messages.success.submitted',
            'replace' => ['resource' => __('messages.resources.review')],
        ]);
    }

    public function updateReview(Request $request, Decision $decision, DecisionReview $review)
    {
        $this->ensureReviewable($decision);
        abort_if($review->decision_id !== $decision->id, 404);
        abort_if(auth()->id() !== $review->reviewer_id, 403);

        $data = $request->validate(
            $this->reviewRules(),
            $this->reviewMessages()
        );

        $review->update([
            'outcome' => $data['outcome'],
            'review_note' => $data['review_note'] ?? null,
        ]);

        return redirect()->route('decisions.show', $decision)->with('success', [
            'key' => 'messages.success.updated',
            'replace' => ['resource' => __('messages.resources.review')],
        ]);
    }

    public function destroyReview(Decision $decision, DecisionReview $review)
    {
        $this->ensureReviewable($decision);
        abort_if($review->decision_id !== $decision->id, 404);
        abort_if(auth()->id() !== $review->reviewer_id, 403);

        $review->delete();

        return back()->with('success', [
            'key' => 'messages.success.deleted',
            'replace' => ['resource' => __('messages.resources.review')],
        ]);
    }

    public function editReview(Decision $decision, DecisionReview $review)
    {
        $decision->loadMissing('courtCase');
        abort_unless($this->canAccessDecision($decision), 403);
        abort_if($review->decision_id !== $decision->id, 404);
        abort_if(auth()->id() !== $review->reviewer_id, 403);

        return view('admin.decisions.reviews.edit', compact('decision', 'review'));
    }

    public function destroy(Decision $decision)
    {
        $this->ensureMiddleJudge($decision);
        $this->ensureNotPublished($decision);
        $decision->delete();
        return back()->with('success', [
            'key' => 'messages.success.deleted',
            'replace' => ['resource' => __('messages.resources.decision')],
        ]);
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
        $payload['decision_content'] = Purifier::clean(trim($payload['decision_content'] ?? ''), 'cases');
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
            // The applicant name is stored on the case as `title`.
            'applicant_full_name' => trim((string) ($case->title ?: ($case->applicant?->full_name ?? ''))),
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
        $decision->loadMissing('courtCase');
        abort_unless($this->canAccessDecision($decision), 403);

        if ($decision->status === 'published') {
            abort(403, 'Reviews are locked for published decisions.');
        }
    }

    /**
     * Block any modification once a decision has been published.
     */
    private function ensureNotPublished(Decision $decision): void
    {
        if ($decision->status === 'published') {
            abort(403, 'This decision is published and can no longer be changed.');
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
