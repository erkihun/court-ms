<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ForcePasswordController extends Controller
{
    /**
     * The forced password change (React, Auth/ForcedPasswordChange from
     * EUISIS); it posts to password.forced.update.
     */
    public function show(): Response
    {
        return Inertia::render('Auth/ForcedPasswordChange');
    }
}
