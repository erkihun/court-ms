import { Fragment, useId, useState, type ReactNode } from 'react';
import { cx } from '@/Components/ui';
import { useBranding } from '@/hooks/useBranding';
import PublicLayout from '@/court/public/PublicLayout';
import CourtHeroCarousel from '@/court/public/CourtHeroCarousel';
import CourtResourceCard from '@/court/public/CourtResourceCard';
import { usePublicText } from '@/court/i18n/usePublicText';
import type {
    MetricKey,
    PublicHomeMetric,
    PublicHomeProps,
    PublicHomeService,
    PublicHomeStep,
    ServiceAccent,
    ServiceIcon,
    StepColor,
} from '@/court/types/public';

/** Each statistics card's icon and colour, as the Blade cards drew them. */
const METRIC_STYLES: Record<MetricKey, { icon: string; tone: string; bar: string }> = {
    total_cases: {
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        tone: 'bg-primary/10 text-primary ring-primary/15 dark:text-blue-300',
        bar: 'from-primary',
    },
    resolved_cases: {
        icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        tone: 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-900/50',
        bar: 'from-emerald-600',
    },
    pending_cases: {
        icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        tone: 'bg-orange-50 text-orange-700 ring-orange-100 dark:bg-orange-950/50 dark:text-orange-300 dark:ring-orange-900/50',
        bar: 'from-orange-500',
    },
    active_caseload: {
        icon: 'M13 10V3L4 14h7v7l9-11h-7z',
        tone: 'bg-primary/10 text-primary ring-primary/15 dark:text-blue-300',
        bar: 'from-blue-800',
    },
    hearings_this_week: {
        icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        tone: 'bg-violet-50 text-violet-700 ring-violet-100 dark:bg-violet-950/50 dark:text-violet-300 dark:ring-violet-900/50',
        bar: 'from-violet-600',
    },
    avg_resolution_time: {
        icon: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
        tone: 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700',
        bar: 'from-slate-500',
    },
};

/** The landing manager's step colours; white text stays readable on each. */
const STEP_COLORS: Record<StepColor, string> = {
    blue: 'bg-primary text-white dark:bg-blue-500',
    orange: 'bg-orange-700 text-white',
    emerald: 'bg-emerald-700 text-white',
    violet: 'bg-violet-700 text-white',
    amber: 'bg-amber-700 text-white',
};

/** The landing manager's service icons (Services → icon). */
const SERVICE_ICONS: Record<ServiceIcon, string> = {
    document: 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12',
    calendar: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    lock: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
    chart: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    database: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
    chat: 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z',
};

/** The landing manager's service accents (Services → accent). */
const SERVICE_ACCENTS: Record<ServiceAccent, string> = {
    blue: 'bg-primary/10 text-primary dark:text-blue-300',
    orange: 'bg-orange-50 text-orange-700 dark:bg-orange-950/50 dark:text-orange-300',
    emerald: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300',
    violet: 'bg-violet-50 text-violet-700 dark:bg-violet-950/50 dark:text-violet-300',
    amber: 'bg-amber-50 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300',
    pink: 'bg-pink-50 text-pink-700 dark:bg-pink-950/50 dark:text-pink-300',
};

/** Up to five steps share one row on wide screens. */
const STEP_COLUMNS: Record<number, string> = { 1: 'lg:grid-cols-1', 2: 'lg:grid-cols-2', 3: 'lg:grid-cols-3', 4: 'lg:grid-cols-4' };

const cardCls = 'public-card relative min-w-0 overflow-hidden border border-gray-200/80 bg-white dark:border-slate-800/80 dark:bg-slate-900';
const pillCls = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-medium';

const numbers = new Intl.NumberFormat('en-US');

function Icon({ path, className = 'h-5 w-5' }: { path: string; className?: string }) {
    return (
        <svg className={className} fill="none" stroke="currentColor" strokeWidth={1.8} viewBox="0 0 24 24" aria-hidden="true">
            <path strokeLinecap="round" strokeLinejoin="round" d={path} />
        </svg>
    );
}

/** A home section: the Blade page's anchor id, eyebrow, saved or translated heading, and its cards. */
function HomeSection({ id, tinted, badge, title, subtitle, aside, children }: {
    id: string;
    tinted: boolean;
    badge: string;
    title: string;
    subtitle: string;
    aside?: ReactNode;
    children: ReactNode;
}) {
    return (
        <section
            id={id}
            aria-labelledby={`${id}-heading`}
            className={cx(
                'scroll-mt-20 py-16 sm:py-20',
                tinted ? 'border-y border-gray-200 bg-gray-50 dark:border-slate-800 dark:bg-slate-900/60' : 'bg-white dark:bg-slate-950',
            )}
        >
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="mx-auto mb-10 max-w-2xl text-center sm:mb-12">
                    <p className="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-primary dark:text-blue-300">{badge}</p>
                    <h2 id={`${id}-heading`} className="public-heading-accent text-2xl font-bold text-gray-900 sm:text-3xl dark:text-slate-100">{title}</h2>
                    {subtitle && <p className="mt-4 text-base text-gray-600 dark:text-slate-400">{subtitle}</p>}
                    {aside}
                </div>
                {children}
            </div>
        </section>
    );
}

function MetricCard({ metric }: { metric: PublicHomeMetric }) {
    const style = METRIC_STYLES[metric.key] ?? METRIC_STYLES.total_cases;

    return (
        <article className={cx(cardCls, 'p-5')}>
            <div aria-hidden="true" className={cx('absolute inset-x-0 top-0 h-1 bg-linear-to-r to-transparent', style.bar)} />
            <div className="flex items-start justify-between gap-4">
                <div className="min-w-0">
                    <h3 className="text-sm font-semibold text-gray-700 dark:text-slate-300">{metric.label}</h3>
                    <p className="mt-3 text-3xl font-bold tracking-tight text-gray-950 tabular-nums dark:text-white">{numbers.format(metric.value)}</p>
                </div>
                <span className={cx('flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ring-1', style.tone)}>
                    <Icon path={style.icon} />
                </span>
            </div>
            {metric.description && <p className="mt-3 text-xs leading-relaxed text-gray-500 dark:text-slate-400">{metric.description}</p>}
        </article>
    );
}

function StepCard({ step, number }: { step: PublicHomeStep; number: number }) {
    return (
        <li className={cx(cardCls, 'flex flex-col p-5')}>
            <span className={cx('mb-4 flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold shadow-sm', STEP_COLORS[step.color] ?? STEP_COLORS.blue)}>
                {number}
            </span>
            <h3 className="text-sm font-semibold leading-snug text-gray-900 dark:text-slate-100">{step.title}</h3>
            {step.description && <p className="mt-1 text-xs leading-relaxed text-gray-600 dark:text-slate-400">{step.description}</p>}
            {(step.meta || step.duration) && (
                <div className="mt-auto flex flex-wrap items-center gap-1.5 pt-3">
                    {step.meta && <span className={cx(pillCls, 'bg-gray-100 text-gray-700 dark:bg-slate-800 dark:text-slate-300')}>{step.meta}</span>}
                    {step.duration && <span className={cx(pillCls, 'bg-primary/10 text-primary dark:text-blue-300')}>{step.duration}</span>}
                </div>
            )}
        </li>
    );
}

function ServiceCard({ service }: { service: PublicHomeService }) {
    const accent = SERVICE_ACCENTS[service.accent] ?? SERVICE_ACCENTS.blue;

    return (
        <article className={cx(cardCls, 'flex flex-col p-6')}>
            <div className="mb-4 flex items-center gap-3">
                <span className={cx('flex h-10 w-10 shrink-0 items-center justify-center rounded-xl', accent)}>
                    <Icon path={SERVICE_ICONS[service.icon] ?? SERVICE_ICONS.document} />
                </span>
                {service.meta && (
                    <span className={cx(pillCls, 'bg-gray-100 font-semibold uppercase tracking-wider text-gray-600 dark:bg-slate-800 dark:text-slate-300')}>{service.meta}</span>
                )}
            </div>
            <h3 className="text-base font-semibold text-gray-900 dark:text-slate-100">{service.title}</h3>
            {service.description && <p className="mt-2 text-sm leading-relaxed text-gray-600 dark:text-slate-400">{service.description}</p>}
            {service.features.length > 0 && (
                <ul className="mt-4 space-y-1.5">
                    {service.features.map((feature, index) => (
                        <li key={`${feature}-${index}`} className="flex items-start gap-2 text-xs text-gray-700 dark:text-slate-300">
                            <span className={cx('mt-0.5 shrink-0 rounded-full', accent)}>
                                <Icon path="M5 13l4 4L19 7" className="h-3.5 w-3.5" />
                            </span>
                            {feature}
                        </li>
                    ))}
                </ul>
            )}
        </article>
    );
}

/** The questions as a disclosure list: each opens on its own, as the Blade accordion did. */
function FaqList({ faqs }: { faqs: PublicHomeProps['faqs'] }) {
    const id = useId();
    const [open, setOpen] = useState<number[]>([]);
    const toggle = (index: number) => setOpen((list) => (list.includes(index) ? list.filter((item) => item !== index) : [...list, index]));

    return (
        <ul className="mx-auto max-w-3xl space-y-3">
            {faqs.map((faq, index) => {
                const expanded = open.includes(index);
                return (
                    <li key={index} className={cardCls}>
                        <h3>
                            <button
                                type="button"
                                id={`${id}-q${index}`}
                                aria-expanded={expanded}
                                aria-controls={`${id}-a${index}`}
                                onClick={() => toggle(index)}
                                className="flex min-h-12 w-full items-center justify-between gap-4 px-5 py-4 text-start text-sm font-semibold text-gray-900 hover:bg-gray-50 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary dark:text-slate-100 dark:hover:bg-slate-800/60"
                            >
                                <span className="min-w-0 wrap-anywhere">{faq.question}</span>
                                <svg
                                    className={cx('h-4 w-4 shrink-0 text-primary transition-transform duration-200 motion-reduce:transition-none dark:text-blue-300', expanded && 'rotate-180')}
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </h3>
                        <div id={`${id}-a${index}`} role="region" aria-labelledby={`${id}-q${index}`} hidden={!expanded} className="px-5 pb-4">
                            <p className="whitespace-pre-line border-t border-gray-100 pt-4 text-sm leading-relaxed text-gray-600 dark:border-slate-800 dark:text-slate-400">{faq.answer}</p>
                        </div>
                    </li>
                );
            })}
        </ul>
    );
}

/**
 * The public home page (PublicHomeController@home), the Blade page's sections
 * in the EUISIS public look: hero slides, statistics, the case journey,
 * services, resources and questions. Headings and items arrive resolved
 * (saved content, else the translations); a section the landing manager hid
 * arrives empty and is left out, and the bands alternate over what is shown.
 */
export default function PublicHome({ slides, sections, metrics, steps, services, resources, hasMoreResources, faqs, publicSite }: PublicHomeProps) {
    const { t } = usePublicText();
    const { brandName } = useBranding();

    const blocks: Array<{ key: string; render: (tinted: boolean) => ReactNode }> = [];

    if (sections.metrics.visible && metrics.length > 0) {
        blocks.push({
            key: 'metrics',
            render: (tinted) => (
                <HomeSection
                    id="statistics"
                    tinted={tinted}
                    badge={t('cm.home.metrics.section_badge')}
                    title={sections.metrics.title}
                    subtitle={sections.metrics.subtitle}
                    aside={(
                        <p className="mt-5 inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-600/15 dark:bg-emerald-950/50 dark:text-emerald-300">
                            <span aria-hidden="true" className="h-2 w-2 rounded-full bg-emerald-500" />
                            {t('cm.home.metrics.live_data')}
                        </p>
                    )}
                >
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {metrics.map((metric) => <MetricCard key={metric.key} metric={metric} />)}
                    </div>
                </HomeSection>
            ),
        });
    }

    if (sections.process.visible && steps.length > 0) {
        blocks.push({
            key: 'process',
            render: (tinted) => (
                <HomeSection id="process" tinted={tinted} badge={t('cm.home.timeline.section_badge')} title={sections.process.title} subtitle={sections.process.subtitle}>
                    <ol className={cx('grid gap-4 sm:grid-cols-2', STEP_COLUMNS[steps.length] ?? 'lg:grid-cols-5')}>
                        {steps.map((step, index) => <StepCard key={`${step.title}-${index}`} step={step} number={index + 1} />)}
                    </ol>
                </HomeSection>
            ),
        });
    }

    if (sections.services.visible && services.length > 0) {
        blocks.push({
            key: 'services',
            render: (tinted) => (
                <HomeSection id="services" tinted={tinted} badge={t('cm.home.services.section_badge')} title={sections.services.title} subtitle={sections.services.subtitle}>
                    <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        {services.map((service, index) => <ServiceCard key={`${service.title}-${index}`} service={service} />)}
                    </div>
                </HomeSection>
            ),
        });
    }

    if (sections.resources.visible && resources.length > 0) {
        blocks.push({
            key: 'resources',
            render: (tinted) => (
                <HomeSection id="resources" tinted={tinted} badge={t('cm.home.resources.section_badge')} title={sections.resources.title} subtitle={sections.resources.subtitle}>
                    <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        {resources.map((resource) => <CourtResourceCard key={resource.id} resource={resource} />)}
                    </div>
                    {hasMoreResources && (
                        <p className="mt-10 text-center">
                            <a
                                href={route('public.resources')}
                                className="inline-flex min-h-11 items-center gap-2 rounded-card border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-primary dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800"
                            >
                                {t('cm.home.resources.view_all')}
                                <Icon path="M17 8l4 4m0 0l-4 4m4-4H3" className="h-4 w-4 rtl:rotate-180" />
                            </a>
                        </p>
                    )}
                </HomeSection>
            ),
        });
    }

    if (sections.faq.visible && faqs.length > 0) {
        blocks.push({
            key: 'faq',
            render: (tinted) => (
                <HomeSection id="faq" tinted={tinted} badge={t('cm.home.faq.section_badge')} title={sections.faq.title} subtitle={sections.faq.subtitle}>
                    <FaqList faqs={faqs} />
                </HomeSection>
            ),
        });
    }

    // The saved title ends with the short name ("… | PSHRDBATC"); the app adds the brand itself.
    return (
        <PublicLayout title={t('cm.home.meta.title').split(' | ')[0]} description={t('cm.home.meta.description')} publicSite={publicSite}>
            <h1 className="sr-only">{brandName}</h1>
            <CourtHeroCarousel slides={slides} />
            {blocks.map((block, index) => <Fragment key={block.key}>{block.render(index % 2 === 1)}</Fragment>)}
        </PublicLayout>
    );
}
