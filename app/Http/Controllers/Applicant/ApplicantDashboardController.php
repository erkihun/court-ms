<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;

class ApplicantDashboardController extends Controller
{
    public function index()
    {
        $applicantId = auth('applicant')->id();

        $total   = DB::table('court_cases')->where('applicant_id', $applicantId)->count();
        $pending = DB::table('court_cases')->where('applicant_id', $applicantId)->where('status', 'pending')->count();
        $active  = DB::table('court_cases')->where('applicant_id', $applicantId)->where('status', 'active')->count();
        $closed  = DB::table('court_cases')->where('applicant_id', $applicantId)->whereIn('status', ['closed', 'dismissed'])->count();

        $caseNumbers = DB::table('court_cases')
            ->where('applicant_id', $applicantId)
            ->pluck('case_number')
            ->filter()
            ->values();

        $lettersCount = 0;
        $responsesCount = 0;
        $decisionsCount = 0;

        $caseLetters = collect();
        $caseResponses = collect();
        $caseDecisions = collect();

        if ($caseNumbers->isNotEmpty()) {
            $lettersCount = DB::table('letters')
                ->whereIn('case_number', $caseNumbers)
                ->where('approval_status', 'approved')
                ->count();

            $responsesCount = DB::table('respondent_responses')
                ->whereIn('case_number', $caseNumbers)
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

            $caseResponses = DB::table('respondent_responses')
                ->select('id', 'title', 'case_number', 'created_at')
                ->whereIn('case_number', $caseNumbers)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            $caseDecisions = DB::table('decisions')
                ->select('id', 'name', 'case_number', 'decision_date', 'status', 'created_at')
                ->whereIn('case_number', $caseNumbers)
                ->orderByDesc('decision_date')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        }

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
            'lettersCount',
            'responsesCount',
            'decisionsCount'
        ));
    }
}
