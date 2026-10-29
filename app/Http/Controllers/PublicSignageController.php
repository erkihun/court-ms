<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\CourtCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Mews\Purifier\Facades\Purifier;

class PublicSignageController extends Controller
{
    /** How many of today's sittings the board pages through, and how many filings and case types it lists. */
    public const HEARING_LIMIT = 60;

    public const FILING_LIMIT = 6;

    public const CASE_TYPE_LIMIT = 5;

    /**
     * The court's wall board (a 1920x1080 screen in the lobby): today's cause
     * list with times and rooms, today's figures, the caseload and the active
     * notices. It is public, so it shows case numbers and types, a filing
     * party's initials only, and no staff: the board once listed every active
     * account as "on duty", which was neither true nor the public's to see.
     */
    public function show()
    {
        $tz = config('app.timezone', 'UTC');
        $now = Carbon::now($tz);
        // Timestamps are stored in the app's time zone, so today is bounded there too: converted
        // to UTC, the window lost every case filed after 21:00 in Addis Ababa.
        $startOfDay = $now->copy()->startOfDay();
        $endOfDay = $now->copy()->endOfDay();

        $statusCounts = CourtCase::select(
            DB::raw("COALESCE(NULLIF(status, ''), 'unspecified') as label"),
            DB::raw('count(*) as total')
        )
            ->groupBy('label')
            ->orderByDesc('total')
            ->get();

        $categoryCounts = DB::table('court_cases as c')
            ->leftJoin('case_types as t', 't.id', '=', 'c.case_type_id')
            ->select('t.name', DB::raw('count(*) as total'))
            ->groupBy('t.name')
            ->orderByDesc('total')
            ->limit(self::CASE_TYPE_LIMIT)
            ->get();

        $filedToday = CourtCase::query()->whereBetween('created_at', [$startOfDay, $endOfDay]);

        $todayCases = (clone $filedToday)
            ->select('id', 'case_number', 'case_type_id', 'created_at', 'applicant_id')
            ->with(['caseType:id,name', 'applicant:id,first_name,middle_name,last_name'])
            ->latest('created_at')
            ->limit(self::FILING_LIMIT)
            ->get();

        $hearingsToday = DB::table('case_hearings as h')
            ->join('court_cases as c', 'c.id', '=', 'h.case_id')
            ->whereBetween('h.hearing_at', [$startOfDay, $endOfDay]);

        $todayHearings = (clone $hearingsToday)
            ->leftJoin('case_types as t', 't.id', '=', 'c.case_type_id')
            ->orderBy('h.hearing_at')
            ->orderBy('h.id')
            ->limit(self::HEARING_LIMIT)
            ->get(['h.id', 'h.hearing_at', 'h.type', 'h.location', 'c.case_number', 't.name as case_type']);

        $announcements = Announcement::select('id', 'title', 'content', 'created_at')
            ->where('status', 'active')
            ->latest('created_at')
            ->limit(5)
            ->get();

        return Inertia::render('Court/Public/Signage', [
            'now' => $now->toIso8601String(),
            'timezone' => $tz,
            'totalCases' => CourtCase::count(),
            'filedToday' => (clone $filedToday)->count(),
            'hearingsToday' => (clone $hearingsToday)->count(),
            'statusCounts' => $statusCounts->map(fn ($stat): array => ['label' => (string) $stat->label, 'total' => (int) $stat->total])->values(),
            'categoryCounts' => $categoryCounts->map(fn ($cat): array => ['name' => $cat->name, 'total' => (int) $cat->total])->values(),
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
            'todayHearings' => $todayHearings->map(function (object $row): array {
                // The stored wall-clock time, as the public site's hearing list shows it.
                $raw = (string) $row->hearing_at;

                return [
                    'id' => (int) $row->id,
                    'time' => strlen($raw) >= 16 ? substr($raw, 11, 5) : null,
                    'case_number' => $row->case_number,
                    'case_type' => $row->case_type,
                    'type' => $row->type,
                    'location' => $row->location,
                ];
            })->values(),
            'announcements' => $announcements->map(fn (Announcement $announcement): array => [
                'id' => $announcement->id,
                'title' => (string) $announcement->title,
                // The board reads a notice as one line of text; the full notice is on the news page.
                'summary' => $this->plainText((string) $announcement->content, 220),
                'created_at' => $announcement->created_at?->toIso8601String(),
            ])->values(),
            'refreshSeconds' => 60,
        ]);
    }

    /** A notice's HTML as plain text: cleaned, tags dropped with a space each, whitespace folded, shortened. */
    private function plainText(string $html, int $limit): string
    {
        $clean = (string) Purifier::clean($html, 'default');
        $text = html_entity_decode(strip_tags(str_replace('<', ' <', $clean)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return Str::limit(trim((string) preg_replace('/\s+/u', ' ', $text)), $limit);
    }
}
