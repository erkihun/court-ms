import { PrinterIcon } from '@heroicons/react/24/outline';
import { Head, Link } from '@inertiajs/react';
import { Breakdown, type BreakdownRow, formatNumber } from '@/components/admin/reports/Breakdown';
import { Button } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { EthDateInput } from '@/components/ui/EthDateInput';
import { Field } from '@/components/ui/Field';
import { Select } from '@/components/ui/Input';
import { useCan } from '@/hooks/useCan';
import { useFilters } from '@/hooks/useFilters';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

type BreakdownKey = 'cases_status' | 'cases_types' | 'appeals_status' | 'decisions_status' | 'letters_status' | 'applicants_status' | 'users_status';

interface Props {
    summaryCards: Array<{ key: string; value: number }>;
    filtered: { count: number; byStatus: BreakdownRow[]; byJudge: BreakdownRow[] };
    breakdowns: Record<BreakdownKey, BreakdownRow[]>;
    recentCases: Array<{ id: number; number: string; title: string; status: string; party: string; when: string }>;
    recentAppeals: Array<{ id: number; number: string; caseNumber: string; title: string; status: string; when: string }>;
    caseStatuses: Array<{ value: string; label: string }>;
    judges: Array<{ id: number; name: string }>;
    filters: { start_date: string; end_date: string; status: string; judge_id: string };
}

const sum = (rows: BreakdownRow[]) => rows.reduce((total, row) => total + row.total, 0);

export default function ReportsIndex({ summaryCards, filtered, breakdowns, recentCases, recentAppeals, caseStatuses, judges, filters }: Props) {
    const can = useCan();
    const { values, change } = useFilters(route('reports.index'), filters);
    const statusLabel = caseStatuses.find((status) => status.value === filters.status)?.label;
    const judgeName = judges.find((judge) => String(judge.id) === filters.judge_id)?.name;

    const summaryRows: Array<[string, string | undefined]> = [
        [t('reports.filter.start'), filters.start_date || undefined],
        [t('reports.filter.end'), filters.end_date || undefined],
        [t('reports.filter.status'), statusLabel],
        [t('reports.filter.judge'), filters.judge_id ? (judgeName ?? t('reports.unknown')) : undefined],
    ];

    return (
        <>
            <Head title={t('reports.title')} />

            <div className="space-y-6">
                <Card>
                    <p className="text-xs tracking-widest text-blue-600 uppercase dark:text-blue-300">{t('reports.summary')}</p>
                    <h1 className="text-2xl font-semibold text-(--text)">{t('reports.title')}</h1>
                    <p className="text-sm text-(--text-muted)">{t('reports.description')}</p>

                    <div className="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {summaryCards.map((card) => (
                            <div key={card.key} className="rounded-2xl border border-(--border) bg-(--surface-soft) p-4">
                                <p className="text-[11px] font-semibold tracking-[0.2em] text-(--text-subtle) uppercase">{t(`reports.cards.${card.key}`)}</p>
                                <div className="mt-3 text-3xl font-bold text-(--text) tabular-nums">{formatNumber(card.value)}</div>
                            </div>
                        ))}
                    </div>
                </Card>

                <Card>
                    <div className="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p className="text-xs tracking-widest text-blue-600 uppercase dark:text-blue-300">{t('reports.filter.title')}</p>
                            <h2 className="text-lg font-semibold text-(--text)">{t('reports.filter.subtitle')}</h2>
                        </div>
                        <Button variant="secondary" onClick={() => window.print()} className="print:hidden">
                            <PrinterIcon className="h-4 w-4" />
                            {t('reports.print')}
                        </Button>
                    </div>

                    <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 print:hidden" role="search">
                        <Field label={t('reports.filter.start')}>
                            <EthDateInput value={values.start_date} onChange={(value) => change('start_date', value)} />
                        </Field>
                        <Field label={t('reports.filter.end')}>
                            <EthDateInput value={values.end_date} onChange={(value) => change('end_date', value)} />
                        </Field>
                        <Field label={t('reports.filter.status')}>
                            <Select value={values.status} onChange={(event) => change('status', event.target.value)}>
                                <option value="">{t('reports.filter.status_all')}</option>
                                {caseStatuses.map((status) => (
                                    <option key={status.value} value={status.value}>
                                        {status.label}
                                    </option>
                                ))}
                            </Select>
                        </Field>
                        <Field label={t('reports.filter.judge')}>
                            <Select value={values.judge_id} onChange={(event) => change('judge_id', event.target.value)}>
                                <option value="">{t('reports.filter.judge_all')}</option>
                                {judges.map((judge) => (
                                    <option key={judge.id} value={String(judge.id)}>
                                        {judge.name}
                                    </option>
                                ))}
                            </Select>
                        </Field>
                    </div>

                    <div className="mt-5 grid gap-4 lg:grid-cols-2">
                        <section className="rounded-2xl border border-(--border) bg-(--surface-soft) p-4">
                            <h3 className="text-sm font-semibold text-(--text)">{t('reports.filter.summary')}</h3>
                            <p className="mt-1 text-sm text-(--text-muted)">{t('reports.filter.summary_hint')}</p>
                            <dl className="mt-3 divide-y divide-dashed divide-(--border) text-sm">
                                <div className="flex justify-between py-2">
                                    <dt className="text-(--text-muted)">{t('reports.filter.cases_filtered')}</dt>
                                    <dd className="font-semibold text-(--text) tabular-nums">{formatNumber(filtered.count)}</dd>
                                </div>
                                {summaryRows
                                    .filter(([, value]) => value !== undefined)
                                    .map(([label, value]) => (
                                        <div key={label} className="flex justify-between py-2">
                                            <dt className="text-(--text-muted)">{label}</dt>
                                            <dd className="text-(--text)">{value}</dd>
                                        </div>
                                    ))}
                            </dl>
                        </section>
                        <Breakdown title={t('reports.filter.status_breakdown')} hint={t('reports.filter.status_hint')} rows={filtered.byStatus} />
                    </div>

                    <div className="mt-4">
                        <Breakdown
                            title={t('reports.filter.judge_breakdown')}
                            hint={t('reports.filter.judge_hint')}
                            rows={filtered.byJudge}
                            aside={t('reports.filter.cases')}
                        />
                    </div>
                </Card>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Breakdown
                        title={t('reports.sections.cases_status')}
                        rows={breakdowns.cases_status}
                        aside={`${formatNumber(sum(breakdowns.cases_status))} ${t('reports.total')}`}
                    />
                    <Breakdown title={t('reports.sections.cases_types')} rows={breakdowns.cases_types} aside={t('reports.cases')} />
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    <Breakdown title={t('reports.sections.appeals_status')} rows={breakdowns.appeals_status} />
                    <Breakdown title={t('reports.sections.decisions_status')} rows={breakdowns.decisions_status} />
                    <Breakdown title={t('reports.sections.letters_status')} rows={breakdowns.letters_status} />
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Breakdown title={t('reports.sections.applicants_status')} rows={breakdowns.applicants_status} />
                    <Breakdown title={t('reports.sections.users_status')} rows={breakdowns.users_status} />
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <RecentList
                        title={t('reports.sections.recent_cases')}
                        hint={t('reports.recent_hint.cases')}
                        allHref={can('cases.view') ? route('cases.index') : null}
                        allLabel={t('reports.view_all', { thing: t('app.Cases') })}
                        items={recentCases.map((item) => ({
                            key: item.id,
                            href: can('cases.view') ? route('cases.show', item.id) : null,
                            heading: item.number,
                            status: item.status,
                            subtitle: item.title,
                            footStart: item.party,
                            footEnd: item.when,
                        }))}
                    />
                    <RecentList
                        title={t('reports.sections.recent_appeals')}
                        hint={t('reports.recent_hint.appeals')}
                        allHref={can('appeals.view') ? route('appeals.index') : null}
                        allLabel={t('reports.view_all', { thing: t('app.Appeals') })}
                        items={recentAppeals.map((item) => ({
                            key: item.id,
                            href: can('appeals.view') ? route('appeals.show', item.id) : null,
                            heading: item.number,
                            status: item.status,
                            subtitle: item.caseNumber,
                            footStart: item.title,
                            footEnd: item.when,
                        }))}
                    />
                </div>
            </div>
        </>
    );
}

interface RecentItem {
    key: number;
    href: string | null;
    heading: string;
    status: string;
    subtitle: string;
    footStart: string;
    footEnd: string;
}

function RecentList({ title, hint, allHref, allLabel, items }: { title: string; hint: string; allHref: string | null; allLabel: string; items: RecentItem[] }) {
    return (
        <section className="ui-card p-5">
            <div className="flex items-center justify-between gap-3">
                <div>
                    <h3 className="text-sm font-semibold text-(--text)">{title}</h3>
                    <p className="text-xs text-(--text-subtle)">{hint}</p>
                </div>
                {allHref && (
                    <Link
                        href={allHref}
                        className="text-xs font-semibold tracking-wide text-blue-600 uppercase hover:underline dark:text-blue-300 print:hidden"
                    >
                        {allLabel}
                    </Link>
                )}
            </div>
            {items.length === 0 ? (
                <p className="mt-4 text-xs text-(--text-subtle)">{t('reports.no_data')}</p>
            ) : (
                <ul className="mt-4 space-y-3">
                    {items.map((item) => {
                        const body = (
                            <>
                                <div className="flex items-center justify-between gap-3 text-(--text)">
                                    <span className="font-semibold">{item.heading}</span>
                                    <span className="text-[11px] tracking-wide text-(--text-subtle) uppercase">{item.status}</span>
                                </div>
                                <p className="text-xs text-(--text-muted)">{item.subtitle}</p>
                                <div className="flex items-center justify-between gap-3 text-[11px] text-(--text-subtle)">
                                    <span className="truncate">{item.footStart}</span>
                                    <span className="whitespace-nowrap">{item.footEnd}</span>
                                </div>
                            </>
                        );
                        const classes = 'flex flex-col gap-1 rounded-2xl border border-(--border) p-3 text-sm';

                        return (
                            <li key={item.key}>
                                {item.href ? (
                                    <Link href={item.href} className={`${classes} transition hover:border-blue-300 hover:bg-(--surface-soft)`}>
                                        {body}
                                    </Link>
                                ) : (
                                    <div className={classes}>{body}</div>
                                )}
                            </li>
                        );
                    })}
                </ul>
            )}
        </section>
    );
}
