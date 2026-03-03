<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('userHasPermission')) {
    function userHasPermission(string $perm): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        return method_exists($user, 'hasPermission')
            ? (bool) $user->hasPermission($perm)
            : false;
    }
}
