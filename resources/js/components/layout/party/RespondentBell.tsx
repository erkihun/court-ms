import { BellIcon } from '@heroicons/react/24/outline';
import { Link, router } from '@inertiajs/react';
import { Dropdown } from '@/components/ui/Dropdown';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import { headline } from '@/lib/str';
import type { RespondentNotificationFeedData, RespondentNotificationItem } from '@/types/party';
import { badgeText } from './ApplicantBell';
import { useFeed } from './useFeed';

/** Header bell in respondent mode: activity on the cases this respondent has opened. */
export function RespondentBell() {
    const { feed, reload } = useFeed<RespondentNotificationFeedData>(route('respondent.notifications.feed'));
    const count = feed?.count ?? 0;

    const markSeen = (item: RespondentNotificationItem) =>
        router.post(route('respondent.notifications.markOne'), { type: item.type, sourceId: item.sourceId }, { preserveScroll: true, onSuccess: () => void reload() });
    const markAll = () => router.post(route('respondent.notifications.markAll'), {}, { preserveScroll: true, onSuccess: () => void reload() });

    return (
        <Dropdown
            panelClassName="w-80 max-w-[93vw]"
            trigger={({ open, toggle }) => (
                <button
                    type="button"
                    onClick={() => {
                        if (!open) void reload();
                        toggle();
                    }}
                    className={cn('ap-icon-btn', open && 'ap-icon-btn-active')}
                    aria-label={t('respondent.notifications')}
                    aria-expanded={open}
                >
                    <BellIcon className="h-5 w-5" strokeWidth={1.8} />
                    {count > 0 && <span className="ap-badge">{badgeText(count)}</span>}
                </button>
            )}
        >
            {(close) => (
                <div role="dialog" aria-label={t('respondent.notifications')}>
                    <div className="flex items-center justify-between border-b border-(--border) px-4 py-3">
                        <div>
                            <div className="text-xs tracking-wide text-(--text-subtle) uppercase">{t('respondent.notifications')}</div>
                            <div className="text-sm font-semibold text-(--text)">
                                {count} {t('respondent.notifications')}
                            </div>
                        </div>
                        {count > 0 && (
                            <button type="button" onClick={markAll} className="text-xs font-semibold text-[rgb(var(--ac))] hover:underline">
                                {t('respondent.mark_all_as_read')}
                            </button>
                        )}
                    </div>
                    <div className="max-h-80 divide-y divide-(--border) overflow-auto">
                        {!feed ? (
                            <div className="px-4 py-4 text-sm text-(--text-subtle)">{t('ui.loading')}</div>
                        ) : feed.items.length === 0 ? (
                            <div className="px-4 py-4 text-sm text-(--text-subtle)">{t('respondent.no_notifications')}</div>
                        ) : (
                            feed.items.map((item) => (
                                <div key={`${item.type}-${item.sourceId}`} className="flex items-center justify-between gap-2 px-4 py-3 text-sm">
                                    <div className="min-w-0">
                                        <div className="mb-1 inline-flex items-center rounded-full bg-(--surface-soft) px-2 py-0.5 text-[11px] text-(--text-muted)">{headline(item.type)}</div>
                                        <div className="font-semibold text-(--text)">{item.caseNumber}</div>
                                        <div className="truncate text-xs text-(--text-subtle)">{item.title ?? t('respondent.view_case_details')}</div>
                                        {item.when && <div className="mt-1 text-[11px] text-(--text-subtle)">{item.when}</div>}
                                    </div>
                                    <div className="flex shrink-0 flex-col items-end gap-2">
                                        <Link href={item.href} onClick={close} className="text-xs font-semibold text-[rgb(var(--ac))] hover:underline">
                                            {t('respondent.view_case_details')}
                                        </Link>
                                        <button type="button" onClick={() => markSeen(item)} className="text-[11px] text-(--text-subtle) hover:text-[rgb(var(--ac))]">
                                            {t('respondent.mark_as_read')}
                                        </button>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                </div>
            )}
        </Dropdown>
    );
}
