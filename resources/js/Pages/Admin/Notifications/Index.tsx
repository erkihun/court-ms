import { BellIcon } from '@heroicons/react/24/outline';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/Button';
import { Card, CardTitle } from '@/components/ui/Card';
import { EmptyState } from '@/components/ui/EmptyState';
import { PageHeader } from '@/components/ui/PageHeader';
import { Pagination } from '@/components/ui/Pagination';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { AdminNotificationItem, Paginated } from '@/types';

type NotificationType = AdminNotificationItem['type'];

interface Props {
    sections: Record<NotificationType, Paginated<AdminNotificationItem>>;
}

const SECTIONS: Array<{ type: NotificationType; title: string; empty: string }> = [
    { type: 'message', title: 'app.Applicant messages', empty: 'app.admin_notifications.no_new_messages' },
    { type: 'case', title: 'app.New cases', empty: 'app.admin_notifications.no_new_cases' },
    { type: 'hearing', title: 'app.Upcoming hearings', empty: 'app.admin_notifications.no_upcoming_hearings' },
    { type: 'respondent_view', title: 'app.admin_notifications.respondent_views', empty: 'app.admin_notifications.no_respondent_views' },
];

export default function NotificationsIndex({ sections }: Props) {
    const [busy, setBusy] = useState<string | null>(null);

    const markOne = (item: AdminNotificationItem) => {
        const key = `${item.type}-${item.sourceId}`;
        router.post(
            route('admin.notifications.markOne'),
            { type: item.type, sourceId: item.sourceId },
            { preserveScroll: true, onStart: () => setBusy(key), onFinish: () => setBusy(null) },
        );
    };

    const markAll = () => {
        router.post(route('admin.notifications.markAll'), {}, { preserveScroll: true, onStart: () => setBusy('all'), onFinish: () => setBusy(null) });
    };

    return (
        <>
            <Head title={t('app.Notifications')} />
            <PageHeader
                title={t('app.admin_notifications.your_notifications')}
                description={t('app.Notifications')}
                actions={
                    <Button variant="secondary" onClick={markAll} loading={busy === 'all'}>
                        {t('app.Mark all as seen')}
                    </Button>
                }
                className="mb-6"
            />

            <div className="grid gap-6 lg:grid-cols-2">
                {SECTIONS.map(({ type, title, empty }) => {
                    const page = sections[type];

                    return (
                        <Card
                            key={type}
                            padding="none"
                            header={<CardTitle title={t(title)} actions={<span className="ui-badge ui-badge-slate">{page.total}</span>} />}
                        >
                            {page.data.length === 0 ? (
                                <EmptyState icon={<BellIcon className="h-6 w-6" />} title={t(empty)} className="m-5" />
                            ) : (
                                <ul className="divide-y divide-(--border)">
                                    {page.data.map((item) => (
                                        <li key={item.sourceId} className="flex items-center justify-between gap-4 px-5 py-3 hover:bg-(--surface-soft)">
                                            <Link href={item.href} className="min-w-0 flex-1 text-sm">
                                                <div className="font-medium text-(--text)">{item.caseNumber}</div>
                                                <div className="text-xs text-(--text-muted)">
                                                    {item.text}
                                                    {item.when && <> · {item.when}</>}
                                                </div>
                                            </Link>
                                            <Button
                                                variant="secondary"
                                                size="sm"
                                                onClick={() => markOne(item)}
                                                loading={busy === `${item.type}-${item.sourceId}`}
                                            >
                                                {t('app.Seen')}
                                            </Button>
                                        </li>
                                    ))}
                                </ul>
                            )}
                            {page.last_page > 1 && (
                                <div className="border-t border-(--border) px-5 py-3">
                                    <Pagination meta={page} />
                                </div>
                            )}
                        </Card>
                    );
                })}
            </div>
        </>
    );
}
