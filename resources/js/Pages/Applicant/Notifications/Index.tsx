import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { Pagination } from '@/components/ui/Pagination';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Paginated } from '@/types';
import type { ApplicantNotificationItem, ApplicantNotificationType } from '@/types/party';

interface Props {
    sections: Record<ApplicantNotificationType, Paginated<ApplicantNotificationItem>>;
}

const sectionText: Record<ApplicantNotificationType, { title: string; empty: string }> = {
    hearing: { title: 'cases.hearings.title', empty: 'app.admin_notifications.no_upcoming_hearings' },
    message: { title: 'app.admin_notifications.messages', empty: 'app.admin_notifications.no_new_messages' },
    respondent_view: { title: 'app.admin_notifications.respondent_views', empty: 'app.admin_notifications.no_respondent_views' },
    status: { title: 'app.admin_notifications.status_updates', empty: 'app.admin_notifications.no_new_status_updates' },
};

const order: ApplicantNotificationType[] = ['hearing', 'message', 'respondent_view', 'status'];

/** Every unseen notification, one paginated list per kind. */
export default function NotificationsIndex({ sections }: Props) {
    const markSeen = (item: ApplicantNotificationItem) =>
        router.post(route('applicant.notifications.markOne'), { type: item.type, sourceId: item.sourceId }, { preserveScroll: true });

    return (
        <>
            <Head title={t('app.Notifications')} />
            <div className="flex items-center justify-between">
                <h1 className="text-lg font-semibold text-(--text)">{t('app.Notifications')}</h1>
                <Button variant="secondary" size="sm" onClick={() => router.post(route('applicant.notifications.markAll'), {}, { preserveScroll: true })}>
                    {t('app.Mark all as seen')}
                </Button>
            </div>

            {order.map((type) => {
                const page = sections[type];

                return (
                    <Card key={type} padding="sm">
                        <div className="mb-2 flex items-center justify-between">
                            <h2 className="text-sm font-semibold text-(--text)">{t(sectionText[type].title)}</h2>
                            <span className="rounded-full bg-(--surface-soft) px-2 py-0.5 text-[11px] text-(--text-muted)">{page.total}</span>
                        </div>

                        {page.data.length === 0 ? (
                            <p className="text-sm text-(--text-subtle)">{t(sectionText[type].empty)}</p>
                        ) : (
                            <>
                                <ul className="divide-y divide-(--border)">
                                    {page.data.map((item) => (
                                        <li key={item.sourceId} className="flex items-center justify-between gap-3 py-2">
                                            <div className="min-w-0 flex-1 text-sm">
                                                <Link href={item.href} className="font-medium text-(--text) hover:underline">
                                                    {item.caseNumber}
                                                    {type === 'hearing' && item.when && <> — {item.when}</>}
                                                </Link>
                                                <div className="mt-0.5 text-xs text-(--text-subtle)">
                                                    {item.url ? (
                                                        <a href={item.url} target="_blank" rel="noreferrer" className="text-[rgb(var(--ac))] hover:underline">
                                                            {item.text}
                                                        </a>
                                                    ) : (
                                                        item.text
                                                    )}
                                                    {type !== 'hearing' && item.when && <> · {item.when}</>}
                                                </div>
                                            </div>
                                            <Button variant="secondary" size="sm" onClick={() => markSeen(item)}>
                                                {t('app.Seen')}
                                            </Button>
                                        </li>
                                    ))}
                                </ul>
                                <div className="mt-3">
                                    <Pagination meta={page} />
                                </div>
                            </>
                        )}
                    </Card>
                );
            })}
        </>
    );
}
