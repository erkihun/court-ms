import { router, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { AuthAlerts } from '@/components/auth/AuthAlerts';
import { AuthCardLayout } from '@/components/auth/AuthCardLayout';
import { AuthField, fieldAria } from '@/components/auth/AuthField';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

/** Second factor after a staff sign-in: an authenticator code or a recovery code. */
export default function MfaChallenge() {
    const form = useForm({ code: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('mfa.challenge.store'), { onError: () => form.setData('code', '') });
    };

    return (
        <AuthCardLayout title={t('auth.mfa_challenge_title')} subtitle={t('auth.mfa_challenge_hint')} accent="blue">
            <AuthAlerts errors="none" />
            <form onSubmit={submit} className="space-y-4">
                <AuthField id="code" label={t('auth.mfa_code_or_recovery')} error={form.errors.code}>
                    <input
                        {...fieldAria('code', form.errors.code)}
                        className="auth-input text-center tracking-widest"
                        name="code"
                        value={form.data.code}
                        onChange={(event) => form.setData('code', event.target.value.trim())}
                        required
                        autoFocus
                        maxLength={20}
                        autoComplete="one-time-code"
                    />
                </AuthField>
                <button type="submit" className="auth-primary-btn" disabled={form.processing || form.data.code === ''}>
                    {t('auth.mfa_verify')}
                </button>
            </form>
            <div className="mt-4 text-center">
                <button type="button" className="auth-link underline" onClick={() => router.post(route('logout'))}>
                    {t('auth.logout')}
                </button>
            </div>
        </AuthCardLayout>
    );
}
