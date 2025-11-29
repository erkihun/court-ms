<?php

namespace App\Http\Controllers\Respondent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CaseSearchController extends Controller
{
    public function index(Request $request)
    {
        $caseNumber = trim((string) $request->get('case_number', ''));
        $case = null;

        if ($caseNumber !== '') {
            $case = DB::table('court_cases')->where('case_number', $caseNumber)->first();

            if ($case) {
                $history = collect(session('respondent_viewed_cases', []));
                $history = $history->prepend($caseNumber)->unique()->take(12);
                session(['respondent_viewed_cases' => $history->values()->all()]);
            }
        }

        return view('respondant.cases.search', [
            'caseNumber' => $caseNumber,
            'case' => $case,
        ]);
    }

    public function show(string $caseNumber)
    {
        $case = DB::table('court_cases as c')
            ->leftJoin('case_types as ct', 'ct.id', '=', 'c.case_type_id')
            ->select('c.*', 'ct.name as case_type')
            ->where('c.case_number', trim($caseNumber))
            ->first();

        abort_if(!$case, 404);

        $caseId = $case->id;

        $timeline = DB::table('case_status_logs')
            ->where('case_id', $caseId)
            ->orderBy('created_at')
            ->get();

        $files = DB::table('case_files')
            ->where('case_id', $caseId)
            ->orderByDesc('created_at')
            ->get();

        $msgs = DB::table('case_messages as m')
            ->leftJoin('users as u', 'u.id', '=', 'm.sender_user_id')
            ->select('m.*', 'u.name as admin_name')
            ->where('m.case_id', $caseId)
            ->orderBy('m.created_at')
            ->get();

        $hearings = DB::table('case_hearings')
            ->where('case_id', $caseId)
            ->orderBy('hearing_at')
            ->get();

        $docs = DB::table('case_evidences')
            ->where('case_id', $caseId)
            ->where('type', 'document')
            ->orderBy('id')
            ->get();

        $witnesses = DB::table('case_witnesses')
            ->where('case_id', $caseId)
            ->orderBy('id')
            ->get();

        $audits = DB::table('case_audits')
            ->where('case_id', $caseId)
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('respondant.cases.show', compact(
            'case',
            'timeline',
            'files',
            'msgs',
            'hearings',
            'docs',
            'witnesses',
            'audits'
        ));
    }

    public function myCases()
    {
        $caseNumbers = session('respondent_viewed_cases', []);

        $cases = collect();
        if (!empty($caseNumbers)) {
            $cases = DB::table('court_cases as c')
                ->leftJoin('case_types as ct', 'ct.id', '=', 'c.case_type_id')
                ->select('c.*', 'ct.name as case_type')
                ->whereIn('c.case_number', $caseNumbers)
                ->get()
                ->sortBy(fn($c) => array_search($c->case_number, $caseNumbers));
        }

        return view('respondant.cases.my', [
            'cases' => $cases->values(),
        ]);
    }
}
