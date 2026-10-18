import { Bars3BottomLeftIcon, Bars3Icon, CalendarIcon, MagnifyingGlassIcon } from '@heroicons/react/24/outline';
import { Link, router } from '@inertiajs/react';
import { Fragment, useEffect } from 'react';
import { useShared } from '@/hooks/useShared';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import { LanguageSwitch } from '../LanguageSwitch';
import { ThemeToggle } from '../ThemeToggle';
import { NotificationBell } from './NotificationBell';
import { ProfileMenu } from './ProfileMenu';

interface TopbarProps {
    onOpenMobile: () => void;
    onToggleCompact: () => void;
    compact: boolean;
}

/** Breadcrumb from the active sidebar entry (group, then link), so it is already translated. */
function useBreadcrumb(): string[] {
    const { navigation } = useShared();

    for (const section of navigation ?? []) {
        for (const item of section.items) {
            if (item.type === 'link' && item.active) return [item.label];
            if (item.type === 'group' && item.active) {
                const child = item.children.find((link) => link.active);
                return child ? [item.label, child.label] : [item.label];
            }
        }
    }

    return [];
}

export function Topbar({ onOpenMobile, onToggleCompact, compact }: TopbarProps) {
    const { app } = useShared();
    const crumbs = useBreadcrumb();

    // Ctrl/Cmd+K jumps to case search, as the shortcut hint promises.
    useEffect(() => {
        const onKey = (event: KeyboardEvent) => {
            if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                router.visit(route('cases.index'));
            }
        };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, []);

    return (
        <header className="topnav-root" role="banner">
            <div className="flex min-w-0 flex-1 items-center gap-2">
                <button type="button" className="topnav-icon-btn md:hidden" onClick={onOpenMobile} aria-label={t('app.Open sidebar')}>
                    <Bars3Icon className="h-[1.1rem] w-[1.1rem]" />
                </button>
                <button
                    type="button"
                    className="topnav-icon-btn hidden md:inline-flex"
                    onClick={onToggleCompact}
                    aria-pressed={compact}
                    aria-label={t('app.Toggle sidebar width')}
                >
                    <Bars3BottomLeftIcon className="h-[1.1rem] w-[1.1rem]" />
                </button>
                <span className="mx-1 hidden h-5 w-px shrink-0 bg-slate-200 md:block dark:bg-slate-700" aria-hidden="true" />
                <nav className="topnav-breadcrumb hidden md:flex" aria-label="Breadcrumb">
                    {crumbs.map((crumb, index) => (
                        <Fragment key={`${index}-${crumb}`}>
                            {index > 0 && (
                                <span className="topnav-breadcrumb-sep" aria-hidden="true">
                                    /
                                </span>
                            )}
                            <span className="topnav-breadcrumb-item">{crumb}</span>
                        </Fragment>
                    ))}
                </nav>
                <span className="truncate text-[14px] font-semibold text-slate-800 md:hidden dark:text-slate-100">{crumbs.at(-1) ?? app.shortName}</span>
            </div>

            <div className="hidden w-64 shrink-0 md:flex lg:w-80">
                <Link href={route('cases.index')} className="topnav-search group" title={t('app.cases.search_cases')}>
                    <MagnifyingGlassIcon className="h-3.5 w-3.5 shrink-0" />
                    <span className="flex-1 truncate">{t('app.cases.search_cases')}…</span>
                    <kbd className="hidden shrink-0 items-center gap-0.5 rounded-sm border border-slate-300 bg-white px-1.5 py-0.5 font-mono text-[10px] text-slate-400 lg:inline-flex dark:border-slate-600 dark:bg-slate-800 dark:text-slate-500">
                        <span className="text-[11px]">⌘</span>K
                    </kbd>
                </Link>
            </div>

            <div className="flex shrink-0 items-center gap-1">
                <span className="mr-0.5 hidden shrink-0 items-center gap-1.5 rounded-md border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-500 select-none lg:inline-flex dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-400">
                    <CalendarIcon className="h-3 w-3 shrink-0 opacity-60" />
                    {app.today.long}
                </span>
                <ThemeToggle />
                <LanguageSwitch className="hidden sm:flex" />
                <NotificationBell />
                <span className="mx-0.5 h-5 w-px shrink-0 bg-slate-200 dark:bg-slate-700" aria-hidden="true" />
                <ProfileMenu />
            </div>
        </header>
    );
}
