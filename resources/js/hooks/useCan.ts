import { useCallback } from 'react';
import { useShared } from './useShared';

/**
 * Permission check for showing or hiding UI. It mirrors User::hasPermission()
 * on the server, which remains the only real authorisation.
 */
export function useCan(): (permission: string) => boolean {
    const permissions = useShared().auth.user?.permissions;

    return useCallback(
        (permission: string) => !!permissions && (permissions.includes('*') || permissions.includes(permission)),
        [permissions],
    );
}
