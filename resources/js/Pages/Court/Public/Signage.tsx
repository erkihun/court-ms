import { Head, router } from '@inertiajs/react';
import { useEffect, type ReactNode } from 'react';
import LocalizedDateDisplay from '@/Components/Calendar/LocalizedDateDisplay';
import RichTextViewer from '@/Components/forms/RichTextViewer';
import LocalizedUiProvider from '@/Components/ui/LocalizedUiProvider';
import { usePublicText } from '@/court/i18n/usePublicText';
import type { PublicSignageProps } from '@/court/types/public';

const STATUS_COLORS: Record<string, { bar: string; text: string; border: string }> = {
    active: { bar: 'bg-blue-600', text: 'text-blue-700', border: 'border-blue-200' },
    pending: { bar: 'bg-amber-500', text: 'text-amber-700', border: 'border-amber-200' },
    closed: { bar: 'bg-slate-400', text: 'text-slate-700', border: 'border-slate-200' },
    hearing: { bar: 'bg-blue-600', text: 'text-blue-700', border: 'border-blue-200' },
    dismissed: { bar: 'bg-rose-600', text: 'text-rose-700', border: 'border-rose-200' },
};

/** Str::limit($text, 18), as the Blade board shortened applicant names. */
function limit(text: string, length: number): string {
    return [...text].length > length ? `${[...text].slice(0, length).join('')}...` : text;
}

function Panel({ icon, title, subtitle, badge, children }: { icon: string; title: string; subtitle: string; badge?: ReactNode; children: ReactNode }) {
    return (
        <section className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_10px_25px_-5px_rgba(0,0,0,0.05)]">
            <div className="border-b border-slate-100 bg-linear-to-r from-slate-50 to-white px-6 py-5">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50" aria-hidden="true">
                            <svg className="h-6 w-6 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}><path d={icon} /></svg>
                        </div>
                        <div>
                            <h2 className="text-lg font-bold text-slate-900">{title}</h2>
                            <p className="mt-0.5 text-sm text-slate-500">{subtitle}</p>
                        </div>
                    </div>
                    {badge}
                </div>
            </div>
            <div className="p-6">{children}</div>
        </section>
    );
}

/**
 * The public display board (PublicSignageController): live totals, the case
 * distribution and categories, today's filings and hearings, active
 * announcements and the staff on duty. It is a screen for a wall display,
 * not a document, so it has no site header or footer, and it refreshes its
 * data on the interval the Blade board's meta refresh used.
 */
export default function PublicSignage(props: PublicSignageProps) {
    const { t, fill, locale } = usePublicText();
    const { now, appName, logoUrl, totalCases, statusCounts, categoryCounts, todayCases, todayHearings, activeAnnouncements, activeStaff, refreshSeconds } = props;
    const number = new Intl.NumberFormat(locale === 'am' ? 'am-ET' : 'en-US');
    const maxCategory = Math.max(1, ...categoryCounts.map((category) => category.total));
    const updatedAt = new Date(now);
    const updatedTime = Number.isNaN(updatedAt.getTime())
        ? ''
        : updatedAt.toLocaleTimeString(locale === 'am' ? 'am-ET' : 'en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });

    useEffect(() => {
        const timer = window.setInterval(() => router.reload(), Math.max(30, refreshSeconds) * 1000);
        return () => window.clearInterval(timer);
    }, [refreshSeconds]);

    const statusLabel = (label: string) => {
        const key = `cm.signage.status_labels.${label.toLowerCase()}`;
        const value = t(key);
        return value === key ? label : value;
    };

    const todayBadge = (tone: 'blue' | 'amber') => (
        <span className={`rounded-full px-2.5 py-1 text-xs font-semibold ${tone === 'blue' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600'}`}>{t('cm.signage.today')}</span>
    );

    return (
        <LocalizedUiProvider>
            <Head title={t('cm.signage.title')} />

            <div className="min-h-screen bg-linear-135 from-slate-50 via-slate-100 to-slate-200 text-slate-900">
                <div className="fixed right-4 top-4 z-20 flex items-center gap-2 rounded-full bg-white px-4 py-2 shadow-lg ring-1 ring-slate-900/5" role="status">
                    <span className="relative flex h-3 w-3" aria-hidden="true">
                        <span className="absolute inset-0 animate-ping rounded-full bg-emerald-500 opacity-75 motion-reduce:animate-none" />
                        <span className="relative h-3 w-3 rounded-full bg-emerald-500" />
                    </span>
                    <span className="text-sm font-semibold text-slate-700">{t('cm.signage.live_updates')}</span>
                </div>

                <main className="mx-auto grid min-h-screen max-w-7xl items-start gap-6 p-4 sm:p-6 lg:p-8 xl:grid-cols-[3fr_2fr]">
                    <div className="flex min-w-0 flex-col gap-6">
                        <header className="overflow-hidden rounded-2xl border border-blue-700/30 bg-linear-to-br from-blue-900 via-blue-800 to-blue-900 text-white shadow-2xl">
                            <div className="relative px-6 py-6 md:px-8 md:py-8">
                                <div className="absolute inset-0 opacity-10" aria-hidden="true" style={{ backgroundImage: 'radial-gradient(circle at 1px 1px, white 1px, transparent 0)', backgroundSize: '20px 20px' }} />
                                <div className="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                                    <div className="flex items-center gap-5">
                                        <div className="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl border-2 border-white/20 bg-white/10 p-3 shadow-xl backdrop-blur-sm">
                                            {logoUrl && <img className="h-full w-full object-contain drop-shadow-lg" src={logoUrl} alt="" />}
                                        </div>
                                        <div className="min-w-0">
                                            <div className="mb-1 flex items-center gap-2">
                                                <span className="text-xs font-semibold uppercase text-blue-200/90">{t('cm.signage.court_management')}</span>
                                                <span className="h-2 w-2 animate-pulse rounded-full bg-emerald-400 motion-reduce:animate-none" aria-hidden="true" />
                                            </div>
                                            <h1 className="text-2xl font-black tracking-tight drop-shadow-lg md:text-3xl">{appName}</h1>
                                            <div className="mt-3 flex flex-wrap items-center gap-2 text-sm font-medium text-blue-100">
                                                <span className="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 backdrop-blur-sm">
                                                    <LocalizedDateDisplay value={now} withTime />
                                                </span>
                                                <span className="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 backdrop-blur-sm">{t('cm.signage.public_display')}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="md:text-right">
                                        <div className="inline-block rounded-xl border border-white/20 bg-white/10 p-4 backdrop-blur-sm">
                                            <p className="mb-2 text-xs font-semibold uppercase text-blue-200/80">{t('cm.signage.total_cases')}</p>
                                            <div className="flex items-baseline gap-2 md:justify-end">
                                                <p className="text-5xl font-black leading-none tracking-tight drop-shadow-xl md:text-6xl">{number.format(totalCases)}</p>
                                                <span className="text-lg font-bold text-blue-200">{t('cm.signage.cases')}</span>
                                            </div>
                                            <p className="mt-2 text-xs font-medium text-blue-200/70">{t('cm.signage.across_departments')}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </header>

                        <div className="grid gap-6 md:grid-cols-2">
                            <Panel
                                icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                title={t('cm.signage.case_distribution')}
                                subtitle={t('cm.signage.status_breakdown')}
                                badge={(
                                    <span className="inline-flex items-center gap-1 rounded-full border border-orange-200 bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-700">
                                        <span className="h-1.5 w-1.5 animate-pulse rounded-full bg-orange-500 motion-reduce:animate-none" aria-hidden="true" />
                                        {t('cm.signage.live_status')}
                                    </span>
                                )}
                            >
                                <ul className="grid gap-4">
                                    {statusCounts.map((stat) => {
                                        const pct = totalCases > 0 ? Math.round((stat.total / totalCases) * 100) : 0;
                                        const color = STATUS_COLORS[stat.label.toLowerCase()] ?? STATUS_COLORS.active;
                                        return (
                                            <li key={stat.label} className={`rounded-xl border bg-white p-3 ${color.border}`}>
                                                <div className="mb-2 flex items-center justify-between">
                                                    <span className="font-semibold text-slate-900">{statusLabel(stat.label)}</span>
                                                    <span className="text-lg font-bold text-slate-900">{number.format(stat.total)}</span>
                                                </div>
                                                <div className="flex items-center gap-3">
                                                    <div className="h-2.5 flex-1 overflow-hidden rounded-full bg-slate-200" aria-hidden="true">
                                                        <div className={`h-full rounded-full ${color.bar}`} style={{ width: `${pct}%` }} />
                                                    </div>
                                                    <span className={`min-w-12 text-right text-sm font-bold ${color.text}`}>{pct}%</span>
                                                </div>
                                            </li>
                                        );
                                    })}
                                </ul>
                            </Panel>

                            <Panel
                                icon="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                title={t('cm.signage.case_categories')}
                                subtitle={t('cm.signage.top_categories')}
                                badge={(
                                    <span className="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                        {fill(t('cm.signage.top_count_label'), { count: categoryCounts.length })}
                                    </span>
                                )}
                            >
                                <ul className="grid gap-4">
                                    {categoryCounts.map((category) => {
                                        const pct = Math.round((category.total / maxCategory) * 100);
                                        return (
                                            <li key={category.name} className="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4">
                                                <div className="flex min-w-0 items-center gap-4">
                                                    <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-linear-to-br from-blue-50 to-blue-100">
                                                        <span className="text-lg font-bold text-blue-700">{category.total}</span>
                                                    </div>
                                                    <div className="min-w-0">
                                                        <h3 className="truncate font-semibold text-slate-900">{category.name}</h3>
                                                        <div className="mt-2 flex items-center gap-2">
                                                            <div className="h-2 w-24 overflow-hidden rounded-full bg-slate-200" aria-hidden="true">
                                                                <div className="h-full rounded-full bg-linear-to-r from-blue-500 to-blue-600" style={{ width: `${pct}%` }} />
                                                            </div>
                                                            <span className="text-sm text-slate-500">{pct}%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span className="text-sm font-medium text-slate-500">{t('cm.signage.cases')}</span>
                                            </li>
                                        );
                                    })}
                                </ul>
                            </Panel>
                        </div>

                        <Panel
                            icon="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            title={t('cm.signage.todays_activity')}
                            subtitle={t('cm.signage.live_status')}
                            badge={(
                                <div className="flex flex-wrap gap-2 text-sm font-semibold">
                                    <span className="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-blue-700">
                                        <span className="h-2 w-2 rounded-full bg-blue-500" aria-hidden="true" />
                                        {todayCases.length} {t('cm.signage.new_submissions')}
                                    </span>
                                    <span className="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-amber-700">
                                        <span className="h-2 w-2 rounded-full bg-amber-500" aria-hidden="true" />
                                        {todayHearings.length} {t('cm.signage.hearings')}
                                    </span>
                                </div>
                            )}
                        >
                            <div className="grid gap-6 lg:grid-cols-2">
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <h3 className="text-base font-bold text-slate-900">{t('cm.signage.new_submissions')}</h3>
                                            <p className="mt-0.5 text-sm text-slate-500">{t('cm.signage.live_status')}</p>
                                        </div>
                                        {todayBadge('blue')}
                                    </div>
                                    <div className="max-h-[300px] overflow-y-auto rounded-xl border border-slate-200">
                                        <table className="w-full text-sm">
                                            <thead className="sticky top-0 bg-slate-50">
                                                <tr className="border-b border-slate-200 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                                    <th scope="col" className="px-4 py-3">{t('cm.signage.case_number')}</th>
                                                    <th scope="col" className="px-4 py-3">{t('cm.signage.applicant_column')}</th>
                                                    <th scope="col" className="px-4 py-3">{t('cm.cases.case_type')}</th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-slate-100">
                                                {todayCases.length === 0 ? (
                                                    <tr><td colSpan={3} className="px-4 py-8 text-center text-slate-400">{t('cm.signage.no_new_submissions')}</td></tr>
                                                ) : todayCases.map((item) => (
                                                    <tr key={item.id}>
                                                        <td className="px-4 py-3 font-mono text-sm font-bold text-slate-800">{item.case_number ?? `${t('cm.signage.case_number')} ${item.id}`}</td>
                                                        <td className="px-4 py-3 text-slate-700">{limit(item.applicant ?? t('cm.signage.unknown_applicant'), 18)}</td>
                                                        <td className="px-4 py-3">
                                                            <span className="inline-flex items-center rounded-md border border-blue-100 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                                                                {item.case_type ?? t('cm.signage.general_case_type')}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <h3 className="text-base font-bold text-slate-900">{t('cm.signage.scheduled_hearings')}</h3>
                                            <p className="mt-0.5 text-sm text-slate-500">{t('cm.signage.live_status')}</p>
                                        </div>
                                        {todayBadge('amber')}
                                    </div>
                                    <div className="max-h-[300px] overflow-y-auto rounded-xl border border-slate-200">
                                        <table className="w-full text-sm">
                                            <thead className="sticky top-0 bg-slate-50">
                                                <tr className="border-b border-slate-200 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                                    <th scope="col" className="px-4 py-3">{t('cm.signage.case_number')}</th>
                                                    <th scope="col" className="px-4 py-3">{t('cm.signage.time')}</th>
                                                    <th scope="col" className="px-4 py-3">{t('cm.signage.type')}</th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-slate-100">
                                                {todayHearings.length === 0 ? (
                                                    <tr><td colSpan={3} className="px-4 py-8 text-center text-slate-400">{t('cm.signage.no_hearings')}</td></tr>
                                                ) : todayHearings.map((hearing) => (
                                                    <tr key={hearing.id}>
                                                        <td className="px-4 py-3 font-mono text-sm font-bold text-slate-800">{hearing.case_number ?? `${t('cm.signage.case_number')} ${hearing.case_id}`}</td>
                                                        <td className="px-4 py-3 font-medium text-slate-800">
                                                            <LocalizedDateDisplay value={hearing.hearing_at} fallback="-" /> {hearing.time ?? '-'}
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <span className="inline-flex items-center rounded-md border border-blue-100 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                                                                {hearing.case_type ?? t('cm.signage.hearings')}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </Panel>

                        <section className="rounded-2xl border border-slate-200 bg-linear-to-r from-white to-slate-50 p-4 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.05)]">
                            <div className="mb-3 flex items-center justify-between gap-3">
                                <div>
                                    <h2 className="text-base font-bold text-slate-900">{t('cm.signage.active_announcements')}</h2>
                                    <p className="text-xs text-slate-500">{t('cm.signage.today')}</p>
                                </div>
                                <span className="rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-600">
                                    {activeAnnouncements.length} {t('cm.signage.active_announcements')}
                                </span>
                            </div>
                            <div className="space-y-4">
                                {activeAnnouncements.length === 0 ? (
                                    <p className="text-sm text-slate-500">{t('cm.signage.no_active_announcements')}</p>
                                ) : activeAnnouncements.map((announcement) => (
                                    <article key={announcement.id} className="rounded-xl border border-slate-100 bg-white/80 p-3">
                                        <h3 className="text-sm font-semibold text-slate-900">{announcement.title}</h3>
                                        <p className="mb-2 text-xs text-slate-500"><LocalizedDateDisplay value={announcement.created_at} /></p>
                                        <RichTextViewer html={announcement.content} className="text-justify text-xs text-slate-600" />
                                    </article>
                                ))}
                            </div>
                        </section>
                    </div>

                    <Panel
                        icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0a9 9 0 11-18 0 9 9 0 0118 0z"
                        title={t('cm.signage.on_duty_staff')}
                        subtitle={t('cm.signage.currently_active_personnel')}
                        badge={(
                            <span className="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                <span className="h-2 w-2 animate-pulse rounded-full bg-emerald-500 motion-reduce:animate-none" aria-hidden="true" />
                                {activeStaff.length} {t('cm.signage.active_staff_label')}
                            </span>
                        )}
                    >
                        {activeStaff.length === 0 ? (
                            <div className="rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 p-10 text-center text-slate-400">
                                <p className="font-medium">{t('cm.signage.no_staff')}</p>
                                <p className="text-sm">{t('cm.signage.offline_staff')}</p>
                            </div>
                        ) : (
                            <ul className="grid gap-4 sm:grid-cols-2">
                                {activeStaff.map((person) => (
                                    <li key={person.id} className="flex items-center gap-4 rounded-xl border border-slate-200 bg-linear-to-r from-white to-slate-50 p-4">
                                        <div className="relative shrink-0">
                                            <div className="flex h-16 w-16 items-center justify-center overflow-hidden rounded-xl bg-linear-to-br from-blue-500 to-blue-600 text-xl font-bold text-white shadow-md">
                                                {person.avatar_url
                                                    ? <img className="h-full w-full object-cover" src={person.avatar_url} alt={person.name} />
                                                    : [...(person.name || 'N/A')][0]?.toLocaleUpperCase()}
                                            </div>
                                            <span className="absolute -bottom-1 -right-1 h-4 w-4 rounded-full border-2 border-white bg-emerald-500" aria-hidden="true" />
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <h3 className="truncate font-bold text-slate-900">{person.name}</h3>
                                            <p className="truncate text-sm text-slate-600">{person.position ?? t('cm.signage.staff_position_fallback')}</p>
                                            <span className="mt-2 inline-flex items-center gap-1.5 rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                                <span className="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500 motion-reduce:animate-none" aria-hidden="true" />
                                                {t('cm.signage.on_duty')}
                                            </span>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        )}

                        <div className="mt-8 flex items-center justify-between border-t border-slate-200 pt-6">
                            <div>
                                <p className="text-sm font-semibold text-slate-900">{t('cm.signage.public_display')}</p>
                                <p className="text-xs text-slate-500">{t('cm.signage.auto_refresh')}</p>
                            </div>
                            <div className="text-right">
                                <p className="text-xs font-medium text-slate-500">{t('cm.signage.updated')}</p>
                                <p className="text-sm font-bold text-slate-900">{updatedTime}</p>
                            </div>
                        </div>
                    </Panel>
                </main>
            </div>
        </LocalizedUiProvider>
    );
}
