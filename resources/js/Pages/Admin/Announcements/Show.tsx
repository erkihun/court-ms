import { Head } from '@inertiajs/react';
import { Badge } from '@/components/ui/Badge';
import { LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { PageHeader } from '@/components/ui/PageHeader';
import { RichText } from '@/components/ui/RichText';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    announcement: { id: number; title: string; status: 'active' | 'inactive'; created: string; contentHtml: string };
}

export default function AnnouncementShow({ announcement }: Props) {
    return (
        <>
            <Head title={announcement.title} />
            <PageHeader
                title={announcement.title}
                description={announcement.created}
                className="mb-6"
                actions={
                    <>
                        <LinkButton href={route('announcements.index')} variant="secondary">
                            {t('announcements.back_to_list')}
                        </LinkButton>
                        <LinkButton href={route('announcements.edit', announcement.id)}>{t('announcements.action_edit')}</LinkButton>
                    </>
                }
            />
            <Card className="max-w-4xl">
                <Badge tone={announcement.status === 'active' ? 'success' : 'neutral'}>{t(`announcements.status_${announcement.status}`)}</Badge>
                <RichText html={announcement.contentHtml} className="mt-4" />
            </Card>
        </>
    );
}
