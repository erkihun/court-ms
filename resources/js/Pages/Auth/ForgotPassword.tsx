import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { AuthAlerts } from '@/components/auth/AuthAlerts';
import { AuthCardLayout } from '@/components/auth/AuthCardLayout';
import { AuthField, fieldAria } from '@/components/auth/AuthField';
import { type AuthPortal, authPortals } from '@/lib/authPortal';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

/** Step 1 of the password reset: ask for the account email, then a code is mailed. */
export default function ForgotPassword({ portal }: { portal: AuthPortal }) {
    const config = authPortals[portal];
    const form = useForm({ email: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route(config.sendCode));
    };

    return (
        <AuthCardLayout
            title={t('auth.forgot_password_title')}
            subtitle={t(config.forgotSubtitle)}
            accent={config.accent}
            loginHref={route(config.login)}
        >
            <AuthAlerts />
            <form onSubmit={submit} className="space-y-4">
                <AuthField id="email" label={t('auth.email')} error={form.errors.email}>
                    <input
                        {...fieldAria('email', form.errors.email)}
                        className="auth-input"
                        type="email"
                        name="email"
                        value={form.data.email}
                        onChange={(event) => form.setData('email', event.target.value)}
                        required
                        autoFocus
                        autoComplete="email"
                    />
                </AuthField>
                <button type="submit" className="auth-primary-btn" disabled={form.processing}>
                    {t('auth.send_reset_link')}
                </button>
            </form>
        </AuthCardLayout>
    );
}
