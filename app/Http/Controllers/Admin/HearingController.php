<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HearingController extends Controller
{
    /**
     * Display a paginated list of hearings for admin users.
     */
    public function index(Request $request)
    {
        $caseNumber = $request->query('case_number');
        $hearingDate = $request->query('hearing_date');

        $hearings = DB::table('case_hearings as h')
            ->select('h.*', 'c.case_number', 'c.title', 'creator.name as creator_name')
            ->leftJoin('court_cases as c', 'c.id', '=', 'h.case_id')
            ->leftJoin('users as creator', 'creator.id', '=', 'h.created_by_user_id')
            ->when($caseNumber, fn($query) => $query->where('c.case_number', 'like', '%' . trim($caseNumber) . '%'))
            ->when($hearingDate, fn($query) => $query->whereDate('h.hearing_at', $hearingDate))
            ->orderByDesc('h.hearing_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.hearings.index', [
            'hearings'    => $hearings,
            'caseNumber'  => $caseNumber,
            'hearingDate' => $hearingDate,
        ]);
    }
}
