import { BellIcon } from '@heroicons/react/24/outline';
import { Link, router } from '@inertiajs/react';
import { Dropdown } from '@/components/ui/Dropdown';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { ApplicantNotificationFeedData, ApplicantNotificationItem, ApplicantNotificationType } from '@/types/party';
import { useFeed } from './useFeed';

const groupLabels: Record<ApplicantNotificationType, string> = {
    message: 'cases.navigation.messages',
    hearing: 'app.Hearing',
    respondent_view: 'app.admin_notifications.respondent_views',
    status: 'app.admin_notifications.status_updates',
};

export function badgeText(count: number): string {
    return count > 9 ? '9+' : String(count);
}

/** Header bell for applicants: unseen messages, hearings, respondent views and status changes. */
export function ApplicantBell() {
    const { feed, reload } = useFeed<ApplicantNotificationFeedData>(route('applicant.notifications.feed'));
    const count = feed?.count ?? 0;

    const markSeen = (item: ApplicantNotificationItem) =>
        router.post(route('applicant.notifications.markOne'), { type: item.type, sourceId: item.sourceId }, { preserveScroll: true, onSuccess: () => void reload() });
    const markAll = () => router.post(route('applicant.notifications.markAll'), {}, { preserveScroll: true, onSuccess: () => void reload() });

    return (
        <Dropdown
            panelClassName="w-md max-w-[93vw]"
            trigger={({ open, toggle }) => (
                <button
                    type="button"
                    onClick={() => {
                        if (!open) void reload();
                        toggle();
                    }}
                    className={cn('ap-icon-btn', open && 'ap-icon-btn-active')}
                    aria-label={t('app.Notifications')}
                    aria-expanded={open}
                >
                    <BellIcon className="h-5 w-5" strokeWidth={1.8} />
                    {count > 0 && <span className="ap-badge">{badgeText(count)}</span>}
                </button>
            )}
        >
            {(close) => (
                <div className="p-3" role="dialog" aria-label={t('app.Notifications')}>
                    <div className="flex items-center justify-between">
                        <div className="text-sm font-semibold text-(--text)">{t('app.Notifications')}</div>
                        {count > 0 && (
                            <button type="button" onClick={markAll} className="rounded-sm border border-(--border) px-2 py-1 text-xs text-(--text-muted) hover:bg-(--surface-soft)">
                                {t('app.Mark all as seen')}
                            </button>
                        )}
                    </div>

                    <div className="mt-3 max-h-96 space-y-3 overflow-y-auto overscroll-contain">
                        {!feed ? (
                            <div className="text-sm text-(--text-subtle)">{t('ui.loading')}</div>
                        ) : feed.groups.length === 0 ? (
                            <div className="text-sm text-(--text-subtle)">{t('app.youre_all_caught_up')}</div>
                        ) : (
                            feed.groups.map((group) => (
                                <div key={group.type}>
                                    <div className="mb-1 flex items-center justify-between">
                                        <div className="text-xs font-medium text-(--text-subtle)">{t(groupLabels[group.type])}</div>
                                        <span className="rounded-full bg-(--surface-soft) px-2 py-0.5 text-[11px] text-(--text-muted)">{group.items.length}</span>
                                    </div>
                                    <ul className="divide-y divide-(--border)">
                                        {group.items.map((item) => (
                                            <li key={`${item.type}-${item.sourceId}`} className="flex items-center justify-between gap-3 py-2">
                                                <Link href={item.href} onClick={close} className="min-w-0 flex-1 text-sm">
                                                    <div className="font-medium text-(--text)">
                                                        {item.caseNumber}
                                                        {item.type === 'hearing' && item.when && <span className="text-(--text-subtle)"> · {item.when}</span>}
                                                    </div>
                                                    <div className="truncate text-xs text-(--text-subtle)">
                                                        {item.text}
                                                        {item.type !== 'hearing' && item.when && <> · {item.when}</>}
                                                    </div>
                                                </Link>
                                                <button
                                                    type="button"
                                                    onClick={() => markSeen(item)}
                                                    className="shrink-0 rounded-sm border border-(--border) px-2 py-1 text-xs text-(--text-muted) hover:bg-(--surface-soft)"
                                                >
                                                    {t('app.Seen')}
                                                </button>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            ))
                        )}
                    </div>

                    <div className="mt-3 flex items-center justify-between border-t border-(--border) pt-2 text-xs text-(--text-muted)">
                        <Link href={route('applicant.notifications.index')} onClick={close} className="hover:text-(--text)">
                            {t('app.View all')}
                        </Link>
                        <Link href={route('applicant.notifications.settings')} onClick={close} className="hover:text-(--text)">
                            {t('app.Settings')}
                        </Link>
                    </div>
                </div>
            )}
        </Dropdown>
    );
}
