import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { AuthAlerts } from '@/components/auth/AuthAlerts';
import { AuthCardLayout } from '@/components/auth/AuthCardLayout';
import { AuthField, fieldAria } from '@/components/auth/AuthField';
import { PasswordInput } from '@/components/auth/PasswordInput';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

/** Re-enter the password before a sensitive staff action. */
export default function ConfirmPassword() {
    const form = useForm({ password: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('password.confirm'), { onFinish: () => form.reset() });
    };

    return (
        <AuthCardLayout title={t('auth.confirm_password')} subtitle={t('auth.confirm_password_description')} accent="blue" loginHref={route('login')}>
            <AuthAlerts />
            <form onSubmit={submit} className="space-y-4">
                <AuthField id="password" label={t('auth.password')} error={form.errors.password}>
                    <PasswordInput
                        {...fieldAria('password', form.errors.password)}
                        className="auth-input pe-11"
                        name="password"
                        value={form.data.password}
                        onChange={(event) => form.setData('password', event.target.value)}
                        required
                        autoFocus
                        autoComplete="current-password"
                    />
                </AuthField>
                <button type="submit" className="auth-primary-btn" disabled={form.processing}>
                    {t('auth.confirm')}
                </button>
            </form>
        </AuthCardLayout>
    );
}
