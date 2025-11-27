<?php

namespace App\Http\Controllers\Respondent;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('respondant.dashboard');
    }
}
