import { Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { AuthAlerts } from '@/components/auth/AuthAlerts';
import { AuthCardLayout } from '@/components/auth/AuthCardLayout';
import { OtpInput } from '@/components/auth/OtpInput';
import { type AuthPortal, authPortals } from '@/lib/authPortal';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

/** Step 2 of the password reset: enter the six-digit code from the email. */
export default function PasswordOtp({ portal }: { portal: AuthPortal }) {
    const config = authPortals[portal];
    const form = useForm({ code: '' });
    const resend = useForm({});

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route(config.verifyCode), { onError: () => form.setData('code', '') });
    };

    return (
        <AuthCardLayout
            title={t('auth.password_otp_title')}
            subtitle={t('auth.password_otp_description')}
            accent={config.accent}
            loginHref={route(config.login)}
            footer={
                <div className="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-4 text-sm">
                    <Link href={route(config.forgot)} className="auth-link">
                        {t('auth.use_different_email')}
                    </Link>
                    <button
                        type="button"
                        className="auth-accent-link"
                        disabled={resend.processing}
                        onClick={() => resend.post(route(config.resendCode), { preserveScroll: true })}
                    >
                        {t('auth.resend_code')}
                    </button>
                </div>
            }
        >
            <AuthAlerts />
            <form onSubmit={submit} className="space-y-5">
                <div>
                    <label className="auth-label text-center" htmlFor="code">
                        {t('auth.verification_code')}
                    </label>
                    <OtpInput id="code" value={form.data.code} onChange={(code) => form.setData('code', code)} invalid={!!form.errors.code} autoFocus />
                </div>
                <button type="submit" className="auth-primary-btn" disabled={form.processing || form.data.code.length < 6}>
                    {t('auth.verify_code')}
                </button>
            </form>
        </AuthCardLayout>
    );
}
