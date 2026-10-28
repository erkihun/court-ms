import type { CSSProperties, ReactNode } from 'react';
import LanguageSwitcher from '@/Components/LanguageSwitcher';
import ThemeToggle from '@/Components/ThemeToggle';
import { useBranding } from '@/hooks/useBranding';
import { useLocale } from '@/hooks/useLocale';

/**
 * The court's sign-in look: a court-ink panel with the scales of justice, the
 * tribunal's name and its case path from filing to appeal, beside a parchment
 * work surface. Its colours are local to these pages (court ink, brass,
 * parchment), so the shell's appearance settings stay those of the system.
 */
const COURT_COLOURS = {
    '--court-ink': '#0c1a31',
    '--court-navy': '#16305a',
    '--court-navy-hover': '#1d3c70',
    '--court-brass': '#c8a14a',
    '--court-brass-soft': '#e8d6a2',
    '--court-parchment': '#f6f1e6',
} as CSSProperties;

const STEPS = ['filing', 'review', 'hearing', 'decision', 'appeal'] as const;

/** The scales of justice, drawn in the court's brass. */
export function CourtEmblem({ className = 'h-12 w-12' }: { className?: string }) {
    return (
        <svg viewBox="0 0 64 64" fill="none" aria-hidden="true" className={className}>
            <circle cx="32" cy="32" r="30" stroke="currentColor" strokeWidth="1.5" opacity="0.55" />
            <circle cx="32" cy="32" r="26" stroke="currentColor" strokeWidth="0.75" opacity="0.35" />
            <path d="M32 14v34M22 50h20M26 50l6-4 6 4" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
            <circle cx="32" cy="13" r="2" fill="currentColor" />
            <path d="M17 21h30" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
            <path d="M19 21l-5 11M19 21l5 11M45 21l-5 11M45 21l5 11" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
            <path d="M12.5 32a6.5 3.2 0 0 0 13 0zM38.5 32a6.5 3.2 0 0 0 13 0z" fill="currentColor" opacity="0.9" />
        </svg>
    );
}

/** A colonnade, faint, across the foot of the court panel. */
function Colonnade() {
    return (
        <svg viewBox="0 0 480 160" preserveAspectRatio="xMidYMax slice" aria-hidden="true" className="pointer-events-none absolute inset-x-0 bottom-0 h-56 w-full text-(--court-brass) opacity-[0.09]">
            <path d="M20 40 L240 4 L460 40 Z" fill="currentColor" />
            <rect x="24" y="44" width="432" height="8" fill="currentColor" />
            {Array.from({ length: 8 }, (_, i) => 44 + i * 56).map((x) => (
                <g key={x} fill="currentColor">
                    <rect x={x - 4} y="58" width="32" height="6" />
                    <rect x={x} y="64" width="24" height="80" />
                    <rect x={x - 4} y="144" width="32" height="6" />
                </g>
            ))}
            <rect x="12" y="150" width="456" height="10" fill="currentColor" />
        </svg>
    );
}

export default function CourtAuthLayout({ children }: { children: ReactNode }) {
    const { t, locale } = useLocale();
    const { brandNameEn, brandNameAm, officialNameEn, officialNameAm } = useBranding();
    const court = locale === 'am' ? (officialNameAm || brandNameAm) : (officialNameEn || brandNameEn);
    const text = (key: string) => t(`court.signIn.${key}`);

    return (
        <div style={COURT_COLOURS} className="flex min-h-screen flex-col bg-(--court-parchment) text-slate-900 lg:flex-row dark:bg-[#0a1324] dark:text-slate-100">
            {/* The court: name, emblem and the path of a case. On a phone, a band above the form. */}
            <aside className="relative overflow-hidden bg-(--court-ink) text-white lg:flex lg:w-[44%] lg:max-w-[640px] lg:flex-col lg:justify-between">
                <div aria-hidden="true" className="absolute inset-0 bg-[radial-gradient(120%_80%_at_0%_0%,rgba(200,161,74,0.16),transparent_55%),radial-gradient(90%_70%_at_100%_100%,rgba(22,48,90,0.9),transparent_60%)]" />
                <div aria-hidden="true" className="absolute inset-y-0 right-0 w-px bg-gradient-to-b from-transparent via-(--court-brass)/50 to-transparent" />

                <div className="relative flex items-center gap-4 px-6 py-5 sm:px-10 lg:pt-12">
                    <span className="text-(--court-brass)"><CourtEmblem className="h-12 w-12 lg:h-14 lg:w-14" /></span>
                    <div className="min-w-0">
                        <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-(--court-brass-soft)">{text('eyebrow')}</p>
                        <p className="truncate text-base font-semibold leading-snug sm:text-lg">{court}</p>
                    </div>
                </div>

                <div className="relative hidden px-10 pb-10 lg:block">
                    <div className="mb-8 h-px w-16 bg-(--court-brass)" />
                    <h2 className="max-w-md text-3xl font-semibold leading-tight tracking-tight">{text('panelHeading')}</h2>
                    <p className="mt-3 max-w-md text-sm leading-relaxed text-slate-300">{text('panelText')}</p>

                    <ol className="relative mt-10 max-w-md space-y-5">
                        <span aria-hidden="true" className="absolute left-[15px] top-2 bottom-2 w-px bg-(--court-brass)/35" />
                        {STEPS.map((step, index) => (
                            <li key={step} className="relative flex gap-4">
                                <span className="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-(--court-brass)/70 bg-(--court-ink) text-xs font-semibold text-(--court-brass)">{index + 1}</span>
                                <span className="pt-0.5">
                                    <span className="block text-sm font-semibold">{text(`steps.${step}.title`)}</span>
                                    <span className="block text-xs leading-relaxed text-slate-400">{text(`steps.${step}.text`)}</span>
                                </span>
                            </li>
                        ))}
                    </ol>
                </div>

                <div className="relative hidden items-center gap-2 px-10 pb-10 text-xs text-slate-400 lg:flex">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" className="h-4 w-4 text-(--court-brass)"><path d="M7 11V8a5 5 0 0 1 10 0v3M6 11h12v9H6z" stroke="currentColor" strokeWidth="1.6" strokeLinejoin="round" /></svg>
                    {text('securityNote')}
                </div>
                <Colonnade />
            </aside>

            {/* The work surface. */}
            <main className="relative flex flex-1 flex-col">
                <div className="flex justify-end gap-2 px-4 py-4 sm:px-8">
                    <LanguageSwitcher />
                    <ThemeToggle />
                </div>
                <div className="flex flex-1 items-start justify-center px-4 pb-12 pt-2 sm:items-center sm:px-8">
                    <div className="w-full max-w-[420px]">{children}</div>
                </div>
            </main>
        </div>
    );
}
