<?php
// app/Http/Controllers/PublicController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{
    public function home()
    {
        return view('public.home');
    }

    public function cases(Request $request)
    {
        $q = trim($request->get('q', ''));

        $builder = DB::table('court_cases as c')
            ->leftJoin('case_types as ct', 'ct.id', '=', 'c.case_type_id')

            ->select(
                'c.id',
                'c.case_number',
                'c.title',
                'c.status',
                'c.filing_date',
                'ct.name as case_type',

            );

        if ($q !== '') {
            $builder->where(function ($w) use ($q) {
                $w->where('c.case_number', 'like', "%{$q}%")
                    ->orWhere('c.title', 'like', "%{$q}%")
                    ->orWhere('ct.name', 'like', "%{$q}%");
            });
        }

        // Optional: only show public-safe statuses
        // $builder->whereIn('c.status', ['pending','active','closed','dismissed']);

        $cases = $builder->orderByDesc('c.created_at')
            ->paginate(10)
            ->withQueryString();

        return view('public.cases.index', compact('cases', 'q'));
    }

    public function caseShow(string $num)
    {
        $case = DB::table('court_cases as c')
            ->leftJoin('case_types as ct', 'ct.id', '=', 'c.case_type_id')

            ->select('c.*', 'ct.name as case_type')
            ->where('c.case_number', $num)
            ->first();

        abort_if(!$case, 404);

        return view('public.cases.show', compact('case'));
    }
}
