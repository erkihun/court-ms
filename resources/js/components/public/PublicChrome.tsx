import { Bars3Icon, EnvelopeIcon, MapPinIcon, PhoneIcon, ScaleIcon, SwatchIcon, XMarkIcon } from '@heroicons/react/24/outline';
import { Link, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { useDismiss } from '@/hooks/useDismiss';
import { useShared } from '@/hooks/useShared';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Locale } from '@/types';

/** Page props the public chrome reads when the landing page provides them. */
export interface PublicFooterContent {
    description: string;
    phone: string | null;
    email: string | null;
    address: string | null;
    social: Array<{ network: string; url: string }>;
}

interface ChromeProps {
    footer?: PublicFooterContent;
    userManual?: { url: string; label: string } | null;
    [key: string]: unknown;
}

const SECTIONS = ['home', 'process', 'services', 'resources', 'faq'] as const;

/** In-page anchors on the landing page; elsewhere they lead back to it. */
function useSectionLinks() {
    const onHome = usePage().component === 'Public/Home';
    const home = route('landing.home');

    return SECTIONS.map((section) => ({ key: section, label: t(`home.nav.${section}`), href: onHome ? `#${section}` : `${home}#${section}` }));
}

function Brand({ inverted = false }: { inverted?: boolean }) {
    const { app } = useShared();

    return (
        <Link href={route('landing.home')} className="flex shrink-0 items-center gap-3">
            {app.logoUrl ? (
                <img src={app.logoUrl} alt="" className="h-10 w-10 rounded-xl object-contain" />
            ) : (
                <span
                    className={cn(
                        'flex h-10 w-10 items-center justify-center rounded-xl ring-1',
                        inverted ? 'bg-white/10 ring-white/15' : 'pub-ac-soft ring-slate-200',
                    )}
                >
                    <ScaleIcon className="h-5 w-5" />
                </span>
            )}
            <span>
                <span className={cn('hidden text-[10px] font-semibold tracking-[0.3em] uppercase sm:block', inverted ? 'pub-ac-text' : 'text-slate-400')}>
                    {t('home.nav.agency_label')}
                </span>
                <span className={cn('block text-sm leading-none font-semibold', inverted ? 'text-white' : 'text-slate-900')}>{app.name}</span>
            </span>
        </Link>
    );
}

/** EN / AM switch for the light public pages (full reload, like the app-wide switch). */
export function PublicLanguageSwitch({ dark = false, className }: { dark?: boolean; className?: string }) {
    const { app } = useShared();
    if (!app.showLanguageSwitcher) return null;

    return (
        <div
            role="group"
            aria-label={t('app.Language')}
            className={cn('inline-flex items-center gap-1 rounded-full p-0.5', !dark && 'border border-slate-200 bg-slate-50', className)}
        >
            {(['en', 'am'] as Locale[]).map((code) => (
                <a
                    key={code}
                    lang={code}
                    href={route('language.switch', { locale: code, return: window.location.href })}
                    aria-current={app.locale === code ? 'true' : undefined}
                    className={cn(
                        'rounded-full px-3 py-1 text-xs font-semibold transition',
                        app.locale === code ? 'pub-ac-btn' : dark ? 'text-slate-400 hover:text-white' : 'text-slate-500 hover:bg-slate-100',
                    )}
                >
                    {code.toUpperCase()}
                </a>
            ))}
        </div>
    );
}

export function PublicHeader() {
    const { userManual } = usePage<ChromeProps>().props;
    const links = useSectionLinks();
    const [open, setOpen] = useState(false);

    const portalLinks = (
        <>
            <a
                href={route('applicant.login')}
                className="rounded-full border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
            >
                {t('home.nav.applicant_portal')}
            </a>
            <a
                href={route('applicant.login', { login_as: 'respondent' })}
                className="pub-ac-btn rounded-full px-4 py-1.5 text-xs font-semibold shadow-xs transition"
            >
                {t('home.nav.respondent_portal')}
            </a>
        </>
    );

    return (
        <header className="pub-no-print sticky top-0 z-50 border-b border-slate-200 bg-white/85 backdrop-blur-xl">
            <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <Brand />

                <nav className="hidden items-center gap-0.5 lg:flex" aria-label={t('home.accessibility.main_navigation')}>
                    {links.map((link) => (
                        <a
                            key={link.key}
                            href={link.href}
                            className="rounded-full px-3 py-1.5 text-sm text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                        >
                            {link.label}
                        </a>
                    ))}
                    {userManual && (
                        <a href={userManual.url} className="rounded-full px-3 py-1.5 text-sm text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
                            {userManual.label}
                        </a>
                    )}
                </nav>

                <div className="flex items-center gap-2">
                    <div className="hidden sm:block">
                        <PublicLanguageSwitch />
                    </div>
                    <div className="hidden items-center gap-2 md:flex">{portalLinks}</div>
                    <button
                        type="button"
                        onClick={() => setOpen((value) => !value)}
                        aria-expanded={open}
                        aria-controls="public-mobile-menu"
                        aria-label={open ? t('home.accessibility.close_menu') : t('home.accessibility.menu_toggle')}
                        className="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 lg:hidden"
                    >
                        {open ? <XMarkIcon className="h-5 w-5" /> : <Bars3Icon className="h-5 w-5" />}
                    </button>
                </div>
            </div>

            {open && (
                <div id="public-mobile-menu" className="space-y-1 border-t border-slate-200 bg-white px-4 pt-3 pb-4 lg:hidden">
                    {links.map((link) => (
                        <a
                            key={link.key}
                            href={link.href}
                            onClick={() => setOpen(false)}
                            className="block rounded-lg px-3 py-2.5 text-sm text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                        >
                            {link.label}
                        </a>
                    ))}
                    {userManual && (
                        <a
                            href={userManual.url}
                            className="block rounded-lg px-3 py-2.5 text-sm text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                        >
                            {userManual.label}
                        </a>
                    )}
                    <div className="flex flex-wrap items-center justify-center gap-2 border-t border-slate-200 pt-3">
                        <PublicLanguageSwitch />
                        {portalLinks}
                    </div>
                </div>
            )}
        </header>
    );
}

const SOCIAL_LABELS: Record<string, string> = {
    facebook: 'Facebook',
    twitter: 'X (Twitter)',
    linkedin: 'LinkedIn',
    youtube: 'YouTube',
    telegram: 'Telegram',
    instagram: 'Instagram',
};

export function PublicFooter() {
    const { app } = useShared();
    const { footer } = usePage<ChromeProps>().props;
    const links = useSectionLinks();
    const year = new Date().getFullYear();
    const linkClass = 'text-sm text-slate-400 transition hover:text-orange-300';

    return (
        <footer className="bg-slate-950 text-slate-300">
            <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                    <div className="lg:col-span-2">
                        <Brand inverted />
                        <p className="mt-4 max-w-sm text-sm leading-relaxed text-slate-400">{footer?.description ?? t('home.footer.description')}</p>
                        {footer && (footer.phone || footer.email || footer.address) && (
                            <ul className="mt-4 space-y-1.5 text-xs text-slate-400">
                                {footer.phone && (
                                    <li className="flex items-center gap-2">
                                        <PhoneIcon className="h-3.5 w-3.5" />
                                        <a href={`tel:${footer.phone}`} className="hover:text-orange-300">
                                            {footer.phone}
                                        </a>
                                    </li>
                                )}
                                {footer.email && (
                                    <li className="flex items-center gap-2">
                                        <EnvelopeIcon className="h-3.5 w-3.5" />
                                        <a href={`mailto:${footer.email}`} className="hover:text-orange-300">
                                            {footer.email}
                                        </a>
                                    </li>
                                )}
                                {footer.address && (
                                    <li className="flex items-center gap-2">
                                        <MapPinIcon className="h-3.5 w-3.5" />
                                        <span>{footer.address}</span>
                                    </li>
                                )}
                            </ul>
                        )}
                        {footer && footer.social.length > 0 && (
                            <ul className="mt-4 flex flex-wrap items-center gap-2">
                                {footer.social.map((item) => (
                                    <li key={item.network}>
                                        <a
                                            href={item.url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="inline-flex rounded-lg bg-white/5 px-2.5 py-1 text-xs text-slate-300 transition hover:bg-white/10 hover:text-orange-300"
                                        >
                                            {SOCIAL_LABELS[item.network] ?? item.network}
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        )}
                        <div className="mt-4 flex flex-wrap items-center gap-4">
                            <Link href={route('public.terms')} className={linkClass}>
                                {t('home.footer.terms_of_service')}
                            </Link>
                            <Link href={route('public.signage')} className={linkClass}>
                                {t('home.landing.public_signage')}
                            </Link>
                        </div>
                    </div>

                    <nav aria-label={t('home.footer.quick_links')}>
                        <h2 className="mb-4 text-xs font-semibold tracking-widest text-slate-400 uppercase">{t('home.footer.quick_links')}</h2>
                        <ul className="space-y-2">
                            {links.map((link) => (
                                <li key={link.key}>
                                    <a href={link.href} className={linkClass}>
                                        {link.label}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </nav>

                    <nav aria-label={t('home.footer.portals')}>
                        <h2 className="mb-4 text-xs font-semibold tracking-widest text-slate-400 uppercase">{t('home.footer.portals')}</h2>
                        <ul className="space-y-2">
                            <li>
                                <a href={route('applicant.login')} className={linkClass}>
                                    {t('home.nav.applicant_portal')}
                                </a>
                            </li>
                            <li>
                                <a href={route('applicant.register')} className={linkClass}>
                                    {t('respondent.register_action')}
                                </a>
                            </li>
                            <li>
                                <a href={route('applicant.login', { login_as: 'respondent' })} className={linkClass}>
                                    {t('home.nav.respondent_portal')}
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>

                <div className="mt-10 flex flex-col items-center justify-between gap-4 border-t border-white/10 pt-6 text-xs text-slate-400 sm:flex-row">
                    <p>
                        © {year} {app.name}. {t('home.footer.rights')}
                    </p>
                    <PublicLanguageSwitch dark />
                </div>
            </div>
        </footer>
    );
}

const ACCENTS = [
    { key: 'blue', hex: '#2563eb', label: 'Blue' },
    { key: 'teal', hex: '#0d9488', label: 'Teal' },
    { key: 'emerald', hex: '#059669', label: 'Emerald' },
    { key: 'violet', hex: '#7c3aed', label: 'Violet' },
    { key: 'rose', hex: '#e11d48', label: 'Rose' },
] as const;

/** Accent colour picker; the choice is shared with the portals through localStorage 'accent'. */
export function AccentSwitcher() {
    const root = useRef<HTMLDivElement>(null);
    const [open, setOpen] = useState(false);
    const [accent, setAccent] = useState(() => document.documentElement.dataset.accent ?? 'blue');
    useDismiss(root, open, () => setOpen(false));

    useEffect(() => {
        document.documentElement.dataset.accent = accent;
        try {
            localStorage.setItem('accent', accent);
        } catch {
            // Storage blocked: the choice lasts for this page only.
        }
    }, [accent]);

    return (
        <div ref={root} className="fixed end-4 bottom-4 z-40 sm:end-6 sm:bottom-6">
            {open && (
                <div className="absolute end-0 bottom-14 w-52 rounded-2xl border border-slate-200 bg-white p-4 shadow-xl">
                    <p className="mb-2.5 text-[10px] font-semibold tracking-widest text-slate-400 uppercase">{t('home.accessibility.accent_color')}</p>
                    <div className="grid grid-cols-5 gap-2">
                        {ACCENTS.map((item) => (
                            <button
                                key={item.key}
                                type="button"
                                onClick={() => setAccent(item.key)}
                                aria-pressed={accent === item.key}
                                title={item.label}
                                className={cn(
                                    'h-8 w-8 rounded-full ring-2 ring-offset-2 transition hover:scale-110',
                                    accent === item.key ? 'scale-110 ring-slate-900' : 'ring-transparent',
                                )}
                                style={{ backgroundColor: item.hex }}
                            >
                                <span className="sr-only">{item.label}</span>
                            </button>
                        ))}
                    </div>
                </div>
            )}
            <button
                type="button"
                onClick={() => setOpen((value) => !value)}
                aria-expanded={open}
                aria-label={t('home.accessibility.accent_color')}
                className="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-600 shadow-lg ring-1 ring-slate-200 transition hover:bg-slate-50"
            >
                <SwatchIcon className="h-5 w-5" />
            </button>
        </div>
    );
}
