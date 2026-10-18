import { usePage } from '@inertiajs/react';
import { routeIs } from '@/lib/route';

/**
 * routeIs() that re-evaluates after every Inertia visit, for components in a
 * persistent layout (they are not remounted when the page changes).
 */
export function useRouteIs(): (...patterns: string[]) => boolean {
    usePage().url;

    return routeIs;
}
