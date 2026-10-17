import '../../../css/pages/staff-login.css';

import { ExclamationTriangleIcon } from '@heroicons/react/24/outline';
import { Head, Link, useForm } from '@inertiajs/react';
import { type CSSProperties, type FormEvent, useEffect, useState } from 'react';
import { PasswordInput } from '@/components/auth/PasswordInput';
import { useShared } from '@/hooks/useShared';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import { initials } from '@/lib/str';

interface Props {
    bannerUrl: string | null;
}

const inputClass =
    'admin-login-input w-full rounded-xl border border-white/22 bg-white/8 px-3.5 py-3 text-sm text-slate-50 shadow-xs transition-base focus:ring-4 focus:ring-blue-500/20 focus:outline-hidden';

/** Staff (court personnel) sign-in. */
export default function Login({ bannerUrl }: Props) {
    const { app, toasts } = useShared();
    const form = useForm({ email: '', password: '', remember: false });
    const [shown, setShown] = useState(false);

    useEffect(() => {
        setShown(true);
        // A page restored from the back/forward cache carries a stale CSRF token.
        const onPageShow = (event: PageTransitionEvent) => {
            if (event.persisted) window.location.reload();
        };
        window.addEventListener('pageshow', onPageShow);
        return () => window.removeEventListener('pageshow', onPageShow);
    }, []);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('login'), { onFinish: () => form.reset('password') });
    };

    const sessionErrors = toasts.filter((toast) => toast.type === 'error' && toast.details.length === 0).map((toast) => toast.message);
    const fieldErrors = Object.values(form.errors);
    const notices = toasts.filter((toast) => toast.type !== 'error');
    const shellStyle = (bannerUrl ? { '--admin-login-banner': `url("${bannerUrl}")` } : {}) as CSSProperties;

    return (
        <div className="relative min-h-screen overflow-hidden bg-slate-100 font-ui text-slate-900 dark:bg-slate-950 dark:text-slate-100">
            <Head title={t('auth.login_title')} />
            <div className="relative min-h-screen">
                <section className="admin-login-shell absolute inset-0 hidden min-h-screen overflow-hidden lg:flex" style={shellStyle}>
                    <div className="admin-aurora" />
                    <div className="admin-galaxy" aria-hidden="true">
                        <div className="layer stars-far" />
                        <div className="layer stars-mid" />
                        <div className="layer stars-near" />
                    </div>
                    <div className="admin-login-beam" />
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_18%_18%,rgba(96,165,250,0.24),transparent_30%),radial-gradient(circle_at_76%_76%,rgba(249,115,22,0.18),transparent_28%)]" />

                    <div className="pointer-events-none absolute top-1/2 -right-10 hidden -translate-y-1/2 xl:block" aria-hidden="true">
                        <div className="relative h-[360px] w-[360px]">
                            <div className="admin-emblem-glow absolute inset-8 rounded-full bg-[radial-gradient(circle,rgba(96,165,250,0.28),transparent_68%)]" />
                            <ScalesEmblem />
                        </div>
                    </div>

                    <div className="relative z-10 flex w-full max-w-3xl flex-col justify-between px-10 py-12 xl:px-14">
                        <div className="flex items-center gap-4">
                            {app.logoUrl ? (
                                <div className="flex h-16 w-16 items-center justify-center">
                                    <img src={app.logoUrl} alt={app.name} className="max-h-full w-auto object-contain" />
                                </div>
                            ) : (
                                <div className="flex h-16 w-16 items-center justify-center rounded-2xl border border-white/15 bg-white/10 text-lg font-bold text-white shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                                    {initials(app.name, 2)}
                                </div>
                            )}
                            <div className="min-w-0">
                                <p className="text-xs font-semibold tracking-[0.24em] text-blue-100/80 uppercase">{t('auth.admin_access_label')}</p>
                                <h1 className="mt-1 text-xl font-semibold text-white">{app.name}</h1>
                            </div>
                        </div>

                        <div className="admin-login-panel admin-stagger max-w-2xl space-y-6 text-white">
                            <div className="space-y-4">
                                <p className="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3.5 py-1.5 text-xs font-semibold tracking-[0.2em] text-blue-50/90 uppercase shadow-lg shadow-slate-950/10 backdrop-blur-sm">
                                    {t('auth.admin_system_label')}
                                </p>
                                <div className="space-y-4">
                                    <h2 className="max-w-xl text-justify text-2xl leading-tight font-semibold tracking-tight xl:text-3xl">{t('auth.admin_login_headline')}</h2>
                                    <p className="max-w-xl text-justify text-base leading-7 text-slate-200/88">{t('auth.admin_login_intro')}</p>
                                </div>
                            </div>
                            <div className="grid max-w-2xl gap-4 sm:grid-cols-2">
                                <Signal title={t('auth.trusted_access_title')} body={t('auth.trusted_access_body')} />
                                <Signal title={t('auth.operational_clarity_title')} body={t('auth.operational_clarity_body')} />
                            </div>
                        </div>

                        <div className="flex items-center justify-between gap-4 text-sm text-slate-200/78">
                            <span>
                                {new Date().getFullYear()} {app.name}
                            </span>
                            <span className="h-px flex-1 bg-linear-to-r from-white/20 to-transparent" />
                        </div>
                    </div>
                </section>

                <section className="relative z-20 flex min-h-screen items-center justify-center px-4 py-8 sm:px-6 lg:ml-auto lg:max-w-xl lg:justify-end lg:px-10 xl:px-14">
                    <div className="relative mx-auto w-full max-w-md lg:mx-0 lg:ml-auto">
                        <div className="mb-6 flex flex-col items-center justify-center gap-4 text-center lg:hidden">
                            {app.logoUrl ? (
                                <div className="flex h-16 w-16 items-center justify-center">
                                    <img src={app.logoUrl} alt={app.name} className="max-h-full w-auto object-contain" />
                                </div>
                            ) : (
                                <div className="flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-200 bg-white text-sm font-bold text-blue-700 shadow-xl shadow-slate-900/8 dark:border-slate-700 dark:bg-slate-900 dark:text-blue-300">
                                    {initials(app.name, 2)}
                                </div>
                            )}
                            <div>
                                <div className="text-xs font-semibold tracking-[0.2em] text-blue-700 uppercase dark:text-blue-300">{t('auth.admin_portal')}</div>
                                <div className="text-lg font-semibold text-slate-950 dark:text-slate-100">{app.name}</div>
                            </div>
                        </div>

                        <div
                            className={cn(
                                'admin-login-form-card relative overflow-hidden rounded-2xl border border-white/25 shadow-2xl ring-1 shadow-black/40 ring-white/10 transition duration-500 ease-out',
                                shown ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                            )}
                        >
                            <div className="h-1.5 bg-[linear-gradient(90deg,#2563eb_0%,#0f766e_48%,#f97316_100%)]" />
                            <div className="dark p-6 text-slate-100 sm:p-8">
                                <div className="space-y-2">
                                    <p className="text-xs font-semibold tracking-[0.22em] text-blue-300 uppercase">{t('auth.admin_access_label')}</p>
                                    <h2 className="text-2xl font-semibold tracking-tight text-slate-100">{t('auth.login_title')}</h2>
                                    <p className="text-sm leading-6 text-slate-400">{t('auth.admin_login_subtitle')}</p>
                                </div>

                                {notices.map((notice, index) => (
                                    <div
                                        key={index}
                                        role="status"
                                        className="mt-5 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200"
                                    >
                                        {notice.message}
                                    </div>
                                ))}

                                {(sessionErrors.length > 0 || fieldErrors.length > 0) && (
                                    <div
                                        role="alert"
                                        aria-live="assertive"
                                        className="mt-5 rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200"
                                    >
                                        <div className="flex items-start gap-2.5">
                                            <ExclamationTriangleIcon className="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />
                                            <div className="min-w-0">
                                                {sessionErrors.map((message) => (
                                                    <p key={message} className="font-semibold">
                                                        {message}
                                                    </p>
                                                ))}
                                                {fieldErrors.length > 0 && (
                                                    <ul className={cn('space-y-1', sessionErrors.length > 0 && 'mt-1.5')}>
                                                        {fieldErrors.map((error) => (
                                                            <li key={error}>{error}</li>
                                                        ))}
                                                    </ul>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                )}

                                <form onSubmit={submit} className="mt-8 space-y-5">
                                    <div className="space-y-2">
                                        <label htmlFor="email" className="block text-sm font-medium text-slate-300">
                                            {t('auth.email')}
                                        </label>
                                        <input
                                            id="email"
                                            name="email"
                                            type="email"
                                            value={form.data.email}
                                            onChange={(event) => form.setData('email', event.target.value)}
                                            required
                                            autoFocus
                                            autoComplete="username"
                                            aria-invalid={form.errors.email ? true : undefined}
                                            className={inputClass}
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <div className="flex items-center justify-between gap-3">
                                            <label htmlFor="password" className="block text-sm font-medium text-slate-300">
                                                {t('auth.password')}
                                            </label>
                                            <Link
                                                href={route('password.request')}
                                                className="text-sm font-medium text-blue-300 transition-fast hover:text-blue-200"
                                            >
                                                {t('auth.forgot_password')}
                                            </Link>
                                        </div>
                                        <PasswordInput
                                            id="password"
                                            name="password"
                                            value={form.data.password}
                                            onChange={(event) => form.setData('password', event.target.value)}
                                            required
                                            autoComplete="current-password"
                                            className={cn(inputClass, 'pr-11')}
                                            toggleClassName="absolute top-1/2 right-3 inline-flex -translate-y-1/2 items-center justify-center rounded-md p-1 text-slate-400 transition-fast hover:bg-slate-800 hover:text-slate-200 focus:ring-2 focus:ring-blue-500 focus:outline-hidden"
                                        />
                                    </div>

                                    <label htmlFor="remember_me" className="inline-flex items-center gap-3 text-sm text-slate-300">
                                        <input
                                            id="remember_me"
                                            type="checkbox"
                                            name="remember"
                                            checked={form.data.remember}
                                            onChange={(event) => form.setData('remember', event.target.checked)}
                                            className="h-4 w-4 rounded-sm border-slate-700 bg-slate-900 text-blue-700 focus:ring-blue-500"
                                        />
                                        <span>{t('auth.remember_me')}</span>
                                    </label>

                                    <button
                                        type="submit"
                                        disabled={form.processing}
                                        className="admin-login-submit inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-900/18 transition-base hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-blue-900/28 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-slate-950 focus:outline-hidden active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-80 disabled:hover:translate-y-0"
                                    >
                                        {form.processing && <Spinner />}
                                        <span>{form.processing ? `${t('auth.sign_in')}…` : t('auth.sign_in')}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    );
}

function Signal({ title, body }: { title: string; body: string }) {
    return (
        <div className="admin-login-signal rounded-2xl border border-white/12 bg-white/10 p-3 shadow-xl shadow-slate-950/10 backdrop-blur-sm">
            <div className="text-xs font-semibold tracking-[0.18em] text-slate-200 uppercase">{title}</div>
            <p className="mt-2 text-justify text-sm leading-5 text-slate-200/82">{body}</p>
        </div>
    );
}

function Spinner() {
    return (
        <svg className="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
            <path className="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
        </svg>
    );
}

function ScalesEmblem() {
    return (
        <svg
            className="admin-scale-emblem relative h-full w-full text-blue-100/25"
            viewBox="0 0 200 200"
            fill="none"
            stroke="currentColor"
            strokeWidth="2.4"
            strokeLinecap="round"
            strokeLinejoin="round"
        >
            <line x1="100" y1="30" x2="100" y2="150" />
            <line x1="40" y1="60" x2="160" y2="60" />
            <circle cx="100" cy="30" r="6" stroke="none" fill="currentColor" />
            <line x1="70" y1="150" x2="130" y2="150" />
            <line x1="40" y1="60" x2="22" y2="104" />
            <line x1="40" y1="60" x2="58" y2="104" />
            <line x1="160" y1="60" x2="142" y2="104" />
            <line x1="160" y1="60" x2="178" y2="104" />
            <path className="pan" d="M18 104c0 14 10 22 22 22s22-8 22-22Z" />
            <path className="pan" d="M138 104c0 14 10 22 22 22s22-8 22-22Z" />
        </svg>
    );
}
