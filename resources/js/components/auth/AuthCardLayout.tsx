import '../../../css/auth.css';

import { Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { useShared } from '@/hooks/useShared';
import { t } from '@/lib/i18n';
import { initials } from '@/lib/str';

interface AuthCardLayoutProps {
    title: string;
    subtitle?: string | null;
    accent: 'blue' | 'orange';
    /** "Back to login" link under the card; omit to hide it. */
    loginHref?: string | null;
    footer?: ReactNode;
    children: ReactNode;
}

/** Centered card used by every auth step except the two sign-in screens and registration. */
export function AuthCardLayout({ title, subtitle, accent, loginHref, footer, children }: AuthCardLayoutProps) {
    const { app } = useShared();

    return (
        <div className="auth-page min-h-screen font-ui" data-auth-accent={accent}>
            <Head title={title} />
            <main className="flex min-h-screen items-center justify-center px-4 py-10">
                <div className="w-full max-w-md">
                    <div className="auth-card rounded-2xl p-6 md:p-7">
                        <div className="mb-6 flex flex-col items-center text-center">
                            {app.logoUrl ? (
                                <img src={app.logoUrl} alt={app.name} className="mb-3 h-14 w-auto object-contain" />
                            ) : (
                                <span className="auth-logo-fallback mb-3 inline-flex h-14 w-14 items-center justify-center rounded-2xl text-base font-bold tracking-wide text-white uppercase">
                                    {initials(app.shortName, 2)}
                                </span>
                            )}
                            <div className="text-sm font-semibold text-slate-500">{app.name}</div>
                            <h1 className="mt-1 text-lg font-semibold text-slate-900 md:text-xl">{title}</h1>
                            {subtitle && <p className="mt-1 text-xs text-slate-500 md:text-sm">{subtitle}</p>}
                        </div>

                        {children}
                        {footer}
                    </div>

                    {loginHref && (
                        <div className="mt-4 text-center">
                            <Link href={loginHref} className="auth-link">
                                {t('auth.back_to_login')}
                            </Link>
                        </div>
                    )}
                </div>
            </main>
        </div>
    );
}
