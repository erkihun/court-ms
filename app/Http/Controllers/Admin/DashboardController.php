<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCases    = DB::table('court_cases')->count();
        $pendingCases  = DB::table('court_cases')->where('status', 'pending')->count();
        $resolvedCases = DB::table('court_cases')->whereIn('status', ['closed', 'dismissed'])->count();
        $activeUsers   = DB::table('users')->where('status', 'active')->count();

        // recent 8 cases
        $recent = DB::table('court_cases')
            ->select('id', 'case_number', 'title', 'status', 'created_at')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        // monthly cases (last 6 months)
        $reference = Carbon::now();
        $months = DB::table('court_cases')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt")
            ->where('created_at', '>=', $reference->copy()->subMonths(5)->startOfMonth())
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        // recent users
        $recentUsers = DB::table('users')
            ->select('id', 'name', 'email', 'avatar_path', 'status', 'created_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // applicant gender counts
        $genderCounts = DB::table('applicants')
            ->select('gender', DB::raw('COUNT(*) as total'))
            ->groupBy('gender')
            ->pluck('total', 'gender')
            ->toArray();

        // cases by type
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
        // Build continuous 6-month window
        for ($i = 5; $i >= 0; $i--) {
            $point = $reference->copy()->subMonths($i);
            $ym = $point->format('Y-m');
            $labels[] = $point->format('M Y');
            $values[] = (int) ($months->firstWhere('ym', $ym)->cnt ?? 0);
        }

        return view('admin.dashboard', [
            'totalCases'     => $totalCases,
            'pendingCases'   => $pendingCases,
            'resolvedCases'  => $resolvedCases,
            'activeUsers'    => $activeUsers,
            'recent'         => $recent,
            'labels'         => $labels,
            'values'         => $values,
            'recentUsers'    => $recentUsers,
            'genderCounts'   => $genderCounts,
            'caseTypeCounts' => $caseTypeCounts,
        ]);
    }

    /**
     * Returns dashboard stats for a selected date range via AJAX.
     */
    public function stats(Request $request)
    {
        $now = Carbon::now();
        $start = null;
        $end = null;

        if ($request->filled('start') && $request->filled('end')) {
            try {
                $start = Carbon::parse($request->input('start'))->startOfDay();
                $end   = Carbon::parse($request->input('end'))->endOfDay();
            } catch (\Exception $e) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Invalid date format.',
                ], 422);
            }
        } else {
            $range = $request->input('range', '6m');
            switch ($range) {
                case '7d':
                    $start = $now->copy()->subDays(6)->startOfDay();
                    $end = $now->copy();
                    break;
                case '30d':
                    $start = $now->copy()->subDays(29)->startOfDay();
                    $end = $now->copy();
                    break;
                case '90d':
                    $start = $now->copy()->subDays(89)->startOfDay();
                    $end = $now->copy();
                    break;
                case '12m':
                    $start = $now->copy()->subMonths(11)->startOfMonth();
                    $end = $now->copy();
                    break;
                case '6m':
                default:
                    $start = $now->copy()->subMonths(5)->startOfMonth();
                    $end = $now->copy();
                    break;
            }
        }

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end->copy(), $start->copy()];
        }

        $totalCases = DB::table('court_cases')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $pendingCases = DB::table('court_cases')
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'pending')
            ->count();

        $resolvedCases = DB::table('court_cases')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['closed', 'dismissed'])
            ->count();

        $activeUsers = DB::table('users')
            ->whereBetween('updated_at', [$start, $end])
            ->count();

        $recent = DB::table('court_cases')
            ->select('id', 'case_number', 'title', 'status', 'created_at')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $recentUsers = DB::table('users')
            ->select('id', 'name', 'email', 'status', 'avatar_path', 'updated_at')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        $useDaily = $end->diffInDays($start) <= 45;
        $labels = [];
        $values = [];

        if ($useDaily) {
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $labels[] = $cursor->format('M d');
                $values[] = DB::table('court_cases')
                    ->whereDate('created_at', $cursor->toDateString())
                    ->count();
                $cursor->addDay();
            }
        } else {
            $cursor = $start->copy()->startOfMonth();
            while ($cursor->lte($end)) {
                $labels[] = $cursor->format('M Y');
                $values[] = DB::table('court_cases')
                    ->whereBetween('created_at', [$cursor->copy()->startOfMonth(), $cursor->copy()->endOfMonth()])
                    ->count();
                $cursor->addMonth();
            }
        }

        $genderCounts = DB::table('applicants')
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
            ->map(fn($row) => [
                'label' => $row->label,
                'total' => (int) $row->total,
            ]);

        $genderLabels = array_keys($genderCounts);
        $genderValues = array_values($genderCounts);

        $typeLabels = $caseTypeCounts->pluck('label')->all();
        $typeValues = $caseTypeCounts->pluck('total')->all();

        return response()->json([
            'ok' => true,
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
            'recent'      => $recent,
            'recentUsers' => $recentUsers,
            'charts' => [
                'line' => [
                    'labels' => $labels,
                    'values' => $values,
                ],
                'gender' => [
                    'labels' => $genderLabels,
                    'values' => $genderValues,
                ],
                'types' => [
                    'labels' => $typeLabels,
                    'values' => $typeValues,
                ],
            ],
        ]);
    }
}
