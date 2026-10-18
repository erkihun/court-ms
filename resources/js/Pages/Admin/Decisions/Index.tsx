import { DocumentCheckIcon } from '@heroicons/react/24/outline';
import { Head } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { ApprovalBadge, DecisionStatusBadge } from '@/components/admin/decisions/DecisionBadges';
import { Badge } from '@/components/ui/Badge';
import { Button, LinkButton } from '@/components/ui/Button';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { EmptyState } from '@/components/ui/EmptyState';
import { Input } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { Pagination } from '@/components/ui/Pagination';
import { Table } from '@/components/ui/Table';
import { useCan } from '@/hooks/useCan';
import { useFilters } from '@/hooks/useFilters';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Paginated } from '@/types';

interface DecisionRow {
    id: number;
    rowNumber: number;
    caseId: number | null;
    caseNumber: string | null;
    filed: string | null;
    applicant: string | null;
    respondent: string | null;
    judge: string | null;
    decisionDate: string;
    status: string | null;
    isApproved: boolean;
    canEdit: boolean;
    canDelete: boolean;
}

interface Props {
    decisions: Paginated<DecisionRow>;
    filters: { q: string };
}

export default function DecisionsIndex({ decisions, filters }: Props) {
    const can = useCan();
    const filter = useFilters(route('decisions.index'), filters);

    const search = (event: FormEvent) => {
        event.preventDefault();
        filter.apply();
    };

    return (
        <>
            <Head title={t('decisions.index.title')} />
            <PageHeader
                title={t('decisions.index.title')}
                className="mb-6"
                actions={can('decision.create') ? <LinkButton href={route('decisions.create')}>{t('decisions.index.new')}</LinkButton> : undefined}
            />

            <div className="ui-toolbar mb-6">
                <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div className="flex flex-wrap items-center gap-2 text-sm text-(--text-muted)">
                        {t('decisions.index.showing')}
                        <Badge tone="neutral">
                            {decisions.total.toLocaleString()} {t('decisions.index.total')}
                        </Badge>
                    </div>
                    <form onSubmit={search} className="flex items-center gap-2" role="search">
                        <label htmlFor="decisions-q" className="sr-only">
                            {t('app.Search')}
                        </label>
                        <Input
                            id="decisions-q"
                            value={filter.values.q}
                            onChange={(event) => filter.set('q', event.target.value)}
                            placeholder={t('decisions.index.search_placeholder')}
                        />
                        <Button type="submit" variant="secondary">
                            {t('app.Search')}
                        </Button>
                        {filters.q !== '' && (
                            <Button variant="ghost" onClick={filter.reset}>
                                {t('app.Reset')}
                            </Button>
                        )}
                    </form>
                </div>
            </div>

            <Table
                head={
                    <tr>
                        <th scope="col" className="w-12">
                            {t('decisions.index.no')}
                        </th>
                        <th scope="col">{t('decisions.index.case_number')}</th>
                        <th scope="col">{t('decisions.index.parties')}</th>
                        <th scope="col">{t('decisions.index.judge')}</th>
                        <th scope="col">{t('decisions.index.decision_date')}</th>
                        <th scope="col">{t('app.Status')}</th>
                        <th scope="col">{t('decisions.index.approval_status')}</th>
                        <th scope="col">{t('decisions.index.actions')}</th>
                    </tr>
                }
                footer={decisions.last_page > 1 ? <Pagination meta={decisions} /> : undefined}
            >
                {decisions.data.length === 0 ? (
                    <tr>
                        <td colSpan={8} className="py-12">
                            <EmptyState icon={<DocumentCheckIcon className="h-6 w-6" />} title={t('decisions.index.empty')} />
                        </td>
                    </tr>
                ) : (
                    decisions.data.map((decision) => (
                        <tr key={decision.id} className="align-top">
                            <td className="text-(--text-subtle)">{decision.rowNumber}</td>
                            <td className="whitespace-nowrap">
                                {decision.caseId ? (
                                    <a
                                        href={route('cases.show', decision.caseId)}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="font-semibold text-blue-600 hover:underline dark:text-blue-400"
                                    >
                                        {decision.caseNumber}
                                    </a>
                                ) : (
                                    <span className="font-semibold text-(--text)">{decision.caseNumber ?? '—'}</span>
                                )}
                                {decision.filed && (
                                    <div className="mt-1 text-[11px] text-(--text-subtle)">{t('decisions.index.filed', { date: decision.filed })}</div>
                                )}
                            </td>
                            <td className="min-w-48 text-sm">
                                <Party label={t('decisions.index.applicant')} name={decision.applicant} />
                                <Party label={t('decisions.index.respondent')} name={decision.respondent} className="mt-2" />
                            </td>
                            <td>{decision.judge ?? '—'}</td>
                            <td className="whitespace-nowrap">{decision.decisionDate}</td>
                            <td>
                                <DecisionStatusBadge status={decision.status} />
                            </td>
                            <td>
                                <ApprovalBadge approved={decision.isApproved} />
                            </td>
                            <td className="whitespace-nowrap">
                                <div className="flex items-center gap-2">
                                    <LinkButton href={route('decisions.show', decision.id)} variant="secondary" size="sm">
                                        {t('decisions.index.view')}
                                    </LinkButton>
                                    {decision.canEdit && (
                                        <LinkButton href={route('decisions.edit', decision.id)} size="sm">
                                            {t('decisions.index.edit')}
                                        </LinkButton>
                                    )}
                                    {decision.canDelete && (
                                        <ConfirmButton
                                            url={route('decisions.destroy', decision.id)}
                                            title={t('decisions.index.delete')}
                                            message={decision.caseNumber ?? undefined}
                                        >
                                            {t('decisions.index.delete')}
                                        </ConfirmButton>
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

function Party({ label, name, className }: { label: string; name: string | null; className?: string }) {
    return (
        <div className={className}>
            <div className="text-[10px] tracking-wide text-(--text-subtle) uppercase">{label}</div>
            <div className="text-(--text)">{name ?? '—'}</div>
        </div>
    );
}
