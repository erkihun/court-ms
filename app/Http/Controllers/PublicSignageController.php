<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\CourtCase;
use App\Support\Ui\PublicSiteChrome;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Mews\Purifier\Facades\Purifier;

class PublicSignageController extends Controller
{
    /** How many of today's sittings the board pages through. */
    public const HEARING_LIMIT = 60;

    /**
     * The court's lobby display (a portrait floor screen, or a landscape wall
     * one): today's cause list with times and rooms and the next sitting,
     * today's figures, the caseload, the registry's hours and the active
     * notices. It is public, so it shows case numbers and types, no party and
     * no staff: the board once listed every active account as "on duty",
     * which was neither true nor the public's to see.
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

        $footer = PublicSiteChrome::props()['footer'] ?? [];

        return Inertia::render('Court/Public/Signage', [
            'now' => $now->toIso8601String(),
            'timezone' => $tz,
            'totalCases' => CourtCase::count(),
            'filedToday' => CourtCase::query()->whereBetween('created_at', [$startOfDay, $endOfDay])->count(),
            'hearingsToday' => (clone $hearingsToday)->count(),
            'statusCounts' => $statusCounts->map(fn ($stat): array => ['label' => (string) $stat->label, 'total' => (int) $stat->total])->values(),
            // The registry's hours and phone from the landing manager's contact settings, in the request's language.
            'registry' => [
                'office_hours' => $footer['office_hours'] ?? null,
                'phone' => $footer['contact_phone'] ?? null,
            ],
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
