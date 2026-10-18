<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    // Cache TTL in seconds — short enough to feel live, long enough to matter
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Case statistics need cases.view and the system panels settings.manage;
     * other staff get an empty dashboard with the calendar.
     */
    public function index(): Response
    {
        $user = Auth::user();
        $canSeeCases = $user?->hasPermission('cases.view') ?? false;
        $canSeeSystem = $user?->hasPermission('settings.manage') ?? false;

        return Inertia::render('Admin/Dashboard', [
            'cases' => $canSeeCases ? $this->caseStatistics() : null,
            'system' => $canSeeSystem ? [
                'health' => $this->serviceHealth(),
                'storage' => $this->dataStorage(),
            ] : null,
        ]);
    }

    /** @return array<string, mixed> */
    private function caseStatistics(): array
    {
        $data = Cache::remember('dashboard_index_v4', self::CACHE_TTL, function (): array {
            $counts = DB::table('court_cases')
                ->selectRaw("COUNT(*) as total, SUM(status = 'pending') as pending, SUM(status = 'active') as active")
                ->first();
            $resolvedCases = DB::table('decisions')
                ->where('status', 'published')
                ->whereNotNull('court_case_id')
                ->distinct()
                ->count('court_case_id');

            $recent = DB::table('court_cases')
                ->select('id', 'case_number', 'title', 'status')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            // monthly cases (last 6 months) — single query, build labels in PHP
            $reference = Carbon::now();
            $months = DB::table('court_cases')
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt")
                ->where('created_at', '>=', $reference->copy()->subMonths(5)->startOfMonth())
                ->groupBy('ym')
                ->orderBy('ym')
                ->get();

            // team case counts — single aggregated query
            $teamCaseCounts = DB::table('teams as t')
                ->leftJoin('court_cases as c', 'c.assigned_team_id', '=', 't.id')
                ->select('t.id', 't.name', DB::raw('COUNT(c.id) as cases_count'))
                ->groupBy('t.id', 't.name')
                ->orderByDesc('cases_count')
                ->orderBy('t.name')
                ->get();

            // member counts + type breakdown — two queries, merged in PHP (no N+1)
            $memberCaseCounts = DB::table('team_user as tu')
                ->join('users as u', 'u.id', '=', 'tu.user_id')
                ->leftJoin('teams as t', 't.id', '=', 'tu.team_id')
                ->leftJoin('court_cases as c', function ($join) {
                    $join->on('c.assigned_member_user_id', '=', 'u.id')
                        ->orOn(function ($query) {
                            $query->on('c.assigned_user_id', '=', 'u.id')
                                ->whereNull('c.assigned_member_user_id');
                        });
                })
                ->leftJoin('decisions as d', function ($join) {
                    $join->on('d.court_case_id', '=', 'c.id')
                        ->where('d.status', '=', 'published');
                })
                ->select(
                    'u.id', 'u.name', 'u.status', 'u.avatar_path',
                    't.name as team_name',
                    DB::raw('COUNT(DISTINCT c.id) as cases_count'),
                    DB::raw("COUNT(DISTINCT CASE WHEN c.status = 'pending' THEN c.id END) as pending_cases_count"),
                    DB::raw("COUNT(DISTINCT CASE WHEN c.status = 'active' THEN c.id END) as active_cases_count"),
                    DB::raw('COUNT(DISTINCT CASE WHEN d.id IS NOT NULL THEN c.id END) as resolved_cases_count')
                )
                ->groupBy('u.id', 'u.name', 'u.status', 'u.avatar_path', 't.name')
                ->orderByDesc('cases_count')
                ->orderBy('u.name')
                ->get();

            $memberCaseTypeCounts = DB::table('team_user as tu')
                ->join('users as u', 'u.id', '=', 'tu.user_id')
                ->join('court_cases as c', function ($join) {
                    $join->on('c.assigned_member_user_id', '=', 'u.id')
                        ->orOn(function ($query) {
                            $query->on('c.assigned_user_id', '=', 'u.id')
                                ->whereNull('c.assigned_member_user_id');
                        });
                })
                ->leftJoin('case_types as ct', 'ct.id', '=', 'c.case_type_id')
                ->select('u.id as user_id', 'ct.name as case_type', DB::raw('COUNT(DISTINCT c.id) as cases_count'))
                ->groupBy('u.id', 'ct.name')
                ->get()
                ->groupBy('user_id');

            $genderCounts = DB::table('applicants')
                ->select('gender', DB::raw('COUNT(*) as total'))
                ->groupBy('gender')
                ->pluck('total', 'gender');

            $caseTypeCounts = DB::table('court_cases as c')
                ->leftJoin('case_types as t', 't.id', '=', 'c.case_type_id')
                ->select('t.name as label', DB::raw('COUNT(*) as total'))
                ->groupBy('t.name')
                ->orderByDesc('total')
                ->limit(8)
                ->get();

            $monthLabels = [];
            $monthValues = [];
            for ($i = 5; $i >= 0; $i--) {
                $point = $reference->copy()->subMonths($i);
                $monthLabels[] = $point->format('M Y');
                $monthValues[] = (int) ($months->firstWhere('ym', $point->format('Y-m'))->cnt ?? 0);
            }

            return [
                'kpis' => [
                    'total' => (int) ($counts->total ?? 0),
                    'pending' => (int) ($counts->pending ?? 0),
                    'active' => (int) ($counts->active ?? 0),
                    'resolved' => $resolvedCases,
                ],
                'members' => $memberCaseCounts->map(fn (object $member): array => [
                    'id' => (int) $member->id,
                    'name' => (string) $member->name,
                    'teamName' => $member->team_name,
                    'active' => $member->status === 'active',
                    'avatarPath' => $member->avatar_path,
                    'cases' => (int) $member->cases_count,
                    'pending' => (int) $member->pending_cases_count,
                    'activeCases' => (int) $member->active_cases_count,
                    'resolved' => (int) $member->resolved_cases_count,
                    'types' => $memberCaseTypeCounts->get($member->id, collect())
                        ->map(fn (object $row): array => ['label' => $row->case_type, 'count' => (int) $row->cases_count])
                        ->values()
                        ->all(),
                ])->values()->all(),
                'teams' => $teamCaseCounts->map(fn (object $team): array => [
                    'id' => (int) $team->id,
                    'name' => (string) $team->name,
                    'cases' => (int) $team->cases_count,
                ])->values()->all(),
                'recent' => $recent->map(fn (object $case): array => [
                    'id' => (int) $case->id,
                    'caseNumber' => (string) $case->case_number,
                    'title' => (string) ($case->title ?? ''),
                    'status' => (string) $case->status,
                ])->values()->all(),
                'monthly' => ['labels' => $monthLabels, 'values' => $monthValues],
                'gender' => $genderCounts->map(fn ($total, $gender): array => ['gender' => (string) $gender, 'total' => (int) $total])->values()->all(),
                'types' => $caseTypeCounts->map(fn (object $row): array => ['label' => $row->label, 'total' => (int) $row->total])->values()->all(),
            ];
        });

        // Avatars are resolved outside the cache so a removed file never lingers.
        $data['members'] = array_map(function (array $member): array {
            $path = $member['avatarPath'];
            unset($member['avatarPath']);
            $member['avatarUrl'] = filled($path) && Storage::disk('public')->exists($path) ? Storage::disk('public')->url($path) : null;

            return $member;
        }, $data['members']);

        return $data;
    }

    /** @return array<string, mixed> */
    private function serviceHealth(): array
    {
        $database = true;
        try { DB::connection()->getPdo(); } catch (\Throwable) { $database = false; }
        $queue = null;
        try { $queue = Schema::hasTable('jobs') ? (int) DB::table('jobs')->count() : null; } catch (\Throwable) { $queue = null; }
        $settings = SystemSetting::current();

        return [
            'database' => $database,
            'queue_waiting' => $queue,
            'queue_ready' => $queue !== null,
            'mail' => (bool) ($settings->mail_enabled ?? false) && filled($settings->mail_host ?? config('mail.mailers.smtp.host')),
            'sms' => (bool) ($settings->sms_enabled ?? false),
            'sms_ready' => (bool) ($settings->sms_enabled ?? false) && filled($settings->sms_base_url ?? null),
            'telegram' => (bool) ($settings->telegram_enabled ?? false) && filled($settings->telegram_bot_token ?? null) && filled($settings->telegram_default_chat_id ?? null),
            'in_app' => Schema::hasTable('notifications'),
        ];
    }

    /** @return array<string, int|string|null> */
    private function dataStorage(): array
    {
        $tables = 0;
        try { $tables = count(Schema::getTables()); } catch (\Throwable) {}
        $size = null;
        try {
            $driver = DB::connection()->getDriverName();
            $database = (string) DB::connection()->getDatabaseName();
            $bytes = match ($driver) {
                'mysql' => (int) DB::selectOne('select coalesce(sum(data_length + index_length), 0) as bytes from information_schema.tables where table_schema = ?', [$database])->bytes,
                'sqlite' => $this->sqliteSize($database),
                default => null,
            };
            $size = $bytes === null ? null : round($bytes / 1048576, 2).' MB';
        } catch (\Throwable) {}

        return [
            'tables' => $tables,
            'database_size' => $size,
            'storage_free' => disk_free_space(storage_path()),
            'audit_events' => Schema::hasTable('system_audits') ? (int) DB::table('system_audits')->count() : 0,
            'last_backup' => null,
        ];
    }

    private function sqliteSize(string $database): ?int
    {
        if ($database === ':memory:') return null;
        $path = realpath($database);
        if ($path === false || ! is_file($path)) return null;
        $bytes = filesize($path);
        return $bytes === false ? null : $bytes;
    }

    /**
     * Returns dashboard stats for a selected date range via AJAX.
     * Cached per range key so repeated toggles hit cache, not DB.
     */
    public function stats(Request $request): JsonResponse
    {
        abort_unless(Auth::user()?->hasPermission('cases.view') ?? false, 403);

        $now = Carbon::now();

        if ($request->filled('start') && $request->filled('end')) {
            try {
                $start = Carbon::parse($request->input('start'))->startOfDay();
                $end   = Carbon::parse($request->input('end'))->endOfDay();
            } catch (\Exception) {
                return response()->json(['ok' => false, 'message' => 'Invalid date format.'], 422);
            }
        } else {
            $range = $request->input('range', '6m');
            [$start, $end] = match ($range) {
                '7d'  => [$now->copy()->subDays(6)->startOfDay(),         $now->copy()],
                '30d' => [$now->copy()->subDays(29)->startOfDay(),        $now->copy()],
                '90d' => [$now->copy()->subDays(89)->startOfDay(),        $now->copy()],
                '12m' => [$now->copy()->subMonths(11)->startOfMonth(),    $now->copy()],
                default=>[$now->copy()->subMonths(5)->startOfMonth(),     $now->copy()],
            };
        }

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end->copy(), $start->copy()];
        }

        // Cache key encodes the exact day range so custom ranges also benefit
        $cacheKey = 'dashboard_stats_v3_' . $start->toDateString() . '_' . $end->toDateString();

        $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($start, $end, $now) {

            // All 4 KPI counts in a single pass using conditional aggregation
            $kpiRow = DB::table('court_cases as c')
                ->leftJoin('decisions as d', function ($join) {
                    $join->on('d.court_case_id', '=', 'c.id')
                        ->where('d.status', '=', 'published');
                })
                ->selectRaw("
                    COUNT(DISTINCT c.id) as total,
                    COUNT(DISTINCT CASE WHEN c.status = 'pending' THEN c.id END) as pending,
                    COUNT(DISTINCT CASE WHEN d.id IS NOT NULL THEN c.id END) as resolved
                ")
                ->whereBetween('c.created_at', [$start, $end])
                ->first();

            $totalCases    = (int) $kpiRow->total;
            $pendingCases  = (int) $kpiRow->pending;
            $resolvedCases = (int) $kpiRow->resolved;

            $activeUsers = DB::table('users')
                ->whereBetween('updated_at', [$start, $end])
                ->count();

            $recent = DB::table('court_cases')
                ->select('id', 'case_number', 'title', 'status', 'created_at')
                ->orderByDesc('created_at')
                ->limit(8)
                ->get();

            $teamCaseCounts = DB::table('teams as t')
                ->leftJoin('court_cases as c', function ($join) use ($start, $end) {
                    $join->on('c.assigned_team_id', '=', 't.id')
                        ->whereBetween('c.created_at', [$start, $end]);
                })
                ->select('t.id', 't.name', DB::raw('COUNT(c.id) as cases_count'))
                ->groupBy('t.id', 't.name')
                ->orderByDesc('cases_count')
                ->orderBy('t.name')
                ->get();

            $memberCaseCounts = DB::table('team_user as tu')
                ->join('users as u', 'u.id', '=', 'tu.user_id')
                ->leftJoin('teams as t', 't.id', '=', 'tu.team_id')
                ->leftJoin('court_cases as c', function ($join) use ($start, $end) {
                    $join->whereBetween('c.created_at', [$start, $end])
                        ->where(function ($q) {
                            $q->whereColumn('c.assigned_member_user_id', 'u.id')
                                ->orWhere(function ($inner) {
                                    $inner->whereColumn('c.assigned_user_id', 'u.id')
                                        ->whereNull('c.assigned_member_user_id');
                                });
                        });
                })
                ->leftJoin('decisions as d', function ($join) {
                    $join->on('d.court_case_id', '=', 'c.id')
                        ->where('d.status', '=', 'published');
                })
                ->select(
                    'u.id', 'u.name', 'u.email', 'u.status', 'u.avatar_path',
                    't.name as team_name',
                    DB::raw('COUNT(DISTINCT c.id) as cases_count'),
                    DB::raw("COUNT(DISTINCT CASE WHEN c.status = 'pending' THEN c.id END) as pending_cases_count"),
                    DB::raw("COUNT(DISTINCT CASE WHEN c.status = 'active' THEN c.id END) as active_cases_count"),
                    DB::raw('COUNT(DISTINCT CASE WHEN d.id IS NOT NULL THEN c.id END) as resolved_cases_count')
                )
                ->groupBy('u.id', 'u.name', 'u.email', 'u.status', 'u.avatar_path', 't.name')
                ->orderByDesc('cases_count')
                ->orderBy('u.name')
                ->get();

            $memberCaseTypeCounts = DB::table('team_user as tu')
                ->join('users as u', 'u.id', '=', 'tu.user_id')
                ->join('court_cases as c', function ($join) use ($start, $end) {
                    $join->whereBetween('c.created_at', [$start, $end])
                        ->where(function ($q) {
                            $q->whereColumn('c.assigned_member_user_id', 'u.id')
                                ->orWhere(function ($inner) {
                                    $inner->whereColumn('c.assigned_user_id', 'u.id')
                                        ->whereNull('c.assigned_member_user_id');
                                });
                        });
                })
                ->leftJoin('case_types as ct', 'ct.id', '=', 'c.case_type_id')
                ->select(
                    'u.id as user_id',
                    DB::raw("COALESCE(ct.name, '__unknown__') as case_type"),
                    DB::raw('COUNT(DISTINCT c.id) as cases_count')
                )
                ->groupBy('u.id', 'case_type')
                ->get()
                ->groupBy('user_id');

            $memberCaseCounts = $memberCaseCounts->map(function ($member) use ($memberCaseTypeCounts) {
                $member->case_type_counts = $memberCaseTypeCounts
                    ->get($member->id, collect())
                    ->map(fn($row) => ['label' => $row->case_type, 'count' => (int) $row->cases_count])
                    ->values();
                return $member;
            });

            $useDaily = $end->diffInDays($start) <= 45;
            $labels = [];
            $values = [];

            if ($useDaily) {
                $rows = DB::table('court_cases')
                    ->selectRaw("DATE(created_at) as day, COUNT(*) as cnt")
                    ->whereBetween('created_at', [$start, $end])
                    ->groupBy('day')
                    ->pluck('cnt', 'day');

                $cursor = $start->copy();
                while ($cursor->lte($end)) {
                    $labels[] = $cursor->format('M d');
                    $values[] = (int) ($rows[$cursor->toDateString()] ?? 0);
                    $cursor->addDay();
                }
            } else {
                $rows = DB::table('court_cases')
                    ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt")
                    ->whereBetween('created_at', [$start, $end])
                    ->groupBy('ym')
                    ->pluck('cnt', 'ym');

                $cursor = $start->copy()->startOfMonth();
                while ($cursor->lte($end)) {
                    $labels[] = $cursor->format('M Y');
                    $values[] = (int) ($rows[$cursor->format('Y-m')] ?? 0);
                    $cursor->addMonth();
                }
            }

            // Both gender + case-type in one query pass each
            $genderCounts  = DB::table('applicants')
                ->select('gender', DB::raw('COUNT(*) as total'))
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('gender')
                ->pluck('total', 'gender')
                ->toArray();

            $caseTypeCounts = DB::table('court_cases as c')
                ->leftJoin('case_types as t', 't.id', '=', 'c.case_type_id')
                ->select(DB::raw('COALESCE(t.name, "Unknown") as label'), DB::raw('COUNT(*) as total'))
                ->whereBetween('c.created_at', [$start, $end])
                ->groupBy('label')
                ->orderByDesc('total')
                ->get()
                ->map(fn($row) => ['label' => $row->label, 'total' => (int) $row->total]);

            return [
                'ok'    => true,
                'range' => [
                    'start' => $start->toDateString(),
                    'end'   => $end->toDateString(),
                    'mode'  => $useDaily ? 'daily' : 'monthly',
                ],
                'kpis' => [
                    'totalCases'    => $totalCases,
                    'pendingCases'  => $pendingCases,
                    'resolvedCases' => $resolvedCases,
                    'activeUsers'   => $activeUsers,
                ],
                'recent'          => $recent,
                'teamCaseCounts'  => $teamCaseCounts,
                'memberCaseCounts'=> $memberCaseCounts,
                'charts' => [
                    'line'   => ['labels' => $labels, 'values' => $values],
                    'gender' => [
                        'labels' => array_keys($genderCounts),
                        'values' => array_values($genderCounts),
                    ],
                    'types'  => [
                        'labels' => $caseTypeCounts->pluck('label')->all(),
                        'values' => $caseTypeCounts->pluck('total')->all(),
                    ],
                ],
            ];
        });

        return response()->json($result);
    }
}
