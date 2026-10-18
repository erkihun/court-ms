import { router, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { AuthCardLayout } from '@/components/auth/AuthCardLayout';
import { AuthField, fieldAria } from '@/components/auth/AuthField';
import { PasswordInput } from '@/components/auth/PasswordInput';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

const fields = [
    { name: 'current_password', label: 'auth.current_password', autoComplete: 'current-password' },
    { name: 'password', label: 'auth.new_password', autoComplete: 'new-password' },
    { name: 'password_confirmation', label: 'auth.confirm_password', autoComplete: 'new-password' },
] as const;

/**
 * Staff who must change their password before using the system (new
 * account or administrator reset). A successful change signs them out.
 */
export default function ForcePassword() {
    const form = useForm({ current_password: '', password: '', password_confirmation: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put(route('password.update'), { errorBag: 'updatePassword', onFinish: () => form.reset() });
    };

    return (
        <AuthCardLayout title={t('auth.force_password_title')} subtitle={t('auth.force_password_notice')} accent="blue">
            <p className="mb-4 text-sm text-slate-500">{t('auth.profile.password_security_hint')}</p>
            <form onSubmit={submit} className="space-y-4">
                {fields.map((field, index) => (
                    <AuthField key={field.name} id={field.name} label={t(field.label)} error={form.errors[field.name]}>
                        <PasswordInput
                            {...fieldAria(field.name, form.errors[field.name])}
                            className="auth-input pe-11"
                            name={field.name}
                            value={form.data[field.name]}
                            onChange={(event) => form.setData(field.name, event.target.value)}
                            required
                            autoFocus={index === 0}
                            autoComplete={field.autoComplete}
                        />
                    </AuthField>
                ))}
                <button type="submit" className="auth-primary-btn" disabled={form.processing}>
                    {t('ui.save')}
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
