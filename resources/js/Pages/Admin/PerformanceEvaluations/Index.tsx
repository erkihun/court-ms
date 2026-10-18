import { ChartBarIcon, PlusIcon, Squares2X2Icon } from '@heroicons/react/24/outline';
import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import { AddCategoryDialog, AddCriterionDialog } from '@/components/admin/performance/QuickAddDialogs';
import { EvaluationStatusBadge, type EvaluationRow, MemberAvatar, percentTone } from '@/components/admin/performance/scoring';
import { Badge } from '@/components/ui/Badge';
import { Button, LinkButton } from '@/components/ui/Button';
import { EmptyState } from '@/components/ui/EmptyState';
import { Select } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { Pagination } from '@/components/ui/Pagination';
import { Table } from '@/components/ui/Table';
import { useFilters } from '@/hooks/useFilters';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Paginated } from '@/types';

interface Props {
    evaluations: Paginated<EvaluationRow>;
    users: Array<{ id: number; name: string }>;
    stats: { total: number; draft: number; submitted: number; reviewed: number; avg_score: number };
    filters: { status: string; user_id: string; period_type: string };
    periodTypes: string[];
    categories: Array<{ slug: string; name: string }>;
    can: { create: boolean; update: boolean; manageSettings: boolean };
}

export default function PerformanceEvaluationsIndex({ evaluations, users, stats, filters, periodTypes, categories, can }: Props) {
    const { values, change, reset, active } = useFilters(route('performance-evaluations.index'), filters);
    const [dialog, setDialog] = useState<'criterion' | 'category' | null>(null);
    const na = t('performance.not_available');

    const statCards = [
        { label: t('performance.stats.total'), value: stats.total, tone: 'text-(--text)' },
        { label: t('performance.stats.draft'), value: stats.draft, tone: 'text-amber-600 dark:text-amber-400' },
        { label: t('performance.stats.submitted'), value: stats.submitted, tone: 'text-blue-600 dark:text-blue-400' },
        { label: t('performance.stats.reviewed'), value: stats.reviewed, tone: 'text-emerald-600 dark:text-emerald-400' },
        { label: t('performance.stats.avg_score'), value: `${stats.avg_score}%`, tone: 'text-violet-600 dark:text-violet-400' },
    ];

    const tabs = [
        { status: '', label: t('performance.tabs.all'), count: stats.total },
        { status: 'draft', label: t('performance.statuses.draft'), count: stats.draft },
        { status: 'submitted', label: t('performance.statuses.submitted'), count: stats.submitted },
        { status: 'reviewed', label: t('performance.statuses.reviewed'), count: stats.reviewed },
    ];

    const tabHref = (status: string) => {
        const query = Object.fromEntries(Object.entries({ ...filters, status }).filter(([, value]) => value !== ''));
        return route('performance-evaluations.index', query);
    };

    return (
        <>
            <Head title={t('performance.title')} />
            <PageHeader
                title={t('performance.title')}
                className="mb-6"
                actions={
                    <>
                        {can.manageSettings && (
                            <>
                                <Button variant="secondary" onClick={() => setDialog('criterion')}>
                                    <PlusIcon className="h-4 w-4" />
                                    {t('performance.settings.actions.add_criterion')}
                                </Button>
                                <Button variant="secondary" onClick={() => setDialog('category')}>
                                    <Squares2X2Icon className="h-4 w-4" />
                                    {t('performance.settings.actions.add_category')}
                                </Button>
                            </>
                        )}
                        {can.create && (
                            <LinkButton href={route('performance-evaluations.create')}>
                                <PlusIcon className="h-4 w-4" />
                                {t('performance.actions.new')}
                            </LinkButton>
                        )}
                    </>
                }
            />

            <div className="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-5">
                {statCards.map((stat) => (
                    <div key={stat.label} className="ui-card p-4 text-center">
                        <div className={cn('text-2xl font-extrabold tabular-nums', stat.tone)}>{stat.value}</div>
                        <div className="mt-0.5 text-xs font-semibold tracking-wide text-(--text-subtle) uppercase">{stat.label}</div>
                    </div>
                ))}
            </div>

            <nav className="mb-4 flex gap-1 overflow-x-auto border-b border-(--border)" aria-label={t('performance.fields.status')}>
                {tabs.map((tab) => {
                    const selected = filters.status === tab.status;

                    return (
                        <Link
                            key={tab.status || 'all'}
                            href={tabHref(tab.status)}
                            preserveScroll
                            aria-current={selected ? 'page' : undefined}
                            className={cn(
                                '-mb-px inline-flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-semibold whitespace-nowrap transition-colors',
                                selected ? 'border-blue-600 text-blue-700 dark:text-blue-300' : 'border-transparent text-(--text-muted) hover:text-(--text)',
                            )}
                        >
                            {tab.label}
                            <span className="rounded-full bg-(--surface-soft) px-2 py-0.5 text-xs text-(--text-subtle)">{tab.count}</span>
                        </Link>
                    );
                })}
            </nav>

            <div className="mb-4 flex flex-wrap gap-2" role="search">
                <Select
                    aria-label={t('performance.fields.member')}
                    value={values.user_id}
                    onChange={(event) => change('user_id', event.target.value)}
                    className="w-auto"
                >
                    <option value="">{t('performance.filters.all_members')}</option>
                    {users.map((user) => (
                        <option key={user.id} value={String(user.id)}>
                            {user.name}
                        </option>
                    ))}
                </Select>
                <Select
                    aria-label={t('performance.fields.period_type')}
                    value={values.period_type}
                    onChange={(event) => change('period_type', event.target.value)}
                    className="w-auto"
                >
                    <option value="">{t('performance.filters.all_periods')}</option>
                    {periodTypes.map((period) => (
                        <option key={period} value={period}>
                            {t(`performance.periods.${period}`)}
                        </option>
                    ))}
                </Select>
                {active && (
                    <Button variant="ghost" onClick={reset}>
                        {t('performance.filters.clear')}
                    </Button>
                )}
            </div>

            <Table
                head={
                    <tr>
                        <th scope="col">{t('performance.fields.member')}</th>
                        <th scope="col">{t('performance.fields.period')}</th>
                        <th scope="col">{t('performance.fields.type')}</th>
                        <th scope="col" className="text-center">
                            {t('performance.fields.score')}
                        </th>
                        <th scope="col" className="text-center">
                            {t('performance.fields.status')}
                        </th>
                        <th scope="col">{t('performance.fields.evaluator')}</th>
                        <th scope="col">{t('performance.fields.date')}</th>
                        <th scope="col">
                            <span className="sr-only">{t('performance.fields.actions')}</span>
                        </th>
                    </tr>
                }
                footer={
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <p className="text-xs text-(--text-subtle)">
                            {evaluations.total > 0
                                ? t('performance.pagination.showing', { from: evaluations.from, to: evaluations.to, total: evaluations.total })
                                : t('performance.empty.evaluations')}
                        </p>
                        {evaluations.last_page > 1 && <Pagination meta={evaluations} />}
                    </div>
                }
            >
                {evaluations.data.length === 0 ? (
                    <tr>
                        <td colSpan={8} className="py-12">
                            <EmptyState icon={<ChartBarIcon className="h-6 w-6" />} title={t('performance.empty.evaluations')}>
                                {can.create && (
                                    <Link href={route('performance-evaluations.create')} className="text-xs text-blue-600 hover:underline dark:text-blue-300">
                                        {t('performance.actions.create_first')}
                                    </Link>
                                )}
                            </EmptyState>
                        </td>
                    </tr>
                ) : (
                    evaluations.data.map((evaluation) => (
                        <tr key={evaluation.id}>
                            <td>
                                <div className="flex items-center gap-2.5">
                                    <MemberAvatar name={evaluation.member} url={evaluation.memberAvatarUrl} />
                                    <span className="font-medium text-(--text)">{evaluation.member ?? na}</span>
                                </div>
                            </td>
                            <td className="whitespace-nowrap">{evaluation.period}</td>
                            <td>
                                <Badge tone="neutral">{t(`performance.periods.${evaluation.periodType}`)}</Badge>
                            </td>
                            <td className="text-center">
                                <span className={cn('text-lg font-extrabold tabular-nums', percentTone(evaluation.score))}>{evaluation.score.toFixed(1)}%</span>
                            </td>
                            <td className="text-center">
                                <EvaluationStatusBadge status={evaluation.status} />
                            </td>
                            <td>{evaluation.evaluator ?? na}</td>
                            <td className="text-xs whitespace-nowrap text-(--text-subtle)">{evaluation.created}</td>
                            <td>
                                <div className="flex justify-end gap-2">
                                    <LinkButton href={route('performance-evaluations.show', evaluation.id)} variant="secondary" size="sm">
                                        {t('performance.actions.view')}
                                    </LinkButton>
                                    {can.update && evaluation.status !== 'reviewed' && (
                                        <LinkButton href={route('performance-evaluations.edit', evaluation.id)} variant="secondary" size="sm">
                                            {t('performance.actions.edit')}
                                        </LinkButton>
                                    )}
                                </div>
                            </td>
                        </tr>
                    ))
                )}
            </Table>

            {can.manageSettings && (
                <>
                    <AddCriterionDialog open={dialog === 'criterion'} onClose={() => setDialog(null)} categories={categories} />
                    <AddCategoryDialog open={dialog === 'category'} onClose={() => setDialog(null)} />
                </>
            )}
        </>
    );
}
