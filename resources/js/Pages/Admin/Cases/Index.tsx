import { FolderOpenIcon } from '@heroicons/react/24/outline';
import { Head, Link } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { CASE_STATUSES, CaseStatusBadge, ReviewStatusBadge } from '@/components/admin/cases/CaseBadges';
import { Button, LinkButton } from '@/components/ui/Button';
import { EmptyState } from '@/components/ui/EmptyState';
import { EthDateInput } from '@/components/ui/EthDateInput';
import { Field } from '@/components/ui/Field';
import { Input, Select } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { Pagination } from '@/components/ui/Pagination';
import { Table } from '@/components/ui/Table';
import { useCan } from '@/hooks/useCan';
import { useFilters } from '@/hooks/useFilters';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Paginated } from '@/types';

interface CaseRow {
    id: number;
    caseNumber: string;
    title: string;
    /** Set when a lawyer filed the case. */
    applicantName: string | null;
    caseType: string | null;
    teamName: string | null;
    assigneeName: string | null;
    status: string;
    filingDate: string;
    reviewStatus: string;
    reviewerName: string | null;
}

interface Option {
    id: number;
    name: string;
}

interface Filters {
    q: string;
    status: string;
    case_type_id: string;
    assignee_id: string;
    from: string;
    to: string;
    [key: string]: string;
}

interface Props {
    cases: Paginated<CaseRow>;
    filters: Filters;
    types: Option[];
    users: Option[];
    exportUrl: string;
}

export default function CasesIndex({ cases, filters, types, users, exportUrl }: Props) {
    const can = useCan();
    const { values, set, apply, reset, active } = useFilters(route('cases.index'), filters);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        apply();
    };

    return (
        <>
            <Head title={t('cases.case_details')} />
            <PageHeader title={t('cases.case_details')} className="mb-6" />

            <form onSubmit={submit} className="ui-card mb-6 p-5" role="search">
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-6">
                    <Field label={t('cases.search')} className="md:col-span-2 lg:col-span-6">
                        <Input value={values.q} onChange={(event) => set('q', event.target.value)} placeholder={t('cases.search_placeholder')} />
                    </Field>
                    <Field label={t('cases.table.status')}>
                        <Select value={values.status} onChange={(event) => set('status', event.target.value)}>
                            <option value="">{t('cases.filters.all_statuses')}</option>
                            {CASE_STATUSES.map((status) => (
                                <option key={status} value={status}>
                                    {t(`cases.status.${status}`)}
                                </option>
                            ))}
                        </Select>
                    </Field>
                    <Field label={t('cases.table.type')}>
                        <Select value={values.case_type_id} onChange={(event) => set('case_type_id', event.target.value)}>
                            <option value="">{t('cases.filters.all_types')}</option>
                            {types.map((type) => (
                                <option key={type.id} value={String(type.id)}>
                                    {type.name}
                                </option>
                            ))}
                        </Select>
                    </Field>
                    <Field label={t('cases.table.assignee')}>
                        <Select value={values.assignee_id} onChange={(event) => set('assignee_id', event.target.value)}>
                            <option value="">{t('cases.filters.all_assignees')}</option>
                            {users.map((user) => (
                                <option key={user.id} value={String(user.id)}>
                                    {user.name}
                                </option>
                            ))}
                        </Select>
                    </Field>
                    <Field label={t('cases.filters.from')}>
                        <EthDateInput value={values.from} onChange={(value) => set('from', value)} />
                    </Field>
                    <Field label={t('cases.filters.to')}>
                        <EthDateInput value={values.to} onChange={(value) => set('to', value)} align="end" />
                    </Field>
                </div>

                <div className="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-(--border) pt-4">
                    <div className="flex flex-wrap gap-2">
                        <Button type="submit">{t('cases.filters.apply_filters')}</Button>
                        {active && (
                            <Button variant="secondary" onClick={reset}>
                                {t('cases.reset')}
                            </Button>
                        )}
                        {can('reports.export') && (
                            <LinkButton href={exportUrl} variant="secondary" external>
                                {t('cases.export_csv')}
                            </LinkButton>
                        )}
                    </div>
                    {cases.total > 0 && (
                        <span className="text-sm text-(--text-muted)">
                            {t('cases.showing_results', { first: cases.from, last: cases.to, total: cases.total })}
                        </span>
                    )}
                </div>
            </form>

            <Table
                head={
                    <tr>
                        <th scope="col">{t('cases.table.number')}</th>
                        <th scope="col">{t('cases.table.case_number')}</th>
                        <th scope="col">{t('cases.applicant_name')}</th>
                        <th scope="col">{t('cases.table.type')}</th>
                        <th scope="col">{t('cases.table.team')}</th>
                        <th scope="col">{t('cases.table.team_member')}</th>
                        <th scope="col">{t('cases.table.status')}</th>
                        <th scope="col">{t('cases.table.filing_date')}</th>
                        <th scope="col">{t('cases.show.review_actions')}</th>
                        <th scope="col">
                            <span className="sr-only">{t('cases.table.actions')}</span>
                        </th>
                    </tr>
                }
                footer={cases.last_page > 1 ? <Pagination meta={cases} /> : undefined}
            >
                {cases.data.length === 0 ? (
                    <tr>
                        <td colSpan={10} className="py-12">
                            <EmptyState icon={<FolderOpenIcon className="h-6 w-6" />} title={t('cases.table.no_cases_found')} />
                        </td>
                    </tr>
                ) : (
                    cases.data.map((row, index) => (
                        <tr key={row.id}>
                            <td className="text-(--text-subtle)">{(cases.from ?? 1) + index}</td>
                            <td className="font-semibold whitespace-nowrap">
                                <Link href={route('cases.show', row.id)} className="text-blue-700 hover:underline dark:text-blue-300">
                                    {row.caseNumber}
                                </Link>
                            </td>
                            <td className="min-w-48">
                                {row.applicantName && (
                                    <div className="mb-0.5 text-xs text-indigo-700 dark:text-indigo-300">
                                        {t('dashboard.submitted_by_lawyer', { name: row.applicantName })}
                                    </div>
                                )}
                                {row.title || '—'}
                            </td>
                            <td>{row.caseType ?? '—'}</td>
                            <td>{row.teamName ?? '—'}</td>
                            <td>{row.assigneeName ?? '—'}</td>
                            <td>
                                <CaseStatusBadge status={row.status} />
                            </td>
                            <td className="whitespace-nowrap">{row.filingDate}</td>
                            <td>
                                <ReviewStatusBadge status={row.reviewStatus} />
                                {row.reviewerName && (
                                    <div className="mt-1 text-xs text-(--text-subtle)">{t('cases.reviewed_by', { name: row.reviewerName })}</div>
                                )}
                            </td>
                            <td>
                                <LinkButton href={route('cases.show', row.id)} variant="secondary" size="sm">
                                    {t('cases.table.view')}
                                </LinkButton>
                            </td>
                        </tr>
                    ))
                )}
            </Table>
        </>
    );
}
