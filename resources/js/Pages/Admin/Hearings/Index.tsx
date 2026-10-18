import { CalendarDaysIcon } from '@heroicons/react/24/outline';
import { Head } from '@inertiajs/react';
import { type FormEvent, useState } from 'react';
import { Button } from '@/components/ui/Button';
import { DescriptionList } from '@/components/ui/DescriptionList';
import { EmptyState } from '@/components/ui/EmptyState';
import { EthDateInput } from '@/components/ui/EthDateInput';
import { Input } from '@/components/ui/Input';
import { Modal } from '@/components/ui/Modal';
import { PageHeader } from '@/components/ui/PageHeader';
import { Pagination } from '@/components/ui/Pagination';
import { Table } from '@/components/ui/Table';
import { useFilters } from '@/hooks/useFilters';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Paginated } from '@/types';

interface HearingRow {
    id: number;
    caseNumber: string | null;
    caseTitle: string | null;
    when: string | null;
    type: string | null;
    location: string | null;
    notes: string | null;
    creator: string | null;
}

interface Props {
    hearings: Paginated<HearingRow>;
    filters: { case_number: string; hearing_date: string };
}

export default function HearingsIndex({ hearings, filters }: Props) {
    const { values, set, apply, reset, active } = useFilters(route('admin.hearings.index'), filters);
    const [selected, setSelected] = useState<HearingRow | null>(null);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        apply();
    };

    return (
        <>
            <Head title={t('app.Hearings')} />
            <PageHeader
                title={t('app.Hearings')}
                description={t('cases.hearings.index_description')}
                actions={
                    <span className="text-xs font-semibold tracking-wide text-(--text-subtle) uppercase">
                        {hearings.total} {t('cases.hearings.total')}
                    </span>
                }
                className="mb-6"
            />

            <form onSubmit={submit} className="ui-toolbar mb-6 flex flex-col gap-3 sm:flex-row sm:items-center" role="search">
                <Input
                    aria-label={t('cases.hearings.filter_case_number')}
                    placeholder={t('cases.hearings.filter_case_number')}
                    value={values.case_number}
                    onChange={(event) => set('case_number', event.target.value)}
                    className="sm:flex-1"
                />
                <div className="sm:flex-1">
                    <EthDateInput value={values.hearing_date} onChange={(value) => set('hearing_date', value)} placeholder={t('cases.hearings.hearing_date')} />
                </div>
                <div className="flex gap-2">
                    <Button type="submit" variant="secondary">
                        {t('ui.search')}
                    </Button>
                    {active && (
                        <Button variant="ghost" onClick={reset}>
                            {t('cases.hearings.reset')}
                        </Button>
                    )}
                </div>
            </form>

            <Table
                head={
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{t('cases.hearings.case')}</th>
                        <th scope="col">{t('cases.hearings.date_time')}</th>
                        <th scope="col">{t('cases.hearings.name')}</th>
                        <th scope="col">{t('cases.hearings.created_by')}</th>
                        <th scope="col">
                            <span className="sr-only">{t('ui.actions')}</span>
                        </th>
                    </tr>
                }
                footer={hearings.last_page > 1 ? <Pagination meta={hearings} /> : undefined}
            >
                {hearings.data.length === 0 ? (
                    <tr>
                        <td colSpan={6} className="py-12">
                            <EmptyState icon={<CalendarDaysIcon className="h-6 w-6" />} title={t('cases.no_hearings_scheduled')} />
                        </td>
                    </tr>
                ) : (
                    hearings.data.map((hearing, index) => (
                        <tr key={hearing.id}>
                            <td className="text-(--text-subtle)">{(hearings.from ?? 1) + index}</td>
                            <td>
                                <div className="font-medium text-(--text)">{hearing.caseNumber ?? '—'}</div>
                                {hearing.caseTitle && <div className="text-xs text-(--text-subtle)">{hearing.caseTitle}</div>}
                            </td>
                            <td className="whitespace-nowrap">{hearing.when ?? '—'}</td>
                            <td>{hearing.type ?? t('cases.hearings.default_type')}</td>
                            <td className="whitespace-nowrap">{hearing.creator ?? '—'}</td>
                            <td className="text-end">
                                <Button variant="secondary" size="sm" onClick={() => setSelected(hearing)}>
                                    {t('cases.hearings.view')}
                                </Button>
                            </td>
                        </tr>
                    ))
                )}
            </Table>

            <Modal open={selected !== null} onClose={() => setSelected(null)} title={selected?.type ?? t('cases.hearings.details')}>
                {selected && (
                    <div className="space-y-4">
                        {selected.caseNumber && (
                            <p className="text-sm text-(--text-muted)">{t('cases.hearings.case_label', { number: selected.caseNumber })}</p>
                        )}
                        <DescriptionList
                            columns={1}
                            items={[
                                { label: t('cases.hearings.case_title'), value: selected.caseTitle },
                                { label: t('cases.hearings.scheduled_at'), value: selected.when },
                                { label: t('cases.hearings.location'), value: selected.location },
                                { label: t('cases.hearings.notes'), value: selected.notes ? <p className="whitespace-pre-line">{selected.notes}</p> : null },
                                { label: t('cases.hearings.created_by'), value: selected.creator },
                            ]}
                        />
                    </div>
                )}
            </Modal>
        </>
    );
}
