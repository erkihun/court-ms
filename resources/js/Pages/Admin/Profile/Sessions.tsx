import { ComputerDesktopIcon } from '@heroicons/react/24/outline';
import { Head } from '@inertiajs/react';
import { ProfileShell } from '@/components/admin/profile/ProfileShell';
import { Alert } from '@/components/ui/Alert';
import { Card } from '@/components/ui/Card';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface SessionRow {
    /** Keyed hash of the session id; the id itself never reaches the browser. */
    key: string;
    deviceName: string;
    browserName: string;
    location: string;
    ipAddress: string | null;
    isCurrent: boolean;
    lastActive: string;
    lastActiveAt: string;
}

interface Props {
    databaseDriver: boolean;
    sessions: SessionRow[];
}

export default function AdminProfileSessions({ databaseDriver, sessions }: Props) {
    const hasOthers = sessions.some((session) => !session.isCurrent);

    return (
        <ProfileShell active="sessions">
            <Head title={t('auth.sessions_title')} />

            {!databaseDriver && <Alert tone="warning">{t('auth.sessions_file_driver_hint')}</Alert>}

            <Card>
                <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 className="text-lg font-bold text-(--text)">{t('auth.active_sessions')}</h2>
                        <p className="mt-1 text-sm text-(--text-subtle)">{t('auth.sessions_hint')}</p>
                    </div>
                    {hasOthers && (
                        <ConfirmButton url={route('profile.sessions.destroyOthers')} title={t('auth.revoke_other_sessions')} size="md">
                            {t('auth.revoke_other_sessions')}
                        </ConfirmButton>
                    )}
                </div>

                {sessions.length === 0 ? (
                    <p className="rounded-xl border border-dashed border-(--border-strong) p-6 text-center text-sm text-(--text-subtle)">
                        {t('auth.no_active_sessions')}
                    </p>
                ) : (
                    <ul className="space-y-3">
                        {sessions.map((session) => (
                            <li key={session.key} className="flex items-center justify-between gap-4 rounded-xl border border-(--border) p-3">
                                <div className="flex min-w-0 items-center gap-4">
                                    <div className="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                        <ComputerDesktopIcon className="h-6 w-6" />
                                    </div>
                                    <div className="min-w-0">
                                        <p className="truncate font-semibold text-(--text)">
                                            {session.deviceName} – {session.browserName}
                                        </p>
                                        <p className="mt-1 text-sm text-(--text-muted)">
                                            {session.location} • {session.ipAddress || '—'}
                                        </p>
                                        <p className="mt-1 text-xs text-(--text-subtle)">
                                            {t('auth.last_active')}: {session.lastActive} • {session.lastActiveAt}
                                        </p>
                                    </div>
                                </div>
                                {session.isCurrent ? (
                                    <span className="ui-badge ui-badge-slate shrink-0">{t('auth.current_session')}</span>
                                ) : (
                                    <ConfirmButton url={route('profile.sessions.destroy', session.key)} title={t('auth.revoke_session')} variant="secondary">
                                        {t('auth.revoke_session')}
                                    </ConfirmButton>
                                )}
                            </li>
                        ))}
                    </ul>
                )}
            </Card>
        </ProfileShell>
    );
}
