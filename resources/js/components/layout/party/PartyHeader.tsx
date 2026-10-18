import {
    ArrowRightOnRectangleIcon,
    ArrowsRightLeftIcon,
    Bars3Icon,
    BriefcaseIcon,
    ChatBubbleLeftRightIcon,
    ChevronDownIcon,
    DocumentPlusIcon,
    HomeIcon,
    MagnifyingGlassIcon,
    UserIcon,
    XMarkIcon,
} from '@heroicons/react/24/outline';
import { Link, router } from '@inertiajs/react';
import { type ComponentType, type SVGProps, useEffect, useState } from 'react';
import { ThemeToggle } from '@/components/layout/ThemeToggle';
import { Dropdown } from '@/components/ui/Dropdown';
import { useRouteIs } from '@/hooks/useRouteIs';
import { useShared } from '@/hooks/useShared';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import { initials } from '@/lib/str';
import type { Locale } from '@/types';
import { ApplicantBell } from './ApplicantBell';
import { RespondentBell } from './RespondentBell';

interface NavItem {
    href: string;
    label: string;
    Icon: ComponentType<SVGProps<SVGSVGElement>>;
    active: string[];
}

const languages: Array<{ code: Locale; short: string; long: string }> = [
    { code: 'en', short: 'EN', long: 'English' },
    { code: 'am', short: 'አማ', long: 'አማርኛ' },
];

function navItems(respondent: boolean): NavItem[] {
    return respondent
        ? [
              { href: route('respondent.dashboard'), label: t('respondent.dashboard'), Icon: HomeIcon, active: ['respondent.dashboard'] },
              { href: route('respondent.case.search'), label: t('respondent.find_case'), Icon: MagnifyingGlassIcon, active: ['respondent.case.search'] },
              { href: route('respondent.cases.my'), label: t('respondent.my_cases'), Icon: BriefcaseIcon, active: ['respondent.cases.*'] },
              { href: route('respondent.responses.index'), label: t('respondent.my_responses'), Icon: ChatBubbleLeftRightIcon, active: ['respondent.responses.*'] },
          ]
        : [
              { href: route('applicant.dashboard'), label: t('app.home'), Icon: HomeIcon, active: ['applicant.dashboard'] },
              {
                  href: route('applicant.cases.index'),
                  label: t('app.my_cases'),
                  Icon: BriefcaseIcon,
                  active: ['applicant.cases.index', 'applicant.cases.show', 'applicant.cases.edit'],
              },
              { href: route('applicant.cases.create'), label: t('app.new_case'), Icon: DocumentPlusIcon, active: ['applicant.cases.create'] },
          ];
}

/** Sticky header of the applicant / respondent portal. */
export function PartyHeader() {
    const { app, auth } = useShared();
    const routeIs = useRouteIs();
    const [mobileOpen, setMobileOpen] = useState(false);
    const applicant = auth.applicant;
    const respondent = applicant?.actingAsRespondent ?? false;
    const items = applicant ? navItems(respondent) : [];
    const displayName = applicant?.firstName.split(/\s+/)[0] || (respondent ? t('respondent.respondent_label') : t('app.profile'));
    const home = applicant ? route(respondent ? 'respondent.dashboard' : 'applicant.dashboard') : route('applicant.login');
    const profileHref = route(respondent ? 'respondent.profile.edit' : 'applicant.profile.edit');
    const logout = () => router.post(route(respondent ? 'respondent.logout' : 'applicant.logout'));
    const switchRole = () => router.post(route(respondent ? 'respondent.switchToApplicant' : 'applicant.switchToRespondent'));
    const languageHref = (code: Locale) => route('language.switch', { locale: code, return: window.location.href });

    // The layout persists between visits: close the mobile menu when the page changes.
    useEffect(() => router.on('navigate', () => setMobileOpen(false)), []);

    return (
        <header
            className="sticky top-0 z-40 border-b backdrop-blur-md"
            style={{ borderColor: 'var(--border)', background: 'color-mix(in srgb, var(--surface-strong) 88%, transparent)' }}
        >
            <div className="mx-auto flex h-14 max-w-[1600px] items-center justify-between gap-3 px-4">
                <Link href={home} className="focus-ring flex shrink-0 items-center gap-2.5 rounded-lg">
                    {app.logoUrl ? (
                        <img src={app.logoUrl} alt={app.name} className="h-8 w-auto object-contain" />
                    ) : (
                        <span className="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[rgb(var(--ac))] text-xs font-bold tracking-wide text-white uppercase">
                            {initials(app.shortName, 2)}
                        </span>
                    )}
                    <span className="hidden max-w-[200px] truncate text-sm leading-tight font-bold text-(--text) sm:block">{app.name}</span>
                </Link>

                <div className="flex items-center gap-1.5">
                    <ThemeToggle buttonClassName="ap-icon-btn" />

                    <nav className="hidden items-center gap-0.5 md:flex" aria-label={t('app.menu')}>
                        {items.map(({ href, label, Icon, active }) => (
                            <Link key={href} href={href} className={cn('ap-navlink', routeIs(...active) && 'ap-navlink-active')} aria-current={routeIs(...active) ? 'page' : undefined}>
                                <Icon className="h-4 w-4" />
                                {label}
                            </Link>
                        ))}

                        {app.showLanguageSwitcher && (
                            <div className="ap-lang-switch mx-1" role="group" aria-label={t('app.Language')}>
                                {languages.map(({ code, short }) => (
                                    <a key={code} href={languageHref(code)} lang={code} className={cn('ap-lang-opt', app.locale === code && 'active')} aria-current={app.locale === code ? 'true' : undefined}>
                                        {short}
                                    </a>
                                ))}
                            </div>
                        )}

                        {applicant ? (
                            <>
                                {respondent ? <RespondentBell /> : <ApplicantBell />}
                                <button type="button" onClick={switchRole} className="ap-navlink-orange">
                                    <ArrowsRightLeftIcon className="h-4 w-4" />
                                    {t(respondent ? 'app.switch_to_applicant' : 'app.switch_to_respondent')}
                                </button>
                                <Dropdown
                                    className="ml-0.5"
                                    panelClassName="w-52 py-1"
                                    trigger={({ open, toggle }) => (
                                        <button type="button" onClick={toggle} className={cn('ap-profile-btn', open && 'ap-profile-btn-active')} aria-haspopup="menu" aria-expanded={open}>
                                            <UserIcon className="h-4 w-4 shrink-0" />
                                            <span className="max-w-[100px] truncate">{displayName}</span>
                                            <ChevronDownIcon className="h-3 w-3 shrink-0" />
                                        </button>
                                    )}
                                >
                                    {(close) => (
                                        <div role="menu">
                                            <Link href={profileHref} onClick={close} className="ap-dropdown-item" role="menuitem">
                                                <UserIcon className="h-4 w-4 text-slate-400" />
                                                {t(respondent ? 'respondent.profile' : 'app.profile')}
                                            </Link>
                                            <div className="ap-dropdown-divider" />
                                            <button type="button" onClick={logout} className="ap-dropdown-item text-rose-600 hover:bg-rose-50" role="menuitem">
                                                <ArrowRightOnRectangleIcon className="h-4 w-4" />
                                                {t('app.logout')}
                                            </button>
                                        </div>
                                    )}
                                </Dropdown>
                            </>
                        ) : (
                            <>
                                <Link href={route('applicant.register')} className="ap-navlink-cta">
                                    {t('app.register')}
                                </Link>
                                <Link href={route('applicant.login')} className="ap-navlink">
                                    {t('app.login')}
                                </Link>
                            </>
                        )}
                    </nav>

                    <button
                        type="button"
                        onClick={() => setMobileOpen((open) => !open)}
                        className="ap-icon-btn md:hidden"
                        aria-label={t('app.menu')}
                        aria-expanded={mobileOpen}
                        aria-controls="party-mobile-menu"
                    >
                        {mobileOpen ? <XMarkIcon className="h-5 w-5" /> : <Bars3Icon className="h-5 w-5" />}
                    </button>
                </div>
            </div>

            {mobileOpen && (
                <div id="party-mobile-menu" className="overflow-hidden border-t md:hidden" style={{ borderColor: 'var(--border)', background: 'var(--surface-strong)' }}>
                    <div className="mx-auto max-h-[80vh] max-w-[1600px] overflow-y-auto px-4 py-3">
                        <ul className="space-y-0.5">
                            {app.showLanguageSwitcher && (
                                <li className="mb-1 border-b pb-2" style={{ borderColor: 'var(--border)' }}>
                                    <div className="ap-mobile-section">{t('app.language')}</div>
                                    <div className="flex gap-2 px-1">
                                        {languages.map(({ code, long }) => (
                                            <a key={code} href={languageHref(code)} lang={code} className={cn('ap-mobile-lang', app.locale === code && 'ap-mobile-lang-active')}>
                                                {long}
                                            </a>
                                        ))}
                                    </div>
                                </li>
                            )}

                            {items.map(({ href, label, Icon, active }) => (
                                <li key={href}>
                                    <Link href={href} className={cn('ap-mobile-link', routeIs(...active) && 'ap-mobile-link-active')}>
                                        <Icon className="h-4 w-4" />
                                        {label}
                                    </Link>
                                </li>
                            ))}

                            {applicant ? (
                                <>
                                    {!respondent && (
                                        <li>
                                            <Link href={route('applicant.notifications.index')} className="ap-mobile-link">
                                                {t('app.notifications')}
                                            </Link>
                                        </li>
                                    )}
                                    <li className="mt-1 border-t border-(--border) pt-1">
                                        <button type="button" onClick={switchRole} className="ap-mobile-link ap-mobile-link-warn">
                                            <ArrowsRightLeftIcon className="h-4 w-4" />
                                            {t(respondent ? 'app.switch_to_applicant' : 'app.switch_to_respondent')}
                                        </button>
                                    </li>
                                    <li className="mt-1 border-t border-(--border) pt-1">
                                        <div className="ap-mobile-section">{displayName}</div>
                                        <Link href={profileHref} className="ap-mobile-link">
                                            <UserIcon className="h-4 w-4" />
                                            {t(respondent ? 'respondent.profile' : 'app.profile')}
                                        </Link>
                                        <button type="button" onClick={logout} className="ap-mobile-link ap-mobile-link-danger">
                                            <ArrowRightOnRectangleIcon className="h-4 w-4" />
                                            {t('app.logout')}
                                        </button>
                                    </li>
                                </>
                            ) : (
                                <>
                                    <li>
                                        <Link href={route('applicant.register')} className="ap-mobile-link">
                                            {t('app.register')}
                                        </Link>
                                    </li>
                                    <li>
                                        <Link href={route('applicant.login')} className="ap-mobile-link">
                                            {t('app.login')}
                                        </Link>
                                    </li>
                                </>
                            )}
                        </ul>
                    </div>
                </div>
            )}
        </header>
    );
}
