import { FolderIcon } from '@heroicons/react/24/outline';
import { Head } from '@inertiajs/react';
import { LinkButton } from '@/components/ui/Button';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { EmptyState } from '@/components/ui/EmptyState';
import { PageHeader } from '@/components/ui/PageHeader';
import { Pagination } from '@/components/ui/Pagination';
import { Table } from '@/components/ui/Table';
import { useCan } from '@/hooks/useCan';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Paginated } from '@/types';
import type { LetterCategory } from '@/types/admin/letters';

interface Props {
    categories: Paginated<LetterCategory>;
}

export default function LetterCategoriesIndex({ categories }: Props) {
    const can = useCan();
    const canUpdate = can('letters.templet.update');
    const canDelete = can('letters.templet.delete');

    return (
        <>
            <Head title={t('letters.categories.title')} />
            <PageHeader
                title={t('letters.categories.title')}
                description={t('letters.categories.description')}
                className="mb-6"
                actions={
                    <>
                        <LinkButton href={route('letter-templates.index')} variant="secondary">
                            {t('letters.categories.back_to_templates')}
                        </LinkButton>
                        {can('letters.templet.create') && <LinkButton href={route('letter-categories.create')}>{t('letters.categories.new')}</LinkButton>}
                    </>
                }
            />

            <Table
                head={
                    <tr>
                        <th scope="col">{t('letters.categories.table.name')}</th>
                        <th scope="col">{t('letters.categories.table.description')}</th>
                        <th scope="col">
                            <span className="sr-only">{t('letters.categories.table.actions')}</span>
                        </th>
                    </tr>
                }
                footer={categories.last_page > 1 ? <Pagination meta={categories} /> : undefined}
            >
                {categories.data.length === 0 ? (
                    <tr>
                        <td colSpan={3} className="py-12">
                            <EmptyState icon={<FolderIcon className="h-6 w-6" />} title={t('letters.categories.empty')} />
                        </td>
                    </tr>
                ) : (
                    categories.data.map((category) => (
                        <tr key={category.id}>
                            <td className="font-medium whitespace-nowrap text-(--text)">{category.name}</td>
                            <td className="text-(--text-muted)">{category.description ?? '—'}</td>
                            <td className="whitespace-nowrap">
                                <div className="flex justify-end gap-2">
                                    {canUpdate && (
                                        <LinkButton href={route('letter-categories.edit', category.id)} variant="secondary" size="sm">
                                            {t('letters.actions.edit')}
                                        </LinkButton>
                                    )}
                                    {canDelete && (
                                        <ConfirmButton
                                            url={route('letter-categories.destroy', category.id)}
                                            title={t('letters.categories.confirm_delete')}
                                            message={category.name}
                                        >
                                            {t('letters.actions.delete')}
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
