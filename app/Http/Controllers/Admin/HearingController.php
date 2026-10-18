<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\EthiopianDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class HearingController extends Controller
{
    /**
     * Display a paginated list of hearings for admin users.
     */
    public function index(Request $request): Response
    {
        $caseNumber = trim((string) $request->query('case_number', ''));
        $hearingDate = trim((string) $request->query('hearing_date', ''));
        if ($hearingDate !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $hearingDate)) {
            $hearingDate = '';
        }

        $hearings = DB::table('case_hearings as h')
            ->select('h.id', 'h.hearing_at', 'h.type', 'h.location', 'h.notes', 'c.case_number', 'c.title', 'creator.name as creator_name')
            ->leftJoin('court_cases as c', 'c.id', '=', 'h.case_id')
            ->leftJoin('users as creator', 'creator.id', '=', 'h.created_by_user_id')
            ->when($caseNumber !== '', fn ($query) => $query->where('c.case_number', 'like', '%'.$caseNumber.'%'))
            ->when($hearingDate !== '', fn ($query) => $query->whereDate('h.hearing_at', $hearingDate))
            ->orderByDesc('h.hearing_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (object $hearing): array => [
                'id' => (int) $hearing->id,
                'caseNumber' => $hearing->case_number,
                'caseTitle' => $hearing->title,
                'when' => $hearing->hearing_at ? EthiopianDate::format($hearing->hearing_at, withTime: true) : null,
                'type' => $hearing->type ?: null,
                'location' => $hearing->location ?: null,
                'notes' => $hearing->notes ?: null,
                'creator' => $hearing->creator_name,
            ]);

        return Inertia::render('Admin/Hearings/Index', [
            'hearings' => $hearings,
            'filters' => ['case_number' => $caseNumber, 'hearing_date' => $hearingDate],
        ]);
    }
}
