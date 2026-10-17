import { TagIcon } from '@heroicons/react/24/outline';
import { Head, router } from '@inertiajs/react';
import { type FormEvent, useState } from 'react';
import { Badge } from '@/components/ui/Badge';
import { Button, LinkButton } from '@/components/ui/Button';
import { ConfirmDialog } from '@/components/ui/ConfirmDialog';
import { EmptyState } from '@/components/ui/EmptyState';
import { Input } from '@/components/ui/Input';
import { Pagination } from '@/components/ui/Pagination';
import { Table } from '@/components/ui/Table';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Paginated } from '@/types';

interface CaseType {
    id: number;
    name: string;
    prefix: string | null;
    casesCount: number;
}

interface Props {
    types: Paginated<CaseType>;
    filters: { q: string };
}

export default function CaseTypesIndex({ types, filters }: Props) {
    const [query, setQuery] = useState(filters.q);
    const [deleting, setDeleting] = useState<CaseType | null>(null);
    const [processing, setProcessing] = useState(false);

    const search = (event: FormEvent) => {
        event.preventDefault();
        router.get(route('case-types.index'), query ? { q: query } : {}, { preserveState: true, replace: true });
    };

    const destroy = () => {
        if (!deleting) return;
        router.delete(route('case-types.delete', deleting.id), {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setDeleting(null);
            },
        });
    };

    return (
        <>
            <Head title={t('app.case_types')} />

            <div className="ui-toolbar mb-6">
                <div className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <form onSubmit={search} className="flex items-center gap-3" role="search">
                        <label htmlFor="search-q" className="sr-only">
                            {t('Search')}
                        </label>
                        <Input id="search-q" value={query} onChange={(event) => setQuery(event.target.value)} placeholder={t('Search name…')} className="grow" />
                        <Button type="submit" variant="secondary">
                            {t('Search')}
                        </Button>
                    </form>
                    <LinkButton href={route('case-types.create')}>{t('Add Case Type')}</LinkButton>
                </div>
            </div>

            <Table
                head={
                    <tr>
                        <th scope="col">{t('Name')}</th>
                        <th scope="col">{t('Prefix')}</th>
                        <th scope="col">{t('Cases Using')}</th>
                        <th scope="col">
                            <span className="sr-only">{t('ui.actions')}</span>
                        </th>
                    </tr>
                }
                footer={types.last_page > 1 ? <Pagination meta={types} /> : undefined}
            >
                {types.data.length === 0 ? (
                    <tr>
                        <td colSpan={4} className="py-12">
                            <EmptyState icon={<TagIcon className="h-6 w-6" />} title={t('cases.types.empty')} description={t('cases.types.adjust_search_hint')} />
                        </td>
                    </tr>
                ) : (
                    types.data.map((type) => (
                        <tr key={type.id}>
                            <td className="font-medium whitespace-nowrap text-(--text)">{type.name}</td>
                            <td className="font-mono text-sm whitespace-nowrap">{type.prefix ?? '—'}</td>
                            <td className="whitespace-nowrap">
                                <Badge tone="info">{type.casesCount}</Badge>
                            </td>
                            <td className="whitespace-nowrap">
                                <div className="flex justify-end gap-2">
                                    <LinkButton href={route('case-types.edit', type.id)} variant="secondary" size="sm">
                                        {t('Edit')}
                                    </LinkButton>
                                    <Button variant="danger" size="sm" onClick={() => setDeleting(type)}>
                                        {t('Delete')}
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    ))
                )}
            </Table>

            <ConfirmDialog
                open={deleting !== null}
                title={t('Delete this case type?')}
                message={deleting?.name}
                confirmLabel={t('Delete')}
                processing={processing}
                onCancel={() => setDeleting(null)}
                onConfirm={destroy}
            />
        </>
    );
}
