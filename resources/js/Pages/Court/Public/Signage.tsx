import { Head, router } from '@inertiajs/react';
import { useEffect, useMemo, useRef, useState, type CSSProperties, type ReactNode, type RefObject } from 'react';
import LocalizedDateDisplay from '@/Components/Calendar/LocalizedDateDisplay';
import LocalizedUiProvider from '@/Components/ui/LocalizedUiProvider';
import { CourtEmblem } from '@/court/components/auth/CourtAuthLayout';
import { usePublicText } from '@/court/i18n/usePublicText';
import { useCourtName } from '@/court/public/CourtPublicHeader';
import { CountUp, CourtSeal, stagger } from '@/court/public/motion';
import type { PublicSignageProps, SignageHearing } from '@/court/types/public';
import { useSystemSettings } from '@/hooks/useSystemSettings';
import '../../../../css/court-public.css';

/** The two stages: a floor-standing screen stands portrait, a wall screen lies landscape. */
type Orientation = 'portrait' | 'landscape';
const STAGES: Record<Orientation, { width: number; height: number }> = {
    portrait: { width: 1080, height: 1920 },
    landscape: { width: 1920, height: 1080 },
};
/** The height of one sitting on the cause list (stage pixels); a page holds as many as fit. */
const ROW_HEIGHT = 72;
/** The seconds each page, notice and side panel stays. */
const PAGE_SECONDS = 12;
const NOTICE_SECONDS = 14;
const PANEL_SECONDS = 16;
/** After this long without a cursor movement the cursor and the screen tools hide. */
const IDLE_MS = 4000;

const STATUS_TONES: Record<string, string> = {
    pending: '#c8a14a',
    active: '#7fb2ff',
    adjourned: '#b79cff',
    closed: '#94a3b8',
    dismissed: '#f28b8b',
};

/** The time of day in the court's zone: hours and minutes (24-hour, as hearing times are stored) and seconds. */
function useCourtClock(serverNow: string, timezone: string) {
    // The screen's own clock may be wrong; the board keeps the server's, read at each refresh.
    const skew = useMemo(() => {
        const server = new Date(serverNow).getTime();
        return Number.isNaN(server) ? 0 : server - Date.now();
    }, [serverNow]);
    const [tick, setTick] = useState(() => Date.now());

    useEffect(() => {
        let timer = 0;
        const next = () => {
            setTick(Date.now());
            timer = window.setTimeout(next, 1000 - ((Date.now() + skew) % 1000));
        };
        timer = window.setTimeout(next, 1000 - ((Date.now() + skew) % 1000));
        return () => window.clearTimeout(timer);
    }, [skew]);

    const format = useMemo(() => {
        const options: Intl.DateTimeFormatOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hourCycle: 'h23' };
        try {
            return new Intl.DateTimeFormat('en-GB', { ...options, timeZone: timezone });
        } catch {
            return new Intl.DateTimeFormat('en-GB', options);
        }
    }, [timezone]);

    const parts = Object.fromEntries(format.formatToParts(new Date(tick + skew)).map((part) => [part.type, part.value]));
    return { tick, hm: `${parts.hour}:${parts.minute}`, hours: parts.hour, minutes: parts.minute, seconds: parts.second, format };
}

/**
 * The stage for this screen and its scale. A portrait screen (taller than
 * wide) gets the portrait stage; `?orientation=portrait|landscape` fixes it
 * for a kiosk whose browser reports its size wrongly.
 */
function useStage(): { orientation: Orientation; scale: number } {
    const read = (): { orientation: Orientation; scale: number } => {
        const forced = new URLSearchParams(window.location.search).get('orientation');
        const orientation: Orientation = forced === 'portrait' || forced === 'landscape'
            ? forced
            : (window.innerHeight > window.innerWidth ? 'portrait' : 'landscape');
        const stage = STAGES[orientation];
        return { orientation, scale: Math.min(window.innerWidth / stage.width, window.innerHeight / stage.height) };
    };
    const [state, setState] = useState(read);
    useEffect(() => {
        const fit = () => setState(read());
        window.addEventListener('resize', fit);
        return () => window.removeEventListener('resize', fit);
    }, []);
    return state;
}

/** How many items of a given height fit in a box (stage pixels, so the scale does not matter). */
function useFit(box: RefObject<HTMLElement | null>, itemHeight: number, fallback: number, watch: unknown): number {
    const [count, setCount] = useState(fallback);
    useEffect(() => {
        const element = box.current;
        if (!element || typeof ResizeObserver === 'undefined') return undefined;
        const measure = () => setCount(Math.max(1, Math.floor(element.clientHeight / itemHeight)));
        measure();
        const observer = new ResizeObserver(measure);
        observer.observe(element);
        return () => observer.disconnect();
    }, [box, itemHeight, watch]);
    return count;
}

/** Cycles 0..count-1 every `seconds`; restarts when `reset` changes. */
function useRotation(count: number, seconds: number, reset?: unknown): number {
    const [index, setIndex] = useState(0);
    useEffect(() => setIndex(0), [reset]);
    useEffect(() => {
        if (count < 2) return undefined;
        const timer = window.setInterval(() => setIndex((current) => (current + 1) % count), seconds * 1000);
        return () => window.clearInterval(timer);
    }, [count, seconds]);
    return count > 0 ? index % count : 0;
}

/** Keeps the screen awake while the board shows, where the browser allows it. */
function useWakeLock() {
    useEffect(() => {
        let lock: WakeLockSentinel | null = null;
        const acquire = async () => {
            if (document.visibilityState !== 'visible' || !('wakeLock' in navigator)) return;
            try {
                lock = await navigator.wakeLock.request('screen');
            } catch {
                // Not allowed here (no user gesture, a policy): the screen's own power settings apply.
            }
        };
        void acquire();
        document.addEventListener('visibilitychange', acquire);
        return () => {
            document.removeEventListener('visibilitychange', acquire);
            void lock?.release().catch(() => undefined);
        };
    }, []);
}

function Card({ title, aside, children, className = '' }: { title: string; aside?: ReactNode; children: ReactNode; className?: string }) {
    return (
        <section className={`court-signage-card flex min-h-0 min-w-0 flex-col p-7 ${className}`}>
            <div className="mb-5 flex items-center justify-between gap-4">
                <h2 className="text-[26px] font-semibold tracking-wide text-(--court-brass-soft)">{title}</h2>
                {aside}
            </div>
            {children}
        </section>
    );
}

function Dots({ count, active, seconds }: { count: number; active: number; seconds: number }) {
    if (count < 2) return null;
    return (
        <span aria-hidden="true" className="flex items-center gap-2">
            {Array.from({ length: Math.min(count, 12) }).map((_, index) => (
                <span key={index} className="relative h-2.5 w-10 overflow-hidden rounded-full bg-white/15">
                    {index === active && (
                        <span key={`${active}-${count}`} className="court-signage-fill absolute inset-0 origin-left rounded-full bg-(--court-brass)" style={{ '--page': `${seconds}s` } as CSSProperties} />
                    )}
                    {index < active && <span className="absolute inset-0 rounded-full bg-(--court-brass)/50" />}
                </span>
            ))}
        </span>
    );
}

/**
 * The court's public display (PublicSignageController), for the screen in
 * the lobby, standing (portrait, 1080x1920) or on a wall (landscape,
 * 1920x1080): the court's name and clock, today's cause list paged a screen
 * at a time with the next sitting marked, today's figures and the caseload,
 * and the notices, one after another. It is a screen, not a document: no site
 * header or footer, the stage scaled to the display, the cursor and the
 * full-screen button hidden while nobody moves the mouse, and the screen kept
 * awake. It re-reads its data every minute; a failed read leaves the last
 * figures up and says how old they are, never an error page.
 */
export default function PublicSignage(props: PublicSignageProps) {
    const { t, tx, fill, locale } = usePublicText();
    const { getBoolean, getString } = useSystemSettings();
    const courtName = useCourtName();
    const logoUrl = getString('general.identity_system_logo_url');
    const { now, timezone, totalCases, filedToday, hearingsToday, statusCounts, categoryCounts, todayCases, todayHearings, announcements, refreshSeconds } = props;
    const s = (key: string) => t(`court.publicSite.signage.${key}`);
    const number = useMemo(() => new Intl.NumberFormat(locale === 'am' ? 'am-ET' : 'en-US'), [locale]);
    const formatNumber = (value: number) => number.format(value);

    const clock = useCourtClock(now, timezone);
    const { orientation, scale } = useStage();
    const portrait = orientation === 'portrait';
    const stage = STAGES[orientation];
    const listBox = useRef<HTMLDivElement>(null);
    // Re-measured when the orientation changes or the list first appears.
    const rows = useFit(listBox, ROW_HEIGHT, 8, `${orientation}-${todayHearings.length > 0}`);
    const panelBox = useRef<HTMLDivElement>(null);
    useWakeLock();

    // Re-read every minute in place; a failed read keeps what is on screen.
    const [readAt, setReadAt] = useState(() => Date.now());
    useEffect(() => setReadAt(Date.now()), [now]);
    useEffect(() => {
        const timer = window.setInterval(() => router.reload({
            showProgress: false,
            onHttpException: () => false,
            onNetworkError: () => false,
        }), Math.max(30, refreshSeconds) * 1000);
        return () => window.clearInterval(timer);
    }, [refreshSeconds]);
    const stale = clock.tick - readAt > Math.max(30, refreshSeconds) * 2500;
    const readTime = clock.format.format(new Date(now)).slice(0, 5);

    // Nobody at the mouse: hide the cursor and the screen tools.
    const [idle, setIdle] = useState(false);
    const [fullscreen, setFullscreen] = useState(false);
    useEffect(() => {
        let timer = window.setTimeout(() => setIdle(true), IDLE_MS);
        const wake = () => {
            setIdle(false);
            window.clearTimeout(timer);
            timer = window.setTimeout(() => setIdle(true), IDLE_MS);
        };
        const onFullscreen = () => setFullscreen(document.fullscreenElement !== null);
        window.addEventListener('pointermove', wake);
        window.addEventListener('keydown', wake);
        document.addEventListener('fullscreenchange', onFullscreen);
        return () => {
            window.clearTimeout(timer);
            window.removeEventListener('pointermove', wake);
            window.removeEventListener('keydown', wake);
            document.removeEventListener('fullscreenchange', onFullscreen);
        };
    }, []);
    const toggleFullscreen = () => {
        if (document.fullscreenElement) void document.exitFullscreen().catch(() => undefined);
        else void document.documentElement.requestFullscreen?.().catch(() => undefined);
    };

    // The cause list, a page at a time; the next sitting is marked and the ones before it dimmed.
    const pages = useMemo(() => {
        const out: SignageHearing[][] = [];
        for (let index = 0; index < todayHearings.length; index += rows) out.push(todayHearings.slice(index, index + rows));
        return out;
    }, [todayHearings, rows]);
    const page = useRotation(pages.length, PAGE_SECONDS, `${pages.length}-${rows}`);
    const nextTime = todayHearings.find((hearing) => hearing.time !== null && hearing.time >= clock.hm)?.time ?? null;

    const notice = useRotation(announcements.length, NOTICE_SECONDS, announcements.length);
    const panel = useRotation(2, PANEL_SECONDS);
    // The turning panel shows as many filings (44px) or case types (56px) as its box holds.
    const panelFits = { filings: useFit(panelBox, 44, 3, orientation), types: useFit(panelBox, 56, 3, orientation) };
    const statusTotal = Math.max(1, statusCounts.reduce((sum, stat) => sum + stat.total, 0));
    const maxType = Math.max(1, ...categoryCounts.map((category) => category.total));
    const statusLabel = (label: string) => (label === 'unspecified' ? s('unspecified') : tx(`cm.cases.status.${label.toLowerCase()}`, label));
    const current = announcements[notice];
    const columns = portrait ? 'grid-cols-[132px_270px_minmax(0,1fr)_190px]' : 'grid-cols-[150px_300px_minmax(0,1fr)_260px]';

    const figures = (
        <Card title={s('today')} aside={<span className="court-live h-3! w-3!" aria-hidden="true" />} className="court-signage-figures">
            <dl className="grid grid-cols-3 gap-6">
                {([
                    ['hearingsToday', hearingsToday],
                    ['filedToday', filedToday],
                    ['totalCases', totalCases],
                ] as const).map(([key, value]) => (
                    <div key={key} className="flex min-w-0 flex-col-reverse">
                        <dt className="mt-2 text-lg leading-snug text-slate-300">{s(key)}</dt>
                        <dd className={`font-semibold tabular-nums leading-none text-white ${portrait ? 'text-[64px]' : 'text-[56px]'}`}>
                            <CountUp value={value} format={formatNumber} />
                        </dd>
                    </div>
                ))}
            </dl>
        </Card>
    );

    const caseload = (
        <Card title={s('caseload')} className="court-signage-caseload">
            <div className="flex h-4 overflow-hidden rounded-full bg-white/10" aria-hidden="true">
                {statusCounts.map((stat) => (
                    <span key={stat.label} style={{ width: `${(stat.total / statusTotal) * 100}%`, background: STATUS_TONES[stat.label.toLowerCase()] ?? '#64748b' }} />
                ))}
            </div>
            <ul className={`mt-5 grid gap-y-3 ${portrait ? 'grid-cols-1' : 'grid-cols-2 gap-x-6'}`}>
                {statusCounts.map((stat) => (
                    <li key={stat.label} className="flex items-center justify-between gap-3 text-xl">
                        <span className="flex min-w-0 items-center gap-2.5 text-slate-200">
                            <span aria-hidden="true" className="h-3 w-3 shrink-0 rounded-sm" style={{ background: STATUS_TONES[stat.label.toLowerCase()] ?? '#64748b' }} />
                            <span className="truncate">{statusLabel(stat.label)}</span>
                        </span>
                        <span className="tabular-nums text-white">
                            {formatNumber(stat.total)}
                            <span className="ml-2 text-slate-400">{Math.round((stat.total / statusTotal) * 100)}%</span>
                        </span>
                    </li>
                ))}
            </ul>
        </Card>
    );

    // Turns between today's filings and the largest case types.
    const turning = (
        <Card
            className="court-signage-panel"
            title={panel === 0 ? s('filedToday') : s('caseTypes')}
            aside={<Dots count={2} active={panel} seconds={PANEL_SECONDS} />}
        >
            <div ref={panelBox} className="min-h-0 flex-1 overflow-hidden">
            <div key={panel}>
                {panel === 0 ? (
                    todayCases.length === 0 ? (
                        <p className="court-signage-in text-xl text-slate-300">{s('noneFiled')}</p>
                    ) : (
                        <ul className="grid">
                            {todayCases.slice(0, panelFits.filings).map((item, index) => (
                                <li key={item.id} className="court-signage-in grid h-11 grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-4 border-b border-white/6" style={stagger(index)}>
                                    <span className="font-mono text-[22px] font-semibold text-white">{item.case_number ?? '—'}</span>
                                    <span className="truncate text-lg text-slate-400">{item.case_type ?? s('untyped')}</span>
                                    <span className="text-lg text-slate-300" title={s('party')}>{item.applicant ?? '—'}</span>
                                </li>
                            ))}
                        </ul>
                    )
                ) : (
                    <ul className="grid">
                        {categoryCounts.slice(0, panelFits.types).map((category, index) => (
                            <li key={category.name ?? '—'} className="court-signage-in flex h-14 flex-col justify-center" style={stagger(index)}>
                                <div className="mb-1.5 flex items-baseline justify-between gap-4 text-xl">
                                    <span className="truncate text-slate-100">{category.name ?? s('untyped')}</span>
                                    <span className="tabular-nums text-white">{formatNumber(category.total)}</span>
                                </div>
                                <div className="h-2.5 overflow-hidden rounded-full bg-white/10" aria-hidden="true">
                                    <div className="h-full rounded-full bg-linear-to-r from-(--court-brass-strong) to-(--court-brass)" style={{ width: `${(category.total / maxType) * 100}%` }} />
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
            </div>
        </Card>
    );

    return (
        <LocalizedUiProvider>
            <Head title={`${s('title')} · ${courtName}`} />

            <div
                className="court-site court-signage"
                data-motion={getBoolean('appearance.enable_ui_animations', true) ? 'on' : 'off'}
                data-orientation={orientation}
                data-idle={idle ? '' : undefined}
            >
                <div
                    className="court-signage-stage"
                    style={{ width: stage.width, height: stage.height, '--scale': scale } as CSSProperties}
                >
                    {/* The court and the clock. */}
                    <header className="court-signage-head relative flex items-center justify-between gap-8">
                        <CourtSeal className="court-signage-seal" />
                        <div className="flex min-w-0 items-center gap-6">
                            {logoUrl ? (
                                <img src={logoUrl} alt="" className="h-24 w-24 shrink-0 rounded-full bg-white object-contain p-1.5 ring-2 ring-(--court-brass)/50" />
                            ) : (
                                <span aria-hidden="true" className="flex h-24 w-24 shrink-0 items-center justify-center rounded-full bg-(--court-ink-soft) text-(--court-brass) ring-2 ring-(--court-brass)/50">
                                    <CourtEmblem className="h-14 w-14" />
                                </span>
                            )}
                            <div className="min-w-0">
                                <p className="text-lg font-semibold uppercase tracking-[0.2em] text-(--court-brass)">{t('court.publicSite.authority')} · {t('court.publicSite.courtKind')}</p>
                                <h1 className={`mt-1 font-semibold leading-tight text-white ${portrait ? 'line-clamp-3 text-[40px]' : 'line-clamp-2 text-[44px]'}`}>{courtName}</h1>
                            </div>
                        </div>
                        <div className="shrink-0 text-right">
                            <p className="font-semibold tabular-nums leading-none text-white" aria-label={clock.hm}>
                                <span className={portrait ? 'text-[104px]' : 'text-[92px]'}>{clock.hours}<span className="court-signage-colon">:</span>{clock.minutes}</span>
                                <span className="ml-2 text-4xl text-(--court-brass)">{clock.seconds}</span>
                            </p>
                            <p className="mt-2 text-2xl text-slate-300"><LocalizedDateDisplay value={now.slice(0, 10)} /></p>
                        </div>
                    </header>

                    <div className="court-signage-rule court-signage-line" aria-hidden="true" />

                    {/* Today's cause list. */}
                    <Card
                        className="court-signage-list"
                        title={s('causeList')}
                        aside={(
                            <span className="flex items-center gap-4 text-xl text-slate-300">
                                {pages.length > 1 && fill(s('pageOf'), { n: page + 1, total: pages.length })}
                                <Dots count={pages.length} active={page} seconds={PAGE_SECONDS} />
                            </span>
                        )}
                    >
                        {todayHearings.length === 0 ? (
                            <div className="flex flex-1 flex-col items-center justify-center gap-5 text-center">
                                <CourtEmblem className="h-24 w-24 text-(--court-brass)/60" />
                                <p className="text-3xl text-slate-300">{s('noneToday')}</p>
                            </div>
                        ) : (
                            <div role="table" aria-label={s('causeList')} className="flex min-h-0 flex-1 flex-col">
                                <div role="row" className={`grid ${columns} gap-4 border-b border-white/10 px-5 pb-3 text-base font-semibold uppercase tracking-[0.16em] text-slate-400`}>
                                    <span role="columnheader">{t('court.publicSite.hearings.time')}</span>
                                    <span role="columnheader">{t('court.publicSite.hearings.case')}</span>
                                    <span role="columnheader">{t('court.publicSite.hearings.caseType')} · {t('court.publicSite.hearings.sitting')}</span>
                                    <span role="columnheader">{t('court.publicSite.hearings.room')}</span>
                                </div>
                                <div ref={listBox} className="min-h-0 flex-1 overflow-hidden">
                                <div key={`${page}-${rows}`} role="rowgroup">
                                    {(pages[page] ?? []).map((hearing, index) => {
                                        const past = hearing.time !== null && hearing.time < clock.hm;
                                        const next = hearing.time !== null && hearing.time === nextTime;
                                        return (
                                            <div
                                                key={hearing.id}
                                                role="row"
                                                className={`court-signage-row court-signage-in grid ${columns} h-[72px] items-center gap-4 border-b border-white/6 px-5`}
                                                data-next={next ? '' : undefined}
                                                data-past={past ? '' : undefined}
                                                style={stagger(index)}
                                            >
                                                <span role="cell" className="text-[32px] font-semibold tabular-nums text-white">{hearing.time ?? '—'}</span>
                                                <span role="cell" className="truncate font-mono text-[26px] font-semibold text-white">{hearing.case_number ?? '—'}</span>
                                                <span role="cell" className="min-w-0">
                                                    <span className="block truncate text-[22px] text-slate-100">{hearing.case_type ?? '—'}</span>
                                                    {hearing.type && <span className="block truncate text-lg text-slate-400">{hearing.type}</span>}
                                                </span>
                                                <span role="cell" className="flex min-w-0 items-center justify-between gap-3">
                                                    <span className="truncate text-[22px] text-slate-100">{hearing.location ?? '—'}</span>
                                                    {next && (
                                                        <span className="shrink-0 rounded-full bg-(--court-brass) px-3 py-1 text-base font-bold uppercase tracking-wider text-(--court-ink)">
                                                            {s('next')}
                                                        </span>
                                                    )}
                                                </span>
                                            </div>
                                        );
                                    })}
                                </div>
                                </div>
                                <p className="pt-4 text-lg text-slate-400">{t('court.publicSite.hearings.note')}</p>
                            </div>
                        )}
                    </Card>

                    <div className="court-signage-side">
                        {figures}
                        {caseload}
                        {turning}
                    </div>

                    {/* The notices, one after another, and how fresh the board is. */}
                    <footer className="court-signage-foot flex items-center gap-8 border-t border-(--court-brass)/25 bg-black/25">
                        <span className="inline-flex shrink-0 items-center rounded-md bg-(--court-brass) px-4 py-2 text-xl font-bold uppercase tracking-[0.14em] text-(--court-ink)">
                            {s('notices')}
                        </span>
                        <div className="min-w-0 flex-1" aria-live="off">
                            {current ? (
                                <div key={current.id} className="court-signage-in min-w-0">
                                    <p className="truncate text-[26px] font-semibold text-white">{current.title}</p>
                                    {current.summary && <p className={`mt-1 text-xl text-slate-300 ${portrait ? 'line-clamp-2' : 'truncate'}`}>{current.summary}</p>}
                                </div>
                            ) : (
                                <p className="text-xl text-slate-400">{s('noNotices')}</p>
                            )}
                        </div>
                        <div className={`flex shrink-0 gap-3 ${portrait ? 'flex-col items-end' : 'items-center gap-8'}`}>
                            <Dots count={announcements.length} active={notice} seconds={NOTICE_SECONDS} />
                            <p className={`text-lg ${stale ? 'font-semibold text-amber-300' : 'text-slate-400'}`} role="status">
                                {fill(s(stale ? 'stale' : 'updated'), { time: readTime })}
                            </p>
                        </div>
                    </footer>
                </div>

                <button
                    type="button"
                    onClick={toggleFullscreen}
                    className="court-signage-tools fixed left-1/2 top-4 z-20 -translate-x-1/2 rounded-md border border-white/20 bg-(--court-ink)/90 px-4 py-2 text-sm font-semibold text-white shadow-lg hover:bg-(--court-navy) focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-(--court-brass)"
                >
                    {fullscreen ? s('exitFullscreen') : s('fullscreen')}
                </button>
            </div>
        </LocalizedUiProvider>
    );
}
