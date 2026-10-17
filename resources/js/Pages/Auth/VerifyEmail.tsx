import { router, useForm } from '@inertiajs/react';
import { AuthAlerts } from '@/components/auth/AuthAlerts';
import { AuthCardLayout } from '@/components/auth/AuthCardLayout';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

/** Staff accounts whose email is not verified yet. */
export default function VerifyEmail() {
    const resend = useForm({});

    return (
        <AuthCardLayout
            title={t('auth.email_verification_label')}
            subtitle={t('auth.email_verification_description')}
            accent="blue"
            loginHref={route('login')}
        >
            <AuthAlerts />
            <div className="auth-alert auth-alert-error mb-4">{t('auth.email_not_verified')}</div>

            <button type="button" className="auth-primary-btn" disabled={resend.processing} onClick={() => resend.post(route('verification.send'))}>
                {t('auth.resend_verification_email')}
            </button>

            <p className="mt-4 text-xs leading-relaxed text-slate-500">{t('auth.verify_email_hint')}</p>

            <button type="button" className="auth-secondary-btn mt-4" onClick={() => router.post(route('logout'))}>
                {t('auth.log_out')}
            </button>
        </AuthCardLayout>
    );
}
