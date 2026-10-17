import { ClockIcon, XMarkIcon } from '@heroicons/react/24/outline';
import { useEffect, useRef, useState } from 'react';
import { useShared } from '@/hooks/useShared';
import { t } from '@/lib/i18n';

/**
 * Warns shortly before the server session expires, using the lifetime and
 * warning window from System Settings. "Continue" reloads, which renews it.
 */
export function SessionTimeoutWarning() {
    const { lifetimeMinutes, warningMinutes, extendOnActivity } = useShared().app.session;
    const [visible, setVisible] = useState(false);
    const warned = useRef(false);

    useEffect(() => {
        const lifetime = lifetimeMinutes * 60_000;
        const warning = warningMinutes * 60_000;
        if (!lifetime || warning >= lifetime) return;

        let timer: number | undefined;
        const schedule = () => {
            window.clearTimeout(timer);
            warned.current = false;
            timer = window.setTimeout(() => {
                warned.current = true;
                setVisible(true);
            }, lifetime - warning);
        };
        const onActivity = () => {
            if (extendOnActivity && !warned.current) schedule();
        };

        const events = ['click', 'keydown', 'mousemove', 'touchstart'] as const;
        events.forEach((event) => window.addEventListener(event, onActivity, { passive: true }));
        schedule();

        return () => {
            window.clearTimeout(timer);
            events.forEach((event) => window.removeEventListener(event, onActivity));
        };
    }, [lifetimeMinutes, warningMinutes, extendOnActivity]);

    if (!visible) return null;

    return (
        <section
            role="alert"
            aria-live="assertive"
            className="fixed right-4 bottom-4 z-10050 w-[min(24rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-amber-500/30 bg-(--surface-strong) text-(--text) shadow-[0_20px_48px_rgb(15_23_42/0.2)] motion-safe:animate-[toast-in_200ms_ease-out] sm:right-5 sm:bottom-5"
        >
            <div className="h-1 bg-linear-to-r from-amber-500 to-orange-500" />
            <div className="p-4">
                <div className="flex items-start gap-3">
                    <span className="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-amber-500/12 text-amber-600">
                        <ClockIcon className="h-[18px] w-[18px]" />
                    </span>
                    <div>
                        <p className="text-[0.85rem] font-extrabold">{t('settings.session_warning_minutes')}</p>
                        <p className="mt-1 text-xs leading-snug text-(--text-muted)">{t('settings.session_warning_browser')}</p>
                    </div>
                    <button
                        type="button"
                        onClick={() => setVisible(false)}
                        className="ms-auto grid h-7 w-7 shrink-0 place-items-center rounded-lg text-(--text-subtle) hover:bg-(--surface-soft) hover:text-(--text)"
                        aria-label={t('settings.session_dismiss')}
                    >
                        <XMarkIcon className="h-4 w-4" />
                    </button>
                </div>
                <div className="mt-3.5 flex justify-end gap-2">
                    <button
                        type="button"
                        onClick={() => setVisible(false)}
                        className="rounded-lg border border-(--border) bg-(--surface-soft) px-3 py-2 text-[0.72rem] font-bold text-(--text-muted)"
                    >
                        {t('settings.session_dismiss')}
                    </button>
                    <button
                        type="button"
                        onClick={() => window.location.reload()}
                        className="rounded-lg bg-[rgb(var(--ac))] px-3 py-2 text-[0.72rem] font-bold text-white hover:brightness-95"
                    >
                        {t('settings.session_continue')}
                    </button>
                </div>
            </div>
        </section>
    );
}
