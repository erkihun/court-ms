import { IdentificationIcon } from '@heroicons/react/24/outline';
import { Head, router } from '@inertiajs/react';
import { type FormEvent, useState } from 'react';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { EmptyState } from '@/components/ui/EmptyState';
import { Input, Select } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { Pagination } from '@/components/ui/Pagination';
import { Table } from '@/components/ui/Table';
import { useFilters } from '@/hooks/useFilters';
import { formatNationalId } from '@/lib/format';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Paginated } from '@/types';

interface ApplicantRow {
    id: number;
    name: string;
    nationalId: string | null;
    email: string;
    phone: string | null;
    position: string | null;
    organization: string | null;
    active: boolean;
    verified: boolean;
    created: string;
}

interface Props {
    applicants: Paginated<ApplicantRow>;
    filters: { q: string; status: '' | 'active' | 'inactive' };
    canManage: boolean;
}

export default function ApplicantsIndex({ applicants, filters, canManage }: Props) {
    const { values, set, change, apply, reset, active } = useFilters(route('applicants.index'), filters);
    const [updating, setUpdating] = useState<number | null>(null);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        apply();
    };

    const setActive = (applicant: ApplicantRow, isActive: boolean) => {
        router.patch(
            route('applicants.status.update', applicant.id),
            { is_active: isActive },
            { preserveScroll: true, onStart: () => setUpdating(applicant.id), onFinish: () => setUpdating(null) },
        );
    };

    return (
        <>
            <Head title={t('applicants.title')} />
            <PageHeader title={t('applicants.page_header')} className="mb-6" />

            <form onSubmit={submit} role="search" className="ui-toolbar mb-6 flex flex-col gap-3 md:flex-row md:items-center">
                <label htmlFor="applicants-q" className="sr-only">
                    {t('applicants.search_placeholder')}
                </label>
                <Input
                    id="applicants-q"
                    value={values.q}
                    onChange={(event) => set('q', event.target.value)}
                    placeholder={t('applicants.search_placeholder')}
                    className="md:w-80"
                />
                <label htmlFor="applicants-status" className="sr-only">
                    {t('applicants.table.status')}
                </label>
                <Select
                    id="applicants-status"
                    value={values.status}
                    onChange={(event) => change('status', event.target.value as Props['filters']['status'])}
                    className="md:w-44"
                >
                    <option value="">{t('applicants.filters.all_statuses')}</option>
                    <option value="active">{t('app.Active')}</option>
                    <option value="inactive">{t('app.Inactive')}</option>
                </Select>
                <div className="flex gap-2">
                    <Button type="submit">{t('applicants.filters.filter')}</Button>
                    {active && (
                        <Button variant="secondary" onClick={reset}>
                            {t('applicants.filters.reset')}
                        </Button>
                    )}
                </div>
            </form>

            <Table
                head={
                    <tr>
                        <th scope="col">{t('applicants.table.applicant')}</th>
                        <th scope="col">{t('applicants.table.email')}</th>
                        <th scope="col">{t('applicants.table.phone')}</th>
                        <th scope="col">{t('applicants.table.organization')}</th>
                        <th scope="col">{t('applicants.table.status')}</th>
                        <th scope="col">{t('applicants.table.verified')}</th>
                        <th scope="col">{t('applicants.table.created')}</th>
                        {canManage && <th scope="col">{t('applicants.table.actions')}</th>}
                    </tr>
                }
                footer={applicants.last_page > 1 ? <Pagination meta={applicants} /> : undefined}
            >
                {applicants.data.length === 0 ? (
                    <tr>
                        <td colSpan={canManage ? 8 : 7} className="py-12">
                            <EmptyState icon={<IdentificationIcon className="h-6 w-6" />} title={t('applicants.empty')} />
                        </td>
                    </tr>
                ) : (
                    applicants.data.map((applicant) => (
                        <tr key={applicant.id}>
                            <td>
                                <div className="font-medium text-(--text)">{applicant.name}</div>
                                {applicant.nationalId && <div className="font-mono text-xs text-(--text-subtle)">{formatNationalId(applicant.nationalId)}</div>}
                            </td>
                            <td className="text-(--text-muted)">{applicant.email}</td>
                            <td className="whitespace-nowrap text-(--text-muted)">{applicant.phone ?? '—'}</td>
                            <td>
                                <div className="text-(--text)">{applicant.position ?? '—'}</div>
                                <div className="text-xs text-(--text-subtle)">{applicant.organization ?? '—'}</div>
                            </td>
                            <td>
                                <Badge tone={applicant.active ? 'success' : 'warning'}>{applicant.active ? t('app.Active') : t('app.Inactive')}</Badge>
                            </td>
                            <td>
                                <Badge tone={applicant.verified ? 'success' : 'warning'}>
                                    {applicant.verified ? t('applicants.verified.verified') : t('applicants.verified.unverified')}
                                </Badge>
                            </td>
                            <td className="whitespace-nowrap text-(--text-muted)">{applicant.created}</td>
                            {canManage && (
                                <td>
                                    {applicant.active ? (
                                        <Button variant="danger" size="sm" loading={updating === applicant.id} onClick={() => setActive(applicant, false)}>
                                            {t('app.Deactivate')}
                                        </Button>
                                    ) : (
                                        <Button variant="success" size="sm" loading={updating === applicant.id} onClick={() => setActive(applicant, true)}>
                                            {t('app.Activate')}
                                        </Button>
                                    )}
                                </td>
                            )}
                        </tr>
                    ))
                )}
            </Table>
        </>
    );
}
