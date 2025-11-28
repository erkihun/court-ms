<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ForcePasswordController extends Controller
{
    /**
     * Show the dedicated force-password-change screen.
     */
    public function show(): View
    {
        return view('admin.auth.force-password');
    }
}
