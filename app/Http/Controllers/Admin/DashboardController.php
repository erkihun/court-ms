<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // Cache TTL in seconds — short enough to feel live, long enough to matter
    private const CACHE_TTL = 300; // 5 minutes

    public function index()
    {
        $data = Cache::remember('dashboard_index', self::CACHE_TTL, function () {
            $totalCases    = DB::table('court_cases')->count();
            $pendingCases  = DB::table('court_cases')->where('status', 'pending')->count();
            $resolvedCases = DB::table('court_cases')->whereIn('status', ['closed', 'dismissed'])->count();
            $activeUsers   = DB::table('users')->where('status', 'active')->count();

            $recent = DB::table('court_cases')
                ->select('id', 'case_number', 'title', 'status', 'created_at')
                ->orderByDesc('created_at')
                ->limit(8)
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
                ->select(
                    'u.id', 'u.name', 'u.email', 'u.status', 'u.avatar_path',
                    't.name as team_name',
                    DB::raw('COUNT(DISTINCT c.id) as cases_count')
                )
                ->groupBy('u.id', 'u.name', 'u.email', 'u.status', 'u.avatar_path', 't.name')
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

            $genderCounts = DB::table('applicants')
                ->select('gender', DB::raw('COUNT(*) as total'))
                ->groupBy('gender')
                ->pluck('total', 'gender')
                ->toArray();

            $caseTypeCounts = DB::table('court_cases as c')
                ->leftJoin('case_types as t', 't.id', '=', 'c.case_type_id')
                ->select(DB::raw('COALESCE(t.name, "Unknown") as label'), DB::raw('COUNT(*) as total'))
                ->groupBy('label')
                ->orderByDesc('total')
                ->limit(8)
                ->get()
                ->mapWithKeys(fn($row) => [$row->label => $row->total])
                ->toArray();

            $labels = [];
            $values = [];
            for ($i = 5; $i >= 0; $i--) {
                $point = $reference->copy()->subMonths($i);
                $ym = $point->format('Y-m');
                $labels[] = $point->format('M Y');
                $values[] = (int) ($months->firstWhere('ym', $ym)->cnt ?? 0);
            }

            return compact(
                'totalCases', 'pendingCases', 'resolvedCases', 'activeUsers',
                'recent', 'labels', 'values',
                'teamCaseCounts', 'memberCaseCounts', 'genderCounts', 'caseTypeCounts'
            );
        });

        return view('admin.dashboard', $data);
    }

    /**
     * Returns dashboard stats for a selected date range via AJAX.
     * Cached per range key so repeated toggles hit cache, not DB.
     */
    public function stats(Request $request)
    {
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
        $cacheKey = 'dashboard_stats_' . $start->toDateString() . '_' . $end->toDateString();

        $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($start, $end, $now) {

            // All 4 KPI counts in a single pass using conditional aggregation
            $kpiRow = DB::table('court_cases')
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status IN ('closed','dismissed') THEN 1 ELSE 0 END) as resolved
                ")
                ->whereBetween('created_at', [$start, $end])
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
                ->select(
                    'u.id', 'u.name', 'u.email', 'u.status', 'u.avatar_path',
                    't.name as team_name',
                    DB::raw('COUNT(DISTINCT c.id) as cases_count')
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
