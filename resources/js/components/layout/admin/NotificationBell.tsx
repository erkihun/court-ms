import { BellIcon, CalendarDaysIcon, ChatBubbleLeftEllipsisIcon, ChevronRightIcon, DocumentTextIcon, EyeIcon, XMarkIcon } from '@heroicons/react/24/outline';
import { Link, router } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { Dropdown } from '@/components/ui/Dropdown';
import { usePolling } from '@/hooks/usePolling';
import { useShared } from '@/hooks/useShared';
import { cn } from '@/lib/cn';
import { getJson } from '@/lib/http';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import { initials } from '@/lib/str';
import type { AdminNotificationFeed, AdminNotificationItem } from '@/types';

const POLL_MS = 20_000;

const groupLabels: Record<AdminNotificationItem['type'], string> = {
    message: 'app.Applicant messages',
    case: 'app.New cases',
    hearing: 'app.Upcoming hearings',
    respondent_view: 'app.admin_notifications.respondent_views',
};

const groupIcons = {
    message: { Icon: ChatBubbleLeftEllipsisIcon, tone: 'bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400' },
    case: { Icon: DocumentTextIcon, tone: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400' },
    hearing: { Icon: CalendarDaysIcon, tone: 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400' },
    respondent_view: { Icon: EyeIcon, tone: 'bg-purple-100 text-purple-600 dark:bg-purple-900/40 dark:text-purple-400' },
} as const;

/** Two short rising tones, as in the Blade layout. Silently skipped where audio is unavailable. */
function chime(): void {
    try {
        const AudioContextClass = window.AudioContext ?? (window as unknown as { webkitAudioContext?: typeof AudioContext }).webkitAudioContext;
        if (!AudioContextClass) return;
        const audio = new AudioContextClass();
        const now = audio.currentTime;
        const gain = audio.createGain();
        gain.gain.setValueAtTime(0.0001, now);
        gain.gain.exponentialRampToValueAtTime(0.12, now + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.42);
        gain.connect(audio.destination);
        [
            [740, now, now + 0.16],
            [980, now + 0.18, now + 0.42],
        ].forEach(([frequency, start, stop]) => {
            const tone = audio.createOscillator();
            tone.type = 'sine';
            tone.frequency.setValueAtTime(frequency!, start!);
            tone.connect(gain);
            tone.start(start);
            tone.stop(stop);
        });
        window.setTimeout(() => void audio.close(), 800);
    } catch {
        // Autoplay restrictions or no audio device: the visual toast still shows.
    }
}

export function NotificationBell() {
    const { app } = useShared();
    const [count, setCount] = useState(0);
    const [feed, setFeed] = useState<AdminNotificationFeed | null>(null);
    const [arrivals, setArrivals] = useState<Array<{ id: number; delta: number }>>([]);
    const known = useRef<number | null>(null);

    const loadFeed = useCallback(async () => {
        const data = await getJson<AdminNotificationFeed>(route('admin.notifications.feed'));
        if (data) {
            setFeed(data);
            setCount(data.count);
            known.current = data.count;
        }
    }, []);

    useEffect(() => {
        void loadFeed();
    }, [loadFeed]);

    usePolling(async () => {
        const data = await getJson<{ count: number }>(route('admin.notifications.count'));
        if (!data) return;
        const previous = known.current;
        known.current = data.count;
        setCount(data.count);
        if (previous !== null && data.count > previous) {
            chime();
            const id = Date.now();
            setArrivals((current) => [{ id, delta: data.count - previous }, ...current].slice(0, 3));
            window.setTimeout(() => setArrivals((current) => current.filter((item) => item.id !== id)), 10_000);
        }
    }, POLL_MS);

    const markSeen = (item: AdminNotificationItem) =>
        router.post(route('admin.notifications.markOne'), { type: item.type, sourceId: item.sourceId }, { preserveScroll: true, onSuccess: () => void loadFeed() });

    const markAll = () => router.post(route('admin.notifications.markAll'), {}, { preserveScroll: true, onSuccess: () => void loadFeed() });

    const badge = count > 99 ? '99+' : String(count);

    return (
        <>
            <Dropdown
                panelClassName="w-120 max-w-[92vw]"
                trigger={({ open, toggle }) => (
                    <button
                        type="button"
                        className="topnav-icon-btn relative"
                        onClick={() => {
                            if (!open) void loadFeed();
                            toggle();
                        }}
                        aria-label={t('app.Notifications')}
                        aria-expanded={open}
                    >
                        <BellIcon className="h-[1.1rem] w-[1.1rem]" strokeWidth={1.7} />
                        {count > 0 && (
                            <span className="topnav-notif-badge" aria-label={`${count} ${t('app.Notifications')}`}>
                                {badge}
                            </span>
                        )}
                    </button>
                )}
            >
                {(close) => (
                    <div role="dialog" aria-label={t('app.Notifications')}>
                        <div className="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                            <div className="flex items-center gap-2">
                                <span className="text-[13.5px] font-bold text-slate-800 dark:text-slate-100">{t('app.Notifications')}</span>
                                {count > 0 && (
                                    <span className="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-100 px-1.5 text-[10.5px] font-bold text-blue-700 dark:bg-blue-900/60 dark:text-blue-300">
                                        {count}
                                    </span>
                                )}
                            </div>
                            {count > 0 && (
                                <button
                                    type="button"
                                    onClick={markAll}
                                    className="focus-ring rounded-sm text-[11.5px] font-medium text-slate-500 transition-colors duration-150 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                                >
                                    {t('app.Mark all as seen')}
                                </button>
                            )}
                        </div>

                        <div className="max-h-104 space-y-3 overflow-y-auto overscroll-contain px-2 py-2">
                            {!feed || feed.groups.length === 0 ? (
                                <div className="flex flex-col items-center gap-2 py-8 text-slate-400 dark:text-slate-500">
                                    <BellIcon className="h-8 w-8 opacity-40" strokeWidth={1.4} />
                                    <span className="text-[13px]">{feed ? t('app.youre_all_caught_up') : t('ui.loading')}</span>
                                </div>
                            ) : (
                                feed.groups.map((group) => {
                                    const { Icon, tone } = groupIcons[group.type];

                                    return (
                                        <div key={group.type}>
                                            <div className="mb-1 px-2 text-[10.5px] font-bold tracking-[0.12em] text-slate-400 uppercase dark:text-slate-500">
                                                {t(groupLabels[group.type])}
                                            </div>
                                            <ul className="space-y-0.5">
                                                {group.items.map((item) => (
                                                    <li key={`${item.type}-${item.sourceId}`} className="topnav-notif-row">
                                                        <div className={cn('mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-full', tone)}>
                                                            <Icon className="h-3.5 w-3.5" />
                                                        </div>
                                                        <Link href={item.href} onClick={close} className="min-w-0 flex-1">
                                                            <div className="truncate text-[12.5px] font-semibold text-slate-800 dark:text-slate-100">{item.caseNumber}</div>
                                                            <div className="truncate text-[11.5px] text-slate-500 dark:text-slate-400">{item.text}</div>
                                                            {item.when && <div className="mt-0.5 text-[10.5px] text-slate-400 dark:text-slate-500">{item.when}</div>}
                                                        </Link>
                                                        <button
                                                            type="button"
                                                            onClick={() => markSeen(item)}
                                                            className="focus-ring shrink-0 rounded-md bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-600 transition-colors hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700"
                                                        >
                                                            {t('app.Seen')}
                                                        </button>
                                                    </li>
                                                ))}
                                            </ul>
                                        </div>
                                    );
                                })
                            )}
                        </div>

                        <div className="border-t border-slate-100 px-4 py-2.5 dark:border-slate-800">
                            <Link
                                href={route('admin.notifications.index')}
                                onClick={close}
                                className="focus-ring flex items-center justify-center gap-1.5 rounded-sm text-[12.5px] font-semibold text-blue-600 transition-colors hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                            >
                                {t('app.View all')}
                                <ChevronRightIcon className="h-3.5 w-3.5" />
                            </Link>
                        </div>
                    </div>
                )}
            </Dropdown>

            {arrivals.length > 0 && (
                <div className="pointer-events-none fixed right-5 bottom-5 z-1100 flex w-[min(94vw,26rem)] flex-col gap-3 sm:right-6 sm:bottom-6">
                    {arrivals.map((arrival) => (
                        <div
                            key={arrival.id}
                            role="status"
                            className="pointer-events-auto relative overflow-hidden rounded-[1.35rem] border border-blue-200/90 bg-white/96 text-slate-900 shadow-2xl ring-1 shadow-slate-950/18 ring-white/70 backdrop-blur-xl motion-safe:animate-[toast-in_300ms_ease-out] dark:border-blue-400/25 dark:bg-slate-950/96 dark:text-slate-100 dark:ring-white/10"
                        >
                            <span className="absolute inset-x-0 bottom-0 h-1 bg-blue-600" />
                            <div className="flex items-stretch">
                                <div className="flex w-20 shrink-0 items-center justify-center border-r border-slate-200/70 bg-slate-50/80 px-3 dark:border-slate-800 dark:bg-slate-900/80">
                                    {app.logoUrl ? (
                                        <img src={app.logoUrl} alt={app.name} className="max-h-11 max-w-14 object-contain" />
                                    ) : (
                                        <div className="grid h-11 w-11 place-items-center rounded-2xl bg-slate-900 text-xs font-black tracking-wide text-white uppercase shadow-lg dark:bg-white dark:text-slate-950">
                                            {initials(app.shortName, 2)}
                                        </div>
                                    )}
                                </div>
                                <div className="min-w-0 flex-1 px-4 py-3.5 pe-12">
                                    <span className="block text-[11px] font-bold tracking-[0.16em] text-slate-400 uppercase dark:text-slate-500">{app.name}</span>
                                    <span className="mt-0.5 block text-sm leading-5 font-semibold">{t('app.admin_notifications.new_unread_title')}</span>
                                    <span className="mt-1 block text-xs leading-5 text-slate-500 dark:text-slate-400">
                                        {arrival.delta === 1
                                            ? t('app.admin_notifications.new_unread_singular')
                                            : t('app.admin_notifications.new_unread_plural', { count: arrival.delta })}
                                    </span>
                                </div>
                            </div>
                            <button
                                type="button"
                                onClick={() => setArrivals((current) => current.filter((item) => item.id !== arrival.id))}
                                className="absolute end-3 top-3 grid h-7 w-7 place-items-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-100"
                                aria-label={t('ui.close')}
                            >
                                <XMarkIcon className="h-3.5 w-3.5" />
                            </button>
                        </div>
                    ))}
                </div>
            )}
        </>
    );
}
