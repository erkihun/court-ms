import { DocumentMagnifyingGlassIcon } from '@heroicons/react/24/outline';
import { Head } from '@inertiajs/react';
import { type Option, SeverityBadge } from '@/components/admin/inspections/InspectionBadges';
import { Badge } from '@/components/ui/Badge';
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

export interface FindingRow {
    id: number;
    caseNumber: string | null;
    requestSubject: string | null;
    title: string;
    date: string;
    severity: string;
    accepted: boolean;
    attachmentName: string | null;
    attachmentUrl: string | null;
}

interface Props {
    findings: Paginated<FindingRow>;
    requests: Option[];
    filters: { request_id: string; severity: string; case_id: string };
    severities: string[];
}

export default function InspectionFindingsIndex({ findings, requests, filters, severities }: Props) {
    const { values, change } = useFilters(route('case-inspection-findings.index'), filters);
    const none = t('case_inspections.common.no_data');

    return (
        <>
            <Head title={t('case_inspections.findings.index_title')} />
            <PageHeader
                title={t('case_inspections.findings.index_title')}
                description={t('case_inspections.findings.index_description')}
                actions={<LinkButton href={route('case-inspection-findings.create')}>{t('case_inspections.findings.new')}</LinkButton>}
                className="mb-6"
            />

            <div className="ui-toolbar mb-6 grid gap-3 md:grid-cols-2" role="search">
                <Select
                    aria-label={t('case_inspections.findings.table_request')}
                    value={values.request_id}
                    onChange={(event) => change('request_id', event.target.value)}
                >
                    <option value="">{t('case_inspections.findings.all_requests')}</option>
                    {requests.map((option) => (
                        <option key={option.id} value={String(option.id)}>
                            {option.label}
                        </option>
                    ))}
                </Select>
                <Select
                    aria-label={t('case_inspections.findings.table_severity')}
                    value={values.severity}
                    onChange={(event) => change('severity', event.target.value)}
                >
                    <option value="">{t('case_inspections.findings.all_severities')}</option>
                    {severities.map((severity) => (
                        <option key={severity} value={severity}>
                            {t(`case_inspections.severity.${severity}`)}
                        </option>
                    ))}
                </Select>
            </div>

            <Table
                head={
                    <tr>
                        <th scope="col">{t('case_inspections.findings.table_case')}</th>
                        <th scope="col">{t('case_inspections.findings.table_request')}</th>
                        <th scope="col">{t('case_inspections.findings.table_title')}</th>
                        <th scope="col">{t('case_inspections.findings.table_date')}</th>
                        <th scope="col">{t('case_inspections.findings.table_severity')}</th>
                        <th scope="col">
                            <span className="sr-only">{t('case_inspections.common.actions')}</span>
                        </th>
                    </tr>
                }
                footer={findings.last_page > 1 ? <Pagination meta={findings} /> : undefined}
            >
                {findings.data.length === 0 ? (
                    <tr>
                        <td colSpan={6} className="py-12">
                            <EmptyState icon={<DocumentMagnifyingGlassIcon className="h-6 w-6" />} title={t('case_inspections.findings.empty')} />
                        </td>
                    </tr>
                ) : (
                    findings.data.map((row) => (
                        <tr key={row.id}>
                            <td className="font-medium whitespace-nowrap text-(--text)">{row.caseNumber ?? none}</td>
                            <td>{row.requestSubject ?? none}</td>
                            <td>
                                <div className="flex flex-wrap items-center gap-2">
                                    {row.title}
                                    {row.accepted && <Badge tone="success">{t('case_inspections.findings.accepted')}</Badge>}
                                </div>
                                {row.attachmentUrl && (
                                    <a
                                        href={row.attachmentUrl}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="mt-1 inline-block text-xs text-blue-700 underline hover:text-blue-800 dark:text-blue-300"
                                    >
                                        {t('case_inspections.findings.labels.download_attachment')}
                                    </a>
                                )}
                            </td>
                            <td className="whitespace-nowrap">{row.date}</td>
                            <td className="whitespace-nowrap">
                                <SeverityBadge severity={row.severity} />
                            </td>
                            <td className="whitespace-nowrap">
                                <div className="flex justify-end gap-2">
                                    {!row.accepted && (
                                        <LinkButton href={route('case-inspection-findings.edit', row.id)} variant="secondary" size="sm">
                                            {t('case_inspections.common.edit')}
                                        </LinkButton>
                                    )}
                                    <LinkButton href={route('case-inspection-findings.show', row.id)} variant="secondary" size="sm">
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
