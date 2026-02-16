<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\CaseHearing;
use App\Models\CourtCase;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PublicSignageController extends Controller
{
    /**
     * Digital signage dashboard (1920x1080) for public display.
     */
    public function show()
    {
        $tz = config('app.timezone', 'UTC');
        $now = Carbon::now($tz);
        $startOfDay = $now->copy()->startOfDay()->timezone('UTC');
        $endOfDay = $now->copy()->endOfDay()->timezone('UTC');
        $todayDate = $now->toDateString();

        $totalCases = CourtCase::count();

        $statusCounts = CourtCase::select(
            DB::raw("COALESCE(NULLIF(status, ''), 'Unspecified') as label"),
            DB::raw('count(*) as total')
        )
            ->groupBy('label')
            ->orderByDesc('total')
            ->get();

        $categoryCounts = DB::table('court_cases as c')
            ->leftJoin('case_types as t', 't.id', '=', 'c.case_type_id')
            ->select(
                DB::raw("COALESCE(t.name, 'Uncategorized') as name"),
                DB::raw('count(*) as total')
            )
            ->groupBy('name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $todayCases = CourtCase::select('id', 'case_number', 'title', 'status', 'case_type_id', 'created_at', 'applicant_id')
            ->with([
                'caseType:id,name',
                'applicant:id,first_name,middle_name,last_name',
            ])
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->latest('created_at')
            ->limit(8)
            ->get();

        $todayHearings = CaseHearing::select('id', 'case_id', 'hearing_at')
            ->with([
                'courtCase:id,case_number,case_type_id,title,applicant_id',
                'courtCase.caseType:id,name',
                'courtCase.applicant:id,first_name,middle_name,last_name',
            ])
            ->whereDate('hearing_at', $todayDate)
            ->orderBy('hearing_at')
            ->limit(8)
            ->get();

        $activeAnnouncements = Announcement::select('id', 'title', 'content', 'created_at')
            ->where('status', 'active')
            ->latest('created_at')
            ->limit(3)
            ->get();

        $activeStaff = User::active()
            ->select('id', 'name', 'position', 'avatar_path')
            ->orderBy('name')
            ->limit(12)
            ->get();

        $settings = SystemSetting::query()->first();

        return view('public.signage', [
            'now' => $now,
            'settings' => $settings,
            'totalCases' => $totalCases,
            'statusCounts' => $statusCounts,
            'categoryCounts' => $categoryCounts,
            'todayCases' => $todayCases,
            'todayHearings' => $todayHearings,
            'activeAnnouncements' => $activeAnnouncements,
            'activeStaff' => $activeStaff,
        ]);
    }
}
