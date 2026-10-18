import { ComputerDesktopIcon } from '@heroicons/react/24/outline';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { type ProfilePortal, profileRoutes, ProfileTabs } from '@/components/party/ProfileTabs';
import { ConfirmDialog } from '@/components/ui/ConfirmDialog';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface SessionRow {
    key: string;
    deviceName: string;
    browserName: string;
    ipAddress: string | null;
    isCurrent: boolean;
    lastActive: string;
}

interface Props {
    portal: ProfilePortal;
    databaseDriver: boolean;
    sessions: SessionRow[];
}

/** Signed-in browsers for this account, with sign-out of the others. */
export default function ProfileSessions({ portal, databaseDriver, sessions }: Props) {
    const routes = profileRoutes[portal];
    const [revokeAll, setRevokeAll] = useState(false);
    const hasOthers = sessions.some((session) => !session.isCurrent);

    return (
        <div className="mx-auto max-w-6xl rounded-xl border border-(--border) bg-(--surface-strong) p-6 shadow-xs md:p-8">
            <Head title={t('auth.sessions_title')} />

            <div className="mb-6 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p className="text-xs font-semibold tracking-[.18em] text-blue-600 uppercase">{t('auth.security_section')}</p>
                    <h1 className="mt-1 text-xl font-semibold text-(--text) md:text-2xl">{t('auth.sessions_title')}</h1>
                    <p className="mt-1 text-sm text-(--text-subtle)">{t('auth.sessions_hint')}</p>
                </div>
                <Link href={route(routes.dashboard)} className="inline-flex items-center rounded-lg border border-(--border) px-3 py-1.5 text-xs font-medium text-(--text-muted) hover:bg-(--surface-soft)">
                    {t('auth.back')}
                </Link>
            </div>

            <ProfileTabs portal={portal} active="sessions" />

            {!databaseDriver && (
                <div className="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">{t('auth.sessions_file_driver_hint')}</div>
            )}

            <section className="overflow-hidden rounded-xl border border-(--border)">
                <div className="flex flex-wrap items-center justify-between gap-3 border-b border-(--border) bg-(--surface-soft) px-5 py-4">
                    <div>
                        <h2 className="text-base font-semibold text-(--text)">{t('auth.active_sessions')}</h2>
                        <p className="mt-1 text-xs text-(--text-subtle)">{t('auth.sessions_hint')}</p>
                    </div>
                    {hasOthers && (
                        <button type="button" onClick={() => setRevokeAll(true)} className="rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">
                            {t('auth.revoke_other_sessions')}
                        </button>
                    )}
                </div>
                <div className="divide-y divide-(--border)">
                    {sessions.map((session) => (
                        <article key={session.key} className="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div className="flex min-w-0 items-start gap-3">
                                <div className="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">
                                    <ComputerDesktopIcon className="h-5 w-5" />
                                </div>
                                <div className="min-w-0">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <h3 className="truncate text-sm font-semibold text-(--text)">{session.deviceName}</h3>
                                        {session.isCurrent && (
                                            <span className="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">{t('auth.current_session')}</span>
                                        )}
                                    </div>
                                    <p className="mt-1 text-xs text-(--text-subtle)">
                                        {session.browserName} · {session.ipAddress || t('auth.unknown_location')}
                                    </p>
                                    <p className="mt-1 text-xs text-(--text-subtle)">
                                        {t('auth.last_active')}: {session.lastActive}
                                    </p>
                                </div>
                            </div>
                            {!session.isCurrent && (
                                <button
                                    type="button"
                                    onClick={() => router.delete(route(routes.destroySession, session.key), { preserveScroll: true })}
                                    className="self-start rounded-lg border border-(--border) px-3 py-2 text-xs font-semibold text-(--text-muted) hover:border-red-200 hover:text-red-700 sm:self-auto"
                                >
                                    {t('auth.revoke_session')}
                                </button>
                            )}
                        </article>
                    ))}
                </div>
            </section>

            <ConfirmDialog
                open={revokeAll}
                title={t('auth.revoke_other_sessions')}
                message={t('auth.sessions_hint')}
                confirmLabel={t('auth.revoke_other_sessions')}
                onCancel={() => setRevokeAll(false)}
                onConfirm={() => router.delete(route(routes.destroyOtherSessions), { preserveScroll: true, onFinish: () => setRevokeAll(false) })}
            />
        </div>
    );
}
