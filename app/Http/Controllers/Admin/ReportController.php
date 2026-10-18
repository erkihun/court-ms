<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\BenchNote;
use App\Models\CaseType;
use App\Models\CourtCase;
use App\Models\Decision;
use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\Respondent;
use App\Models\Team;
use App\Models\User;
use App\Support\EthiopianDate;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    /**
     * Display a system-wide report dashboard.
     */
    public function index(Request $request): Response
    {
        $caseStatuses = ['pending', 'active', 'adjourned', 'dismissed', 'closed'];
        $judges = User::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        $startDate = $this->dateFilter($request->input('start_date'));
        $endDate = $this->dateFilter($request->input('end_date'));
        $selectedStatus = in_array($request->input('status'), $caseStatuses, true) ? $request->input('status') : null;
        $selectedJudge = $request->integer('judge_id') ?: null;

        $caseFilter = CourtCase::query();
        // Qualified: the per-judge query joins users, which also has status and created_at.
        if ($startDate) {
            $caseFilter->where('court_cases.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $caseFilter->where('court_cases.created_at', '<=', $endDate . ' 23:59:59');
        }
        if ($selectedStatus) {
            $caseFilter->where('court_cases.status', $selectedStatus);
        }
        if ($selectedJudge) {
            $caseFilter->where('court_cases.judge_id', $selectedJudge);
        }

        $filteredCaseCount = (clone $caseFilter)->count();
        $filteredCaseStatus = (clone $caseFilter)
            ->select('court_cases.status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $casesByJudge = (clone $caseFilter)
            ->join('users as u', 'u.id', '=', 'court_cases.judge_id')
            ->select('u.id as judge_id', 'u.name as judge_name', DB::raw('count(*) as total'))
            ->groupBy('u.id', 'u.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $caseStatusBreakdown = CourtCase::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $caseTypeBreakdown = CaseType::query()
            ->leftJoin('court_cases', 'case_types.id', '=', 'court_cases.case_type_id')
            ->select('case_types.id', 'case_types.name', DB::raw('count(court_cases.id) as total'))
            ->groupBy('case_types.id', 'case_types.name')
            ->orderByDesc('total')
            ->get();

        $appealStatusBreakdown = DB::table('appeals')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $decisionStatusBreakdown = Decision::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $letterApprovalBreakdown = Letter::query()
            ->selectRaw('COALESCE(approval_status, "pending") as status, count(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $applicantStatusBreakdown = Applicant::query()
            ->selectRaw('CASE WHEN is_active THEN "active" ELSE "inactive" END as state, count(*) as total')
            ->groupBy('state')
            ->get();

        $userStatusBreakdown = User::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $recentCases = CourtCase::with([
            'applicant:id,first_name,middle_name,last_name',
            'caseType:id,name',
        ])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentAppeals = DB::table('appeals as a')
            ->join('court_cases as c', 'c.id', '=', 'a.court_case_id')
            ->select('a.id', 'a.appeal_number', 'a.status', 'a.created_at', 'a.title', 'c.case_number', 'c.id as case_id')
            ->orderByDesc('a.created_at')
            ->limit(5)
            ->get();

        $summaryCards = [
            ['key' => 'cases_total', 'value' => CourtCase::count()],
            ['key' => 'cases_active', 'value' => CourtCase::where('status', 'active')->count()],
            ['key' => 'cases_pending', 'value' => CourtCase::where('status', 'pending')->count()],
            ['key' => 'applicants', 'value' => Applicant::count()],
            ['key' => 'respondents', 'value' => Respondent::count()],
            ['key' => 'appeals', 'value' => DB::table('appeals')->count()],
            ['key' => 'decisions', 'value' => Decision::count()],
            ['key' => 'bench_notes', 'value' => BenchNote::count()],
            ['key' => 'letters', 'value' => Letter::count()],
            ['key' => 'letter_templates', 'value' => LetterTemplate::count()],
            ['key' => 'teams', 'value' => Team::count()],
            ['key' => 'users', 'value' => User::count()],
            ['key' => 'hearings', 'value' => DB::table('case_hearings')->count()],
            ['key' => 'respondent_responses', 'value' => DB::table('respondent_responses')->count()],
        ];

        $caseLabel = fn (?string $status): string => $this->label($status, 'cases.status.');
        $plainLabel = fn (?string $status): string => $this->label($status, null);

        return Inertia::render('Admin/Reports/Index', [
            'summaryCards' => $summaryCards,
            'filtered' => [
                'count' => $filteredCaseCount,
                'byStatus' => $this->breakdown($filteredCaseStatus, 'status', $caseLabel),
                'byJudge' => $casesByJudge->map(fn (object $row): array => ['label' => (string) $row->judge_name, 'total' => (int) $row->total])->values(),
            ],
            'breakdowns' => [
                'cases_status' => $this->breakdown($caseStatusBreakdown, 'status', $caseLabel),
                'cases_types' => $caseTypeBreakdown->map(fn (object $row): array => ['label' => $row->name ?: __('reports.unknown'), 'total' => (int) $row->total])->values(),
                'appeals_status' => $this->breakdown($appealStatusBreakdown, 'status', $plainLabel),
                'decisions_status' => $this->breakdown($decisionStatusBreakdown, 'status', $plainLabel),
                'letters_status' => $this->breakdown($letterApprovalBreakdown, 'status', $plainLabel),
                'applicants_status' => $this->breakdown($applicantStatusBreakdown, 'state', $plainLabel),
                'users_status' => $this->breakdown($userStatusBreakdown, 'status', $plainLabel),
            ],
            'recentCases' => $recentCases->map(fn (CourtCase $case): array => [
                'id' => (int) $case->id,
                'number' => (string) $case->case_number,
                'title' => (string) $case->title,
                'status' => $caseLabel($case->status),
                'party' => $case->applicant?->full_name ?? __('reports.unknown'),
                'when' => EthiopianDate::smartRelative($case->created_at),
            ])->values(),
            'recentAppeals' => $recentAppeals->map(fn (object $appeal): array => [
                'id' => (int) $appeal->id,
                'number' => (string) $appeal->appeal_number,
                'caseNumber' => (string) $appeal->case_number,
                'title' => (string) $appeal->title,
                'status' => $plainLabel($appeal->status),
                'when' => EthiopianDate::smartRelative($appeal->created_at),
            ])->values(),
            'caseStatuses' => collect($caseStatuses)->map(fn (string $status): array => ['value' => $status, 'label' => $caseLabel($status)])->values(),
            'judges' => $judges->map(fn (User $judge): array => ['id' => (int) $judge->id, 'name' => (string) $judge->name])->values(),
            'filters' => [
                'start_date' => $startDate ?? '',
                'end_date' => $endDate ?? '',
                'status' => $selectedStatus ?? '',
                'judge_id' => $selectedJudge ? (string) $selectedJudge : '',
            ],
        ]);
    }

    /** @return Collection<int, array{label: string, total: int}> */
    private function breakdown(Collection $rows, string $column, callable $label): Collection
    {
        return $rows->map(fn (object $row): array => ['label' => $label($row->{$column}), 'total' => (int) $row->total])->values();
    }

    /** Translated label for a raw status value, falling back to a readable form of the value. */
    private function label(?string $value, ?string $translationPrefix): string
    {
        if ($value === null || $value === '') {
            return __('reports.unknown');
        }

        if ($translationPrefix !== null && Lang::has($translationPrefix.$value)) {
            return __($translationPrefix.$value);
        }

        return Str::of($value)->replace('_', ' ')->title()->toString();
    }

    private function dateFilter(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : null;
    }
}
