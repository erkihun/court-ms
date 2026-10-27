<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class HearingController extends Controller
{
    /**
     * Every hearing, newest first (React, Court/Hearings/Index).
     */
    public function index(Request $request): Response
    {
        $caseNumber = trim((string) $request->query('case_number', ''));
        $hearingDate = (string) $request->query('hearing_date', '');

        $hearings = DB::table('case_hearings as h')
            ->select('h.*', 'c.case_number', 'c.title', 'creator.name as creator_name')
            ->leftJoin('court_cases as c', 'c.id', '=', 'h.case_id')
            ->leftJoin('users as creator', 'creator.id', '=', 'h.created_by_user_id')
            ->when($caseNumber !== '', fn ($query) => $query->where('c.case_number', 'like', '%'.$caseNumber.'%'))
            ->when($hearingDate !== '', fn ($query) => $query->whereDate('h.hearing_at', $hearingDate))
            ->orderByDesc('h.hearing_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (object $row): array => [
                'id' => (int) $row->id,
                'case_id' => $row->case_id !== null ? (int) $row->case_id : null,
                'case_number' => $row->case_number,
                'case_title' => $row->title,
                'type' => $row->type ?? null,
                'location' => $row->location ?? null,
                'notes' => $row->notes ?? null,
                'hearing_at' => $row->hearing_at ? Carbon::parse($row->hearing_at)->toIso8601String() : null,
                'creator_name' => $row->creator_name,
            ]);

        return Inertia::render('Court/Hearings/Index', [
            'hearings' => $hearings,
            'filters' => ['case_number' => $caseNumber, 'hearing_date' => $hearingDate],
        ]);
    }
}
