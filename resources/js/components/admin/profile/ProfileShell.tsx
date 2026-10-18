import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { useShared } from '@/hooks/useShared';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import { initials } from '@/lib/str';

export type ProfileTab = 'profile' | 'security' | 'sessions';

interface ProfileShellProps {
    active: ProfileTab;
    /** Switches tabs in place on the profile page; omitted elsewhere, where the tabs are links. */
    onSelect?: (tab: 'profile' | 'security') => void;
    children: ReactNode;
}

/** Staff profile header and section navigation shared by the profile and sessions pages. */
export function ProfileShell({ active, onSelect, children }: ProfileShellProps) {
    const user = useShared().auth.user;

    const tabClass = (tab: ProfileTab) =>
        cn(
            'rounded-lg px-4 py-2 text-sm font-semibold transition-colors',
            tab === active ? 'bg-blue-600 text-white' : 'border border-(--border) bg-(--surface-strong) text-(--text-muted) hover:bg-(--surface-soft)',
        );

    const tabLink = (tab: 'profile' | 'security', label: string) =>
        onSelect ? (
            <a
                href={`#${tab}`}
                onClick={(event) => {
                    event.preventDefault();
                    onSelect(tab);
                }}
                className={tabClass(tab)}
                aria-current={tab === active ? 'page' : undefined}
            >
                {label}
            </a>
        ) : (
            <Link href={`${route('profile.edit')}${tab === 'security' ? '?tab=security' : ''}`} className={tabClass(tab)}>
                {label}
            </Link>
        );

    return (
        <div className="mx-auto max-w-6xl space-y-6">
            <div className="rounded-2xl bg-linear-to-r from-blue-700 to-indigo-700 p-6 text-white shadow-lg">
                <div className="flex items-center gap-4">
                    <div className="grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-2xl bg-white/20 text-2xl font-bold ring-1 ring-white/30">
                        {user?.avatarUrl ? <img src={user.avatarUrl} alt="" className="h-full w-full object-cover" /> : initials(user?.name ?? 'A')}
                    </div>
                    <div className="min-w-0">
                        <p className="text-xs font-semibold tracking-[.18em] text-blue-100 uppercase">{t('auth.profile_section')}</p>
                        <h1 className="mt-1 truncate text-2xl font-bold">{user?.name}</h1>
                        <p className="mt-1 truncate text-sm text-blue-100">{user?.email}</p>
                    </div>
                </div>
            </div>

            <nav className="flex flex-wrap gap-2" aria-label={t('auth.profile_section')}>
                {tabLink('profile', t('auth.profile_section'))}
                {tabLink('security', t('auth.security_section'))}
                <Link href={route('profile.sessions.index')} className={tabClass('sessions')} aria-current={active === 'sessions' ? 'page' : undefined}>
                    {t('auth.sessions_title')}
                </Link>
            </nav>

            {children}
        </div>
    );
}
