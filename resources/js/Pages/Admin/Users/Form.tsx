import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { ImageUpload } from '@/components/admin/accounts/ImageUpload';
import { Alert } from '@/components/ui/Alert';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { EthDateInput } from '@/components/ui/EthDateInput';
import { Field } from '@/components/ui/Field';
import { Checkbox, Input, Select } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { formatNationalId } from '@/lib/format';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface RoleOption {
    id: number;
    name: string;
    /** The admin role, which only administrators may assign. */
    disabled: boolean;
}

interface EditableUser {
    id: number;
    firstName: string;
    middleName: string;
    lastName: string;
    email: string;
    status: 'active' | 'inactive';
    gender: '' | 'male' | 'female';
    dateOfBirth: string;
    nationalId: string;
    position: string;
    phone: string;
    address: string;
    roleIds: number[];
    avatarUrl: string | null;
    signatureUrl: string | null;
    stampUrl: string | null;
}

interface Props {
    /** null when creating. */
    user: EditableUser | null;
    roles: RoleOption[];
    /** False when a user without users.manage edits their own record. */
    canManage: boolean;
}

interface FormData {
    first_name: string;
    middle_name: string;
    last_name: string;
    email: string;
    status: 'active' | 'inactive';
    gender: '' | 'male' | 'female';
    date_of_birth: string;
    national_id: string;
    position: string;
    phone: string;
    address: string;
    roles: number[];
    password: string;
    password_confirmation: string;
    avatar: File | null;
    signature: File | null;
    stamp: File | null;
    remove_avatar: boolean;
    remove_signature: boolean;
    remove_stamp: boolean;
}

/** Create and edit a staff account; `user` is null when creating. */
export default function UserForm({ user, roles, canManage }: Props) {
    const editing = user !== null;
    const form = useForm<FormData>({
        first_name: user?.firstName ?? '',
        middle_name: user?.middleName ?? '',
        last_name: user?.lastName ?? '',
        email: user?.email ?? '',
        status: user?.status ?? 'active',
        gender: user?.gender ?? '',
        date_of_birth: user?.dateOfBirth ?? '',
        national_id: formatNationalId(user?.nationalId ?? ''),
        position: user?.position ?? '',
        phone: user?.phone ?? '',
        address: user?.address ?? '',
        roles: user?.roleIds ?? [],
        password: '',
        password_confirmation: '',
        avatar: null,
        signature: null,
        stamp: null,
        remove_avatar: false,
        remove_signature: false,
        remove_stamp: false,
    });
    const { data, setData, errors, processing } = form;
    const title = editing ? t('users.edit_profile_title') : t('users.create_account_title');

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const scrollToErrors = () => window.scrollTo({ top: 0, behavior: 'smooth' });

        if (editing) {
            form.transform((values) => ({ ...values, _method: 'patch' }));
            form.post(route('users.update', user.id), { forceFormData: true, onError: scrollToErrors });
        } else {
            // The password and remove_* fields are not part of the create contract and are ignored by the server.
            form.post(route('users.store'), { forceFormData: true, onError: scrollToErrors });
        }
    };

    const toggleRole = (id: number, checked: boolean) => setData('roles', checked ? [...data.roles, id] : data.roles.filter((roleId) => roleId !== id));

    return (
        <>
            <Head title={editing ? t('users.edit_user_title') : t('users.new_user_title')} />
            <PageHeader
                title={title}
                description={editing ? t('users.edit_profile_subtitle') : t('users.create_account_subtitle')}
                actions={
                    <LinkButton href={editing ? route('users.show', user.id) : route('users.index')} variant="secondary">
                        {editing ? t('users.view_profile') : t('users.back_to_users')}
                    </LinkButton>
                }
                className="mb-6"
            />

            <form onSubmit={submit} noValidate className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
                <Card className="space-y-5">
                    {!canManage && <Alert tone="info">{t('users.self_edit_notice')}</Alert>}

                    <div className="grid gap-4 md:grid-cols-3">
                        <Field label={t('users.first_name')} error={errors.first_name} required>
                            <Input
                                value={data.first_name}
                                onChange={(event) => setData('first_name', event.target.value)}
                                maxLength={255}
                                autoComplete="given-name"
                            />
                        </Field>
                        <Field label={t('users.middle_name')} error={errors.middle_name}>
                            <Input
                                value={data.middle_name}
                                onChange={(event) => setData('middle_name', event.target.value)}
                                maxLength={255}
                                autoComplete="additional-name"
                            />
                        </Field>
                        <Field label={t('users.last_name')} error={errors.last_name} required>
                            <Input
                                value={data.last_name}
                                onChange={(event) => setData('last_name', event.target.value)}
                                maxLength={255}
                                autoComplete="family-name"
                            />
                        </Field>
                    </div>

                    <Field label={t('users.email')} error={errors.email} required>
                        <Input
                            type="email"
                            value={data.email}
                            onChange={(event) => setData('email', event.target.value)}
                            maxLength={255}
                            autoComplete="email"
                        />
                    </Field>

                    <div className="grid gap-4 md:grid-cols-2">
                        <Field label={t('users.gender')} error={errors.gender}>
                            <Select value={data.gender} onChange={(event) => setData('gender', event.target.value as FormData['gender'])}>
                                <option value="">{t('users.select')}</option>
                                <option value="male">{t('users.male')}</option>
                                <option value="female">{t('users.female')}</option>
                            </Select>
                        </Field>
                        <Field label={t('users.date_of_birth')} error={errors.date_of_birth}>
                            <EthDateInput value={data.date_of_birth} onChange={(value) => setData('date_of_birth', value)} invalid={!!errors.date_of_birth} />
                        </Field>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        <Field label={t('users.national_id')} error={errors.national_id} hint={t('users.national_id_help')}>
                            <Input
                                value={data.national_id}
                                onChange={(event) => setData('national_id', formatNationalId(event.target.value))}
                                inputMode="numeric"
                                maxLength={19}
                                placeholder="0000 0000 0000 0000"
                            />
                        </Field>
                        <Field label={t('users.position')} error={errors.position}>
                            <Input value={data.position} onChange={(event) => setData('position', event.target.value)} maxLength={255} />
                        </Field>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        <Field label={t('users.phone')} error={errors.phone}>
                            <Input
                                type="tel"
                                value={data.phone}
                                onChange={(event) => setData('phone', event.target.value)}
                                maxLength={255}
                                autoComplete="tel"
                            />
                        </Field>
                        <Field label={t('users.address')} error={errors.address}>
                            <Input value={data.address} onChange={(event) => setData('address', event.target.value)} autoComplete="street-address" />
                        </Field>
                    </div>

                    {canManage && (
                        <Field label={t('users.status')} error={errors.status} required>
                            <Select value={data.status} onChange={(event) => setData('status', event.target.value as FormData['status'])}>
                                <option value="active">{t('users.active')}</option>
                                <option value="inactive">{t('users.inactive')}</option>
                            </Select>
                        </Field>
                    )}

                    {editing && canManage && (
                        <div className="grid gap-4 md:grid-cols-2">
                            <Field label={t('users.new_password_optional')} error={errors.password}>
                                <Input
                                    type="password"
                                    value={data.password}
                                    onChange={(event) => setData('password', event.target.value)}
                                    autoComplete="new-password"
                                />
                            </Field>
                            <Field label={t('users.confirm_password')} error={errors.password_confirmation}>
                                <Input
                                    type="password"
                                    value={data.password_confirmation}
                                    onChange={(event) => setData('password_confirmation', event.target.value)}
                                    autoComplete="new-password"
                                />
                            </Field>
                        </div>
                    )}

                    {!editing && <Alert tone="info">{t('users.reset_link_notice')}</Alert>}
                </Card>

                <div className="space-y-6">
                    {canManage && (
                        <Card>
                            <fieldset>
                                <legend className="mb-3 text-sm font-semibold text-(--text)">{editing ? t('users.roles') : t('users.assign_roles')}</legend>
                                <div className="max-h-80 space-y-2 overflow-auto pe-1">
                                    {roles.map((role) => (
                                        <label
                                            key={role.id}
                                            className="flex items-center gap-2.5 rounded-lg border border-(--border) px-3 py-2 text-sm text-(--text) has-disabled:opacity-50"
                                        >
                                            <Checkbox
                                                checked={data.roles.includes(role.id)}
                                                disabled={role.disabled && !data.roles.includes(role.id)}
                                                onChange={(event) => toggleRole(role.id, event.target.checked)}
                                            />
                                            <span>{role.name}</span>
                                        </label>
                                    ))}
                                </div>
                                {(errors.roles || errors['roles.0']) && (
                                    <p className="mt-2 text-xs font-medium text-rose-600">{errors.roles ?? errors['roles.0']}</p>
                                )}
                            </fieldset>
                        </Card>
                    )}

                    <Card className="space-y-4">
                        <ImageUpload
                            label={t('users.avatar')}
                            currentUrl={user?.avatarUrl ?? null}
                            emptyText={t('users.no_image')}
                            file={data.avatar}
                            onFile={(file) => setData('avatar', file)}
                            remove={editing ? { checked: data.remove_avatar, onChange: (checked) => setData('remove_avatar', checked) } : undefined}
                            accept="image/png,image/webp,image/jpeg"
                            error={errors.avatar}
                            round
                        />
                        {canManage && (
                            <>
                                <ImageUpload
                                    label={t('users.signature')}
                                    currentUrl={user?.signatureUrl ?? null}
                                    emptyText={t('users.no_signature')}
                                    file={data.signature}
                                    onFile={(file) => setData('signature', file)}
                                    remove={
                                        editing ? { checked: data.remove_signature, onChange: (checked) => setData('remove_signature', checked) } : undefined
                                    }
                                    error={errors.signature}
                                />
                                <ImageUpload
                                    label={t('users.stamp')}
                                    currentUrl={user?.stampUrl ?? null}
                                    emptyText={t('users.no_stamp')}
                                    file={data.stamp}
                                    onFile={(file) => setData('stamp', file)}
                                    remove={editing ? { checked: data.remove_stamp, onChange: (checked) => setData('remove_stamp', checked) } : undefined}
                                    error={errors.stamp}
                                />
                            </>
                        )}
                    </Card>

                    <div className="flex justify-end gap-3">
                        <LinkButton href={route('users.index')} variant="secondary">
                            {t('users.cancel')}
                        </LinkButton>
                        <Button type="submit" loading={processing}>
                            {editing ? t('users.save') : t('users.create_user')}
                        </Button>
                    </div>
                </div>
            </form>
        </>
    );
}
