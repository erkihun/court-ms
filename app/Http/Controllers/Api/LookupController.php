<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class LookupController extends Controller
{
    public function caseTypes()
    {
        $types = DB::table('case_types')
            ->select('id', 'name', 'prefix')
            ->orderBy('name')
            ->get();

        return response()->json([
            'ok' => true,
            'data' => $types,
        ]);
    }
}
