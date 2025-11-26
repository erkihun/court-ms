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

        return view('applicant.dashboard', compact('total', 'pending', 'active', 'closed', 'recent'));
    }
}
