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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display a system-wide report dashboard.
     */
    public function index(Request $request)
    {
        $caseStatuses = ['pending', 'active', 'adjourned', 'dismissed', 'closed'];
        $judges = User::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $selectedStatus = $request->input('status');
        $selectedJudge = $request->input('judge_id');

        $caseFilter = CourtCase::query();
        if ($startDate) {
            $caseFilter->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $caseFilter->where('created_at', '<=', $endDate . ' 23:59:59');
        }
        if ($selectedStatus) {
            $caseFilter->where('status', $selectedStatus);
        }
        if ($selectedJudge) {
            $caseFilter->where('judge_id', $selectedJudge);
        }

        $filteredCaseCount = (clone $caseFilter)->count();
        $filteredCaseStatus = (clone $caseFilter)
            ->select('status', DB::raw('count(*) as total'))
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

        return view('admin.reports.index', [
            'summaryCards' => $summaryCards,
            'caseStatusBreakdown' => $caseStatusBreakdown,
            'caseTypeBreakdown' => $caseTypeBreakdown,
            'appealStatusBreakdown' => $appealStatusBreakdown,
            'decisionStatusBreakdown' => $decisionStatusBreakdown,
            'letterApprovalBreakdown' => $letterApprovalBreakdown,
            'applicantStatusBreakdown' => $applicantStatusBreakdown,
            'userStatusBreakdown' => $userStatusBreakdown,
            'recentCases' => $recentCases,
            'recentAppeals' => $recentAppeals,
            'caseStatuses' => $caseStatuses,
            'judges' => $judges,
            'filteredCaseCount' => $filteredCaseCount,
            'filteredCaseStatus' => $filteredCaseStatus,
            'casesByJudge' => $casesByJudge,
            'filterParams' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $selectedStatus,
                'judge_id' => $selectedJudge,
            ],
        ]);
    }
}
