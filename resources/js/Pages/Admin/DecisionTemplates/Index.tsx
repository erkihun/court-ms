import { DocumentTextIcon } from '@heroicons/react/24/outline';
import { Head } from '@inertiajs/react';
import { PlaceholderSummary } from '@/components/admin/templates/TemplateFormParts';
import { Badge } from '@/components/ui/Badge';
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
import type { DecisionTemplateRow } from '@/types/admin/letters';

interface Props {
    templates: Paginated<DecisionTemplateRow>;
}

export default function DecisionTemplatesIndex({ templates }: Props) {
    const can = useCan();
    const canUpdate = can('decision.templet.update');
    const canDelete = can('decision.templet.delete');

    return (
        <>
            <Head title={t('decision_templates.title')} />
            <PageHeader
                title={t('decision_templates.title')}
                description={t('decision_templates.description')}
                className="mb-6"
                actions={
                    can('decision.templet.create') && (
                        <LinkButton href={route('decision-templates.create')} variant="success">
                            {t('decision_templates.actions.new_template')}
                        </LinkButton>
                    )
                }
            />

            <Table
                head={
                    <tr>
                        <th scope="col">{t('decision_templates.table.title')}</th>
                        <th scope="col">{t('decision_templates.table.category')}</th>
                        <th scope="col">{t('decision_templates.table.placeholders')}</th>
                        <th scope="col">{t('decision_templates.table.updated')}</th>
                        <th scope="col">
                            <span className="sr-only">{t('decision_templates.table.actions')}</span>
                        </th>
                    </tr>
                }
                footer={templates.last_page > 1 ? <Pagination meta={templates} /> : undefined}
            >
                {templates.data.length === 0 ? (
                    <tr>
                        <td colSpan={5} className="py-12">
                            <EmptyState icon={<DocumentTextIcon className="h-6 w-6" />} title={t('decision_templates.table.empty')} />
                        </td>
                    </tr>
                ) : (
                    templates.data.map((template) => (
                        <tr key={template.id}>
                            <td className="font-medium text-(--text)">
                                {template.title}
                                {template.isDefault && (
                                    <Badge tone="success" className="ms-2 uppercase">
                                        {t('decision_templates.output.default_layout')}
                                    </Badge>
                                )}
                            </td>
                            <td className="text-(--text-muted)">{template.category ?? t('decision_templates.table.missing')}</td>
                            <td>
                                <PlaceholderSummary placeholders={template.placeholders} none={t('decision_templates.table.none')} />
                            </td>
                            <td className="whitespace-nowrap text-(--text-subtle)">{template.updated}</td>
                            <td className="whitespace-nowrap">
                                <div className="flex justify-end gap-2">
                                    {canUpdate && (
                                        <LinkButton href={route('decision-templates.edit', template.id)} variant="secondary" size="sm">
                                            {t('decision_templates.actions.edit')}
                                        </LinkButton>
                                    )}
                                    {canDelete && (
                                        <ConfirmButton
                                            url={route('decision-templates.destroy', template.id)}
                                            title={t('decision_templates.actions.delete_template')}
                                            message={template.title}
                                        >
                                            {t('decision_templates.actions.delete')}
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
