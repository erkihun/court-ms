import { Head } from '@inertiajs/react';
import { CategoryForm } from '@/components/admin/performance-settings/CategoryForm';
import { LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { PageHeader } from '@/components/ui/PageHeader';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

export default function CreateCategory() {
    const back = (
        <LinkButton href={route('settings.performance-evaluation.index')} variant="secondary">
            {t('performance.actions.back')}
        </LinkButton>
    );

    return (
        <>
            <Head title={t('performance.settings.create_category_title')} />
            <PageHeader
                title={t('performance.settings.create_category_title')}
                description={t('performance.settings.create_category_subtitle')}
                className="mb-6"
            />
            <Card className="max-w-xl">
                <CategoryForm cancel={back} />
            </Card>
        </>
    );
}
