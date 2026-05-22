<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class ForcePasswordController extends Controller
{
    /**
     * Show the dedicated force-password-change screen.
     */
    public function show(): Response
    {
        return response()
            ->view('admin.auth.force-password')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }
}
