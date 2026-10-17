<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Concerns\RendersAuthPages;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordController extends Controller
{
    use RendersAuthPages;

    /**
     * Show the dedicated force-password-change screen.
     */
    public function show(): Response
    {
        return $this->authPage('Auth/ForcePassword');
    }
}
