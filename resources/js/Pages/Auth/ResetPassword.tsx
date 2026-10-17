import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { AuthAlerts } from '@/components/auth/AuthAlerts';
import { AuthCardLayout } from '@/components/auth/AuthCardLayout';
import { AuthField, fieldAria } from '@/components/auth/AuthField';
import { PasswordInput } from '@/components/auth/PasswordInput';
import { type AuthPortal, authPortals } from '@/lib/authPortal';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

/** Step 3 of the password reset: choose the new password (the verified email is held in the session). */
export default function ResetPassword({ portal }: { portal: AuthPortal }) {
    const config = authPortals[portal];
    const form = useForm({ password: '', password_confirmation: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route(config.resetPassword), { onFinish: () => form.reset() });
    };

    return (
        <AuthCardLayout
            title={t('auth.reset_password_title')}
            subtitle={t('auth.reset_password_subtitle')}
            accent={config.accent}
            loginHref={route(config.login)}
        >
            <AuthAlerts errors="list" />
            <form onSubmit={submit} className="space-y-4">
                <AuthField id="password" label={t('auth.new_password')} error={form.errors.password}>
                    <PasswordInput
                        {...fieldAria('password', form.errors.password)}
                        className="auth-input pe-11"
                        name="password"
                        value={form.data.password}
                        onChange={(event) => form.setData('password', event.target.value)}
                        required
                        autoFocus
                        autoComplete="new-password"
                    />
                </AuthField>
                <AuthField id="password_confirmation" label={t(config.confirmLabel)} error={form.errors.password_confirmation}>
                    <PasswordInput
                        {...fieldAria('password_confirmation', form.errors.password_confirmation)}
                        className="auth-input pe-11"
                        name="password_confirmation"
                        value={form.data.password_confirmation}
                        onChange={(event) => form.setData('password_confirmation', event.target.value)}
                        required
                        autoComplete="new-password"
                    />
                </AuthField>
                <button type="submit" className="auth-primary-btn" disabled={form.processing}>
                    {t('auth.reset_password_button')}
                </button>
            </form>
        </AuthCardLayout>
    );
}
