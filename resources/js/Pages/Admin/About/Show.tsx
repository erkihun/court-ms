import { Head } from '@inertiajs/react';
import { PublishedBadge } from '@/components/admin/content/PublishedBadge';
import { LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { PageHeader } from '@/components/ui/PageHeader';
import { RichText } from '@/components/ui/RichText';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    about: { id: number; title: string; slug: string; isPublished: boolean; bodyHtml: string };
}

export default function AboutShow({ about }: Props) {
    return (
        <>
            <Head title={about.title} />
            <PageHeader
                title={about.title}
                description={<span className="font-mono">/{about.slug}</span>}
                className="mb-6"
                actions={
                    <>
                        <LinkButton href={route('about.index')} variant="secondary">
                            {t('about.form_cancel')}
                        </LinkButton>
                        <LinkButton href={route('about.edit', about.id)}>{t('about.action_edit')}</LinkButton>
                    </>
                }
            />
            <Card className="max-w-4xl">
                <PublishedBadge published={about.isPublished} />
                <RichText html={about.bodyHtml} className="mt-4" />
            </Card>
        </>
    );
}
