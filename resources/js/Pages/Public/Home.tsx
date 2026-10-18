import {
    BoltIcon,
    CalendarDaysIcon,
    ChartBarIcon,
    ChatBubbleLeftRightIcon,
    CheckCircleIcon,
    CheckIcon,
    ChevronDownIcon,
    ClockIcon,
    CloudArrowUpIcon,
    DocumentTextIcon,
    EyeIcon,
    LockClosedIcon,
    ServerStackIcon,
} from '@heroicons/react/24/outline';
import { Head, Link } from '@inertiajs/react';
import { type ComponentType, type ReactNode, type SVGProps, useId, useState } from 'react';
import { HeroCarousel, type HeroSlide } from '@/components/public/HeroCarousel';
import type { PublicFooterContent } from '@/components/public/PublicChrome';
import { type PublicResource, ResourceCard } from '@/components/public/ResourceCard';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

type Icon = ComponentType<SVGProps<SVGSVGElement>>;

interface SectionText {
    visible: boolean;
    title: string;
    subtitle: string;
}

interface Props {
    userManual: { url: string; label: string } | null;
    slides: HeroSlide[];
    sections: Record<'metrics' | 'process' | 'services' | 'resources' | 'faq', SectionText>;
    metrics: Array<{ key: string; label: string; description: string; value: number }>;
    timeline: Array<{ title: string; description: string; meta: string | null; duration: string | null }>;
    services: Array<{ title: string; description: string; meta: string | null; features: string[]; icon: string; accent: string }>;
    resources: { items: PublicResource[]; hasMore: boolean };
    faqs: Array<{ question: string; answer: string }>;
    footer: PublicFooterContent;
}

const METRIC_LOOK: Record<string, { icon: Icon; accent: string; ring: string }> = {
    total_cases: { icon: DocumentTextIcon, accent: 'from-blue-700 to-blue-500', ring: 'ring-blue-500/30' },
    resolved_cases: { icon: CheckCircleIcon, accent: 'from-emerald-700 to-emerald-500', ring: 'ring-emerald-500/30' },
    pending_cases: { icon: ClockIcon, accent: 'from-orange-600 to-orange-400', ring: 'ring-orange-400/30' },
    active_caseload: { icon: BoltIcon, accent: 'from-blue-800 to-blue-600', ring: 'ring-blue-600/30' },
    hearings_this_week: { icon: CalendarDaysIcon, accent: 'from-violet-700 to-violet-500', ring: 'ring-violet-500/30' },
    avg_resolution_time: { icon: EyeIcon, accent: 'from-slate-600 to-slate-400', ring: 'ring-slate-400/30' },
};

const SERVICE_ICONS: Record<string, Icon> = {
    document: CloudArrowUpIcon,
    calendar: CalendarDaysIcon,
    lock: LockClosedIcon,
    chart: ChartBarIcon,
    database: ServerStackIcon,
    chat: ChatBubbleLeftRightIcon,
};

const SERVICE_ACCENTS: Record<string, string> = {
    blue: 'text-blue-500',
    orange: 'text-orange-500',
    emerald: 'text-emerald-500',
    violet: 'text-violet-500',
    amber: 'text-amber-500',
    pink: 'text-pink-500',
};

function SectionHeading({ badge, text, center = false }: { badge: string; text: SectionText; center?: boolean }) {
    return (
        <div className={cn('mb-12 max-w-2xl', center && 'mx-auto text-center')}>
            <p className="pub-ac-text mb-3 text-xs font-semibold tracking-[0.2em] uppercase">{badge}</p>
            <h2 className="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">{text.title}</h2>
            <p className="mt-3 text-base text-slate-500">{text.subtitle}</p>
        </div>
    );
}

function Container({ children, className }: { children: ReactNode; className?: string }) {
    return <div className={cn('mx-auto max-w-7xl px-4 sm:px-6 lg:px-8', className)}>{children}</div>;
}

export default function PublicHome({ slides, sections, metrics, timeline, services, resources, faqs }: Props) {
    return (
        <>
            <Head title={t('home.meta.page_title')}>
                <meta name="description" content={t('home.meta.description')} />
            </Head>

            <HeroCarousel slides={slides} />

            {sections.metrics.visible && (
                <section id="statistics" aria-labelledby="statistics-title" className="scroll-mt-20 bg-white py-16 sm:py-20">
                    <Container>
                        <div className="mb-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                            <div className="max-w-2xl">
                                <p className="pub-ac-text mb-3 text-xs font-semibold tracking-[0.2em] uppercase">{t('home.metrics.section_badge')}</p>
                                <h2 id="statistics-title" className="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                                    {sections.metrics.title}
                                </h2>
                                <p className="mt-3 text-slate-500">{sections.metrics.subtitle}</p>
                            </div>
                            <p className="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-600/15">
                                <span className="h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true" />
                                {t('home.metrics.live_data')}
                            </p>
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {metrics.map((metric) => {
                                const look = METRIC_LOOK[metric.key] ?? METRIC_LOOK.total_cases!;
                                const MetricIcon = look.icon;

                                return (
                                    <article
                                        key={metric.key}
                                        className="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-xs transition duration-300 hover:-translate-y-1 hover:shadow-lg motion-reduce:transition-none motion-reduce:hover:translate-y-0"
                                    >
                                        <div className={cn('absolute inset-x-0 top-0 h-1 bg-linear-to-r', look.accent)} />
                                        <div className="flex items-start justify-between gap-4">
                                            <div>
                                                <h3 className="text-sm font-semibold text-slate-700">{metric.label}</h3>
                                                <p className="mt-3 text-3xl font-bold tracking-tight text-slate-950 tabular-nums">
                                                    {metric.value.toLocaleString()}
                                                </p>
                                            </div>
                                            <span
                                                className={cn(
                                                    'flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-linear-to-br text-white shadow-xs ring-4',
                                                    look.accent,
                                                    look.ring,
                                                )}
                                            >
                                                <MetricIcon className="h-5 w-5" />
                                            </span>
                                        </div>
                                        <p className="mt-3 text-xs leading-relaxed text-slate-500">{metric.description}</p>
                                    </article>
                                );
                            })}
                        </div>
                    </Container>
                </section>
            )}

            {sections.process.visible && (
                <section id="process" className="scroll-mt-20 border-y border-slate-200 bg-slate-50 py-20 sm:py-28">
                    <Container>
                        <SectionHeading badge={t('home.timeline.section_badge')} text={sections.process} />
                        <div className="relative">
                            <div className="absolute top-5 right-[10%] left-[10%] hidden h-px bg-slate-200 lg:block" aria-hidden="true" />
                            <ol className="relative grid gap-8 sm:grid-cols-2 lg:grid-cols-5">
                                {timeline.map((step, index) => (
                                    <li key={index} className="relative flex flex-col text-start">
                                        <span className="pub-ac-btn relative z-10 mb-4 flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold shadow-xs ring-4 ring-slate-50">
                                            {index + 1}
                                        </span>
                                        <h3 className="mb-1 text-sm leading-snug font-semibold text-slate-900">{step.title}</h3>
                                        <p className="mb-3 text-xs leading-relaxed text-slate-500">{step.description}</p>
                                        <div className="flex flex-wrap items-center gap-1.5">
                                            {step.meta && (
                                                <span className="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">
                                                    {step.meta}
                                                </span>
                                            )}
                                            {step.duration && (
                                                <span className="pub-ac-soft rounded-full px-2 py-0.5 text-[10px] font-medium">{step.duration}</span>
                                            )}
                                        </div>
                                    </li>
                                ))}
                            </ol>
                        </div>
                    </Container>
                </section>
            )}

            {sections.services.visible && (
                <section id="services" className="scroll-mt-20 py-20 sm:py-28">
                    <Container>
                        <SectionHeading badge={t('home.services.section_badge')} text={sections.services} />
                        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            {services.map((service, index) => {
                                const ServiceIcon = SERVICE_ICONS[service.icon] ?? CloudArrowUpIcon;

                                return (
                                    <article
                                        key={index}
                                        className="flex flex-col rounded-2xl border border-slate-200 bg-white p-6 transition hover:border-slate-300 hover:shadow-xs"
                                    >
                                        <div className="mb-4 flex items-center gap-3">
                                            <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100">
                                                <ServiceIcon className={cn('h-5 w-5', SERVICE_ACCENTS[service.accent] ?? 'text-blue-500')} />
                                            </span>
                                            {service.meta && (
                                                <span className="rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-semibold tracking-wider text-slate-500 uppercase">
                                                    {service.meta}
                                                </span>
                                            )}
                                        </div>
                                        <h3 className="mb-2 text-base font-semibold text-slate-900">{service.title}</h3>
                                        <p className="mb-4 flex-1 text-sm leading-relaxed text-slate-500">{service.description}</p>
                                        {service.features.length > 0 && (
                                            <ul className="space-y-1.5">
                                                {service.features.map((feature) => (
                                                    <li key={feature} className="flex items-center gap-2 text-xs text-slate-600">
                                                        <CheckIcon className="h-3.5 w-3.5 shrink-0 text-emerald-500" />
                                                        {feature}
                                                    </li>
                                                ))}
                                            </ul>
                                        )}
                                    </article>
                                );
                            })}
                        </div>
                    </Container>
                </section>
            )}

            {sections.resources.visible && resources.items.length > 0 && (
                <section id="resources" className="scroll-mt-20 py-20 sm:py-24">
                    <Container>
                        <SectionHeading badge={t('home.resources.section_badge')} text={sections.resources} />
                        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            {resources.items.map((resource) => (
                                <ResourceCard key={resource.id} resource={resource} />
                            ))}
                        </div>
                        {resources.hasMore && (
                            <Link
                                href={route('public.resources')}
                                className="mt-10 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                {t('home.resources.view_all')}
                            </Link>
                        )}
                    </Container>
                </section>
            )}

            {sections.faq.visible && faqs.length > 0 && (
                <section id="faq" className="scroll-mt-20 py-20 sm:py-28">
                    <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                        <SectionHeading badge={t('home.faq.section_badge')} text={sections.faq} center />
                        <div className="space-y-3">
                            {faqs.map((faq, index) => (
                                <FaqItem key={index} question={faq.question} answer={faq.answer} />
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </>
    );
}

function FaqItem({ question, answer }: { question: string; answer: string }) {
    const [open, setOpen] = useState(false);
    const id = useId();

    return (
        <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <h3>
                <button
                    type="button"
                    onClick={() => setOpen((value) => !value)}
                    aria-expanded={open}
                    aria-controls={id}
                    className="flex w-full items-center justify-between gap-4 px-5 py-4 text-start transition hover:bg-slate-50"
                >
                    <span className="text-sm font-semibold text-slate-900">{question}</span>
                    <ChevronDownIcon className={cn('h-4 w-4 shrink-0 text-slate-400 transition-transform', open && 'rotate-180')} />
                </button>
            </h3>
            <div id={id} hidden={!open} className="px-5 pb-4">
                <p className="border-t border-slate-100 pt-4 text-sm leading-relaxed text-slate-500">{answer}</p>
            </div>
        </div>
    );
}
