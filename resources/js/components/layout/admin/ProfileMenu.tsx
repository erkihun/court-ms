import { ArrowRightOnRectangleIcon, ChevronDownIcon, UserIcon } from '@heroicons/react/24/outline';
import { Link, router } from '@inertiajs/react';
import { Dropdown } from '@/components/ui/Dropdown';
import { useShared } from '@/hooks/useShared';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import { initials } from '@/lib/str';
import { LanguageSwitch } from '../LanguageSwitch';

function Avatar({ name, url, size }: { name: string; url: string | null; size: 'sm' | 'md' }) {
    const box = size === 'sm' ? 'h-8 w-8 text-[13px]' : 'h-9 w-9 text-sm';

    return url ? (
        <img src={url} alt="" className={cn(box, 'shrink-0 rounded-full object-cover ring-2 ring-slate-200 dark:ring-slate-700')} />
    ) : (
        <div className={cn(box, 'grid shrink-0 place-items-center rounded-full bg-linear-to-br from-blue-500 to-blue-700 font-bold text-white ring-2 ring-blue-200 dark:ring-blue-900')} aria-hidden="true">
            {initials(name)}
        </div>
    );
}

export function ProfileMenu() {
    const { auth } = useShared();
    const user = auth.user;
    if (!user) return null;

    const roleLabel = user.activeRole?.label ?? 'Administrator';

    return (
        <Dropdown
            panelClassName="w-60"
            trigger={({ open, toggle }) => (
                <button type="button" onClick={toggle} className="topnav-profile-btn" aria-haspopup="menu" aria-expanded={open}>
                    <Avatar name={user.name} url={user.avatarUrl} size="sm" />
                    <span className="hidden min-w-0 text-start sm:block">
                        <span className="block max-w-28 truncate text-[12.5px] font-semibold text-slate-800 dark:text-slate-100">{user.name}</span>
                        <span className="block max-w-28 truncate text-[10.5px] text-slate-400 capitalize dark:text-slate-500">{roleLabel}</span>
                    </span>
                    <ChevronDownIcon className={cn('hidden h-3.5 w-3.5 shrink-0 text-slate-400 transition-transform duration-200 sm:block dark:text-slate-500', open && 'rotate-180')} />
                </button>
            )}
        >
            {(close) => (
                <div role="menu">
                    <div className="flex items-center gap-3 border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                        <Avatar name={user.name} url={user.avatarUrl} size="md" />
                        <div className="min-w-0">
                            <div className="truncate text-[13px] font-semibold text-slate-800 dark:text-slate-100">{user.name}</div>
                            <div className="truncate text-[11px] text-slate-400 dark:text-slate-500">{user.email}</div>
                        </div>
                    </div>

                    {user.roles.length > 1 && (
                        <div className="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                            <label htmlFor="admin-active-role" className="mb-1.5 block text-[10px] font-semibold tracking-wider text-slate-400 uppercase dark:text-slate-500">
                                {t('app.active_role')}
                            </label>
                            <select
                                id="admin-active-role"
                                value={user.activeRole?.id ?? ''}
                                onChange={(event) => router.post(route('admin.roles.switch'), { role_id: Number(event.target.value) })}
                                className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-[12px] font-semibold text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:focus:ring-blue-900"
                            >
                                {user.roles.map((role) => (
                                    <option key={role.id} value={role.id}>
                                        {role.label}
                                    </option>
                                ))}
                            </select>
                            <p className="mt-1.5 text-[10.5px] leading-4 text-slate-400 dark:text-slate-500">{t('app.role_switch_help')}</p>
                        </div>
                    )}

                    <div className="space-y-0.5 p-1.5" role="none">
                        <Link
                            href={route('profile.edit')}
                            onClick={close}
                            role="menuitem"
                            className="focus-ring flex w-full items-center gap-3 rounded-lg px-3 py-2 text-[13px] text-slate-700 transition-colors duration-100 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            <span className="grid h-6 w-6 shrink-0 place-items-center rounded-md bg-blue-50 dark:bg-blue-950/50">
                                <UserIcon className="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" />
                            </span>
                            {t('app.Profile')}
                        </Link>
                        <div className="px-3 py-2 sm:hidden">
                            <p className="mb-2 text-[10px] font-semibold tracking-wider text-slate-400 uppercase dark:text-slate-500">{t('app.Language')}</p>
                            <LanguageSwitch className="w-fit" />
                        </div>
                    </div>

                    <div className="border-t border-slate-100 p-1.5 dark:border-slate-800">
                        <button
                            type="button"
                            role="menuitem"
                            onClick={() => router.post(route('logout'))}
                            className="focus-ring flex w-full items-center gap-3 rounded-lg px-3 py-2 text-[13px] text-rose-600 transition-colors duration-100 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/30"
                        >
                            <span className="grid h-6 w-6 shrink-0 place-items-center rounded-md bg-rose-50 dark:bg-rose-950/40">
                                <ArrowRightOnRectangleIcon className="h-3.5 w-3.5 text-rose-500 dark:text-rose-400" />
                            </span>
                            {t('app.Logout')}
                        </button>
                    </div>
                </div>
            )}
        </Dropdown>
    );
}
