import '../../../../css/pages/party-register.css';

import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent, ReactNode } from 'react';
import { PasswordInput } from '@/components/auth/PasswordInput';
import { useShared } from '@/hooks/useShared';
import { cn } from '@/lib/cn';
import { formatNationalId } from '@/lib/format';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import { initials } from '@/lib/str';
import type { Locale } from '@/types';

const PHONE_PREFIX = '+251';

const icons = {
    position:
        'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    organization:
        'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    email: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    address: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z',
    lock: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
} as const;

const languages: Array<{ code: Locale; label: string }> = [
    { code: 'en', label: 'EN' },
    { code: 'am', label: 'አማ' },
];

/** Applicant self-registration. Respondents use the same account and switch role at sign-in. */
export default function Register() {
    const { app } = useShared();
    const form = useForm({
        is_lawyer: '0' as '0' | '1',
        lawyer_document: null as File | null,
        first_name: '',
        middle_name: '',
        last_name: '',
        gender: '',
        phone: '',
        national_id_number: '',
        position: '',
        organization_name: '',
        email: '',
        address: '',
        password: '',
        password_confirmation: '',
    });
    const { data, errors } = form;
    const phoneDigits = data.phone.startsWith(PHONE_PREFIX) ? data.phone.slice(PHONE_PREFIX.length) : '';
    const hasErrors = Object.keys(errors).length > 0;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.transform((values) => ({
            ...values,
            lawyer_document: values.is_lawyer === '1' ? values.lawyer_document : null,
        }));
        form.post(route('applicant.register.submit'), {
            forceFormData: true,
            onError: (fieldErrors) => {
                // Bring the first problem into view; fall back to the summary at the top.
                const first = Object.keys(fieldErrors)
                    .map((name) => document.getElementById(name))
                    .find((element) => element !== null);
                if (first) first.focus();
                else window.scrollTo({ top: 0 });
            },
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    };

    const text = (name: 'first_name' | 'middle_name' | 'last_name' | 'position' | 'organization_name' | 'email' | 'address') => ({
        id: name,
        name,
        value: data[name],
        onChange: (event: { target: { value: string } }) => form.setData(name, event.target.value),
        'aria-invalid': errors[name] ? true : undefined,
    });

    return (
        <div className="ar-shell font-ui">
            <Head title={t('auth.sign_up_title')} />

            <div className="ar-topstrip">
                <a href="/" className="ar-brand">
                    {app.logoUrl ? <img src={app.logoUrl} alt={app.name} className="ar-brand-logo" /> : <span className="ar-brand-mark">{initials(app.name, 2)}</span>}
                    <span className="ar-brand-name">{app.name}</span>
                </a>

                {app.showLanguageSwitcher && (
                    <div className="ar-lang-switch" role="group" aria-label={t('app.Language')}>
                        {languages.map(({ code, label }) => (
                            <a
                                key={code}
                                href={route('language.switch', { locale: code, return: window.location.href })}
                                className={cn('ar-lang-opt', app.locale === code && 'active')}
                                aria-current={app.locale === code ? 'true' : undefined}
                                lang={code}
                            >
                                <span>{label}</span>
                            </a>
                        ))}
                    </div>
                )}
            </div>

            <div className="ar-body">
                <div className="ar-card">
                    <div className="ar-heading">
                        <h1>{t('auth.sign_up_title')}</h1>
                        <p>{t('auth.sign_up_subtitle')}</p>
                    </div>

                    {hasErrors && (
                        <div className="ar-alert" role="alert">
                            <svg xmlns="http://www.w3.org/2000/svg" className="ar-alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="1.8"
                                    d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"
                                />
                            </svg>
                            <span>{t('auth.please_fix_errors')}</span>
                        </div>
                    )}

                    <form onSubmit={submit} className="ar-form">
                        <div className="ar-section">
                            <div className="ar-section-label" id="is_lawyer_label">
                                {t('auth.lawyer_question')}
                            </div>
                            <div className="ar-radio-group" role="radiogroup" aria-labelledby="is_lawyer_label">
                                {(['1', '0'] as const).map((value) => (
                                    <label key={value} className="ar-radio-label">
                                        <input
                                            type="radio"
                                            name="is_lawyer"
                                            value={value}
                                            className="ar-radio"
                                            checked={data.is_lawyer === value}
                                            onChange={() => form.setData('is_lawyer', value)}
                                        />
                                        <span className="ar-radio-dot" />
                                        {value === '1' ? t('auth.yes') : t('auth.no')}
                                    </label>
                                ))}
                            </div>
                            <FieldError message={errors.is_lawyer} />

                            {data.is_lawyer === '1' && (
                                <div className="ar-field" style={{ marginTop: '1rem' }}>
                                    <label className="ar-label" htmlFor="lawyer_document">
                                        {t('auth.lawyer_document')}
                                    </label>
                                    <input
                                        id="lawyer_document"
                                        type="file"
                                        name="lawyer_document"
                                        accept=".pdf,application/pdf"
                                        required
                                        onChange={(event) => form.setData('lawyer_document', event.target.files?.[0] ?? null)}
                                        className={cn('ar-input', errors.lawyer_document && 'ar-input-error')}
                                    />
                                    <FieldError message={errors.lawyer_document} hint={t('auth.lawyer_document_hint')} />
                                </div>
                            )}
                        </div>

                        <div className="ar-section">
                            <div className="ar-section-label">{t('auth.personal_info')}</div>
                            <div className="ar-grid-3">
                                {(['first_name', 'middle_name', 'last_name'] as const).map((name) => (
                                    <Field key={name} id={name} label={t(`auth.${name}`)} error={errors[name]}>
                                        <input {...text(name)} required maxLength={100} className={cn('ar-input', errors[name] && 'ar-input-error')} />
                                    </Field>
                                ))}
                            </div>

                            <div className="ar-grid-3">
                                <Field id="gender" label={t('auth.gender')} error={errors.gender}>
                                    <select
                                        id="gender"
                                        name="gender"
                                        value={data.gender}
                                        onChange={(event) => form.setData('gender', event.target.value)}
                                        className={cn('ar-input ar-select', errors.gender && 'ar-input-error')}
                                        required
                                    >
                                        <option value="">{t('auth.select_option')}</option>
                                        <option value="male">{t('auth.male')}</option>
                                        <option value="female">{t('auth.female')}</option>
                                    </select>
                                </Field>

                                <Field id="phone" label={t('auth.phone')} error={errors.phone} hint={t('auth.phone_hint', { min: 9, max: 10 })}>
                                    <div className={cn('ar-phone-wrap', errors.phone && 'ar-input-error')}>
                                        <span className="ar-phone-prefix">{PHONE_PREFIX}</span>
                                        <input
                                            id="phone"
                                            type="tel"
                                            inputMode="numeric"
                                            placeholder="9XXXXXXXXX"
                                            minLength={9}
                                            maxLength={10}
                                            pattern="[0-9]{9,10}"
                                            value={phoneDigits}
                                            onChange={(event) => {
                                                const digits = event.target.value.replace(/\D/g, '').slice(0, 10);
                                                form.setData('phone', digits ? PHONE_PREFIX + digits : '');
                                            }}
                                            className="ar-phone-input"
                                            autoComplete="tel-national"
                                            required
                                        />
                                    </div>
                                </Field>

                                <Field id="national_id_number" label={t('auth.national_id')} error={errors.national_id_number} hint={t('auth.national_id_hint')}>
                                    <input
                                        id="national_id_number"
                                        name="national_id_number"
                                        value={data.national_id_number}
                                        onChange={(event) => form.setData('national_id_number', formatNationalId(event.target.value))}
                                        required
                                        inputMode="numeric"
                                        autoComplete="off"
                                        maxLength={19}
                                        pattern="\d{4}\s\d{4}\s\d{4}\s\d{4}"
                                        title={t('auth.national_id_hint')}
                                        placeholder={t('auth.national_id_placeholder')}
                                        className={cn('ar-input', errors.national_id_number && 'ar-input-error')}
                                    />
                                </Field>
                            </div>
                        </div>

                        <div className="ar-section">
                            <div className="ar-section-label">{t('auth.organization_info')}</div>
                            <div className="ar-grid-2">
                                <Field id="position" label={t('auth.position')} error={errors.position}>
                                    <IconInput icon={icons.position}>
                                        <input {...text('position')} required maxLength={150} className={iconInputClass(errors.position)} />
                                    </IconInput>
                                </Field>
                                <Field id="organization_name" label={t('auth.organization_name')} error={errors.organization_name}>
                                    <IconInput icon={icons.organization}>
                                        <input {...text('organization_name')} required maxLength={150} className={iconInputClass(errors.organization_name)} />
                                    </IconInput>
                                </Field>
                            </div>
                        </div>

                        <div className="ar-section">
                            <div className="ar-section-label">{t('auth.account_details')}</div>
                            <div className="ar-grid-2">
                                <Field id="email" label={t('auth.email')} error={errors.email}>
                                    <IconInput icon={icons.email}>
                                        <input {...text('email')} type="email" required maxLength={255} autoComplete="email" className={iconInputClass(errors.email)} />
                                    </IconInput>
                                </Field>
                                <Field id="address" label={t('auth.address')} error={errors.address}>
                                    <IconInput icon={icons.address}>
                                        <input {...text('address')} required maxLength={255} autoComplete="street-address" className={iconInputClass(errors.address)} />
                                    </IconInput>
                                </Field>
                            </div>

                            <div className="ar-grid-2">
                                {(['password', 'password_confirmation'] as const).map((name) => (
                                    <Field key={name} id={name} label={t(name === 'password' ? 'auth.password' : 'auth.confirm_password')} error={errors[name]}>
                                        <PasswordInput
                                            wrapperClassName="ar-input-wrap"
                                            leading={<FieldIcon d={icons.lock} />}
                                            id={name}
                                            name={name}
                                            value={data[name]}
                                            onChange={(event) => form.setData(name, event.target.value)}
                                            required
                                            autoComplete="new-password"
                                            aria-invalid={errors[name] ? true : undefined}
                                            className={cn(iconInputClass(errors[name]), 'ar-input-pr')}
                                            toggleClassName="ar-eye-btn"
                                        />
                                    </Field>
                                ))}
                            </div>
                        </div>

                        <div className="ar-actions">
                            <Link href={route('applicant.login')} className="ar-login-link">
                                {t('auth.already_have_account')}
                            </Link>
                            <button type="submit" className="ar-submit" disabled={form.processing}>
                                {t('auth.create_account_button')}
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div className="ar-footer">
                &copy; {new Date().getFullYear()} {app.name}
            </div>
        </div>
    );
}

function iconInputClass(error?: string): string {
    return cn('ar-input ar-input-with-icon', error && 'ar-input-error');
}

function Field({ id, label, error, hint, children }: { id: string; label: string; error?: string; hint?: string; children: ReactNode }) {
    return (
        <div className="ar-field">
            <label className="ar-label" htmlFor={id}>
                {label}
            </label>
            {children}
            <FieldError message={error} hint={hint} />
        </div>
    );
}

function FieldError({ message, hint }: { message?: string; hint?: string }) {
    if (message) return <p className="ar-error">{message}</p>;
    return hint ? <p className="ar-hint">{hint}</p> : null;
}

function IconInput({ icon, children }: { icon: string; children: ReactNode }) {
    return (
        <div className="ar-input-wrap">
            <FieldIcon d={icon} />
            {children}
        </div>
    );
}

function FieldIcon({ d }: { d: string }) {
    return (
        <svg className="ar-input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.6" d={d} />
        </svg>
    );
}
