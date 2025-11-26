<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

if (!function_exists('userHasPermission')) {
    function userHasPermission(string $perm): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        // Join user -> role_user -> roles -> permission_role -> permissions
        return DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->join('permission_role', 'permission_role.role_id', '=', 'roles.id')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('role_user.user_id', $user->id)
            ->where('permissions.name', $perm)
            ->exists();
    }
}
