import { Head, useForm } from '@inertiajs/react';
import { type FormEvent, useEffect, useState } from 'react';
import { ImageUpload } from '@/components/admin/accounts/ImageUpload';
import { ProfileShell } from '@/components/admin/profile/ProfileShell';
import { Alert } from '@/components/ui/Alert';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { Field } from '@/components/ui/Field';
import { Input } from '@/components/ui/Input';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    profile: { name: string; email: string; avatarUrl: string | null; signatureUrl: string | null };
    mfa: { confirmed: boolean; requiredByRole: boolean };
    initialTab: 'profile' | 'security';
}

export default function AdminProfileEdit({ profile, mfa, initialTab }: Props) {
    const [tab, setTab] = useState<'profile' | 'security'>(() => (window.location.hash === '#security' ? 'security' : initialTab));

    useEffect(() => setTab(initialTab), [initialTab]);
    useEffect(() => {
        const onHash = () => setTab(window.location.hash === '#security' ? 'security' : 'profile');
        window.addEventListener('hashchange', onHash);

        return () => window.removeEventListener('hashchange', onHash);
    }, []);

    const select = (next: 'profile' | 'security') => {
        setTab(next);
        window.history.replaceState(window.history.state, '', `#${next}`);
    };

    return (
        <ProfileShell active={tab} onSelect={select}>
            <Head title={t('auth.profile_section')} />
            {tab === 'profile' ? <ProfileForm profile={profile} /> : <SecurityPanel mfa={mfa} />}
        </ProfileShell>
    );
}

function ProfileForm({ profile }: Pick<Props, 'profile'>) {
    const form = useForm<{ name: string; email: string; avatar: File | null }>({ name: profile.name, email: profile.email, avatar: null });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.transform((data) => ({ ...data, _method: 'patch' }));
        form.post(route('profile.update'), { forceFormData: true, preserveScroll: true, onSuccess: () => form.setData('avatar', null) });
    };

    return (
        <form onSubmit={submit} noValidate className="grid gap-6 lg:grid-cols-[1.4fr_.8fr]">
            <Card>
                <div className="mb-6">
                    <h2 className="text-lg font-bold text-(--text)">{t('auth.profile_section')}</h2>
                    <p className="mt-1 text-sm text-(--text-subtle)">{t('auth.profile_basic_info')}</p>
                </div>
                <div className="grid gap-5 md:grid-cols-2">
                    <Field label={t('auth.name')} error={form.errors.name} required>
                        <Input value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} maxLength={255} autoComplete="name" />
                    </Field>
                    <Field label={t('auth.email')} error={form.errors.email} required>
                        <Input
                            type="email"
                            value={form.data.email}
                            onChange={(event) => form.setData('email', event.target.value)}
                            maxLength={255}
                            autoComplete="email"
                        />
                    </Field>
                </div>
                <div className="mt-6 flex justify-end">
                    <Button type="submit" loading={form.processing}>
                        {t('auth.save')}
                    </Button>
                </div>
            </Card>

            <div className="space-y-6">
                <ImageUpload
                    label={t('auth.avatar')}
                    currentUrl={profile.avatarUrl}
                    emptyText={t('users.no_image')}
                    file={form.data.avatar}
                    onFile={(file) => form.setData('avatar', file)}
                    accept="image/png,image/webp,image/jpeg"
                    error={form.errors.avatar}
                    round
                />
                <Card>
                    <h2 className="text-sm font-bold text-(--text)">{t('auth.signature')}</h2>
                    {profile.signatureUrl ? (
                        <img src={profile.signatureUrl} alt={t('auth.signature')} className="mt-3 max-h-16 border border-(--border)" />
                    ) : (
                        <p className="mt-3 text-xs text-(--text-subtle)">{t('auth.no_signature_uploaded')}</p>
                    )}
                    <p className="mt-3 text-xs text-(--text-subtle)">{t('auth.signature_admin_only')}</p>
                </Card>
            </div>
        </form>
    );
}

function SecurityPanel({ mfa }: Pick<Props, 'mfa'>) {
    const password = useForm({ current_password: '', password: '', password_confirmation: '' });
    const disableMfa = useForm({ password: '' });

    const updatePassword = (event: FormEvent) => {
        event.preventDefault();
        password.put(route('password.update'), { errorBag: 'updatePassword', preserveScroll: true, onSuccess: () => password.reset() });
    };

    const turnOffMfa = (event: FormEvent) => {
        event.preventDefault();
        disableMfa.delete(route('mfa.setup.destroy'), { preserveScroll: true, onFinish: () => disableMfa.reset() });
    };

    return (
        <div className="grid gap-6 lg:grid-cols-2">
            <Card>
                <h2 className="text-lg font-bold text-(--text)">{t('auth.update_password')}</h2>
                <p className="mt-1 text-sm text-(--text-subtle)">{t('auth.profile.password_security_hint')}</p>
                <form onSubmit={updatePassword} noValidate className="mt-6 space-y-5">
                    <Field label={t('auth.current_password')} error={password.errors.current_password} required>
                        <Input
                            type="password"
                            value={password.data.current_password}
                            onChange={(event) => password.setData('current_password', event.target.value)}
                            autoComplete="current-password"
                        />
                    </Field>
                    <Field label={t('auth.new_password')} error={password.errors.password} required>
                        <Input
                            type="password"
                            value={password.data.password}
                            onChange={(event) => password.setData('password', event.target.value)}
                            autoComplete="new-password"
                        />
                    </Field>
                    <Field label={t('auth.confirm_password')} error={password.errors.password_confirmation} required>
                        <Input
                            type="password"
                            value={password.data.password_confirmation}
                            onChange={(event) => password.setData('password_confirmation', event.target.value)}
                            autoComplete="new-password"
                        />
                    </Field>
                    <Button type="submit" loading={password.processing}>
                        {t('auth.save')}
                    </Button>
                </form>
            </Card>

            <Card>
                <div className="flex items-start justify-between gap-4">
                    <div>
                        <h2 className="text-lg font-bold text-(--text)">{t('auth.mfa_title')}</h2>
                        <p className="mt-1 text-sm text-(--text-subtle)">{mfa.confirmed ? t('auth.mfa_active') : t('auth.mfa_not_configured')}</p>
                    </div>
                    <span className={mfa.confirmed ? 'ui-badge ui-badge-emerald' : 'ui-badge ui-badge-amber'}>
                        {mfa.confirmed ? t('auth.active') : t('auth.review_needed')}
                    </span>
                </div>

                {mfa.confirmed ? (
                    <>
                        <p className="mt-4 text-sm leading-6 text-(--text-muted)">{t('auth.mfa_active_hint')}</p>
                        {mfa.requiredByRole ? (
                            <Alert tone="warning" className="mt-5">
                                {t('auth.mfa_required_by_role')}
                            </Alert>
                        ) : (
                            <form onSubmit={turnOffMfa} noValidate className="mt-5 space-y-3">
                                <Field label={t('auth.current_password')} error={disableMfa.errors.password} required>
                                    <Input
                                        type="password"
                                        value={disableMfa.data.password}
                                        onChange={(event) => disableMfa.setData('password', event.target.value)}
                                        autoComplete="current-password"
                                    />
                                </Field>
                                <Button type="submit" variant="danger" loading={disableMfa.processing}>
                                    {t('auth.mfa_disable')}
                                </Button>
                            </form>
                        )}
                    </>
                ) : (
                    <>
                        <p className="mt-4 text-sm leading-6 text-(--text-muted)">{t('auth.mfa_setup_hint')}</p>
                        <LinkButton href={route('mfa.setup.show')} className="mt-5">
                            {t('auth.mfa_begin')}
                        </LinkButton>
                    </>
                )}
            </Card>
        </div>
    );
}
