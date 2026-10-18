import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { CategoryForm } from '@/components/admin/performance-settings/CategoryForm';
import { CriterionForm } from '@/components/admin/performance-settings/CriterionForm';
import type { CategoryOption, Criterion } from '@/components/admin/performance-settings/types';
import { Badge } from '@/components/ui/Badge';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card, CardTitle } from '@/components/ui/Card';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { DescriptionList } from '@/components/ui/DescriptionList';
import { Modal } from '@/components/ui/Modal';
import { PageHeader } from '@/components/ui/PageHeader';
import { Table } from '@/components/ui/Table';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    criteria: Criterion[];
    categories: CategoryOption[];
    totalActiveWeight: number;
}

type Dialog = { kind: 'createCriterion' } | { kind: 'createCategory' } | { kind: 'view'; criterion: Criterion } | { kind: 'edit'; criterion: Criterion } | null;

export default function PerformanceSettingsIndex({ criteria, categories, totalActiveWeight }: Props) {
    const [dialog, setDialog] = useState<Dialog>(null);
    const close = () => setDialog(null);
    const cancel = (
        <Button variant="secondary" onClick={close}>
            {t('performance.actions.cancel')}
        </Button>
    );
    const notAvailable = t('performance.not_available');

    return (
        <>
            <Head title={t('performance.settings.title')} />
            <PageHeader
                title={t('performance.settings.title')}
                description={t('performance.settings.subtitle')}
                className="mb-6"
                actions={
                    <>
                        <Button variant="success" onClick={() => setDialog({ kind: 'createCriterion' })}>
                            {t('performance.settings.actions.add_criterion')}
                        </Button>
                        <Button variant="secondary" onClick={() => setDialog({ kind: 'createCategory' })}>
                            {t('performance.settings.actions.add_category')}
                        </Button>
                        <LinkButton href={route('performance-evaluations.index')} variant="ghost">
                            {t('performance.settings.back_to_evaluations')}
                        </LinkButton>
                    </>
                }
            />

            <Card
                padding="none"
                header={
                    <CardTitle
                        title={t('performance.settings.criteria_title')}
                        description={t('performance.settings.criteria_hint')}
                        actions={
                            <span
                                className={cn(
                                    'rounded-lg border px-3 py-1.5 text-sm font-semibold',
                                    totalActiveWeight === 100
                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300'
                                        : 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300',
                                )}
                            >
                                {t('performance.settings.active_weight')}: {totalActiveWeight}%
                            </span>
                        }
                    />
                }
            >
                <Table
                    className="rounded-none border-0"
                    head={
                        <tr>
                            <th scope="col">{t('performance.settings.fields.name')}</th>
                            <th scope="col">{t('performance.settings.fields.name_am')}</th>
                            <th scope="col">{t('performance.settings.fields.category')}</th>
                            <th scope="col">{t('performance.settings.fields.weight')}</th>
                            <th scope="col">{t('performance.settings.fields.order')}</th>
                            <th scope="col">{t('performance.settings.fields.status')}</th>
                            <th scope="col">{t('performance.settings.fields.description')}</th>
                            <th scope="col" className="text-center">
                                {t('performance.settings.used_scores')}
                            </th>
                            <th scope="col">
                                <span className="sr-only">{t('performance.fields.actions')}</span>
                            </th>
                        </tr>
                    }
                >
                    {criteria.length === 0 ? (
                        <tr>
                            <td colSpan={9} className="py-10 text-center text-(--text-subtle)">
                                {t('performance.settings.empty')}
                            </td>
                        </tr>
                    ) : (
                        criteria.map((criterion) => (
                            <tr key={criterion.id} className="align-top">
                                <td className="font-semibold text-(--text)">{criterion.name}</td>
                                <td>{criterion.nameAm || notAvailable}</td>
                                <td>
                                    <Badge tone="neutral">{criterion.categoryLabel}</Badge>
                                </td>
                                <td className="whitespace-nowrap">{criterion.weight}%</td>
                                <td>{criterion.sortOrder}</td>
                                <td>
                                    <Badge tone={criterion.active ? 'success' : 'neutral'}>
                                        {criterion.active ? t('performance.settings.active') : t('performance.settings.inactive')}
                                    </Badge>
                                </td>
                                <td>
                                    <div className="max-w-xs truncate" title={criterion.description ?? undefined}>
                                        {criterion.description || notAvailable}
                                    </div>
                                </td>
                                <td className="text-center font-semibold">{criterion.scoresCount}</td>
                                <td className="whitespace-nowrap">
                                    <div className="flex justify-end gap-2">
                                        <Button variant="ghost" size="sm" onClick={() => setDialog({ kind: 'view', criterion })}>
                                            {t('performance.actions.view')}
                                        </Button>
                                        <Button variant="secondary" size="sm" onClick={() => setDialog({ kind: 'edit', criterion })}>
                                            {t('performance.actions.edit')}
                                        </Button>
                                        <ConfirmButton
                                            url={route('settings.performance-evaluation.criteria.destroy', criterion.id)}
                                            title={t('performance.settings.confirm_delete')}
                                            message={criterion.name}
                                        >
                                            {t('performance.settings.actions.delete')}
                                        </ConfirmButton>
                                    </div>
                                </td>
                            </tr>
                        ))
                    )}
                </Table>
            </Card>

            <Modal open={dialog?.kind === 'createCriterion'} onClose={close} title={t('performance.settings.create_criterion_title')} size="lg">
                <p className="mb-4 text-sm text-(--text-subtle)">{t('performance.settings.create_criterion_subtitle')}</p>
                <CriterionForm categories={categories} onSuccess={close} cancel={cancel} />
            </Modal>

            <Modal open={dialog?.kind === 'createCategory'} onClose={close} title={t('performance.settings.create_category_title')}>
                <p className="mb-4 text-sm text-(--text-subtle)">{t('performance.settings.create_category_subtitle')}</p>
                <CategoryForm onSuccess={close} cancel={cancel} />
            </Modal>

            <Modal open={dialog?.kind === 'edit'} onClose={close} title={t('performance.settings.edit_criterion_title')} size="lg">
                {dialog?.kind === 'edit' && (
                    <CriterionForm key={dialog.criterion.id} categories={categories} criterion={dialog.criterion} onSuccess={close} cancel={cancel} />
                )}
            </Modal>

            <Modal open={dialog?.kind === 'view'} onClose={close} title={t('performance.settings.view_criterion_title')} size="lg">
                {dialog?.kind === 'view' && (
                    <DescriptionList
                        items={[
                            { label: t('performance.settings.fields.name'), value: dialog.criterion.name },
                            { label: t('performance.settings.fields.name_am'), value: dialog.criterion.nameAm || notAvailable },
                            { label: t('performance.settings.fields.category'), value: dialog.criterion.categoryLabel },
                            { label: t('performance.settings.fields.weight'), value: `${dialog.criterion.weight}%` },
                            { label: t('performance.settings.fields.order'), value: dialog.criterion.sortOrder },
                            { label: t('performance.settings.used_scores'), value: dialog.criterion.scoresCount },
                            {
                                label: t('performance.settings.fields.description'),
                                value: <span className="whitespace-pre-line">{dialog.criterion.description || notAvailable}</span>,
                                wide: true,
                            },
                        ]}
                    />
                )}
            </Modal>
        </>
    );
}
