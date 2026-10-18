import { useCallback, useEffect, useState } from 'react';
import { usePolling } from '@/hooks/usePolling';
import { getJson } from '@/lib/http';

const POLL_MS = 60_000;

/** Loads a notification feed on mount, every minute while visible, and on demand. */
export function useFeed<T extends { count: number }>(url: string) {
    const [feed, setFeed] = useState<T | null>(null);

    const reload = useCallback(async () => {
        const data = await getJson<T>(url);
        if (data) setFeed(data);
    }, [url]);

    useEffect(() => {
        void reload();
    }, [reload]);

    usePolling(reload, POLL_MS);

    return { feed, reload };
}
