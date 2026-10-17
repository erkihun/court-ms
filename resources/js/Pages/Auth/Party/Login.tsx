import '../../../../css/pages/party-login.css';

import { Head, Link, useForm } from '@inertiajs/react';
import { type FormEvent, useCallback, useEffect, useLayoutEffect, useRef, useState } from 'react';
import { PasswordInput } from '@/components/auth/PasswordInput';
import { useShared } from '@/hooks/useShared';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import { initials } from '@/lib/str';
import type { Locale } from '@/types';

type LoginAs = 'applicant' | 'respondent';

interface Props {
    loginAs: LoginAs;
    bannerUrl: string | null;
    /** Published "about" page; `html` is sanitised on the server. */
    about: { title: string; html: string } | null;
}

const roles: Array<{ value: LoginAs; label: string; panel: string; icon: string }> = [
    {
        value: 'applicant',
        label: 'auth.role_applicant',
        panel: 'auth.applicant_panel_label',
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    },
    {
        value: 'respondent',
        label: 'auth.role_respondent',
        panel: 'auth.respondent_panel_label',
        icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
    },
];

const languages: Array<{ code: Locale; label: string }> = [
    { code: 'en', label: 'EN' },
    { code: 'am', label: 'አማ' },
];

/** Sign-in for applicants and respondents (one account, two roles). */
export default function PartyLogin({ loginAs, bannerUrl, about }: Props) {
    const { app, toasts } = useShared();
    const form = useForm({ email: '', password: '', remember: false, login_as: loginAs });
    const [aboutOpen, setAboutOpen] = useState(false);
    const tabs = useRef<Partial<Record<LoginAs, HTMLButtonElement | null>>>({});
    const [indicator, setIndicator] = useState<{ left: number; width: number } | null>(null);

    const placeIndicator = useCallback(() => {
        const tab = tabs.current[form.data.login_as];
        if (tab) setIndicator({ left: tab.offsetLeft, width: tab.offsetWidth });
    }, [form.data.login_as]);

    useLayoutEffect(placeIndicator, [placeIndicator]);

    useEffect(() => {
        window.addEventListener('resize', placeIndicator);
        return () => window.removeEventListener('resize', placeIndicator);
    }, [placeIndicator]);

    useEffect(() => {
        if (!aboutOpen) return;
        const onKey = (event: KeyboardEvent) => event.key === 'Escape' && setAboutOpen(false);
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, [aboutOpen]);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('applicant.login.submit'), { onFinish: () => form.reset('password') });
    };

    const activeRole = roles.find((role) => role.value === form.data.login_as) ?? roles[0]!;

    return (
        <div className="al-shell font-ui">
            <Head title={t('auth.sign_in_title')} />

            <div className="al-left">
                <div className="al-topstrip">
                    <a href="/" className="al-logo-link">
                        {app.logoUrl ? <img src={app.logoUrl} alt={app.name} className="al-logo-img" /> : <span className="al-logo-mark">{initials(app.name, 2)}</span>}
                        <span className="al-logo-name">{app.name}</span>
                    </a>

                    <div className="al-top-actions">
                        <button type="button" className="al-mobile-about-btn al-mobile-about-btn-top" onClick={() => setAboutOpen(true)}>
                            <span>{t('app.View details')}</span>
                        </button>

                        {app.showLanguageSwitcher && (
                            <div className="al-lang-switch" role="group" aria-label={t('app.Language')}>
                                {languages.map(({ code, label }) => (
                                    <a
                                        key={code}
                                        href={route('language.switch', { locale: code, return: window.location.href })}
                                        className={cn('al-lang-btn', app.locale === code && 'active')}
                                        aria-current={app.locale === code ? 'true' : undefined}
                                        lang={code}
                                    >
                                        <span>{label}</span>
                                    </a>
                                ))}
                            </div>
                        )}
                    </div>
                </div>

                <div className="al-form-area">
                    <div className="al-form-inner">
                        <div className="al-heading al-anim">
                            <h1>{t('auth.sign_in_title')}</h1>
                            <p>{t('auth.sign_in_subtitle')}</p>
                        </div>

                        <div className="al-role-tabs al-anim" role="group" aria-label={t('auth.sign_in_as')}>
                            {roles.map((role) => (
                                <button
                                    key={role.value}
                                    ref={(element) => {
                                        tabs.current[role.value] = element;
                                    }}
                                    type="button"
                                    className={cn('al-role-tab', form.data.login_as === role.value && 'active')}
                                    aria-pressed={form.data.login_as === role.value}
                                    onClick={() => form.setData('login_as', role.value)}
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d={role.icon} />
                                    </svg>
                                    {t(role.label)}
                                </button>
                            ))}
                            <span className="al-role-indicator" style={indicator ? { left: indicator.left, width: indicator.width } : undefined} />
                        </div>
                        <p className="al-panel-label al-anim">{t(activeRole.panel)}</p>

                        {toasts.map((toast, index) =>
                            toast.type === 'error' ? (
                                <div key={index} role="alert" className="al-alert al-alert-err">
                                    <AlertIcon d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    {toast.details.length > 0 ? (
                                        <ul>
                                            {toast.details.map((detail) => (
                                                <li key={detail}>{detail}</li>
                                            ))}
                                        </ul>
                                    ) : (
                                        <span>{toast.message}</span>
                                    )}
                                </div>
                            ) : (
                                <div key={index} role="status" className="al-alert al-alert-ok">
                                    <AlertIcon d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    <span>{toast.message}</span>
                                </div>
                            ),
                        )}

                        <form onSubmit={submit} className="al-form al-anim">
                            <div className="al-field">
                                <label htmlFor="email" className="al-label">
                                    {t('auth.email')}
                                </label>
                                <div className="al-input-wrap">
                                    <InputIcon d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        value={form.data.email}
                                        onChange={(event) => form.setData('email', event.target.value)}
                                        placeholder={t('auth.email_placeholder')}
                                        className="al-input"
                                        aria-invalid={form.errors.email ? true : undefined}
                                        required
                                        autoFocus
                                        autoComplete="email"
                                    />
                                </div>
                            </div>

                            <div className="al-field">
                                <div className="mb-1.5 flex items-center justify-between">
                                    <label htmlFor="password" className="al-label" style={{ margin: 0 }}>
                                        {t('auth.password')}
                                    </label>
                                    <Link href={route('applicant.password.request')} className="al-forgot">
                                        {t('auth.forgot_password')}
                                    </Link>
                                </div>
                                <PasswordInput
                                    wrapperClassName="al-input-wrap"
                                    leading={<InputIcon d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />}
                                    id="password"
                                    name="password"
                                    value={form.data.password}
                                    onChange={(event) => form.setData('password', event.target.value)}
                                    placeholder={t('auth.password_placeholder')}
                                    className="al-input pr-11"
                                    toggleClassName="al-pw-eye"
                                    required
                                    autoComplete="current-password"
                                />
                            </div>

                            <label className="al-remember">
                                <input
                                    type="checkbox"
                                    name="remember"
                                    className="al-checkbox"
                                    checked={form.data.remember}
                                    onChange={(event) => form.setData('remember', event.target.checked)}
                                />
                                <span>{t('auth.remember_me')}</span>
                            </label>

                            <button type="submit" className="al-submit" disabled={form.processing}>
                                {t('auth.sign_in')}
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </button>
                        </form>

                        <p className="al-register-line al-anim">
                            {t('auth.no_account')} <Link href={route('applicant.register')}>{t('auth.create_account')}</Link>
                        </p>
                    </div>
                </div>

                <p className="al-footer">
                    &copy; {new Date().getFullYear()} {app.name}
                </p>
            </div>

            <div className={cn('al-right', bannerUrl && 'has-banner', aboutOpen && 'open')}>
                {bannerUrl && (
                    <>
                        <div className="al-banner" aria-hidden="true" style={{ backgroundImage: `url("${bannerUrl}")` }} />
                        <div className="al-banner-overlay" aria-hidden="true" />
                    </>
                )}
                <div className="al-orb al-orb-1" aria-hidden="true" />
                <div className="al-orb al-orb-2" aria-hidden="true" />
                <div className="al-orb al-orb-3" aria-hidden="true" />

                <button type="button" className="al-about-close" onClick={() => setAboutOpen(false)} aria-label={t('app.Close details')}>
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

                {about ? (
                    <div className="al-about">
                        <h2 className="al-about-title">{about.title}</h2>
                        <div className="al-about-divider" aria-hidden="true" />
                        {/* Sanitised server-side with the 'cases' HTMLPurifier profile. */}
                        <div className="al-about-body" dangerouslySetInnerHTML={{ __html: about.html }} />
                    </div>
                ) : (
                    <div className="al-about-empty">
                        <div className="al-empty-mark">{initials(app.name, 2)}</div>
                        <p className="al-empty-name">{app.name}</p>
                        <p className="al-empty-sub">{t('auth.sign_in_subtitle')}</p>
                    </div>
                )}
            </div>
        </div>
    );
}

function InputIcon({ d }: { d: string }) {
    return (
        <svg className="al-input-ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.7" d={d} />
        </svg>
    );
}

function AlertIcon({ d }: { d: string }) {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" className="mt-px h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d={d} />
        </svg>
    );
}
