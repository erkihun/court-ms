import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { AuthAlerts } from '@/components/auth/AuthAlerts';
import { AuthCardLayout } from '@/components/auth/AuthCardLayout';
import { OtpInput } from '@/components/auth/OtpInput';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

/** Applicant email verification by six-digit code (a code is sent when this page opens). */
export default function VerifyEmailOtp() {
    const form = useForm({ code: '' });
    const resend = useForm({});

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('applicant.verification.verify-otp'), { onError: () => form.setData('code', '') });
    };

    return (
        <AuthCardLayout
            title={t('auth.verify_email_title')}
            subtitle={t('auth.verify_email_otp_description')}
            accent="orange"
            loginHref={route('applicant.login')}
            footer={
                <div className="mt-4 flex items-center justify-between border-t border-slate-100 pt-4 text-sm">
                    <span className="text-slate-500">{t('auth.didnt_receive_code')}</span>
                    <button
                        type="button"
                        className="auth-accent-link"
                        disabled={resend.processing}
                        onClick={() => resend.post(route('applicant.verification.send'), { preserveScroll: true })}
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
                    {t('auth.verify_email_button')}
                </button>
            </form>
        </AuthCardLayout>
    );
}
