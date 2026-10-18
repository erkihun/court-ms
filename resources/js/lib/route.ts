import { route as ziggyRoute } from 'ziggy-js';

/**
 * Typed wrapper around Ziggy. Route names are checked against
 * resources/js/types/ziggy.d.ts (regenerate with `npm run routes:types`).
 * The route list itself is embedded by resources/views/app.blade.php.
 */
export const route = ((...args: Parameters<typeof ziggyRoute>) => {
    const [name, params, absolute, config] = args;

    return ziggyRoute(name, params, absolute, config ?? window.Ziggy);
}) as typeof ziggyRoute;

/** True when the current URL matches the given route name pattern(s), e.g. 'cases.*'. */
export function routeIs(...patterns: string[]): boolean {
    const current = ziggyRoute(undefined, undefined, undefined, window.Ziggy);

    return patterns.some((pattern) => current.current(pattern));
}
