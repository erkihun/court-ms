import { ArrowRightOnRectangleIcon, ChevronRightIcon, XMarkIcon } from '@heroicons/react/24/outline';
import { Link, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { useShared } from '@/hooks/useShared';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import { headline, initials } from '@/lib/str';
import type { NavGroup, NavLink } from '@/types';
import { NavIcon } from '../NavIcon';

interface SidebarProps {
    compact: boolean;
    mobileOpen: boolean;
    onCloseMobile: () => void;
}

export function Sidebar({ compact, mobileOpen, onCloseMobile }: SidebarProps) {
    const { app, auth, navigation } = useShared();
    const user = auth.user;

    return (
        <aside
            className={cn(
                'fixed top-0 left-0 z-40 flex h-screen w-72 -translate-x-full flex-col bg-[#0c1527] font-sidebar transition-[transform,width] duration-300 md:translate-x-0',
                'border-r border-white/6 shadow-[1px_0_0_0_rgba(255,255,255,0.04),4px_0_24px_-4px_rgba(0,0,0,0.45)]',
                mobileOpen && 'translate-x-0',
                compact ? 'md:w-18' : 'md:w-64',
            )}
            aria-label={t('app.Sidebar')}
        >
            <div className={cn('flex items-center gap-2.5 border-b border-white/[0.07] px-3 py-3.5', compact && 'md:justify-center')}>
                <Link href={route('dashboard')} aria-label={t('app.Dashboard')} className="focus-ring shrink-0 rounded-lg">
                    {app.logoUrl ? (
                        <img src={app.logoUrl} alt={app.name} className="h-8 w-8 rounded-lg object-contain ring-1 ring-white/10" />
                    ) : (
                        <div className="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-linear-to-br from-blue-500 to-blue-700 shadow-lg ring-1 shadow-blue-900/40 ring-blue-400/30">
                            <span className="text-sm font-black text-white">{initials(app.shortName)}</span>
                        </div>
                    )}
                </Link>
                <div className={cn('min-w-0 flex-1', compact && 'md:hidden')}>
                    <div className="truncate text-[13px] leading-tight font-bold tracking-tight text-white">{app.name}</div>
                    <div className="mt-px truncate text-[10px] font-medium tracking-wide text-slate-500 uppercase">{t('ui.admin_portal')}</div>
                </div>
                <button
                    type="button"
                    onClick={onCloseMobile}
                    className="focus-ring inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-slate-500 transition-colors duration-150 hover:bg-white/8 hover:text-slate-200 md:hidden"
                    aria-label={t('app.Close sidebar')}
                >
                    <XMarkIcon className="h-4 w-4" />
                </button>
            </div>

            <div className="scrollbar-thin flex-1 overflow-x-hidden overflow-y-auto">
                <nav className="space-y-0.5 px-2.5 py-3">
                    {(navigation ?? []).map((section) => (
                        <div key={section.key} className="space-y-0.5">
                            {section.label && <div className={cn('sidebar-section-label', compact && 'md:hidden')}>{section.label}</div>}
                            {section.items.map((item) =>
                                item.type === 'group' ? (
                                    <SidebarGroup key={item.key} group={item} compact={compact} />
                                ) : (
                                    <SidebarLink key={item.key} link={item} compact={compact} />
                                ),
                            )}
                        </div>
                    ))}
                </nav>
            </div>

            {user && (
                <div className="sidebar-user-card">
                    <div className={cn('flex items-center gap-2.5', compact && 'md:justify-center')}>
                        {user.avatarUrl ? (
                            <img src={user.avatarUrl} alt={user.name} className="h-8 w-8 shrink-0 rounded-full object-cover shadow-md ring-2 ring-white/10" />
                        ) : (
                            <div className="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-linear-to-br from-slate-600 to-slate-800 shadow-md ring-1 ring-white/10">
                                <span className="text-xs font-bold text-slate-200">{initials(user.name)}</span>
                            </div>
                        )}
                        <div className={cn('min-w-0 flex-1', compact && 'md:hidden')}>
                            <div className="truncate text-[12.5px] leading-tight font-semibold text-slate-200">{user.name}</div>
                            <div className="mt-px truncate text-[10.5px] text-slate-500 capitalize">
                                {user.activeRole?.label ?? headline('administrator')}
                            </div>
                        </div>
                        <button
                            type="button"
                            onClick={() => router.post(route('logout'))}
                            className={cn(
                                'focus-ring inline-flex h-7 w-7 items-center justify-center rounded-md text-slate-600 transition-colors duration-150 hover:bg-rose-500/10 hover:text-rose-400',
                                compact && 'md:hidden',
                            )}
                            title={t('app.Logout')}
                            aria-label={t('app.Logout')}
                        >
                            <ArrowRightOnRectangleIcon className="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>
            )}
        </aside>
    );
}

function SidebarLink({ link, compact }: { link: NavLink; compact: boolean }) {
    return (
        <Link
            href={link.href}
            className={cn('sidebar-menu-item focus-ring', link.active ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive', compact && 'md:justify-center')}
            aria-current={link.active ? 'page' : undefined}
            title={compact ? link.label : undefined}
        >
            <span className="grid w-6 place-items-center" aria-hidden="true">
                <NavIcon name={link.icon} className="h-4 w-4 text-blue-100" />
            </span>
            <span className={cn('sidebar-menu-label truncate', compact && 'md:hidden')}>{link.label}</span>
        </Link>
    );
}

function SidebarGroup({ group, compact }: { group: NavGroup; compact: boolean }) {
    const [open, setOpen] = useState(group.active);

    // The layout persists between visits: open the group when navigation lands inside it.
    useEffect(() => {
        if (group.active) setOpen(true);
    }, [group.active]);

    return (
        <div className="space-y-1">
            <button
                type="button"
                onClick={() => setOpen((value) => !value)}
                className={cn('sidebar-menu-toggle focus-ring', group.active ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive', compact && 'md:justify-center')}
                aria-expanded={open}
                title={compact ? group.label : undefined}
            >
                <span className="flex items-center gap-3">
                    <span className="grid w-6 place-items-center" aria-hidden="true">
                        <NavIcon name={group.icon} className="h-4 w-4 text-blue-100" />
                    </span>
                    <span className={cn('sidebar-menu-label truncate', compact && 'md:hidden')}>{group.label}</span>
                </span>
                <ChevronRightIcon className={cn('h-4 w-4 text-blue-100 transition-transform duration-150', open && 'rotate-90', compact && 'md:hidden')} />
            </button>
            {open && (
                <div className={cn('space-y-1 ps-11', compact && 'md:hidden')}>
                    {group.children.map((child) => (
                        <Link
                            key={child.key}
                            href={child.href}
                            className={cn('sidebar-submenu-item focus-ring', child.active ? 'sidebar-submenu-item-active' : 'sidebar-submenu-item-inactive')}
                            aria-current={child.active ? 'page' : undefined}
                        >
                            <NavIcon name={child.icon} className="h-4 w-4 text-blue-100" />
                            <span>{child.label}</span>
                        </Link>
                    ))}
                </div>
            )}
        </div>
    );
}

