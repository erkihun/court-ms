import { Link } from '@inertiajs/react';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

export type ProfilePortal = 'applicant' | 'respondent';
export type ProfileTab = 'profile' | 'security' | 'sessions';

export const profileRoutes = {
    applicant: {
        edit: 'applicant.profile.edit',
        update: 'applicant.profile.update',
        password: 'applicant.profile.password.update',
        sessions: 'applicant.profile.sessions.index',
        destroySession: 'applicant.profile.sessions.destroy',
        destroyOtherSessions: 'applicant.profile.sessions.destroyOthers',
        dashboard: 'applicant.dashboard',
    },
    respondent: {
        edit: 'respondent.profile.edit',
        update: 'respondent.profile.update',
        password: 'respondent.profile.password',
        sessions: 'respondent.profile.sessions.index',
        destroySession: 'respondent.profile.sessions.destroy',
        destroyOtherSessions: 'respondent.profile.sessions.destroyOthers',
        dashboard: 'respondent.dashboard',
    },
} as const;

const tabClass = (active: boolean) =>
    cn(
        'rounded-lg px-4 py-2 text-sm font-semibold transition',
        active ? 'bg-blue-600 text-white' : 'border border-(--border) bg-(--surface-strong) text-(--text-muted) hover:border-blue-300',
    );

/**
 * Profile / Security / Sessions switcher. The first two are tabs on the
 * profile page (`onSelect`); Sessions is its own page.
 */
export function ProfileTabs({ portal, active, onSelect }: { portal: ProfilePortal; active: ProfileTab; onSelect?: (tab: 'profile' | 'security') => void }) {
    const routes = profileRoutes[portal];
    const tab = (name: 'profile' | 'security', label: string) =>
        onSelect ? (
            <button type="button" onClick={() => onSelect(name)} className={tabClass(active === name)} aria-pressed={active === name}>
                {label}
            </button>
        ) : (
            <Link href={`${route(routes.edit)}#${name}`} className={tabClass(false)}>
                {label}
            </Link>
        );

    return (
        <nav className="mb-6 flex flex-wrap gap-2 border-b border-(--border) pb-4" aria-label={t('auth.profile_section')}>
            {tab('profile', t('auth.profile_section'))}
            {tab('security', t('auth.security_section'))}
            <Link href={route(routes.sessions)} className={tabClass(active === 'sessions')} aria-current={active === 'sessions' ? 'page' : undefined}>
                {t('auth.sessions_title')}
            </Link>
        </nav>
    );
}
