<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Decision;
use App\Support\DecisionPdf;
use Illuminate\Support\Facades\DB;

class ApplicantDashboardController extends Controller
{
    /**
     * Download the sealed decision PDF for a case the applicant owns.
     * Only available once the decision is approved AND published.
     */
    public function downloadDecision(Decision $decision)
    {
        $applicantId = auth('applicant')->id();

        $ownsCase = DB::table('court_cases')
            ->where('applicant_id', $applicantId)
            ->where('case_number', $decision->case_number)
            ->exists();

        abort_unless($ownsCase, 404);
        abort_unless($decision->isDownloadableByParties(), 403);

        return DecisionPdf::render($decision)->download(DecisionPdf::filename($decision));
    }

    public function index()
    {
        $applicantId = auth('applicant')->id();

        $counts = DB::table('court_cases')
            ->where('applicant_id', $applicantId)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'pending') as pending,
                SUM(status = 'active') as active,
                SUM(status IN ('closed', 'dismissed')) as closed
            ")
            ->first();

        $total   = (int) ($counts->total ?? 0);
        $pending = (int) ($counts->pending ?? 0);
        $active  = (int) ($counts->active ?? 0);
        $closed  = (int) ($counts->closed ?? 0);

        $caseNumbers = DB::table('court_cases')
            ->where('applicant_id', $applicantId)
            ->pluck('case_number')
            ->filter()
            ->values();

        $lettersCount = 0;
        $responsesCount = 0;
        $decisionsCount = 0;
        $responseRepliesCount = 0;

        $caseLetters = collect();
        $caseResponses = collect();
        $caseDecisions = collect();
        $responseReplies = collect();

        if ($caseNumbers->isNotEmpty()) {
            $lettersCount = DB::table('letters')
                ->whereIn('case_number', $caseNumbers)
                ->where('approval_status', 'approved')
                ->count();

            $responsesCount = DB::table('respondent_responses')
                ->whereIn('case_number', $caseNumbers)
                ->where('review_status', 'accepted')
                ->count();

            $decisionsCount = DB::table('decisions')
                ->whereIn('case_number', $caseNumbers)
                ->count();

            $caseLetters = DB::table('letters as l')
                ->leftJoin('letter_templates as lt', 'lt.id', '=', 'l.letter_template_id')
                ->select(
                    'l.id',
                    'l.subject',
                    'l.reference_number',
                    'l.case_number',
                    'l.created_at',
                    'lt.title as template_title'
                )
                ->whereIn('l.case_number', $caseNumbers)
                ->where('l.approval_status', 'approved')
                ->orderByDesc('l.created_at')
                ->limit(5)
                ->get();

            $caseResponses = DB::table('respondent_responses as rr')
                ->leftJoin('court_cases as cc', function ($join) {
                    $join->on('cc.case_number', '=', 'rr.case_number');
                })
                ->select('rr.id', 'rr.title', 'rr.case_number', 'rr.created_at', 'cc.id as case_id')
                ->whereIn('rr.case_number', $caseNumbers)
                ->where('rr.review_status', 'accepted')
                ->orderByDesc('rr.created_at')
                ->limit(5)
                ->get();

            $caseDecisions = DB::table('decisions')
                ->select('id', 'name', 'case_number', 'decision_date', 'status', 'approved_at', 'created_at')
                ->whereIn('case_number', $caseNumbers)
                ->orderByDesc('decision_date')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        }

        $responseRepliesCount = DB::table('applicant_response_replies')
            ->where('applicant_id', $applicantId)
            ->count();

        $responseReplies = DB::table('applicant_response_replies as arr')
            ->join('court_cases as cc', 'cc.id', '=', 'arr.case_id')
            ->join('respondent_responses as rr', 'rr.id', '=', 'arr.respondent_response_id')
            ->select(
                'arr.id',
                'arr.case_id',
                'arr.respondent_response_id',
                'arr.review_status',
                'arr.review_note',
                'arr.created_at',
                'cc.case_number',
                'rr.response_number'
            )
            ->where('arr.applicant_id', $applicantId)
            ->orderByDesc('arr.created_at')
            ->limit(5)
            ->get();

        $recent = DB::table('court_cases')
            ->leftJoin('case_types', 'case_types.id', '=', 'court_cases.case_type_id')

            ->where('court_cases.applicant_id', $applicantId)
            ->select(
                'court_cases.id',
                'court_cases.case_number',
                'court_cases.title',
                'court_cases.status',
                'case_types.name as case_type',

                'court_cases.created_at'
            )
            ->orderByDesc('court_cases.created_at')
            ->limit(6)
            ->get();

        return view('applicant.dashboard', compact(
            'total',
            'pending',
            'active',
            'closed',
            'recent',
            'caseLetters',
            'caseResponses',
            'caseDecisions',
            'responseReplies',
            'lettersCount',
            'responsesCount',
            'decisionsCount',
            'responseRepliesCount'
        ));
    }
}
