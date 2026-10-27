<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ActiveSessionController extends Controller
{
    /** The profile page's Devices tab lists sessions now (ProfileController::edit). */
    public function index(): RedirectResponse
    {
        return redirect()->to(route('profile.edit').'#devices');
    }

    public function destroy(Request $request, string $session): RedirectResponse
    {
        abort_if(hash_equals($request->session()->getId(), $session), 422, __('auth.session_current_cannot_revoke'));
        DB::table('sessions')->where('user_id', $request->user()->getAuthIdentifier())->where('id', $session)->delete();

        return back()->with('status', __('auth.session_revoked'));
    }

    public function destroyOthers(Request $request): RedirectResponse
    {
        DB::table('sessions')->where('user_id', $request->user()->getAuthIdentifier())->where('id', '!=', $request->session()->getId())->delete();

        return back()->with('status', __('auth.other_sessions_revoked'));
    }

    /**
     * @return array{platform?: string, platform_version?: string, brands?: string}|null
     */
}
