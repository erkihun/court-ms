import { useEffect, useId, useRef, useState, type FocusEvent, type PointerEvent } from 'react';
import { cx } from '@/Components/ui';
import { useSystemSettings } from '@/hooks/useSystemSettings';
import { usePublicText } from '@/court/i18n/usePublicText';
import type { PublicHomeSlide, SlideStyle } from '@/court/types/public';

/** How long a slide stays before the next one, as on the Blade page. */
const SLIDE_MS = 6000;

/** The landing manager's three slide styles, over the public hero's navy band. */
const STYLES: Record<SlideStyle, { glow: string; tint: string; badge: string }> = {
    blue: {
        glow: 'bg-[radial-gradient(ellipse_at_top_left,rgba(59,130,246,0.35),transparent_55%),radial-gradient(ellipse_at_bottom_right,rgba(234,88,12,0.18),transparent_50%)]',
        tint: 'from-blue-950 via-blue-900/90 to-slate-950',
        badge: 'border-blue-300/30 bg-blue-500/20 text-blue-100',
    },
    orange: {
        glow: 'bg-[radial-gradient(ellipse_at_top_right,rgba(234,88,12,0.28),transparent_55%),radial-gradient(ellipse_at_bottom_left,rgba(99,102,241,0.2),transparent_50%)]',
        tint: 'from-slate-950 via-indigo-950/80 to-orange-950/40',
        badge: 'border-orange-400/30 bg-orange-500/20 text-orange-100',
    },
    emerald: {
        glow: 'bg-[radial-gradient(ellipse_at_center,rgba(16,185,129,0.18),transparent_60%),radial-gradient(ellipse_at_top,rgba(30,64,175,0.3),transparent_50%)]',
        tint: 'from-slate-950 via-blue-950 to-emerald-950/50',
        badge: 'border-emerald-400/30 bg-emerald-500/20 text-emerald-100',
    },
};

const primaryCls = 'inline-flex min-h-12 items-center justify-center gap-2 rounded-card bg-white px-6 py-3 text-sm font-bold text-blue-800 shadow-lg transition-colors hover:bg-blue-50 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-blue-950';
const secondaryCls = 'inline-flex min-h-12 items-center justify-center gap-2 rounded-card border border-white/30 bg-white/10 px-6 py-3 text-sm font-semibold text-white backdrop-blur-xs transition-colors hover:bg-white/20 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-white';
const controlCls = 'flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-white/25 bg-black/25 text-white backdrop-blur-xs transition-colors hover:bg-black/40 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-white';

function prefersReducedMotion(): boolean {
    return typeof window !== 'undefined' && typeof window.matchMedia === 'function'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/** The visitor's reduced-motion preference, followed live. */
function useReducedMotion(): boolean {
    const [reduced, setReduced] = useState(prefersReducedMotion);

    useEffect(() => {
        if (typeof window.matchMedia !== 'function') return;
        const query = window.matchMedia('(prefers-reduced-motion: reduce)');
        const update = () => setReduced(query.matches);
        update();
        query.addEventListener('change', update);
        return () => query.removeEventListener('change', update);
    }, []);

    return reduced;
}

function two(value: number): string {
    return String(value).padStart(2, '0');
}

function SlideBackground({ slide }: { slide: PublicHomeSlide }) {
    const style = STYLES[slide.style] ?? STYLES.blue;

    if (slide.image_url) {
        return (
            <div className="absolute inset-0" aria-hidden="true">
                <img src={slide.image_url} alt="" decoding="async" className="absolute inset-0 h-full w-full object-cover" />
                {/* Dark overlay so the text stays readable, tinted by the slide's style. */}
                <div className="absolute inset-0 bg-slate-950/60" />
                <div className={cx('absolute inset-0 bg-linear-to-br opacity-40', style.tint)} />
            </div>
        );
    }

    return (
        <div className="absolute inset-0" aria-hidden="true">
            <div className={cx('absolute inset-0 bg-linear-to-br opacity-60', style.tint)} />
            <div className={cx('absolute inset-0', style.glow)} />
            <div className="public-hero-grid" />
        </div>
    );
}

/**
 * The home page's hero slides (landing manager → Hero Slides), after the Blade
 * carousel: one slide at a time, moving on every six seconds while nothing
 * holds it — a mouse over it, keyboard focus inside it, the pause button,
 * the visitor's reduced-motion preference or the `appearance.enable_ui_animations`
 * setting. Arrows, dots and the counter move between slides by hand.
 */
export default function CourtHeroCarousel({ slides }: { slides: PublicHomeSlide[] }) {
    const { text } = usePublicText();
    const { getBoolean } = useSystemSettings();
    const reducedMotion = useReducedMotion();
    const id = useId();
    const progressRef = useRef<HTMLDivElement>(null);
    const [current, setCurrent] = useState(0);
    const [stopped, setStopped] = useState(false);
    const [hovered, setHovered] = useState(false);
    const [focused, setFocused] = useState(false);

    const count = slides.length;
    const active = count > 0 ? Math.min(current, count - 1) : 0;
    const canRotate = count > 1 && !reducedMotion && getBoolean('appearance.enable_ui_animations', true);
    const running = canRotate && !stopped && !hovered && !focused;

    // One timer per shown slide; the progress line runs the same six seconds.
    useEffect(() => {
        if (!running) return;
        const timer = window.setTimeout(() => setCurrent((index) => (index + 1) % count), SLIDE_MS);
        const bar = progressRef.current;
        const animation = bar && typeof bar.animate === 'function'
            ? bar.animate([{ transform: 'scaleX(0)' }, { transform: 'scaleX(1)' }], { duration: SLIDE_MS, easing: 'linear', fill: 'forwards' })
            : null;

        return () => {
            window.clearTimeout(timer);
            animation?.cancel();
        };
    }, [running, active, count]);

    if (count === 0) return null;

    const go = (index: number) => setCurrent((index + count) % count);
    // A mouse resting on the band holds it; a tap on a touch screen does not.
    const onPointerEnter = (event: PointerEvent<HTMLElement>) => {
        if (event.pointerType === 'mouse') setHovered(true);
    };
    // Keyboard focus holds it; the focus a click or tap leaves on a control does not.
    const onFocus = (event: FocusEvent<HTMLElement>) => {
        let keyboard = true;
        try {
            keyboard = event.target.matches(':focus-visible');
        } catch {
            // No :focus-visible support: any focus holds the slides.
        }
        if (keyboard) setFocused(true);
    };
    const onBlur = (event: FocusEvent<HTMLElement>) => {
        if (!event.currentTarget.contains(event.relatedTarget as Node | null)) setFocused(false);
    };

    return (
        <section
            aria-roledescription="carousel"
            aria-label={text('slides_label')}
            className="public-hero"
            onPointerEnter={onPointerEnter}
            onPointerLeave={() => setHovered(false)}
            onFocus={onFocus}
            onBlur={onBlur}
        >
            {/* Every slide sits in the same grid cell, so the band is as tall as the longest one. */}
            <div id={`${id}-slides`} className="grid" aria-live={running ? 'off' : 'polite'}>
                {slides.map((slide, index) => {
                    const shown = index === active;
                    const style = STYLES[slide.style] ?? STYLES.blue;

                    return (
                        <div
                            key={index}
                            role="group"
                            aria-roledescription="slide"
                            aria-label={text('slide_position', { number: index + 1, total: count })}
                            aria-hidden={!shown}
                            inert={!shown}
                            className={cx(
                                'relative col-start-1 row-start-1 transition-[opacity,visibility] duration-700 ease-in-out motion-reduce:transition-none',
                                shown ? 'visible opacity-100' : 'invisible opacity-0',
                            )}
                        >
                            <SlideBackground slide={slide} />

                            <div className="relative mx-auto flex min-h-[30rem] max-w-7xl items-center px-4 pb-24 pt-12 sm:min-h-[34rem] sm:px-6 lg:min-h-[38rem] lg:px-8">
                                <div className="min-w-0 max-w-3xl">
                                    {slide.badge && (
                                        <span className={cx('mb-5 inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold', style.badge)}>
                                            <span className="h-2 w-2 shrink-0 rounded-full bg-current" aria-hidden="true" />
                                            {slide.badge}
                                        </span>
                                    )}
                                    <h2 className="text-[clamp(1.75rem,6vw,2.25rem)] font-extrabold leading-tight text-white wrap-anywhere sm:text-5xl lg:text-6xl">
                                        {slide.title}
                                    </h2>
                                    {slide.description && (
                                        <p className="mt-5 max-w-2xl text-base leading-relaxed text-blue-100 sm:text-lg">{slide.description}</p>
                                    )}
                                    <div className="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                                        <a href={slide.primary_href} className={primaryCls}>
                                            {slide.primary_label}
                                            <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 12h14M12 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                        {slide.secondary_label && (
                                            <a href={slide.secondary_href} className={secondaryCls}>{slide.secondary_label}</a>
                                        )}
                                    </div>
                                    {count > 1 && (
                                        <p aria-hidden="true" className="mt-8 text-xs font-semibold uppercase tracking-[0.25em] text-white/50">
                                            {two(index + 1)} / {two(count)}
                                        </p>
                                    )}
                                </div>
                            </div>
                        </div>
                    );
                })}
            </div>

            {count > 1 && (
                <div className="absolute inset-x-0 bottom-5 z-10 flex items-center justify-center gap-2 px-4">
                    <button type="button" onClick={() => go(active - 1)} aria-controls={`${id}-slides`} aria-label={text('slide_previous')} className={controlCls}>
                        <svg className="h-5 w-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <ul className="flex items-center">
                        {slides.map((_, index) => {
                            const shown = index === active;
                            return (
                                <li key={index}>
                                    <button
                                        type="button"
                                        onClick={() => go(index)}
                                        aria-controls={`${id}-slides`}
                                        aria-label={text('slide_go_to', { number: index + 1 })}
                                        aria-current={shown ? 'true' : undefined}
                                        className="flex h-11 min-w-8 items-center justify-center rounded-full focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-white"
                                    >
                                        <span
                                            aria-hidden="true"
                                            className={cx(
                                                'block h-2 rounded-full transition-all duration-300 motion-reduce:transition-none',
                                                shown ? 'w-6 bg-orange-400' : 'w-2 bg-white/50 hover:bg-white/80',
                                            )}
                                        />
                                    </button>
                                </li>
                            );
                        })}
                    </ul>

                    <button type="button" onClick={() => go(active + 1)} aria-controls={`${id}-slides`} aria-label={text('slide_next')} className={controlCls}>
                        <svg className="h-5 w-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    {canRotate && (
                        <button
                            type="button"
                            onClick={() => setStopped((value) => !value)}
                            aria-label={stopped ? text('slides_play') : text('slides_pause')}
                            className={cx(controlCls, 'ms-2')}
                        >
                            {stopped ? (
                                <svg className="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z" /></svg>
                            ) : (
                                <svg className="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 5h4v14H7zM13 5h4v14h-4z" /></svg>
                            )}
                        </button>
                    )}
                </div>
            )}

            {canRotate && (
                <div className="absolute inset-x-0 bottom-0 z-10 h-0.5 bg-white/10" aria-hidden="true">
                    <div ref={progressRef} className="h-full origin-left bg-orange-400/80 rtl:origin-right" style={{ transform: 'scaleX(0)' }} />
                </div>
            )}
        </section>
    );
}
