<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourtCase;
use App\Models\Decision;
use App\Models\User;
use Illuminate\Http\Request;
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
        $cases = $this->loadCases();
        $adminUsers = $this->loadAdminUsers();
        return view('admin.decisions.create', compact('cases', 'adminUsers'));
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
        $cases = $this->loadCases($decision);
        $adminUsers = $this->loadAdminUsers();
        return view('admin.decisions.edit', compact('decision', 'cases', 'adminUsers'));
    }

    public function update(Request $request, Decision $decision)
    {
        $payload = $this->preparePayload($request->validate($this->rules()));
        $case = CourtCase::with('applicant')->findOrFail($payload['case_id']);
        $decision->update($this->hydrateCaseData($payload, $case));

        return redirect()->route('decisions.index')->with('success', 'Decision updated.');
    }

    public function destroy(Decision $decision)
    {
        $decision->delete();
        return back()->with('success', 'Decision deleted.');
    }

    private function rules(): array
    {
        return [
            'case_id' => ['required', 'exists:court_cases,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'decision_content' => ['required', 'string', 'max:5000'],
            'decision_date' => ['required', 'date'],
            'status' => ['required', 'string', 'max:32', Rule::in(self::STATUS_OPTIONS)],
            'reviewing_admin_user_names' => ['nullable', 'array'],
            'reviewing_admin_user_names.*' => ['string', 'max:255'],
        ];
    }

    private function preparePayload(array $payload): array
    {
        $payload['description'] = isset($payload['description']) ? strip_tags(trim($payload['description'])) : null;
        $payload['decision_content'] = trim($payload['decision_content'] ?? '');
        $payload['reviewing_admin_user_names'] = $payload['reviewing_admin_user_names'] ?? [];
        return $payload;
    }

    private function hydrateCaseData(array $payload, CourtCase $case): array
    {
        $reviewerName = auth()->user()?->name ?? 'Admin';
        $reviewers = $this->normalizeReviewerNames($payload['reviewing_admin_user_names'] ?? []);
        if (empty($reviewers)) {
            $reviewers = [$reviewerName];
        }

        return array_merge($payload, [
            'case_number' => $case->case_number,
            'applicant_full_name' => trim((string) ($case->applicant?->full_name ?? '')),
            'respondent_full_name' => $case->respondent_name,
            'case_filed_date' => $case->filing_date?->toDateString() ?? $case->created_at?->toDateString(),
            'reviewing_admin_user_id' => auth()->id(),
            'reviewing_admin_user_name' => $reviewers[0] ?? $reviewerName,
            'reviewing_admin_user_names' => $reviewers,
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

    private function loadCases(Decision $decision = null)
    {
        $cases = CourtCase::with('applicant')
            ->orderByDesc('filing_date')
            ->limit(self::CASE_LIMIT)
            ->get(['id', 'case_number', 'title', 'filing_date', 'applicant_id', 'respondent_name']);

        if ($decision && $decision->court_case_id && !$cases->contains('id', $decision->court_case_id)) {
            $current = CourtCase::find($decision->court_case_id, ['id', 'case_number', 'title', 'filing_date']);
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
}
