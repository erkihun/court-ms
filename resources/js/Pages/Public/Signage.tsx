import '../../../css/public.css';
import {
    CalendarDaysIcon,
    ChartPieIcon,
    ClockIcon,
    DocumentTextIcon,
    MegaphoneIcon,
    RectangleStackIcon,
    ScaleIcon,
    UserGroupIcon,
} from '@heroicons/react/24/outline';
import { Head, router } from '@inertiajs/react';
import type { ComponentType, ReactNode, SVGProps } from 'react';
import { RichText } from '@/components/ui/RichText';
import { usePolling } from '@/hooks/usePolling';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';

interface Props {
    appName: string;
    logoUrl: string | null;
    now: string;
    updatedAt: string;
    totalCases: number;
    statuses: Array<{ key: string; label: string; total: number; percent: number }>;
    categories: Array<{ name: string; total: number }>;
    todayCases: Array<{ id: number; caseNumber: string; applicant: string; caseType: string }>;
    todayHearings: Array<{ id: number; caseNumber: string; when: string; caseType: string }>;
    announcements: Array<{ id: number; title: string; date: string; contentHtml: string }>;
    staff: Array<{ id: number; name: string; position: string | null; avatarUrl: string | null }>;
}

/** The hall display reloads its data every two minutes (paused while the tab is hidden). */
const REFRESH_MS = 120_000;

const STATUS_COLORS: Record<string, { bar: string; text: string; border: string }> = {
    active: { bar: 'bg-blue-600', text: 'text-blue-700', border: 'border-blue-200' },
    pending: { bar: 'bg-amber-500', text: 'text-amber-700', border: 'border-amber-200' },
    closed: { bar: 'bg-slate-400', text: 'text-slate-700', border: 'border-slate-200' },
    hearing: { bar: 'bg-blue-600', text: 'text-blue-700', border: 'border-blue-200' },
    dismissed: { bar: 'bg-rose-600', text: 'text-rose-700', border: 'border-rose-200' },
};

function Panel({
    icon: Icon,
    title,
    subtitle,
    badge,
    children,
}: {
    icon: ComponentType<SVGProps<SVGSVGElement>>;
    title: string;
    subtitle?: string;
    badge?: ReactNode;
    children: ReactNode;
}) {
    return (
        <section className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_10px_25px_-5px_rgba(0,0,0,0.05)]">
            <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-linear-to-r from-slate-50 to-white px-6 py-5">
                <div className="flex items-center gap-3">
                    <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <Icon className="h-5 w-5" />
                    </span>
                    <div>
                        <h2 className="text-lg font-bold text-slate-900">{title}</h2>
                        {subtitle && <p className="mt-0.5 text-sm text-slate-500">{subtitle}</p>}
                    </div>
                </div>
                {badge}
            </div>
            <div className="p-6">{children}</div>
        </section>
    );
}

function Pill({ tone, children }: { tone: 'blue' | 'amber' | 'emerald' | 'orange'; children: ReactNode }) {
    const tones = {
        blue: 'border-blue-200 bg-blue-50 text-blue-700',
        amber: 'border-amber-200 bg-amber-50 text-amber-700',
        emerald: 'border-emerald-200 bg-emerald-50 text-emerald-700',
        orange: 'border-orange-200 bg-orange-50 text-orange-700',
    };

    return <span className={cn('inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold', tones[tone])}>{children}</span>;
}

function MiniTable({ head, empty, rows }: { head: string[]; empty: string; rows: ReactNode[][] }) {
    return (
        <div className="max-h-[300px] overflow-auto rounded-xl border border-slate-200">
            <table className="w-full text-sm">
                <thead className="sticky top-0 bg-slate-50">
                    <tr className="border-b border-slate-200">
                        {head.map((label) => (
                            <th key={label} scope="col" className="px-4 py-3 text-start text-xs font-semibold tracking-wide text-slate-600 uppercase">
                                {label}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                    {rows.length === 0 ? (
                        <tr>
                            <td colSpan={head.length} className="px-4 py-8 text-center text-slate-500">
                                {empty}
                            </td>
                        </tr>
                    ) : (
                        rows.map((cells, index) => (
                            <tr key={index}>
                                {cells.map((cell, cellIndex) => (
                                    <td key={cellIndex} className="px-4 py-3 text-slate-700">
                                        {cell}
                                    </td>
                                ))}
                            </tr>
                        ))
                    )}
                </tbody>
            </table>
        </div>
    );
}

const typeChip = (label: string) => (
    <span className="inline-flex rounded-md border border-blue-100 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">{label}</span>
);
const caseNo = (value: string) => <span className="font-mono text-sm font-bold text-slate-800">{value}</span>;

export default function PublicSignage(props: Props) {
    usePolling(() => router.reload(), REFRESH_MS);
    const maxCategory = Math.max(1, ...props.categories.map((category) => category.total));

    return (
        <div className="min-h-screen bg-linear-to-br from-slate-50 via-slate-100 to-slate-200 font-ui text-slate-900">
            <Head title={t('signage.title')} />

            <p
                className="fixed top-4 right-4 z-20 flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-lg ring-1 ring-slate-900/5"
                role="status"
            >
                <span className="h-3 w-3 animate-pulse rounded-full bg-emerald-500 motion-reduce:animate-none" aria-hidden="true" />
                {t('signage.live_updates')}
            </p>

            <main className="mx-auto max-w-6xl space-y-6 p-4 sm:p-6 lg:p-8">
                <header className="overflow-hidden rounded-2xl border border-blue-700/30 bg-linear-to-br from-blue-900 via-blue-800 to-blue-900 px-6 py-6 text-white shadow-2xl md:px-8 md:py-8">
                    <div className="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                        <div className="flex items-center gap-5">
                            <div className="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl border-2 border-white/20 bg-white/10 p-3 shadow-xl">
                                {props.logoUrl ? (
                                    <img src={props.logoUrl} alt="" className="h-full w-full object-contain" />
                                ) : (
                                    <ScaleIcon className="h-10 w-10" />
                                )}
                            </div>
                            <div className="min-w-0">
                                <p className="mb-1 text-xs font-semibold text-blue-200/90 uppercase">{t('signage.court_management')}</p>
                                <h1 className="text-2xl font-black tracking-tight md:text-3xl">{props.appName}</h1>
                                <div className="mt-3 flex flex-wrap items-center gap-2 text-sm font-medium text-blue-100">
                                    <span className="rounded-full bg-white/10 px-3 py-1.5">{props.now}</span>
                                    <span className="rounded-full bg-white/10 px-3 py-1.5">{t('signage.public_display')}</span>
                                </div>
                            </div>
                        </div>
                        <div className="rounded-xl border border-white/20 bg-white/10 p-4 text-end">
                            <p className="mb-2 text-xs font-semibold text-blue-200/80 uppercase">{t('signage.total_cases')}</p>
                            <p className="flex items-baseline justify-end gap-2">
                                <span className="text-5xl leading-none font-black tracking-tight tabular-nums md:text-6xl">
                                    {props.totalCases.toLocaleString()}
                                </span>
                                <span className="text-lg font-bold text-blue-200">{t('signage.cases')}</span>
                            </p>
                            <p className="mt-2 text-xs font-medium text-blue-200/70">{t('signage.across_departments')}</p>
                        </div>
                    </div>
                </header>

                <div className="grid gap-6 md:grid-cols-2">
                    <Panel
                        icon={ChartPieIcon}
                        title={t('signage.case_distribution')}
                        subtitle={t('signage.status_breakdown')}
                        badge={<Pill tone="orange">{t('signage.live_status')}</Pill>}
                    >
                        <ul className="grid gap-4">
                            {props.statuses.map((status) => {
                                const color = STATUS_COLORS[status.key] ?? STATUS_COLORS.active!;

                                return (
                                    <li key={status.key} className={cn('rounded-xl border bg-white p-3', color.border)}>
                                        <div className="mb-2 flex items-center justify-between">
                                            <span className="font-semibold text-slate-900">{status.label}</span>
                                            <span className="text-lg font-bold text-slate-900 tabular-nums">{status.total.toLocaleString()}</span>
                                        </div>
                                        <div className="flex items-center gap-3">
                                            <div className="h-2.5 flex-1 overflow-hidden rounded-full bg-slate-200" aria-hidden="true">
                                                <div className={cn('h-full rounded-full', color.bar)} style={{ width: `${status.percent}%` }} />
                                            </div>
                                            <span className={cn('min-w-12 text-end text-sm font-bold', color.text)}>{status.percent}%</span>
                                        </div>
                                    </li>
                                );
                            })}
                        </ul>
                    </Panel>

                    <Panel
                        icon={RectangleStackIcon}
                        title={t('signage.case_categories')}
                        subtitle={t('signage.top_categories')}
                        badge={<Pill tone="blue">{t('signage.top_count_label', { count: props.categories.length })}</Pill>}
                    >
                        <ul className="grid gap-4">
                            {props.categories.map((category) => {
                                const percent = Math.round((category.total / maxCategory) * 100);

                                return (
                                    <li key={category.name} className="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-4">
                                        <div className="flex min-w-0 items-center gap-4">
                                            <span className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-lg font-bold text-blue-700 tabular-nums">
                                                {category.total}
                                            </span>
                                            <div className="min-w-0">
                                                <h3 className="truncate font-semibold text-slate-900">{category.name}</h3>
                                                <div className="mt-2 flex items-center gap-2">
                                                    <div className="h-2 w-24 overflow-hidden rounded-full bg-slate-200" aria-hidden="true">
                                                        <div className="h-full rounded-full bg-blue-600" style={{ width: `${percent}%` }} />
                                                    </div>
                                                    <span className="text-sm text-slate-500">{percent}%</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span className="text-sm font-medium text-slate-500">{t('signage.cases')}</span>
                                    </li>
                                );
                            })}
                        </ul>
                    </Panel>
                </div>

                <Panel
                    icon={CalendarDaysIcon}
                    title={t('signage.todays_activity')}
                    subtitle={t('signage.live_status')}
                    badge={
                        <div className="flex flex-wrap gap-2">
                            <Pill tone="blue">
                                {props.todayCases.length} {t('signage.new_submissions')}
                            </Pill>
                            <Pill tone="amber">
                                {props.todayHearings.length} {t('signage.hearings')}
                            </Pill>
                        </div>
                    }
                >
                    <div className="grid gap-6 lg:grid-cols-2">
                        <div className="space-y-4">
                            <h3 className="flex items-center gap-2 text-base font-bold text-slate-900">
                                <DocumentTextIcon className="h-4 w-4 text-blue-600" />
                                {t('signage.new_submissions')}
                            </h3>
                            <MiniTable
                                head={[t('signage.case_number'), t('signage.applicant_column'), t('cases.case_type')]}
                                empty={t('signage.no_new_submissions')}
                                rows={props.todayCases.map((item) => [caseNo(item.caseNumber), item.applicant, typeChip(item.caseType)])}
                            />
                        </div>
                        <div className="space-y-4">
                            <h3 className="flex items-center gap-2 text-base font-bold text-slate-900">
                                <ClockIcon className="h-4 w-4 text-amber-600" />
                                {t('signage.scheduled_hearings')}
                            </h3>
                            <MiniTable
                                head={[t('signage.case_number'), t('signage.time'), t('signage.type')]}
                                empty={t('signage.no_hearings')}
                                rows={props.todayHearings.map((item) => [caseNo(item.caseNumber), item.when, typeChip(item.caseType)])}
                            />
                        </div>
                    </div>
                </Panel>

                <Panel icon={MegaphoneIcon} title={t('signage.active_announcements')} badge={<Pill tone="emerald">{props.announcements.length}</Pill>}>
                    {props.announcements.length === 0 ? (
                        <p className="text-sm text-slate-500">{t('signage.no_active_announcements')}</p>
                    ) : (
                        <div className="space-y-4">
                            {props.announcements.map((announcement) => (
                                <article key={announcement.id} className="rounded-xl border border-slate-100 bg-white p-3">
                                    <h3 className="text-sm font-semibold text-slate-900">{announcement.title}</h3>
                                    <p className="mb-2 text-xs text-slate-500">{announcement.date}</p>
                                    <RichText html={announcement.contentHtml} className="text-justify text-xs text-slate-600" />
                                </article>
                            ))}
                        </div>
                    )}
                </Panel>

                <Panel
                    icon={UserGroupIcon}
                    title={t('signage.on_duty_staff')}
                    subtitle={t('signage.currently_active_personnel')}
                    badge={
                        <Pill tone="emerald">
                            {props.staff.length} {t('signage.active_staff_label')}
                        </Pill>
                    }
                >
                    {props.staff.length === 0 ? (
                        <div className="rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 p-10 text-center text-slate-500">
                            <p className="font-medium">{t('signage.no_staff')}</p>
                            <p className="text-sm">{t('signage.offline_staff')}</p>
                        </div>
                    ) : (
                        <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {props.staff.map((person) => (
                                <li key={person.id} className="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-4">
                                    <span className="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-linear-to-br from-blue-500 to-blue-600 text-xl font-bold text-white">
                                        {person.avatarUrl ? (
                                            <img src={person.avatarUrl} alt="" className="h-full w-full object-cover" />
                                        ) : (
                                            person.name.charAt(0).toUpperCase()
                                        )}
                                    </span>
                                    <div className="min-w-0">
                                        <h3 className="truncate font-bold text-slate-900">{person.name}</h3>
                                        <p className="truncate text-sm text-slate-600">{person.position ?? t('signage.staff_position_fallback')}</p>
                                        <span className="mt-2 inline-flex items-center gap-1.5 rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                            <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden="true" />
                                            {t('signage.on_duty')}
                                        </span>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    )}

                    <div className="mt-8 flex items-center justify-between border-t border-slate-200 pt-6">
                        <div>
                            <p className="text-sm font-semibold text-slate-900">{t('signage.public_display')}</p>
                            <p className="text-xs text-slate-500">{t('signage.auto_refresh')}</p>
                        </div>
                        <div className="text-end">
                            <p className="text-xs font-medium text-slate-500">{t('signage.updated')}</p>
                            <p className="text-sm font-bold text-slate-900">{props.updatedAt}</p>
                        </div>
                    </div>
                </Panel>
            </main>
        </div>
    );
}

// Full-screen hall display: no site header or footer.
PublicSignage.layout = (page: ReactNode) => page;
