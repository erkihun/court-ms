<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Concerns\RendersAuthPages;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailVerificationPromptController extends Controller
{
    use RendersAuthPages;

    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|Response
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('dashboard', absolute: false))
                    : $this->authPage('Auth/VerifyEmail');
    }
}
