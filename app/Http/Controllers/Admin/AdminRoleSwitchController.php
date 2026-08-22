<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SwitchAdminRoleRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class AdminRoleSwitchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(SwitchAdminRoleRequest $request): RedirectResponse
    {
        $role = $request->user()
            ->roles()
            ->findOrFail($request->validated('role_id'));

        $request->session()->regenerate();
        $request->session()->put(User::ACTIVE_ROLE_SESSION_KEY, $role->id);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', __('app.role_switched', [
                'role' => Str::headline($role->name),
            ]));
    }
}
