<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return response()
            ->view('admin.auth.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $request->session()->forget('mfa_verified_at');

        $response = redirect()->intended(route('dashboard', absolute: false));

        if ($this->hasApplicantSession($request)) {
            $response->with('info', __('auth.applicant_session_active_message'));
        }

        return $response;
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        return redirect()->route('login');
    }

    private function hasApplicantSession(Request $request): bool
    {
        $baseCookie = (string) config('session.cookie_base', config('session.cookie'));

        return $request->cookies->has($baseCookie.'-applicant');
    }
}
