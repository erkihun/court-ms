import { useLocale } from '@/hooks/useLocale';
import { useSystemSettings } from '@/hooks/useSystemSettings';
import { useBranding } from '@/hooks/useBranding';
import { usePublicText } from '@/court/i18n/usePublicText';
import { useCourtPublicLinks } from '@/court/public/CourtPublicHeader';
import type { LandingFooter } from '@/court/types/public';

/** The landing manager's social-media fields, in the order the Blade footer form lists them. */
export const SOCIAL_LINKS: Array<{ field: keyof LandingFooter; label: string; icon: string }> = [
    { field: 'social_facebook', label: 'Facebook', icon: 'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z' },
    { field: 'social_twitter', label: 'Twitter / X', icon: 'M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z' },
    { field: 'social_linkedin', label: 'LinkedIn', icon: 'M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z M2 6.5a2 2 0 114 0 2 2 0 01-4 0z' },
    { field: 'social_youtube', label: 'YouTube', icon: 'M22.54 6.42a2.78 2.78 0 00-1.94-1.96C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 00-1.94 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.4 19.54C5.12 20 12 20 12 20s6.88 0 8.6-.46a2.78 2.78 0 001.94-1.96A29 29 0 0023 12a29 29 0 00-.46-5.58zM9.75 15.02V8.98L15.5 12l-5.75 3.02z' },
    { field: 'social_telegram', label: 'Telegram', icon: 'M22 2L11 13M22 2L15 22l-4-9-9-4 20-7z' },
    { field: 'social_instagram', label: 'Instagram', icon: 'M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zM17.5 6.5h.01M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 01-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 017.8 2z' },
];

const linkClass = 'inline-flex min-h-11 max-w-full items-center py-2 text-sm text-white/75 wrap-anywhere hover:text-white hover:underline focus-visible:outline-solid focus-visible:outline-2 focus-visible:outline-offset-2 lg:min-h-8 lg:py-1';
const heading = 'mb-3 text-sm font-semibold text-white';

/**
 * The public footer, after the EUISIS PublicFooter: what the court is, its
 * pages and portals, and the contact details and social links saved in the
 * landing-page manager (the same footer settings the home page shows). A
 * column renders only when it has content; nothing is filled with placeholders.
 */
export default function CourtPublicFooter({ footer, userManualEnabled, userManualTitle = null }: {
    footer: LandingFooter | null;
    userManualEnabled: boolean;
    userManualTitle?: string | null;
}) {
    const { t } = useLocale();
    const { text } = usePublicText();
    const { getString } = useSystemSettings();
    const { brandName } = useBranding();
    const links = useCourtPublicLinks(userManualEnabled, userManualTitle);

    const organization = getString('general.organization_name') || brandName;
    const description = footer?.description || t('cm.home.footer.description');
    const email = footer?.contact_email || getString('general.support_email');
    const phone = footer?.contact_phone || getString('general.support_phone');
    const address = footer?.contact_address || '';
    const socials = SOCIAL_LINKS.filter((social) => Boolean(footer?.[social.field]));

    return (
        <footer className="bg-primary-900 pb-[env(safe-area-inset-bottom)] text-white wrap-anywhere dark:bg-primary-950">
            <div className="mx-auto grid max-w-7xl grid-cols-1 gap-6 px-4 py-8 sm:grid-cols-2 sm:gap-8 sm:px-6 sm:py-10 lg:grid-cols-[2fr_1.4fr_1.4fr_1.6fr] lg:gap-12 lg:px-8 *:min-w-0">
                <div className="sm:col-span-2 lg:col-span-1">
                    <p className="text-[10px] font-semibold uppercase tracking-[0.3em] text-white/60">{t('cm.home.footer.agency_label')}</p>
                    <p className="mt-1 text-base font-bold text-white">{organization}</p>
                    <p className="mt-2 max-w-md text-sm leading-relaxed text-white/75">{description}</p>
                    {socials.length > 0 && (
                        <ul className="mt-4 flex flex-wrap gap-2" aria-label={text('follow_us')}>
                            {socials.map((social) => (
                                <li key={social.field}>
                                    <a
                                        href={footer?.[social.field] ?? '#'}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        aria-label={social.label}
                                        className="flex h-10 w-10 items-center justify-center rounded-lg border border-white/20 text-white/80 hover:bg-white/10 hover:text-white focus-visible:outline-solid focus-visible:outline-2 focus-visible:outline-offset-2"
                                    >
                                        <svg className="h-4 w-4" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24" aria-hidden="true">
                                            <path strokeLinecap="round" strokeLinejoin="round" d={social.icon} />
                                        </svg>
                                    </a>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>

                <nav aria-label={t('cm.home.footer.quick_links')}>
                    <p className={heading}>{t('cm.home.footer.quick_links')}</p>
                    <ul>
                        {links.map((link) => (
                            <li key={link.key}><a href={link.href} className={linkClass}>{link.label}</a></li>
                        ))}
                        <li><a href={route('public.signage')} className={linkClass}>{t('cm.home.landing.public_signage')}</a></li>
                    </ul>
                </nav>

                <nav aria-label={t('cm.home.footer.portals')}>
                    <p className={heading}>{t('cm.home.footer.portals')}</p>
                    <ul>
                        <li><a href={route('applicant.login')} className={linkClass}>{t('cm.home.nav.applicant_portal')}</a></li>
                        <li><a href={route('applicant.register')} className={linkClass}>{t('cm.respondent.register_action')}</a></li>
                        <li><a href={route('applicant.login', { login_as: 'respondent' })} className={linkClass}>{t('cm.home.nav.respondent_portal')}</a></li>
                        <li><a href={route('login')} className={linkClass}>{text('staff_sign_in')}</a></li>
                    </ul>
                </nav>

                {(email || phone || address) && (
                    <div>
                        <p className={heading}>{t('cm.home.footer.contact_title')}</p>
                        <ul className="space-y-1 text-sm text-white/75">
                            {phone && <li><a href={`tel:${phone.replace(/\s+/g, '')}`} className={linkClass}>{phone}</a></li>}
                            {email && <li><a href={`mailto:${email}`} className={linkClass}>{email}</a></li>}
                            {address && <li className="py-2 lg:py-1">{address}</li>}
                        </ul>
                    </div>
                )}
            </div>

            <div className="border-t border-white/15">
                <div className="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-4 text-xs text-white/70 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <p>© {new Date().getFullYear()} {organization}. {t('cm.home.footer.rights')}</p>
                    <p>{t('common.designCredit')}</p>
                </div>
            </div>
        </footer>
    );
}
