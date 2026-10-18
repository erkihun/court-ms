import { InformationCircleIcon } from '@heroicons/react/24/outline';
import { Head, Link } from '@inertiajs/react';
import { PublishedBadge } from '@/components/admin/content/PublishedBadge';
import { LinkButton } from '@/components/ui/Button';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { EmptyState } from '@/components/ui/EmptyState';
import { PageHeader } from '@/components/ui/PageHeader';
import { Pagination } from '@/components/ui/Pagination';
import { Table } from '@/components/ui/Table';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Paginated } from '@/types';

interface AboutRow {
    id: number;
    title: string;
    slug: string;
    isPublished: boolean;
    updated: string;
}

export default function AboutIndex({ pages }: { pages: Paginated<AboutRow> }) {
    const createButton = <LinkButton href={route('about.create')}>{t('about.create_new')}</LinkButton>;

    return (
        <>
            <Head title={t('about.title')} />
            <PageHeader title={t('about.title')} description={t('about.manage_description')} actions={createButton} className="mb-6" />

            {pages.data.length === 0 ? (
                <EmptyState icon={<InformationCircleIcon className="h-6 w-6" />} title={t('about.empty_state')} description={t('about.empty_state_help')}>
                    {createButton}
                </EmptyState>
            ) : (
                <Table
                    head={
                        <tr>
                            <th scope="col">{t('about.table_title')}</th>
                            <th scope="col">{t('about.table_status')}</th>
                            <th scope="col">{t('about.table_updated')}</th>
                            <th scope="col">
                                <span className="sr-only">{t('about.table_actions')}</span>
                            </th>
                        </tr>
                    }
                    footer={pages.last_page > 1 ? <Pagination meta={pages} /> : undefined}
                >
                    {pages.data.map((about) => (
                        <tr key={about.id}>
                            <td>
                                <Link href={route('about.show', about.id)} className="font-semibold text-(--text) hover:underline">
                                    {about.title}
                                </Link>
                                <div className="font-mono text-xs text-(--text-subtle)">/{about.slug}</div>
                            </td>
                            <td>
                                <PublishedBadge published={about.isPublished} />
                            </td>
                            <td className="whitespace-nowrap">{about.updated}</td>
                            <td className="whitespace-nowrap">
                                <div className="flex justify-end gap-2">
                                    <LinkButton href={route('about.show', about.id)} variant="ghost" size="sm">
                                        {t('about.action_view')}
                                    </LinkButton>
                                    <LinkButton href={route('about.edit', about.id)} variant="secondary" size="sm">
                                        {t('about.action_edit')}
                                    </LinkButton>
                                    <ConfirmButton url={route('about.destroy', about.id)} title={t('about.confirm_delete')} message={about.title}>
                                        {t('about.action_delete')}
                                    </ConfirmButton>
                                </div>
                            </td>
                        </tr>
                    ))}
                </Table>
            )}
        </>
    );
}
