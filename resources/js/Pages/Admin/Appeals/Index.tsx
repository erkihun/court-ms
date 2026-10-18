import { ScaleIcon } from '@heroicons/react/24/outline';
import { Head } from '@inertiajs/react';
import { AppealStatusBadge } from '@/components/admin/appeals/AppealStatusBadge';
import { LinkButton } from '@/components/ui/Button';
import { EmptyState } from '@/components/ui/EmptyState';
import { Select } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { Pagination } from '@/components/ui/Pagination';
import { Table } from '@/components/ui/Table';
import { useCan } from '@/hooks/useCan';
import { useFilters } from '@/hooks/useFilters';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Paginated } from '@/types';

interface AppealRow {
    id: number;
    appealNumber: string;
    caseNumber: string;
    title: string;
    status: string;
    decidedBy: string | null;
    created: string;
}

interface Props {
    appeals: Paginated<AppealRow>;
    filters: { status: string };
    statuses: string[];
}

export default function AppealsIndex({ appeals, filters, statuses }: Props) {
    const can = useCan();
    const filter = useFilters(route('appeals.index'), filters);

    return (
        <>
            <Head title={t('appeals.title')} />
            <PageHeader
                title={t('appeals.title')}
                className="mb-6"
                actions={can('appeals.create') ? <LinkButton href={route('appeals.create')}>{t('appeals.new')}</LinkButton> : undefined}
            />

            <div className="ui-toolbar mb-6">
                <div className="flex items-center gap-3">
                    <label htmlFor="appeals-status" className="text-sm font-medium text-(--text-muted)">
                        {t('appeals.fields.status')}
                    </label>
                    <Select
                        id="appeals-status"
                        value={filter.values.status}
                        onChange={(event) => filter.change('status', event.target.value)}
                        className="min-w-44"
                    >
                        <option value="">{t('appeals.filters.all')}</option>
                        {statuses.map((status) => (
                            <option key={status} value={status}>
                                {t(`appeals.statuses.${status}`)}
                            </option>
                        ))}
                    </Select>
                </div>
            </div>

            <Table
                head={
                    <tr>
                        <th scope="col">{t('appeals.fields.appeal_number')}</th>
                        <th scope="col">{t('appeals.fields.case_number')}</th>
                        <th scope="col">{t('appeals.fields.title')}</th>
                        <th scope="col">{t('appeals.fields.status')}</th>
                        <th scope="col">{t('appeals.fields.decided_by')}</th>
                        <th scope="col">
                            <span className="sr-only">{t('ui.actions')}</span>
                        </th>
                    </tr>
                }
                footer={appeals.last_page > 1 ? <Pagination meta={appeals} /> : undefined}
            >
                {appeals.data.length === 0 ? (
                    <tr>
                        <td colSpan={6} className="py-12">
                            <EmptyState icon={<ScaleIcon className="h-6 w-6" />} title={t('appeals.empty')} />
                        </td>
                    </tr>
                ) : (
                    appeals.data.map((appeal) => (
                        <tr key={appeal.id}>
                            <td className="font-semibold whitespace-nowrap text-(--text)">{appeal.appealNumber}</td>
                            <td className="whitespace-nowrap">{appeal.caseNumber}</td>
                            <td>{appeal.title}</td>
                            <td className="whitespace-nowrap">
                                <AppealStatusBadge status={appeal.status} />
                            </td>
                            <td className="text-(--text-muted)">{appeal.decidedBy ?? '—'}</td>
                            <td className="text-end whitespace-nowrap">
                                <LinkButton href={route('appeals.show', appeal.id)} variant="secondary" size="sm">
                                    {t('appeals.actions.open')}
                                </LinkButton>
                            </td>
                        </tr>
                    ))
                )}
            </Table>
        </>
    );
}
