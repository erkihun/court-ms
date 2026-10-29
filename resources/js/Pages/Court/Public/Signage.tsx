import { Head, router } from '@inertiajs/react';
import { useEffect, useMemo, useRef, useState, type CSSProperties, type ReactNode, type RefObject } from 'react';
import LocalizedUiProvider from '@/Components/ui/LocalizedUiProvider';
import { CourtEmblem } from '@/court/components/auth/CourtAuthLayout';
import publicSite from '@/court/i18n/publicSite';
import { CountUp } from '@/court/public/motion';
import type { PublicSignageProps, SignageHearing } from '@/court/types/public';
import { useBranding } from '@/hooks/useBranding';
import { useLocale } from '@/hooks/useLocale';
import { useSystemSettings } from '@/hooks/useSystemSettings';
import { gregorianIsoToEthiopian } from '@/lib/calendar/ethiopianCalendar';
import '../../../../css/court-public.css';

/** The two stages: the floor-standing screen stands portrait, a wall screen lies landscape. */
type Orientation = 'portrait' | 'landscape';
const STAGES: Record<Orientation, { width: number; height: number }> = {
    portrait: { width: 1080, height: 1920 },
    landscape: { width: 1920, height: 1080 },
};
/** One sitting on the cause list (stage pixels); a page holds as many as fit. */
const ROW_HEIGHT = 62;
/** The seconds each page and each notice stays. */
const PAGE_SECONDS = 12;
const NOTICE_SECONDS = 14;
/** After this long without a cursor movement the cursor and the screen tools hide. */
const IDLE_MS = 4000;
/** Every few minutes the whole stage moves a few pixels, so nothing burns into the panel. */
const SHIFT_MINUTES = 4;
const SHIFTS = [[0, 0], [3, 2], [-2, 3], [2, -3], [-3, -2]] as const;

const STATUS_TONES: Record<string, string> = {
    pending: '#d4ad57',
    active: '#7fb2ff',
    adjourned: '#b79cff',
    closed: '#8b98ab',
    dismissed: '#ee8b8b',
};

const ETH_MONTHS = {
    am: ['', 'መስከረም', 'ጥቅምት', 'ኅዳር', 'ታኅሣሥ', 'ጥር', 'የካቲት', 'መጋቢት', 'ሚያዝያ', 'ግንቦት', 'ሰኔ', 'ሐምሌ', 'ነሐሴ', 'ጳጉሜ'],
    en: ['', 'Meskerem', 'Tikimt', 'Hidar', 'Tahsas', 'Tir', 'Yekatit', 'Megabit', 'Miyazia', 'Ginbot', 'Sene', 'Hamle', 'Nehase', 'Pagume'],
};
const WEEKDAYS_AM = ['እሑድ', 'ሰኞ', 'ማክሰኞ', 'ረቡዕ', 'ሐሙስ', 'ዓርብ', 'ቅዳሜ'];
/** The Ethiopian clock counts from dawn: 07:00 is 1 in the morning. The part of the day, by the international hour. */
const ETH_DAY_PARTS: Array<[number, string]> = [[0, 'ከሌሊቱ'], [6, 'ከጠዋቱ'], [12, 'ከቀኑ'], [18, 'ከምሽቱ']];

type Lang = 'en' | 'am';
type SignageText = typeof publicSite.en.signage;
type TextKey = Exclude<keyof SignageText, 'status'>;

const fill = (template: string, values: Record<string, string | number>): string =>
    Object.entries(values).reduce((out, [key, value]) => out.split(`:${key}`).join(String(value)), template);

/** The time of day in the court's zone, on the server's clock (read at each refresh), ticking each second. */
function useCourtClock(serverNow: string, timezone: string) {
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
    const hour = Number(parts.hour);
    const minute = Number(parts.minute);
    const dayPart = [...ETH_DAY_PARTS].reverse().find(([from]) => hour >= from)?.[1] ?? '';
    return {
        tick,
        hm: `${parts.hour}:${parts.minute}`,
        minutesOfDay: hour * 60 + minute,
        hours: parts.hour,
        minutes: parts.minute,
        seconds: parts.second,
        ethiopian: `${dayPart} ${((hour + 6) % 12) || 12}:${parts.minute}`,
        format,
    };
}

/** The stage for this screen and its scale; `?orientation=portrait|landscape` fixes it for a kiosk. */
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

/** A label in the screen's language with the other language beneath it, smaller: the lobby reads both. */
function Bi({ primary, secondary, className = '', secondaryClassName = '' }: { primary: string; secondary: string; className?: string; secondaryClassName?: string }) {
    return (
        <span className={`sg-bi ${className}`}>
            <span className="sg-bi-primary">{primary}</span>
            {secondary && secondary !== primary && <span className={`sg-bi-secondary ${secondaryClassName}`}>{secondary}</span>}
        </span>
    );
}

/** A section's heading: a brass tick, the two-language title, and whatever stands at its end. */
function SectionHead({ primary, secondary, aside }: { primary: string; secondary: string; aside?: ReactNode }) {
    return (
        <div className="sg-section-head">
            <h2><Bi primary={primary} secondary={secondary} /></h2>
            {aside}
        </div>
    );
}

/** The court's medallion: the logo set in System Settings, else the scales, inside a turning ring of brass dots. */
function Medallion({ logoUrl }: { logoUrl: string | null }) {
    return (
        <span className="sg-medallion" aria-hidden="true">
            <svg viewBox="0 0 200 200" className="sg-orbit">
                <circle cx="100" cy="100" r="96" fill="none" stroke="currentColor" strokeWidth="2.2" strokeDasharray="0.6 9.4" strokeLinecap="round" />
                <circle cx="100" cy="100" r="88" fill="none" stroke="currentColor" strokeWidth="0.6" />
            </svg>
            {logoUrl
                ? <img src={logoUrl} alt="" className="sg-medallion-logo" />
                : <span className="sg-medallion-mark"><CourtEmblem className="h-[58%] w-[58%]" /></span>}
        </span>
    );
}

function Progress({ count, active, seconds }: { count: number; active: number; seconds: number }) {
    if (count < 2) return null;
    return (
        <span aria-hidden="true" className="sg-progress">
            {Array.from({ length: Math.min(count, 12) }).map((_, index) => (
                <span key={index} data-done={index < active ? '' : undefined}>
                    {index === active && <span key={`${active}-${count}`} className="sg-progress-fill" style={{ '--page': `${seconds}s` } as CSSProperties} />}
                </span>
            ))}
        </span>
    );
}

/**
 * The court's public display (PublicSignageController) for the lobby: the
 * floor-standing screen (portrait, 1080x1920) or a wall (landscape,
 * 1920x1080). Composed like the court's letterhead over a departure board:
 * the crest and the court's name, the clock with the Ethiopian hour and both
 * calendars' dates, the next sitting with how long until it, today's cause
 * list a page at a time, today's figures and the caseload, then the notices
 * and the registry's hours. Every label is in both languages, the screen's
 * first. It is a screen, not a document: the stage is scaled to the display,
 * shifts a few pixels now and then against burn-in, hides the cursor while
 * the mouse is still and keeps the screen awake. It re-reads its data every
 * minute; a failed read leaves the last figures up and says how old they are.
 */
export default function PublicSignage(props: PublicSignageProps) {
    const { locale } = useLocale();
    const { getBoolean } = useSystemSettings();
    const branding = useBranding();
    const { now, timezone, totalCases, filedToday, hearingsToday, statusCounts, registry, todayHearings, announcements, refreshSeconds } = props;

    const lang: Lang = locale === 'am' ? 'am' : 'en';
    const other: Lang = lang === 'am' ? 'en' : 'am';
    const text = (key: TextKey, values?: Record<string, string | number>) => {
        const value = (publicSite[lang].signage[key] ?? publicSite.en.signage[key]) as string;
        return values ? fill(value, values) : value;
    };
    const otherText = (key: TextKey, values?: Record<string, string | number>) => {
        const value = (publicSite[other].signage[key] ?? '') as string;
        return values ? fill(value, values) : value;
    };
    const bi = (key: TextKey, values?: Record<string, string | number>) => ({ primary: text(key, values), secondary: otherText(key, values) });
    const statusLabel = (label: string, of: Lang) => (publicSite[of].signage.status as Record<string, string>)[label.toLowerCase()] ?? label;

    // The official Amharic name falls back to the English one when it is not set; the Amharic brand name is then better.
    const names = {
        am: (branding.officialNameAm && branding.officialNameAm !== branding.officialNameEn ? branding.officialNameAm : '') || branding.brandNameAm,
        en: branding.officialNameEn || branding.brandNameEn,
    };
    const number = useMemo(() => new Intl.NumberFormat(lang === 'am' ? 'am-ET' : 'en-US'), [lang]);
    const formatNumber = (value: number) => number.format(value);

    const clock = useCourtClock(now, timezone);
    const { orientation, scale } = useStage();
    const portrait = orientation === 'portrait';
    const stage = STAGES[orientation];
    const listBox = useRef<HTMLDivElement>(null);
    // Re-measured when the orientation changes or the list first appears.
    const rows = useFit(listBox, ROW_HEIGHT, 8, `${orientation}-${todayHearings.length > 0}`);
    useWakeLock();

    // Today in both calendars, from the server's date in the court's zone.
    const todayIso = now.slice(0, 10);
    const dates = useMemo(() => {
        const [year, month, day] = todayIso.split('-').map(Number);
        const weekday = new Date(Date.UTC(year, month - 1, day)).getUTCDay();
        const eth = gregorianIsoToEthiopian(todayIso);
        const gregorian = new Intl.DateTimeFormat('en-GB', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' }).format(new Date(Date.UTC(year, month - 1, day)));
        const weekdayEn = new Intl.DateTimeFormat('en-GB', { weekday: 'long', timeZone: 'UTC' }).format(new Date(Date.UTC(year, month - 1, day)));
        return {
            weekday: { am: WEEKDAYS_AM[weekday], en: weekdayEn },
            ethiopian: eth ? { am: `${ETH_MONTHS.am[eth.month]} ${eth.day} ቀን ${eth.year} ዓ.ም.`, en: `${ETH_MONTHS.en[eth.month]} ${eth.day}, ${eth.year} E.C.` } : null,
            gregorian,
        };
    }, [todayIso]);

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

    const shift = SHIFTS[Math.floor(clock.tick / (SHIFT_MINUTES * 60_000)) % SHIFTS.length];

    // The next sitting, and the cause list a page at a time.
    const minutesOf = (time: string | null) => (time ? Number(time.slice(0, 2)) * 60 + Number(time.slice(3, 5)) : -1);
    const nextTime = todayHearings.find((hearing) => hearing.time !== null && minutesOf(hearing.time) >= clock.minutesOfDay)?.time ?? null;
    const nextHearings = nextTime ? todayHearings.filter((hearing) => hearing.time === nextTime) : [];
    const next = nextHearings[0] ?? null;
    const untilNext = next ? minutesOf(next.time) - clock.minutesOfDay : 0;
    const countdown = (of: Lang) => {
        const say = of === lang ? text : otherText;
        if (untilNext <= 0) return say('now');
        return untilNext < 60 ? say('inMinutes', { m: untilNext }) : say('inHours', { h: Math.floor(untilNext / 60), m: untilNext % 60 });
    };

    const pages = useMemo(() => {
        const out: SignageHearing[][] = [];
        for (let index = 0; index < todayHearings.length; index += rows) out.push(todayHearings.slice(index, index + rows));
        return out;
    }, [todayHearings, rows]);
    const page = useRotation(pages.length, PAGE_SECONDS, `${pages.length}-${rows}`);

    const notice = useRotation(announcements.length, NOTICE_SECONDS, announcements.length);
    const current = announcements[notice];
    const statusTotal = Math.max(1, statusCounts.reduce((sum, stat) => sum + stat.total, 0));

    return (
        <LocalizedUiProvider>
            <Head title={`${text('title')} · ${names[lang]}`} />

            <div
                className="court-site court-signage"
                data-motion={getBoolean('appearance.enable_ui_animations', true) ? 'on' : 'off'}
                data-orientation={orientation}
                data-idle={idle ? '' : undefined}
                lang={lang}
            >
                <div
                    className="court-signage-stage"
                    style={{ width: stage.width, height: stage.height, '--scale': scale, '--sx': `${shift[0]}px`, '--sy': `${shift[1]}px` } as CSSProperties}
                >
                    {/* The crest: the court as its letterhead names it. */}
                    <header className="sg-crest">
                        <span className="sg-colonnade" aria-hidden="true" />
                        <span className="sg-sheen" aria-hidden="true" />
                        <Medallion logoUrl={branding.logoUrl} />
                        <div className="sg-crest-text">
                            <p className="sg-eyebrow">{publicSite[lang].authority} · {publicSite[lang].courtKind}</p>
                            <h1 className="sg-court" lang={lang}>{names[lang]}</h1>
                            {names[other] && names[other] !== names[lang] && <p className="sg-court-other" lang={other}>{names[other]}</p>}
                        </div>
                    </header>

                    {/* The clock: the hour as hearings are set, the Ethiopian hour, and today in both calendars. */}
                    <section className="sg-clock" aria-label={clock.hm}>
                        <div>
                            <p className="sg-time">
                                {clock.hours}<span className="sg-colon">:</span>{clock.minutes}
                                <span className="sg-seconds">{clock.seconds}</span>
                            </p>
                            <p className="sg-eth-time" lang="am">
                                {clock.ethiopian}
                                <span>{lang === 'am' ? text('ethiopianTime') : `· ${text('ethiopianTime')}`}</span>
                            </p>
                        </div>
                        <div className="sg-dates">
                            <p className="sg-weekday">{dates.weekday[lang]}<span>{dates.weekday[other]}</span></p>
                            {dates.ethiopian && <p className="sg-date-eth" lang="am">{dates.ethiopian.am}</p>}
                            <p className="sg-date-greg">{lang === 'am' ? dates.gregorian : `${dates.gregorian}${dates.ethiopian ? ` · ${dates.ethiopian.en}` : ''}`}</p>
                        </div>
                    </section>

                    {/* The next sitting, large: what most of the lobby is waiting for. */}
                    <section className="sg-next" data-state={next ? 'next' : todayHearings.length > 0 ? 'done' : 'none'}>
                        <div className="sg-next-head">
                            <Bi {...bi('nextSitting')} className="sg-kicker" />
                            {next && (
                                <span className="sg-countdown">
                                    <span className="sg-pulse" aria-hidden="true" />
                                    {countdown(lang)}
                                    {lang !== other && <span>{countdown(other)}</span>}
                                </span>
                            )}
                        </div>
                        {next ? (
                            <div key={`${next.id}-${nextTime}`} className="sg-next-body court-signage-in">
                                <p className="sg-next-time">{next.time}</p>
                                <div className="min-w-0">
                                    <p className="sg-next-case">{next.case_number ?? '—'}</p>
                                    <p className="sg-next-matter">{[next.case_type, next.type].filter(Boolean).join(' · ') || '—'}</p>
                                    {nextHearings.length > 1 && <p className="sg-next-more">{text('alsoAt', { n: nextHearings.length - 1 })}</p>}
                                </div>
                                {next.location && (
                                    <div className="sg-next-room">
                                        <Bi {...bi('room')} className="sg-mini" />
                                        <p>{next.location}</p>
                                    </div>
                                )}
                            </div>
                        ) : (
                            <p className="sg-next-empty">
                                <Bi {...bi(todayHearings.length > 0 ? 'noMoreToday' : 'noneToday')} />
                            </p>
                        )}
                    </section>

                    {/* Today's cause list, as a board. */}
                    <section className="sg-list">
                        <SectionHead
                            {...bi('causeList')}
                            aside={pages.length > 1 && (
                                <span className="sg-pages">
                                    {text('pageOf', { n: page + 1, total: pages.length })}
                                    <Progress count={pages.length} active={page} seconds={PAGE_SECONDS} />
                                </span>
                            )}
                        />
                        {todayHearings.length === 0 ? (
                            <div className="sg-list-empty">
                                <CourtEmblem className="h-24 w-24" />
                                <Bi {...bi('noneToday')} />
                            </div>
                        ) : (
                            <div role="table" aria-label={text('causeList')} className="sg-table">
                                <div role="row" className="sg-row sg-row-head">
                                    <span aria-hidden="true" />
                                    {(['time', 'case', 'matter', 'room'] as const).map((key) => (
                                        <span key={key} role="columnheader"><Bi {...bi(key)} /></span>
                                    ))}
                                </div>
                                <div ref={listBox} className="sg-rows">
                                    <div key={`${page}-${rows}`} role="rowgroup">
                                        {(pages[page] ?? []).map((hearing, index) => {
                                            const past = hearing.time !== null && minutesOf(hearing.time) < clock.minutesOfDay;
                                            const isNext = hearing.time !== null && hearing.time === nextTime;
                                            return (
                                                <div
                                                    key={hearing.id}
                                                    role="row"
                                                    className="sg-row court-signage-in"
                                                    data-state={isNext ? 'next' : past ? 'past' : 'later'}
                                                    style={{ '--i': Math.min(index, 10) } as CSSProperties}
                                                >
                                                    <span className="sg-marker" aria-hidden="true" />
                                                    <span role="cell" className="sg-cell-time">{hearing.time ?? '—'}</span>
                                                    <span role="cell" className="sg-cell-case">{hearing.case_number ?? '—'}</span>
                                                    <span role="cell" className="sg-cell-matter">
                                                        <span>{hearing.case_type ?? '—'}</span>
                                                        {hearing.type && <span>{hearing.type}</span>}
                                                    </span>
                                                    <span role="cell" className="sg-cell-room">
                                                        <span>{hearing.location ?? '—'}</span>
                                                        {isNext && <span className="sg-tag">{text('next')}</span>}
                                                    </span>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                                <p className="sg-note">{text('note')}</p>
                            </div>
                        )}
                    </section>

                    {/* Today's figures and the caseload: a printed ledger, ruled in brass. */}
                    <section className="sg-figures" aria-label={text('today')}>
                        <dl className="sg-stats">
                            {([
                                ['hearingsToday', hearingsToday],
                                ['filedToday', filedToday],
                                ['totalCases', totalCases],
                            ] as const).map(([key, value]) => (
                                <div key={key}>
                                    <dt><Bi {...bi(key)} /></dt>
                                    <dd><CountUp value={value} format={formatNumber} /></dd>
                                </div>
                            ))}
                        </dl>
                        <div className="sg-caseload">
                            <p className="sg-caseload-title"><Bi {...bi('caseload')} /></p>
                            <div className="sg-bar" aria-hidden="true">
                                {statusCounts.map((stat) => (
                                    <span key={stat.label} style={{ flexGrow: stat.total, background: STATUS_TONES[stat.label.toLowerCase()] ?? '#64748b' }} />
                                ))}
                            </div>
                            <ul className="sg-legend">
                                {statusCounts.map((stat) => (
                                    <li key={stat.label}>
                                        <span aria-hidden="true" style={{ background: STATUS_TONES[stat.label.toLowerCase()] ?? '#64748b' }} />
                                        {statusLabel(stat.label, lang)}
                                        <b>{formatNumber(stat.total)}</b>
                                        <i>{Math.round((stat.total / statusTotal) * 100)}%</i>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </section>

                    {/* The notices, one after another, the registry, and how fresh the board is. */}
                    <footer className="sg-foot">
                        {announcements.length > 1 && (
                            <span className="sg-foot-progress" aria-hidden="true">
                                <span key={notice} className="sg-progress-fill" style={{ '--page': `${NOTICE_SECONDS}s` } as CSSProperties} />
                            </span>
                        )}
                        <span className="sg-foot-label"><Bi {...bi('notices')} /></span>
                        <div className="sg-foot-notice" aria-live="off">
                            {current ? (
                                <div key={current.id} className="court-signage-in">
                                    <p className="sg-notice-title">{current.title}</p>
                                    {current.summary && <p className="sg-notice-summary">{current.summary}</p>}
                                    {announcements.length > 1 && <p className="sg-notice-count">{notice + 1} / {announcements.length}</p>}
                                </div>
                            ) : (
                                <p className="sg-notice-none"><Bi {...bi('noNotices')} /></p>
                            )}
                        </div>
                        <div className="sg-foot-side">
                            {(registry.office_hours || registry.phone) && (
                                <div className="sg-registry">
                                    <p className="sg-mini"><Bi {...bi('registry')} /></p>
                                    {registry.office_hours && <p>{registry.office_hours}</p>}
                                    {registry.phone && <p className="tabular-nums">{registry.phone}</p>}
                                </div>
                            )}
                            <p className={stale ? 'sg-fresh is-stale' : 'sg-fresh'} role="status">
                                {text(stale ? 'stale' : 'updated', { time: readTime })}
                            </p>
                        </div>
                    </footer>
                </div>

                <button type="button" onClick={toggleFullscreen} className="court-signage-tools sg-tool">
                    {fullscreen ? text('exitFullscreen') : text('fullscreen')}
                </button>
            </div>
        </LocalizedUiProvider>
    );
}
