import { useEffect, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/react';
import { buttonClassName } from '@/Components/ui';
import LanguageSwitcher from '@/Components/LanguageSwitcher';
import ThemeToggle from '@/Components/ThemeToggle';
import { MenuIcon, X } from '@/Components/Icons';
import { useSystemSettings } from '@/hooks/useSystemSettings';
import { useBranding } from '@/hooks/useBranding';
import { usePublicText } from '@/court/i18n/usePublicText';
import type { PageProps } from '@/types';

export interface CourtPublicLink {
    key: string;
    label: string;
    href: string;
}

/**
 * The public pages' links. The home page and the resources page are still
 * Blade, so every link is a plain anchor (a full visit), never an Inertia one.
 */
export function useCourtPublicLinks(userManualEnabled: boolean): CourtPublicLink[] {
    const { t } = usePublicText();

    return [
        { key: 'home', label: t('cm.home.nav.home'), href: route('landing.home') },
        { key: 'resources', label: t('cm.home.nav.resources'), href: route('public.resources') },
        ...(userManualEnabled ? [{ key: 'manual', label: t('cm.home.nav.user_manual'), href: route('landing.user-manual') }] : []),
        { key: 'terms', label: t('cm.home.footer.terms_of_service'), href: route('public.terms') },
    ];
}

function pathOf(href: string): string {
    try {
        return new URL(href, window.location.origin).pathname;
    } catch {
        return href;
    }
}

/**
 * The public header, after the EUISIS PublicHeader: identity, the court's
 * public navigation, language and theme, and the applicant and respondent
 * portals. One breakpoint (`lg`) switches to the menu button and its dialog.
 */
export default function CourtPublicHeader({ userManualEnabled }: { userManualEnabled: boolean }) {
    const { t } = usePublicText();
    const page = usePage<PageProps>();
    const { getString } = useSystemSettings();
    const { brandName } = useBranding();
    const [menuOpen, setMenuOpen] = useState(false);
    const links = useCourtPublicLinks(userManualEnabled);
    const logoUrl = getString('general.identity_system_logo_url');
    const path = page.url.split('?')[0].split('#')[0] || '/';
    // Off the portals the shared `auth` is the staff (web) session.
    const user = page.props.auth?.user ?? null;

    useEffect(() => router.on('navigate', () => setMenuOpen(false)), []);

    useEffect(() => {
        const desktop = window.matchMedia('(min-width: 1024px)');
        const closeOnDesktop = () => { if (desktop.matches) setMenuOpen(false); };
        desktop.addEventListener('change', closeOnDesktop);
        return () => desktop.removeEventListener('change', closeOnDesktop);
    }, []);

    const isActive = (href: string) => pathOf(href) === path;

    const navLinkClass = (active: boolean) => [
        'relative inline-flex h-16 items-center px-3 text-sm font-medium transition-colors',
        'focus:outline-hidden focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary',
        active ? 'text-primary' : 'text-gray-600 hover:text-gray-900 dark:text-slate-300 dark:hover:text-white',
    ].join(' ');

    const portalLinks = (
        <>
            {user ? (
                <Link href={route('dashboard')} className={buttonClassName({ variant: 'ghost', size: 'sm' })}>
                    {t('nav.dashboard')}
                </Link>
            ) : null}
            <a href={route('applicant.login')} className={buttonClassName({ variant: 'outline', size: 'sm' })}>
                {t('cm.home.nav.applicant_portal')}
            </a>
            <a href={route('applicant.login', { login_as: 'respondent' })} className={buttonClassName({ variant: 'primary', size: 'sm' })}>
                {t('cm.home.nav.respondent_portal')}
            </a>
        </>
    );

    return (
        <header className="public-header sticky top-0 z-40 border-b border-gray-200 bg-white dark:border-slate-800 dark:bg-slate-950">
            <div aria-hidden="true" className="h-[3px] bg-accent" />

            <div className="mx-auto flex h-16 max-w-7xl items-center gap-4 px-4 sm:px-6 lg:px-8">
                <a
                    href={route('landing.home')}
                    className="flex min-h-11 min-w-0 items-center gap-2.5 rounded-control focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary"
                >
                    {logoUrl ? (
                        <img src={logoUrl} alt="" width={88} height={36} decoding="async" className="h-9 w-auto max-w-[88px] shrink-0 object-contain" />
                    ) : null}
                    <span className="min-w-0">
                        <span className="block truncate text-sm font-semibold text-gray-900 dark:text-slate-100">{brandName}</span>
                        <span className="hidden truncate text-[10px] font-semibold uppercase tracking-[0.2em] text-gray-500 sm:block dark:text-slate-400">
                            {t('cm.home.nav.agency_label')}
                        </span>
                    </span>
                </a>

                <nav aria-label={t('publicSite.mainNavigation')} className="hidden flex-1 justify-center lg:flex">
                    <ul className="flex items-center">
                        {links.map((link) => {
                            const active = isActive(link.href);
                            return (
                                <li key={link.key}>
                                    <a href={link.href} className={navLinkClass(active)} aria-current={active ? 'page' : undefined}>
                                        {link.label}
                                        {active && <span aria-hidden="true" className="absolute inset-x-3 bottom-0 h-0.5 bg-primary" />}
                                    </a>
                                </li>
                            );
                        })}
                    </ul>
                </nav>

                <div className="ms-auto hidden items-center gap-1.5 lg:flex">
                    <LanguageSwitcher />
                    <ThemeToggle />
                    {portalLinks}
                </div>

                <button
                    type="button"
                    onClick={() => setMenuOpen(true)}
                    aria-label={t('nav.openMenu')}
                    aria-haspopup="dialog"
                    aria-expanded={menuOpen}
                    aria-controls="court-public-mobile-menu"
                    className={buttonClassName({ variant: 'ghost', size: 'icon', className: 'ms-auto h-11! w-11! shrink-0 lg:hidden' })}
                >
                    <MenuIcon className="h-5 w-5" aria-hidden="true" />
                </button>
            </div>

            <Dialog open={menuOpen} onClose={setMenuOpen} className="relative z-50 lg:hidden">
                <div className="fixed inset-0 bg-black/40" aria-hidden="true" />
                <DialogPanel
                    id="court-public-mobile-menu"
                    className="fixed inset-y-0 right-0 flex w-full max-w-xs flex-col overflow-y-auto overscroll-contain bg-white pb-[env(safe-area-inset-bottom)] shadow-xl dark:bg-slate-950"
                >
                    <div className="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-gray-200 px-4 dark:border-slate-800">
                        <DialogTitle className="min-w-0 truncate text-sm font-semibold text-gray-900 dark:text-slate-100">{brandName}</DialogTitle>
                        <button
                            type="button"
                            onClick={() => setMenuOpen(false)}
                            aria-label={t('nav.closeMenu')}
                            className={buttonClassName({ variant: 'ghost', size: 'icon', className: 'h-11! w-11! shrink-0' })}
                        >
                            <X className="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>

                    <nav aria-label={t('publicSite.mainNavigation')} className="min-h-20 flex-1 overflow-y-auto overscroll-contain px-2 py-3">
                        <ul className="space-y-0.5">
                            {links.map((link) => {
                                const active = isActive(link.href);
                                return (
                                    <li key={link.key}>
                                        <a
                                            href={link.href}
                                            aria-current={active ? 'page' : undefined}
                                            className={[
                                                'flex min-h-[44px] items-center rounded-control px-3 py-2 text-sm font-medium wrap-anywhere',
                                                active ? 'bg-primary/10 text-primary' : 'text-gray-700 hover:bg-gray-100 dark:text-slate-200 dark:hover:bg-slate-800',
                                            ].join(' ')}
                                        >
                                            {link.label}
                                        </a>
                                    </li>
                                );
                            })}
                        </ul>
                    </nav>

                    <div className="shrink-0 space-y-3 border-t border-gray-200 p-4 dark:border-slate-800">
                        <div className="flex items-center justify-between gap-2">
                            <LanguageSwitcher />
                            <ThemeToggle />
                        </div>
                        <div className="flex flex-wrap gap-2">{portalLinks}</div>
                    </div>
                </DialogPanel>
            </Dialog>
        </header>
    );
}
