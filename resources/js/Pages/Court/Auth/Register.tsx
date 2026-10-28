import { Link, useForm } from '@inertiajs/react';
import type { FormEvent, ReactNode } from 'react';
import { UserPlusIcon } from '@/Components/Icons';
import PasswordPolicyChecklist from '@/Components/PasswordPolicyChecklist';
import CourtAuthShell, { courtAuthButtonClass, courtAuthInputClass, courtAuthLabelClass } from '@/court/components/auth/CourtAuthShell';
import { usePublicText } from '@/court/i18n/usePublicText';

/** XXXX XXXX XXXX XXXX: the Blade page's auto-formatter (letters and digits, upper-cased, 16 at most). */
function formatNationalId(value: string): string {
    const cleaned = (value || '').replace(/[^A-Za-z0-9]/g, '').slice(0, 16).toUpperCase();
    return (cleaned.match(/.{1,4}/g) ?? []).join(' ');
}

function Field({ id, label, error, children, help }: { id: string; label: string; error?: string; help?: ReactNode; children: ReactNode }) {
    return (
        <div>
            <label htmlFor={id} className={courtAuthLabelClass}>{label}</label>
            {children}
            {help}
            {error && <p id={`${id}-error`} className="mt-1.5 text-xs text-red-600 dark:text-red-400">{error}</p>}
        </div>
    );
}

/**
 * Staff self-registration (RegisteredUserController::store): name, email,
 * national ID (16 digits; the server strips the spaces), password. Signs the
 * new account in and opens the dashboard.
 */
export default function Register() {
    const { t } = usePublicText();
    const form = useForm({ name: '', email: '', national_id_number: '', password: '', password_confirmation: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('register'), { onFinish: () => form.reset('password', 'password_confirmation') });
    };

    return (
        <CourtAuthShell
            title={t('cm.auth.register')}
            icon={<UserPlusIcon className="h-5 w-5" />}
            backToLogin={false}
            footer={(
                <div className="mt-6 text-center text-sm">
                    <Link href={route('login')} className="font-semibold text-gray-500 underline hover:text-gray-900 dark:text-slate-400 dark:hover:text-white">
                        {t('cm.auth.already_registered')}
                    </Link>
                </div>
            )}
        >
            <form onSubmit={submit} className="space-y-4">
                <Field id="name" label={t('cm.auth.name')} error={form.errors.name}>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value={form.data.name}
                        onChange={(event) => form.setData('name', event.target.value)}
                        required
                        autoFocus
                        autoComplete="name"
                        aria-invalid={form.errors.name ? true : undefined}
                        className={courtAuthInputClass(Boolean(form.errors.name))}
                    />
                </Field>

                <Field id="email" label={t('cm.auth.email')} error={form.errors.email}>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value={form.data.email}
                        onChange={(event) => form.setData('email', event.target.value)}
                        required
                        autoComplete="username"
                        aria-invalid={form.errors.email ? true : undefined}
                        className={courtAuthInputClass(Boolean(form.errors.email))}
                    />
                </Field>

                <Field
                    id="national_id_number"
                    label={t('cm.auth.national_id')}
                    error={form.errors.national_id_number}
                    help={<p id="national_id_help" className="mt-1 text-xs text-gray-500 dark:text-slate-400">{t('cm.auth.national_id_hint')}</p>}
                >
                    <input
                        id="national_id_number"
                        name="national_id_number"
                        value={form.data.national_id_number}
                        onChange={(event) => form.setData('national_id_number', formatNationalId(event.target.value))}
                        onBlur={(event) => form.setData('national_id_number', formatNationalId(event.target.value))}
                        required
                        inputMode="text"
                        autoComplete="off"
                        maxLength={19}
                        pattern="[A-Za-z0-9]{4}\s[A-Za-z0-9]{4}\s[A-Za-z0-9]{4}\s[A-Za-z0-9]{4}"
                        title={t('cm.auth.national_id_format')}
                        placeholder={t('cm.auth.national_id_placeholder')}
                        aria-describedby="national_id_help"
                        aria-invalid={form.errors.national_id_number ? true : undefined}
                        className={courtAuthInputClass(Boolean(form.errors.national_id_number))}
                    />
                </Field>

                <Field id="password" label={t('cm.auth.password')} error={form.errors.password}>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        value={form.data.password}
                        onChange={(event) => form.setData('password', event.target.value)}
                        required
                        autoComplete="new-password"
                        aria-invalid={form.errors.password ? true : undefined}
                        className={courtAuthInputClass(Boolean(form.errors.password))}
                    />
                </Field>

                <Field id="password_confirmation" label={t('cm.auth.confirm_password')} error={form.errors.password_confirmation}>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        value={form.data.password_confirmation}
                        onChange={(event) => form.setData('password_confirmation', event.target.value)}
                        required
                        autoComplete="new-password"
                        aria-invalid={form.errors.password_confirmation ? true : undefined}
                        className={courtAuthInputClass(Boolean(form.errors.password_confirmation))}
                    />
                </Field>

                <PasswordPolicyChecklist
                    password={form.data.password}
                    confirmation={form.data.password_confirmation}
                    personal={[form.data.name, form.data.email]}
                />

                <button type="submit" disabled={form.processing} className={courtAuthButtonClass}>
                    {t('cm.auth.register')}
                </button>
            </form>
        </CourtAuthShell>
    );
}
