import { ChevronLeftIcon, ChevronRightIcon, PauseIcon, PlayIcon } from '@heroicons/react/24/outline';
import { useCallback, useEffect, useState } from 'react';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';

export interface HeroSlide {
    badge: string;
    title: string;
    description: string;
    primary: { label: string; href: string };
    secondary: { label: string; href: string } | null;
    style: 'blue' | 'orange' | 'emerald';
    imageUrl: string | null;
}

const STYLES: Record<HeroSlide['style'], { bg: string; glow: string; badge: string; dot: string; accent: string }> = {
    blue: {
        bg: 'from-blue-950 via-blue-900/90 to-slate-950',
        glow: 'bg-[radial-gradient(ellipse_at_top_left,rgba(59,130,246,0.35),transparent_55%),radial-gradient(ellipse_at_bottom_right,rgba(234,88,12,0.18),transparent_50%)]',
        badge: 'border-blue-500/30 bg-blue-700/20 text-blue-200',
        dot: 'bg-orange-400',
        accent: 'from-orange-400 to-orange-200',
    },
    orange: {
        bg: 'from-slate-950 via-indigo-950/80 to-orange-950/40',
        glow: 'bg-[radial-gradient(ellipse_at_top_right,rgba(234,88,12,0.28),transparent_55%),radial-gradient(ellipse_at_bottom_left,rgba(99,102,241,0.2),transparent_50%)]',
        badge: 'border-orange-500/30 bg-orange-700/20 text-orange-200',
        dot: 'bg-indigo-400',
        accent: 'from-orange-300 to-amber-200',
    },
    emerald: {
        bg: 'from-slate-950 via-blue-950 to-emerald-950/50',
        glow: 'bg-[radial-gradient(ellipse_at_center,rgba(16,185,129,0.18),transparent_60%),radial-gradient(ellipse_at_top,rgba(30,64,175,0.3),transparent_50%)]',
        badge: 'border-emerald-500/30 bg-emerald-700/20 text-emerald-200',
        dot: 'bg-emerald-400',
        accent: 'from-emerald-400 to-teal-200',
    },
};

const INTERVAL_MS = 6000;

function useReducedMotion(): boolean {
    const [reduced, setReduced] = useState(() => window.matchMedia('(prefers-reduced-motion: reduce)').matches);

    useEffect(() => {
        const media = window.matchMedia('(prefers-reduced-motion: reduce)');
        const sync = () => setReduced(media.matches);
        media.addEventListener('change', sync);
        return () => media.removeEventListener('change', sync);
    }, []);

    return reduced;
}

const pad = (value: number) => String(value).padStart(2, '0');

/**
 * Landing hero. Auto-advances every 6 s unless the visitor hovers or focuses
 * it, pauses it, or prefers reduced motion.
 */
export function HeroCarousel({ slides }: { slides: HeroSlide[] }) {
    const reducedMotion = useReducedMotion();
    const [current, setCurrent] = useState(0);
    const [hovered, setHovered] = useState(false);
    const [focused, setFocused] = useState(false);
    const [stopped, setStopped] = useState(false);
    const total = slides.length;
    const running = total > 1 && !reducedMotion && !stopped && !hovered && !focused;

    const go = useCallback((index: number) => setCurrent(((index % total) + total) % total), [total]);

    useEffect(() => {
        if (!running) return;
        const timer = window.setTimeout(() => go(current + 1), INTERVAL_MS);
        return () => window.clearTimeout(timer);
    }, [running, current, go]);

    if (total === 0) return null;

    return (
        <section
            id="home"
            aria-roledescription="carousel"
            aria-label={t('home.accessibility.hero_carousel')}
            className="relative isolate flex min-h-[600px] items-center overflow-hidden bg-slate-950 py-24 sm:min-h-[calc(100vh-3.75rem)]"
            onMouseEnter={() => setHovered(true)}
            onMouseLeave={() => setHovered(false)}
            onFocus={() => setFocused(true)}
            onBlur={(event) => {
                if (!event.currentTarget.contains(event.relatedTarget as Node | null)) setFocused(false);
            }}
        >
            {slides.map((slide, index) => {
                const style = STYLES[slide.style];
                const active = index === current;

                return (
                    <div
                        key={index}
                        role="group"
                        aria-roledescription="slide"
                        aria-label={t('home.accessibility.slide_of', { current: index + 1, total })}
                        aria-hidden={!active}
                        inert={!active}
                        className={cn(
                            'absolute inset-0 transition-opacity duration-1000 motion-reduce:transition-none',
                            active ? 'z-10 opacity-100' : 'z-0 opacity-0',
                        )}
                    >
                        <div className="absolute inset-0 -z-10">
                            {slide.imageUrl ? (
                                <>
                                    <img src={slide.imageUrl} alt="" className="absolute inset-0 h-full w-full object-cover" />
                                    <div className="absolute inset-0 bg-slate-950/60" />
                                    <div className={cn('absolute inset-0 bg-linear-to-br opacity-40', style.bg)} />
                                </>
                            ) : (
                                <>
                                    <div className={cn('absolute inset-0 bg-linear-to-br', style.bg)} />
                                    <div className={cn('absolute inset-0', style.glow)} />
                                </>
                            )}
                        </div>

                        <div className="flex h-full items-center pb-16">
                            <div className="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                                <div className="max-w-3xl">
                                    {slide.badge && (
                                        <p
                                            className={cn(
                                                'mb-6 inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-sm font-medium',
                                                style.badge,
                                            )}
                                        >
                                            <span className={cn('h-2 w-2 rounded-full', style.dot)} aria-hidden="true" />
                                            {slide.badge}
                                        </p>
                                    )}
                                    {index === 0 ? (
                                        <h1 className="text-4xl leading-tight font-bold tracking-tight sm:text-5xl lg:text-6xl">
                                            <span className={cn('bg-linear-to-r bg-clip-text text-transparent', style.accent)}>{slide.title}</span>
                                        </h1>
                                    ) : (
                                        <h2 className="text-4xl leading-tight font-bold tracking-tight sm:text-5xl lg:text-6xl">
                                            <span className={cn('bg-linear-to-r bg-clip-text text-transparent', style.accent)}>{slide.title}</span>
                                        </h2>
                                    )}
                                    {slide.description && <p className="mt-6 max-w-2xl text-base leading-8 text-slate-300 sm:text-lg">{slide.description}</p>}
                                    <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                                        <a
                                            href={slide.primary.href}
                                            className="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-orange-900/40 transition hover:bg-orange-400"
                                        >
                                            {slide.primary.label}
                                        </a>
                                        {slide.secondary && (
                                            <a
                                                href={slide.secondary.href}
                                                className="inline-flex items-center justify-center rounded-2xl border border-white/25 bg-white/10 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-white/20"
                                            >
                                                {slide.secondary.label}
                                            </a>
                                        )}
                                    </div>
                                    <p className="mt-8 text-xs font-semibold tracking-[0.25em] text-white/40 uppercase" aria-hidden="true">
                                        {pad(index + 1)} / {pad(total)}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                );
            })}

            {total > 1 && (
                <>
                    <button
                        type="button"
                        onClick={() => go(current - 1)}
                        aria-label={t('home.accessibility.previous_slide')}
                        className="absolute start-3 top-1/2 z-20 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/20 bg-black/30 text-white transition hover:bg-black/50 sm:flex"
                    >
                        <ChevronLeftIcon className="h-5 w-5 rtl:rotate-180" />
                    </button>
                    <button
                        type="button"
                        onClick={() => go(current + 1)}
                        aria-label={t('home.accessibility.next_slide')}
                        className="absolute end-3 top-1/2 z-20 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/20 bg-black/30 text-white transition hover:bg-black/50 sm:flex"
                    >
                        <ChevronRightIcon className="h-5 w-5 rtl:rotate-180" />
                    </button>

                    <div className="absolute bottom-8 left-1/2 z-20 flex -translate-x-1/2 items-center gap-3">
                        <button
                            type="button"
                            onClick={() => setStopped((value) => !value)}
                            aria-label={stopped ? t('home.accessibility.play_slideshow') : t('home.accessibility.pause_slideshow')}
                            className="flex h-8 w-8 items-center justify-center rounded-full border border-white/20 bg-black/30 text-white transition hover:bg-black/50"
                        >
                            {stopped ? <PlayIcon className="h-4 w-4" /> : <PauseIcon className="h-4 w-4" />}
                        </button>
                        <div className="flex items-center gap-2">
                            {slides.map((_, index) => (
                                <button
                                    key={index}
                                    type="button"
                                    onClick={() => go(index)}
                                    aria-label={t('home.accessibility.go_to_slide', { number: index + 1 })}
                                    aria-current={index === current ? 'true' : undefined}
                                    className={cn(
                                        'rounded-full transition-all',
                                        index === current ? 'h-2 w-6 bg-orange-400' : 'h-2 w-2 bg-white/40 hover:bg-white/70',
                                    )}
                                />
                            ))}
                        </div>
                    </div>

                    {!reducedMotion && (
                        <div className="absolute inset-x-0 bottom-0 z-20 h-px bg-white/10" aria-hidden="true">
                            {/* Restarted per slide and on resume, in step with the timer. */}
                            <div key={`${current}-${running}`} className="pub-slide-progress h-full bg-orange-500/60" data-paused={!running} />
                        </div>
                    )}
                </>
            )}
        </section>
    );
}
