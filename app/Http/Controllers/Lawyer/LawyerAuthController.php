<?php

declare(strict_types=1);

namespace App\Http\Controllers\Lawyer;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Services\CaseParties;
use App\Support\LoginThrottle;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The lawyers' sign-in (EUISIS ADR 0013 #13). Employees work on their cases in
 * the EUISIS employee portal; the lawyers who represent them have their own
 * account here, opened by the registry (Lawyers screen) and verified against
 * their licence. A lawyer signs in on the applicant guard and works on the cases
 * of those they represent in the court portal, which stays open to them alone.
 * Passwords are set and reset by an emailed link (the `applicants` broker).
 */
class LawyerAuthController extends Controller
{
    public function showLogin(Request $request): Response
    {
        return Inertia::render('Court/Lawyer/Login', ['status' => session('status')]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $throttle = LoginThrottle::for('lawyer', $credentials['email'], $request->ip());
        if ($throttle->tooManyAttempts()) {
            $seconds = $throttle->availableIn();

            return back()->withErrors(['email' => trans('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)])])->onlyInput('email');
        }

        $lawyer = Applicant::query()->where('email', $credentials['email'])->first();
        // One answer for every refusal: an unknown, unverified or inactive lawyer is not told which.
        if ($lawyer === null || ! CaseParties::isVerifiedLawyer($lawyer) || ! Auth::guard('applicant')->attempt([...$credentials, 'is_active' => true, 'is_lawyer' => true], $request->boolean('remember'))) {
            $throttle->hit();

            return back()->withErrors(['email' => __('lawyer.failed')])->onlyInput('email');
        }

        $request->session()->regenerate();
        $request->session()->forget('acting_as_respondent');
        $throttle->clear();

        return redirect()->intended(route('applicant.cases.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('applicant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('lawyer.login');
    }

    public function showForgot(): Response
    {
        return Inertia::render('Court/Lawyer/ForgotPassword', ['status' => session('status')]);
    }

    /** Sends the reset link to a verified lawyer's address; the answer is the same for any address. */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $lawyer = Applicant::query()->where('email', $data['email'])->first();
        if ($lawyer !== null && CaseParties::isVerifiedLawyer($lawyer)) {
            Password::broker('applicants')->sendResetLink(['email' => $data['email']]);
        }

        return back()->with('status', __('lawyer.reset_link_sent'));
    }

    public function showReset(Request $request, string $token): Response
    {
        return Inertia::render('Court/Lawyer/ResetPassword', ['token' => $token, 'email' => (string) $request->query('email', '')]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::broker('applicants')->reset($data, function (Applicant $lawyer, string $password): void {
            $lawyer->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($lawyer));
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('lawyer.login')->with('status', __('lawyer.password_set'))
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    }
}
