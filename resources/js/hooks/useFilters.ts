import { router } from '@inertiajs/react';
import { useCallback, useState } from 'react';

type FilterValues = Record<string, string>;

/**
 * List filters kept in the query string. Empty values are dropped from the URL,
 * and the visit keeps component state and scroll so the page doesn't jump.
 */
export function useFilters<T extends FilterValues>(url: string, initial: T) {
    const [values, setValues] = useState<T>(initial);

    const visit = useCallback(
        (next: T) => {
            const query = Object.fromEntries(Object.entries(next).filter(([, value]) => value !== ''));
            router.get(url, query, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        },
        [url],
    );

    const set = useCallback(<K extends keyof T>(key: K, value: T[K]) => setValues((current) => ({ ...current, [key]: value })), []);

    /** Change one filter and apply immediately (selects, toggles). */
    const change = useCallback(
        <K extends keyof T>(key: K, value: T[K]) => {
            const next = { ...values, [key]: value };
            setValues(next);
            visit(next);
        },
        [values, visit],
    );

    const apply = useCallback(() => visit(values), [values, visit]);

    const reset = useCallback(() => {
        const cleared = Object.fromEntries(Object.keys(values).map((key) => [key, ''])) as T;
        setValues(cleared);
        visit(cleared);
    }, [values, visit]);

    const active = Object.values(values).some((value) => value !== '');

    return { values, set, change, apply, reset, active };
}
