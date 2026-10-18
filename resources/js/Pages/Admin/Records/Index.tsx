import { FolderOpenIcon } from '@heroicons/react/24/outline';
import { Head, Link } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { CaseStatusPill, type ReviewStatus, ReviewStatusPill } from '@/components/party/StatusPill';
import { Button, LinkButton } from '@/components/ui/Button';
import { EmptyState } from '@/components/ui/EmptyState';
import { EthDateInput } from '@/components/ui/EthDateInput';
import { Field } from '@/components/ui/Field';
import { Input, Select } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { Pagination } from '@/components/ui/Pagination';
import { Table } from '@/components/ui/Table';
import { useFilters } from '@/hooks/useFilters';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Paginated } from '@/types';

interface RecordRow {
    id: number;
    caseNumber: string;
    title: string;
    applicant: string | null;
    caseType: string | null;
    team: string | null;
    assignee: string | null;
    status: string;
    filed: string;
    reviewStatus: string;
    reviewer: string | null;
    canAppealPdf: boolean;
}

interface Props {
    cases: Paginated<RecordRow>;
    filters: { q: string; status: string; case_type_id: string; assignee_id: string; from: string; to: string };
    types: Array<{ id: number; name: string }>;
    users: Array<{ id: number; name: string }>;
    statuses: string[];
}

const reviewStatuses: ReviewStatus[] = ['awaiting_review', 'accepted', 'returned', 'rejected'];

export default function RecordsIndex({ cases, filters, types, users, statuses }: Props) {
    const { values, set, change, apply, reset, active } = useFilters(route('recordes.index'), filters);

    const search = (event: FormEvent) => {
        event.preventDefault();
        apply();
    };

    return (
        <>
            <Head title={t('recordes.titles.index')} />
            <PageHeader
                title={t('recordes.titles.index')}
                actions={
                    <LinkButton href={route('cases.index')} variant="secondary">
                        {t('recordes.buttons.back_to_cases')}
                    </LinkButton>
                }
                className="mb-6"
            />

            <form onSubmit={search} className="ui-toolbar mb-4 flex flex-col gap-3 sm:flex-row" role="search">
                <Input
                    aria-label={t('cases.search')}
                    value={values.q}
                    onChange={(event) => set('q', event.target.value)}
                    placeholder={t('cases.search_placeholder')}
                    className="sm:max-w-md"
                />
                <Button type="submit">{t('cases.search')}</Button>
            </form>

            <div className="ui-card mb-6 grid grid-cols-1 gap-4 p-4 md:grid-cols-2 lg:grid-cols-5">
                <Field label={t('cases.table.status')}>
                    <Select value={values.status} onChange={(event) => change('status', event.target.value)}>
                        <option value="">{t('cases.filters.all_statuses')}</option>
                        {statuses.map((status) => (
                            <option key={status} value={status}>
                                {t(`cases.status.${status}`)}
                            </option>
                        ))}
                    </Select>
                </Field>
                <Field label={t('cases.table.type')}>
                    <Select value={values.case_type_id} onChange={(event) => change('case_type_id', event.target.value)}>
                        <option value="">{t('cases.filters.all_types')}</option>
                        {types.map((type) => (
                            <option key={type.id} value={String(type.id)}>
                                {type.name}
                            </option>
                        ))}
                    </Select>
                </Field>
                <Field label={t('cases.table.assignee')}>
                    <Select value={values.assignee_id} onChange={(event) => change('assignee_id', event.target.value)}>
                        <option value="">{t('cases.filters.all_assignees')}</option>
                        {users.map((user) => (
                            <option key={user.id} value={String(user.id)}>
                                {user.name}
                            </option>
                        ))}
                    </Select>
                </Field>
                <Field label={t('cases.filters.from')}>
                    <EthDateInput value={values.from} onChange={(value) => change('from', value)} />
                </Field>
                <Field label={t('cases.filters.to')}>
                    <EthDateInput value={values.to} onChange={(value) => change('to', value)} align="end" />
                </Field>
                <div className="flex flex-wrap items-center justify-between gap-3 md:col-span-2 lg:col-span-5">
                    <span className="text-sm text-(--text-muted)">
                        {cases.total > 0
                            ? t('cases.showing_results', { first: cases.from, last: cases.to, total: cases.total })
                            : t('recordes.messages.no_cases')}
                    </span>
                    {active && (
                        <Button variant="ghost" onClick={reset}>
                            {t('cases.reset')}
                        </Button>
                    )}
                </div>
            </div>

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
                        <th scope="col">{t('recordes.labels.title')}</th>
                        <th scope="col">{t('cases.table.actions')}</th>
                    </tr>
                }
                footer={cases.last_page > 1 ? <Pagination meta={cases} /> : undefined}
            >
                {cases.data.length === 0 ? (
                    <tr>
                        <td colSpan={11} className="py-12">
                            <EmptyState icon={<FolderOpenIcon className="h-6 w-6" />} title={t('recordes.messages.no_cases')} />
                        </td>
                    </tr>
                ) : (
                    cases.data.map((row, index) => (
                        <tr key={row.id}>
                            <td className="text-(--text-subtle)">{(cases.from ?? 1) + index}</td>
                            <td className="font-semibold whitespace-nowrap">
                                <Link href={route('recordes.show', row.id)} className="text-blue-700 hover:underline dark:text-blue-300">
                                    {row.caseNumber}
                                </Link>
                            </td>
                            <td>{row.applicant ?? '-'}</td>
                            <td>{row.caseType ?? '-'}</td>
                            <td>{row.team ?? '-'}</td>
                            <td>{row.assignee ?? '-'}</td>
                            <td className="whitespace-nowrap">
                                <CaseStatusPill status={row.status} withIcon={false} />
                            </td>
                            <td className="whitespace-nowrap">{row.filed}</td>
                            <td>
                                {reviewStatuses.includes(row.reviewStatus as ReviewStatus) ? (
                                    <ReviewStatusPill status={row.reviewStatus as ReviewStatus} />
                                ) : (
                                    t(`cases.review_status.${row.reviewStatus}`)
                                )}
                                {row.reviewer && <div className="mt-1 text-xs text-(--text-subtle)">{t('cases.reviewed_by', { name: row.reviewer })}</div>}
                            </td>
                            <td>{row.title || '-'}</td>
                            <td>
                                <div className="flex items-center gap-2">
                                    <LinkButton href={route('recordes.show', row.id)} variant="secondary" size="sm">
                                        {t('recordes.buttons.view')}
                                    </LinkButton>
                                    <LinkButton href={route('recordes.pdf', row.id)} variant="secondary" size="sm" external target="_blank">
                                        {t('recordes.buttons.pdf')}
                                    </LinkButton>
                                    {row.canAppealPdf && (
                                        <LinkButton href={route('recordes.appeal-pdf', row.id)} size="sm" external>
                                            {t('recordes.buttons.appeal_pdf')}
                                        </LinkButton>
                                    )}
                                </div>
                            </td>
                        </tr>
                    ))
                )}
            </Table>
        </>
    );
}
