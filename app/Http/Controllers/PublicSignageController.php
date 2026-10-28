<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\CaseHearing;
use App\Models\CourtCase;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Mews\Purifier\Facades\Purifier;

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

        $todayCases = CourtCase::select('id', 'case_number', 'status', 'case_type_id', 'created_at', 'applicant_id')
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

        return Inertia::render('Court/Public/Signage', [
            'now' => $now->toIso8601String(),
            'appName' => (string) ($settings->app_name ?? config('app.name', 'Justice Portal')),
            'logoUrl' => $settings?->logo_path ? Storage::url($settings->logo_path) : asset('favicon.ico'),
            'totalCases' => (int) $totalCases,
            'statusCounts' => $statusCounts->map(fn ($stat): array => ['label' => (string) $stat->label, 'total' => (int) $stat->total])->values(),
            'categoryCounts' => $categoryCounts->map(fn ($cat): array => ['name' => (string) $cat->name, 'total' => (int) $cat->total])->values(),
            'todayCases' => $todayCases->map(function (CourtCase $case): array {
                // A public screen shows initials only: the party can recognise
                // their case next to its number, a passer-by cannot identify anyone.
                $applicant = collect([$case->applicant?->first_name, $case->applicant?->middle_name, $case->applicant?->last_name])
                    ->map(fn (?string $part): string => trim((string) $part))
                    ->filter(fn (string $part): bool => $part !== '')
                    ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)).'.')
                    ->implode(' ');

                return [
                    'id' => $case->id,
                    'case_number' => $case->case_number,
                    'applicant' => $applicant !== '' ? $applicant : null,
                    'case_type' => $case->caseType?->name,
                ];
            })->values(),
            'todayHearings' => $todayHearings->map(function (CaseHearing $hearing): array {
                $raw = (string) ($hearing->getRawOriginal('hearing_at') ?? '');

                return [
                    'id' => $hearing->id,
                    'case_id' => $hearing->case_id,
                    'case_number' => $hearing->courtCase?->case_number,
                    'case_type' => $hearing->courtCase?->caseType?->name,
                    'hearing_at' => $hearing->hearing_at ? Carbon::parse($hearing->hearing_at)->toIso8601String() : null,
                    'time' => strlen($raw) >= 19 ? substr($raw, 11, 8) : null,
                ];
            })->values(),
            'activeAnnouncements' => $activeAnnouncements->map(fn (Announcement $announcement): array => [
                'id' => $announcement->id,
                'title' => (string) $announcement->title,
                // Keep announcement HTML within the configured default profile.
                'content' => (string) Purifier::clean($announcement->content ?? '', 'default'),
                'created_at' => $announcement->created_at?->toIso8601String(),
            ])->values(),
            'activeStaff' => $activeStaff->map(fn (User $person): array => [
                'id' => $person->id,
                'name' => (string) $person->name,
                'position' => $person->position,
                'avatar_url' => $person->avatar_path ? Storage::url($person->avatar_path) : null,
            ])->values(),
            // The Blade board refreshed itself every 120 seconds (meta refresh).
            'refreshSeconds' => 120,
        ]);
    }
}
