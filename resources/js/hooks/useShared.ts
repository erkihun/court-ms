import { usePage } from '@inertiajs/react';
import type { PageProps, SharedProps } from '@/types';

/** Props shared with every page (app info, auth, toasts, navigation). */
export function useShared(): SharedProps {
    return usePage<SharedProps>().props;
}

/** The current page's own props together with the shared ones. */
export function usePageProps<T extends Record<string, unknown>>(): PageProps<T> {
    return usePage<PageProps<T>>().props;
}
