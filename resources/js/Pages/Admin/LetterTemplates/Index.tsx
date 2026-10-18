import { DocumentTextIcon } from '@heroicons/react/24/outline';
import { Head } from '@inertiajs/react';
import { PlaceholderSummary } from '@/components/admin/templates/TemplateFormParts';
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
import type { LetterTemplateRow } from '@/types/admin/letters';

interface Props {
    templates: Paginated<LetterTemplateRow>;
}

export default function LetterTemplatesIndex({ templates }: Props) {
    const can = useCan();
    const canUpdate = can('letters.templet.update');
    const canDelete = can('letters.templet.delete');

    return (
        <>
            <Head title={t('letters.templates.title')} />
            <PageHeader
                title={t('letters.templates.title')}
                description={t('letters.templates.description')}
                className="mb-6"
                actions={
                    <>
                        {can('letters.templet.view') && (
                            <LinkButton href={route('letter-categories.index')} variant="secondary">
                                {t('letters.categories.manage')}
                            </LinkButton>
                        )}
                        {can('letters.templet.create') && (
                            <LinkButton href={route('letter-templates.create')} variant="success">
                                {t('letters.templates.actions.new_template')}
                            </LinkButton>
                        )}
                        {can('letters.create') && <LinkButton href={route('letters.compose')}>{t('letters.templates.actions.compose_letter')}</LinkButton>}
                    </>
                }
            />

            <Table
                head={
                    <tr>
                        <th scope="col">{t('letters.templates.table.title')}</th>
                        <th scope="col">{t('letters.templates.table.category')}</th>
                        <th scope="col">{t('letters.templates.table.placeholders')}</th>
                        <th scope="col">{t('letters.templates.table.updated')}</th>
                        <th scope="col">
                            <span className="sr-only">{t('letters.templates.table.actions')}</span>
                        </th>
                    </tr>
                }
                footer={templates.last_page > 1 ? <Pagination meta={templates} /> : undefined}
            >
                {templates.data.length === 0 ? (
                    <tr>
                        <td colSpan={5} className="py-12">
                            <EmptyState icon={<DocumentTextIcon className="h-6 w-6" />} title={t('letters.templates.table.empty')} />
                        </td>
                    </tr>
                ) : (
                    templates.data.map((template) => (
                        <tr key={template.id}>
                            <td className="font-medium text-(--text)">{template.title}</td>
                            <td className="text-(--text-muted)">{template.category ?? t('letters.cards.missing')}</td>
                            <td>
                                <PlaceholderSummary placeholders={template.placeholders} none={t('letters.templates.table.none')} />
                            </td>
                            <td className="whitespace-nowrap text-(--text-subtle)">{template.updated}</td>
                            <td className="whitespace-nowrap">
                                <div className="flex justify-end gap-2">
                                    {canUpdate && (
                                        <LinkButton href={route('letter-templates.edit', template.id)} variant="secondary" size="sm">
                                            {t('letters.actions.edit')}
                                        </LinkButton>
                                    )}
                                    {canDelete && (
                                        <ConfirmButton
                                            url={route('letter-templates.destroy', template.id)}
                                            title={t('letters.templates.actions.delete_template')}
                                            message={template.title}
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
