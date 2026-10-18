import { ClipboardDocumentCheckIcon } from '@heroicons/react/24/outline';
import { Head } from '@inertiajs/react';
import { InspectionStatusBadge, type Option } from '@/components/admin/inspections/InspectionBadges';
import { LinkButton } from '@/components/ui/Button';
import { EmptyState } from '@/components/ui/EmptyState';
import { Select } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { Pagination } from '@/components/ui/Pagination';
import { Table } from '@/components/ui/Table';
import { useFilters } from '@/hooks/useFilters';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Paginated } from '@/types';

export interface InspectionRequestRow {
    id: number;
    caseNumber: string | null;
    subject: string;
    date: string;
    status: string;
    inspector: string | null;
    createdBy: string | null;
    locked: boolean;
}

interface Props {
    requests: Paginated<InspectionRequestRow>;
    cases: Option[];
    filters: { case_id: string; status: string };
    statuses: string[];
}

export default function InspectionRequestsIndex({ requests, cases, filters, statuses }: Props) {
    const { values, change } = useFilters(route('case-inspection-requests.index'), filters);
    const none = t('case_inspections.common.no_data');

    return (
        <>
            <Head title={t('case_inspections.requests.index_title')} />
            <PageHeader
                title={t('case_inspections.requests.index_title')}
                description={t('case_inspections.requests.index_description')}
                actions={<LinkButton href={route('case-inspection-requests.create')}>{t('case_inspections.requests.new')}</LinkButton>}
                className="mb-6"
            />

            <div className="ui-toolbar mb-6 grid gap-3 md:grid-cols-2" role="search">
                <Select
                    aria-label={t('case_inspections.requests.table_case')}
                    value={values.case_id}
                    onChange={(event) => change('case_id', event.target.value)}
                >
                    <option value="">{t('case_inspections.requests.all_cases')}</option>
                    {cases.map((option) => (
                        <option key={option.id} value={String(option.id)}>
                            {option.label}
                        </option>
                    ))}
                </Select>
                <Select
                    aria-label={t('case_inspections.requests.table_status')}
                    value={values.status}
                    onChange={(event) => change('status', event.target.value)}
                >
                    <option value="">{t('case_inspections.requests.all_statuses')}</option>
                    {statuses.map((status) => (
                        <option key={status} value={status}>
                            {t(`case_inspections.status.${status}`)}
                        </option>
                    ))}
                </Select>
            </div>

            <Table
                head={
                    <tr>
                        <th scope="col">{t('case_inspections.requests.table_case')}</th>
                        <th scope="col">{t('case_inspections.requests.table_subject')}</th>
                        <th scope="col">{t('case_inspections.requests.table_date')}</th>
                        <th scope="col">{t('case_inspections.requests.table_status')}</th>
                        <th scope="col">{t('case_inspections.requests.table_inspector')}</th>
                        <th scope="col">{t('case_inspections.requests.table_created_by')}</th>
                        <th scope="col">
                            <span className="sr-only">{t('case_inspections.common.actions')}</span>
                        </th>
                    </tr>
                }
                footer={requests.last_page > 1 ? <Pagination meta={requests} /> : undefined}
            >
                {requests.data.length === 0 ? (
                    <tr>
                        <td colSpan={7} className="py-12">
                            <EmptyState icon={<ClipboardDocumentCheckIcon className="h-6 w-6" />} title={t('case_inspections.requests.empty')} />
                        </td>
                    </tr>
                ) : (
                    requests.data.map((row) => (
                        <tr key={row.id}>
                            <td className="font-medium whitespace-nowrap text-(--text)">{row.caseNumber ?? none}</td>
                            <td>{row.subject}</td>
                            <td className="whitespace-nowrap">{row.date}</td>
                            <td className="whitespace-nowrap">
                                <InspectionStatusBadge status={row.status} />
                            </td>
                            <td className="whitespace-nowrap">{row.inspector ?? none}</td>
                            <td className="whitespace-nowrap">{row.createdBy ?? none}</td>
                            <td className="whitespace-nowrap">
                                <div className="flex justify-end gap-2">
                                    {!row.locked && (
                                        <LinkButton href={route('case-inspection-requests.edit', row.id)} variant="secondary" size="sm">
                                            {t('case_inspections.common.edit')}
                                        </LinkButton>
                                    )}
                                    <LinkButton href={route('case-inspection-requests.show', row.id)} variant="secondary" size="sm">
                                        {t('case_inspections.common.view')}
                                    </LinkButton>
                                </div>
                            </td>
                        </tr>
                    ))
                )}
            </Table>
        </>
    );
}
