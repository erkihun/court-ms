<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Support\EuisisParties;
use App\Support\Ui\PublicSiteChrome;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Mews\Purifier\Facades\Purifier;
use Throwable;

class PublicSignageController extends Controller
{
    /** How many sittings of one day the board pages through. */
    public const HEARING_LIMIT = 60;

    /** How far ahead the board looks for the next sitting day once today's are over. */
    public const NEXT_DAY_WINDOW = 14;

    /** Decisions released to the parties within this many days are listed, at most DECISION_LIMIT of them. */
    public const DECISION_DAYS = 14;

    public const DECISION_LIMIT = 18;

    /**
     * The court's door display: a portrait screen (1080x1920) at the court's
     * entrance for the parties arriving. What they come to learn, in order:
     * whether their case sits today, when, before which bench and in which
     * room; what sits next; which cases have a decision released; and how
     * to follow a case and reach the registry. Once today's sittings are
     * over it shows the next sitting day's list instead.
     *
     * It is public, so a case shows by its number, type and bench only: no
     * party and no staff. (The board once listed every active account as
     * "on duty", which was neither true nor the public's to see.)
     */
    public function show()
    {
        $tz = config('app.timezone', 'UTC');
        $now = Carbon::now($tz);
        // Timestamps are stored in the app's time zone, so days are bounded there too: converted
        // to UTC, a window lost everything after 21:00 in Addis Ababa.
        $today = $now->copy()->startOfDay();

        $nextDay = DB::table('case_hearings')
            ->where('hearing_at', '>', $today->copy()->endOfDay())
            ->where('hearing_at', '<=', $today->copy()->addDays(self::NEXT_DAY_WINDOW)->endOfDay())
            ->min('hearing_at');
        $nextDay = $nextDay ? Carbon::parse($nextDay, $tz)->startOfDay() : null;

        $announcements = Announcement::select('id', 'title', 'content', 'created_at')
            ->where('status', 'active')
            ->latest('created_at')
            ->limit(5)
            ->get();

        $footer = PublicSiteChrome::props()['footer'] ?? [];

        return Inertia::render('Court/Public/Signage', [
            'now' => $now->toIso8601String(),
            'timezone' => $tz,
            // The benches in a fixed order, so each keeps its colour on the board.
            'benches' => DB::table('teams')->whereNull('parent_id')->orderBy('id')->get(['id', 'name'])
                ->map(fn (object $bench): array => ['id' => (int) $bench->id, 'name' => (string) $bench->name])->values(),
            'today' => [
                'date' => $today->toDateString(),
                'hearings' => $this->sittings($today),
            ],
            'nextDay' => $nextDay ? [
                'date' => $nextDay->toDateString(),
                'hearings' => $this->sittings($nextDay),
            ] : null,
            'decisions' => $this->releasedDecisions($now),
            'stats' => $this->statistics($today),
            'week' => $this->week($today),
            'announcements' => $announcements->map(fn (Announcement $announcement): array => [
                'id' => $announcement->id,
                'title' => (string) $announcement->title,
                // The board reads a notice as one line of text; the full notice is on the news page.
                'summary' => $this->plainText((string) $announcement->content, 220),
                'created_at' => $announcement->created_at?->toIso8601String(),
            ])->values(),
            // The registry's hours, phone and address from the landing manager's contact settings.
            'registry' => [
                'office_hours' => $footer['office_hours'] ?? null,
                'phone' => $footer['contact_phone'] ?? null,
                'address' => $footer['contact_address'] ?? null,
            ],
            'portal' => $this->portal(),
            'refreshSeconds' => 60,
        ]);
    }

    /**
     * One day's sittings in time order: the time as stored, the case's number
     * and type, the kind of sitting, the bench the case is assigned to and the
     * room. No party's name.
     *
     * @return Collection<int, array<string, int|string|null>>
     */
    private function sittings(Carbon $day): Collection
    {
        return DB::table('case_hearings as h')
            ->join('court_cases as c', 'c.id', '=', 'h.case_id')
            ->leftJoin('case_types as t', 't.id', '=', 'c.case_type_id')
            ->leftJoin('teams as b', 'b.id', '=', 'c.assigned_team_id')
            ->whereBetween('h.hearing_at', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])
            ->orderBy('h.hearing_at')
            ->orderBy('b.id')
            ->orderBy('h.id')
            ->limit(self::HEARING_LIMIT)
            ->get(['h.id', 'h.hearing_at', 'h.type', 'h.location', 'c.case_number', 't.name as case_type', 'b.id as bench_id', 'b.name as bench'])
            ->map(function (object $row): array {
                // The stored wall-clock time, as the public site's hearing list shows it.
                $raw = (string) $row->hearing_at;

                return [
                    'id' => (int) $row->id,
                    'time' => strlen($raw) >= 16 ? substr($raw, 11, 5) : null,
                    'case_number' => $row->case_number,
                    'case_type' => $row->case_type,
                    'type' => $row->type,
                    'location' => $row->location,
                    'bench_id' => $row->bench_id !== null ? (int) $row->bench_id : null,
                    'bench' => $row->bench,
                ];
            })->values();
    }

    /**
     * The court's figures: today's sittings and filings, the coming week's
     * sittings, the decisions released this month, the cases on record and
     * how they stand. Counts only.
     *
     * @return array<string, mixed>
     */
    private function statistics(Carbon $today): array
    {
        $statusCounts = DB::table('court_cases')
            ->selectRaw("COALESCE(NULLIF(status, ''), 'unspecified') as label, count(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->get();

        return [
            'hearings_today' => DB::table('case_hearings')->whereBetween('hearing_at', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])->count(),
            'hearings_week' => DB::table('case_hearings')->whereBetween('hearing_at', [$today->copy()->startOfDay(), $today->copy()->addDays(6)->endOfDay()])->count(),
            'filed_today' => DB::table('court_cases')->whereBetween('created_at', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])->count(),
            'decisions_month' => DB::table('decisions')->where('status', 'published')->whereNotNull('approved_at')
                ->where('approved_at', '>=', $today->copy()->startOfMonth())->count(),
            'total_cases' => DB::table('court_cases')->count(),
            'by_status' => $statusCounts->map(fn (object $row): array => ['label' => (string) $row->label, 'total' => (int) $row->total])->values(),
        ];
    }

    /**
     * The coming seven days, today first: each day with sittings and how many.
     *
     * @return Collection<int, array{date: string, count: int}>
     */
    private function week(Carbon $today): Collection
    {
        return DB::table('case_hearings')
            ->selectRaw('DATE(hearing_at) as day, count(*) as total')
            ->whereBetween('hearing_at', [$today->copy()->startOfDay(), $today->copy()->addDays(6)->endOfDay()])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn (object $row): array => ['date' => (string) $row->day, 'count' => (int) $row->total])
            ->values();
    }

    /**
     * Decisions released to the parties lately: published and approved, the
     * state in which a party can download one. Case number and date only.
     *
     * @return Collection<int, array{case_number: string, released: string}>
     */
    private function releasedDecisions(Carbon $now): Collection
    {
        return DB::table('decisions')
            ->where('status', 'published')
            ->whereNotNull('approved_at')
            ->where('approved_at', '>=', $now->copy()->subDays(self::DECISION_DAYS)->startOfDay())
            ->whereNotNull('case_number')
            ->orderByDesc('approved_at')
            ->limit(self::DECISION_LIMIT)
            ->get(['case_number', 'approved_at'])
            ->unique('case_number')
            ->map(fn (object $row): array => [
                'case_number' => (string) $row->case_number,
                'released' => substr((string) $row->approved_at, 0, 10),
            ])->values();
    }

    /**
     * Where parties follow their case (the EUISIS My Portal, or the court's
     * own applicant portal while that is open), with a QR code a phone can
     * read from the screen. Drawn once per address and kept a day.
     *
     * @return array{url: string, qr: string|null}
     */
    private function portal(): array
    {
        $url = (EuisisParties::portalClosed() ? EuisisParties::portalUrl() : null) ?? route('applicant.login');

        $qr = Cache::remember('signage_portal_qr:'.sha1($url), 86400, function () use ($url): ?string {
            try {
                $svg = (new Writer(new ImageRenderer(new RendererStyle(320, 1), new SvgImageBackEnd)))->writeString($url);

                // Only the drawing: the XML declaration would not belong inside the page.
                return (string) preg_replace('/^<\?xml[^>]*>\s*/', '', $svg);
            } catch (Throwable) {
                return null;
            }
        });

        return ['url' => $url, 'qr' => $qr];
    }

    /** A notice's HTML as plain text: cleaned, tags dropped with a space each, whitespace folded, shortened. */
    private function plainText(string $html, int $limit): string
    {
        $clean = (string) Purifier::clean($html, 'default');
        $text = html_entity_decode(strip_tags(str_replace('<', ' <', $clean)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return Str::limit(trim((string) preg_replace('/\s+/u', ' ', $text)), $limit);
    }
}
