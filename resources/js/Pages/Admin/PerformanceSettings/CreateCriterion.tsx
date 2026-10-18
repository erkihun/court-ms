import { Head } from '@inertiajs/react';
import { CriterionForm } from '@/components/admin/performance-settings/CriterionForm';
import type { CategoryOption } from '@/components/admin/performance-settings/types';
import { LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { PageHeader } from '@/components/ui/PageHeader';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

export default function CreateCriterion({ categories }: { categories: CategoryOption[] }) {
    const back = (
        <LinkButton href={route('settings.performance-evaluation.index')} variant="secondary">
            {t('performance.actions.back')}
        </LinkButton>
    );

    return (
        <>
            <Head title={t('performance.settings.create_criterion_title')} />
            <PageHeader
                title={t('performance.settings.create_criterion_title')}
                description={t('performance.settings.create_criterion_subtitle')}
                className="mb-6"
            />
            <Card className="max-w-3xl">
                <CriterionForm categories={categories} cancel={back} />
            </Card>
        </>
    );
}
