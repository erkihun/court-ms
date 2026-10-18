<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\CaseHearing;
use App\Models\CourtCase;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\EthiopianDate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Mews\Purifier\Facades\Purifier;

class PublicSignageController extends Controller
{
    /**
     * Digital signage dashboard (1920x1080) for public display.
     */
    public function show(): Response
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
        $statusLabels = (array) __('signage.status_labels');
        $applicantName = function (?CourtCase $case): string {
            $name = trim((string) ($case?->applicant?->full_name ?? ''));

            // Public screen: keep names short.
            return $name !== '' ? Str::limit($name, 18) : __('signage.unknown_applicant');
        };

        return Inertia::render('Public/Signage', [
            'appName' => (string) ($settings?->app_name ?: config('app.name')),
            'logoUrl' => $settings?->logo_path ? Storage::url($settings->logo_path) : null,
            'now' => EthiopianDate::format($now, withTime: true),
            'updatedAt' => EthiopianDate::formatTime($now, timeFormat: 'h:i:s A'),
            'totalCases' => $totalCases,
            'statuses' => $statusCounts->map(function ($row) use ($statusLabels, $totalCases): array {
                $key = strtolower((string) $row->label);

                return [
                    'key' => $key,
                    'label' => (string) ($statusLabels[$key] ?? $row->label),
                    'total' => (int) $row->total,
                    'percent' => $totalCases > 0 ? (int) round($row->total / $totalCases * 100) : 0,
                ];
            })->values(),
            'categories' => $categoryCounts->map(fn (object $row): array => ['name' => (string) $row->name, 'total' => (int) $row->total])->values(),
            'todayCases' => $todayCases->map(fn (CourtCase $case): array => [
                'id' => (int) $case->id,
                'caseNumber' => (string) ($case->case_number ?: __('signage.case_number').' '.$case->id),
                'applicant' => $applicantName($case),
                'caseType' => $case->caseType?->name ?? __('signage.general_case_type'),
            ])->values(),
            'todayHearings' => $todayHearings->map(fn (CaseHearing $hearing): array => [
                'id' => (int) $hearing->id,
                'caseNumber' => (string) ($hearing->courtCase?->case_number ?: __('signage.case_number').' '.$hearing->case_id),
                'when' => trim(EthiopianDate::formatDate($hearing->hearing_at, '-').' '.EthiopianDate::formatTime($hearing->hearing_at)),
                'caseType' => $hearing->courtCase?->caseType?->name ?? __('signage.hearings'),
            ])->values(),
            'announcements' => $activeAnnouncements->map(fn (Announcement $announcement): array => [
                'id' => (int) $announcement->id,
                'title' => (string) $announcement->title,
                'date' => EthiopianDate::formatDate($announcement->created_at),
                // Same purifier profile the Blade signage used.
                'contentHtml' => Purifier::clean($announcement->content ?? '', 'default'),
            ])->values(),
            'staff' => $activeStaff->map(fn (User $person): array => [
                'id' => (int) $person->id,
                'name' => (string) $person->name,
                'position' => $person->position ?: null,
                'avatarUrl' => $person->avatar_path ? Storage::url($person->avatar_path) : null,
            ])->values(),
        ]);
    }
}
