import { useEffect, useRef } from 'react';

/**
 * Runs `task` every `intervalMs` (first run after `initialDelayMs`), pausing
 * while the tab is hidden. The latest `task` is always used.
 */
export function usePolling(task: () => void | Promise<void>, intervalMs: number, initialDelayMs = intervalMs, enabled = true): void {
    const saved = useRef(task);
    saved.current = task;

    useEffect(() => {
        if (!enabled) {
            return;
        }

        let interval: number | undefined;
        const run = () => {
            if (document.visibilityState === 'visible') {
                void saved.current();
            }
        };
        const first = window.setTimeout(() => {
            run();
            interval = window.setInterval(run, intervalMs);
        }, initialDelayMs);

        return () => {
            window.clearTimeout(first);
            if (interval !== undefined) {
                window.clearInterval(interval);
            }
        };
    }, [intervalMs, initialDelayMs, enabled]);
}
