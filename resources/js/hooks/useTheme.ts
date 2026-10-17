import { useCallback, useEffect, useState } from 'react';

export type ThemeMode = 'light' | 'dark' | 'system';
export type Accent = 'blue' | 'teal' | 'violet' | 'emerald' | 'rose';

const ACCENTS: Accent[] = ['blue', 'teal', 'violet', 'emerald', 'rose'];

function read<T extends string>(key: string, allowed: readonly T[], fallback: T): T {
    try {
        const value = localStorage.getItem(key);
        return value !== null && (allowed as readonly string[]).includes(value) ? (value as T) : fallback;
    } catch {
        return fallback;
    }
}

function write(key: string, value: string): void {
    try {
        localStorage.setItem(key, value);
    } catch {
        // Private mode or blocked storage: the choice simply isn't remembered.
    }
}

function apply(mode: ThemeMode): void {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    document.documentElement.classList.toggle('dark', mode === 'dark' || (mode === 'system' && prefersDark));
    document.documentElement.dataset.theme = mode;
}

/** Light/dark/system mode and accent colour, shared with the Blade pages via the same storage keys. */
export function useTheme() {
    const [mode, setModeState] = useState<ThemeMode>(() => read('theme', ['light', 'dark', 'system'] as const, 'system'));
    const [accent, setAccentState] = useState<Accent>(() => read('accent', ACCENTS, 'blue'));

    useEffect(() => {
        apply(mode);
        if (mode !== 'system') {
            return;
        }
        const media = window.matchMedia('(prefers-color-scheme: dark)');
        const sync = () => apply('system');
        media.addEventListener('change', sync);
        return () => media.removeEventListener('change', sync);
    }, [mode]);

    useEffect(() => {
        document.documentElement.dataset.accent = accent;
    }, [accent]);

    const setMode = useCallback((next: ThemeMode) => {
        write('theme', next);
        setModeState(next);
    }, []);

    const setAccent = useCallback((next: Accent) => {
        write('accent', next);
        setAccentState(next);
    }, []);

    return { mode, setMode, accent, setAccent, accents: ACCENTS };
}
