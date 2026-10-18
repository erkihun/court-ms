import { CheckCircleIcon, EyeIcon, UserIcon } from '@heroicons/react/24/outline';
import { Head, Link, useForm } from '@inertiajs/react';
import { type FormEvent, useEffect, useState } from 'react';
import { type ProfilePortal, profileRoutes, ProfileTabs } from '@/components/party/ProfileTabs';
import { Button } from '@/components/ui/Button';
import { Field } from '@/components/ui/Field';
import { Input, Select } from '@/components/ui/Input';
import { formatNationalId } from '@/lib/format';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Profile {
    first_name: string;
    middle_name: string;
    last_name: string;
    gender: string;
    phone: string;
    national_id_number: string;
    email: string;
    address: string;
    position: string;
    organization_name: string;
}

interface Props {
    portal: ProfilePortal;
    profile: Profile;
    isLawyer: boolean;
    hasLawyerDocument: boolean;
}

type TextField = Exclude<keyof Profile, 'gender' | 'national_id_number'>;

const nameFields: Array<{ name: TextField; autoComplete: string }> = [
    { name: 'first_name', autoComplete: 'given-name' },
    { name: 'middle_name', autoComplete: 'additional-name' },
    { name: 'last_name', autoComplete: 'family-name' },
];

const detailFields: Array<{ name: TextField; autoComplete: string; type?: string }> = [
    { name: 'email', autoComplete: 'email', type: 'email' },
    { name: 'address', autoComplete: 'street-address' },
    { name: 'position', autoComplete: 'organization-title' },
    { name: 'organization_name', autoComplete: 'organization' },
];

/** Applicant / respondent profile: personal details and password. */
export default function ProfileEdit({ portal, profile, isLawyer, hasLawyerDocument }: Props) {
    const routes = profileRoutes[portal];
    const [tab, setTab] = useState<'profile' | 'security'>(() => (window.location.hash === '#security' ? 'security' : 'profile'));
    const form = useForm({ ...profile, lawyer_document: null as File | null });
    const security = useForm({ current_password: '', password: '', password_confirmation: '' });

    useEffect(() => {
        const onHash = () => setTab(window.location.hash === '#security' ? 'security' : 'profile');
        window.addEventListener('hashchange', onHash);
        return () => window.removeEventListener('hashchange', onHash);
    }, []);

    const selectTab = (next: 'profile' | 'security') => {
        setTab(next);
        window.history.replaceState(window.history.state, '', `#${next}`);
    };

    const saveProfile = (event: FormEvent) => {
        event.preventDefault();
        // Files cannot travel in a PATCH body, so spoof the method on a multipart POST.
        form.transform((data) => ({ ...data, _method: 'patch' }));
        form.post(route(routes.update), { forceFormData: true, preserveScroll: true, onSuccess: () => form.setData('lawyer_document', null) });
    };

    const savePassword = (event: FormEvent) => {
        event.preventDefault();
        security.patch(route(routes.password), { preserveScroll: true, preserveState: true, onFinish: () => security.reset() });
    };

    const text = (name: TextField, autoComplete: string, type = 'text') => (
        <Field key={name} label={t(`auth.${name}`)} error={form.errors[name]}>
            <Input type={type} value={form.data[name]} autoComplete={autoComplete} onChange={(event) => form.setData(name, event.target.value)} />
        </Field>
    );

    return (
        <div className="mx-auto max-w-6xl rounded-xl border border-(--border) bg-(--surface-strong) p-6 shadow-xs md:p-8">
            <Head title={t('auth.my_profile')} />

            <div className="mb-6 flex items-center justify-between">
                <div className="flex items-center gap-3">
                    <div className="flex h-9 w-9 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                        <UserIcon className="h-5 w-5" />
                    </div>
                    <div>
                        <h1 className="text-xl font-semibold text-(--text) md:text-2xl">{t('auth.my_profile')}</h1>
                        <p className="text-xs text-(--text-subtle)">{t('auth.fill_basic_info')}</p>
                    </div>
                </div>
                <Link href={route(routes.dashboard)} className="hidden rounded-lg border border-(--border) px-3 py-1.5 text-xs font-medium text-(--text-muted) hover:bg-(--surface-soft) md:inline-flex">
                    {t('auth.back')}
                </Link>
            </div>

            <ProfileTabs portal={portal} active={tab} onSelect={selectTab} />

            {tab === 'profile' ? (
                <form id="profile" onSubmit={saveProfile} className="space-y-8" noValidate>
                    <section>
                        <div className="mb-3 flex items-center gap-2">
                            <h2 className="font-semibold text-(--text)">{t('auth.basic_information')}</h2>
                            <span className="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700">{t('auth.profile_basic_info')}</span>
                        </div>

                        <div className="grid gap-4 md:grid-cols-3">{nameFields.map(({ name, autoComplete }) => text(name, autoComplete))}</div>

                        <div className="mt-4 grid gap-4 md:grid-cols-3">
                            <Field label={t('auth.gender')} error={form.errors.gender}>
                                <Select value={form.data.gender} onChange={(event) => form.setData('gender', event.target.value)}>
                                    <option value="">— {t('auth.select_option')} —</option>
                                    <option value="male">{t('auth.male')}</option>
                                    <option value="female">{t('auth.female')}</option>
                                </Select>
                            </Field>
                            {text('phone', 'tel', 'tel')}
                            <Field label={t('auth.national_id')} error={form.errors.national_id_number} hint={t('auth.national_id_hint')}>
                                <Input
                                    value={form.data.national_id_number}
                                    onChange={(event) => form.setData('national_id_number', formatNationalId(event.target.value))}
                                    inputMode="numeric"
                                    autoComplete="off"
                                    maxLength={19}
                                    placeholder={t('auth.national_id_placeholder')}
                                    required
                                />
                            </Field>
                        </div>

                        <div className="mt-4 grid gap-4 md:grid-cols-2">{detailFields.map(({ name, autoComplete, type }) => text(name, autoComplete, type))}</div>
                    </section>

                    {isLawyer && (
                        <>
                            <hr className="border-(--border)" />
                            <section>
                                <h2 className="mb-3 font-semibold text-(--text)">{t('auth.lawyer_document')}</h2>
                                {hasLawyerDocument && (
                                    <div className="mb-3 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3">
                                        <span className="inline-flex items-center gap-2 text-sm font-medium text-emerald-800">
                                            <CheckCircleIcon className="h-4 w-4" />
                                            {t('auth.lawyer_document_uploaded')}
                                        </span>
                                        <a
                                            href={route('applicant.profile.lawyer-document')}
                                            target="_blank"
                                            rel="noopener"
                                            className="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-white px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-50"
                                        >
                                            <EyeIcon className="h-4 w-4" />
                                            {t('cases.show.view_lawyer_document')}
                                        </a>
                                    </div>
                                )}
                                <Field label={t(hasLawyerDocument ? 'auth.lawyer_document_replace' : 'auth.lawyer_document')} error={form.errors.lawyer_document} hint={t('auth.lawyer_document_hint')}>
                                    <Input type="file" accept=".pdf,application/pdf" onChange={(event) => form.setData('lawyer_document', event.target.files?.[0] ?? null)} />
                                </Field>
                            </section>
                        </>
                    )}

                    <div className="flex flex-wrap items-center gap-2 pt-2">
                        <Button type="submit" loading={form.processing} className="bg-orange-500 hover:bg-orange-600">
                            {t('auth.save_changes')}
                        </Button>
                        <Link href={route(routes.dashboard)} className="inline-flex items-center rounded-lg bg-(--surface-soft) px-4 py-2.5 font-medium text-(--text-muted) hover:bg-(--border)">
                            {t('auth.back')}
                        </Link>
                    </div>
                </form>
            ) : (
                <section id="security" className="space-y-6">
                    <div className="rounded-xl border border-(--border) bg-(--surface-soft) p-5">
                        <h2 className="text-lg font-semibold text-(--text)">{t('auth.security_section')}</h2>
                        <p className="mt-1 text-sm leading-6 text-(--text-muted)">{t('auth.profile.password_security_hint')}</p>
                    </div>

                    <form onSubmit={savePassword} className="rounded-xl border border-(--border) bg-(--surface-strong) p-5">
                        <h3 className="text-base font-semibold text-(--text)">{t('auth.change_password')}</h3>
                        <div className="mt-5 grid gap-4 md:grid-cols-3">
                            <Field label={t('auth.current_password')} error={security.errors.current_password}>
                                <Input type="password" value={security.data.current_password} onChange={(event) => security.setData('current_password', event.target.value)} autoComplete="current-password" required />
                            </Field>
                            <Field label={t('auth.new_password')} error={security.errors.password}>
                                <Input type="password" value={security.data.password} onChange={(event) => security.setData('password', event.target.value)} autoComplete="new-password" required />
                            </Field>
                            <Field label={t('auth.confirm_new_password')} error={security.errors.password_confirmation}>
                                <Input
                                    type="password"
                                    value={security.data.password_confirmation}
                                    onChange={(event) => security.setData('password_confirmation', event.target.value)}
                                    autoComplete="new-password"
                                    required
                                />
                            </Field>
                        </div>
                        <div className="mt-5 flex justify-end">
                            <Button type="submit" loading={security.processing} className="bg-orange-500 hover:bg-orange-600">
                                {t('auth.save_changes')}
                            </Button>
                        </div>
                    </form>
                </section>
            )}
        </div>
    );
}
