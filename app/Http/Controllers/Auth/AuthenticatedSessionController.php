<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * The court staff sign-in page (React, Court/Auth/Login).
     */
    public function create(): Response
    {
        // The court's own sign-in (Pages/Court/Auth/Login), posting the same form as the EUISIS one.
        return Inertia::render('Court/Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            // court-ms times sessions out on the server without naming the reason.
            'sessionNotice' => null,
            // Applicants and respondents are pointed to the EUISIS employee portal.
            'portalUrl' => \App\Support\EuisisParties::portalUrl(),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $request->session()->forget('mfa_verified_at');

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

}
