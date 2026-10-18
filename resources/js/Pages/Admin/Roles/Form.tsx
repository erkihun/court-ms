import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { Field } from '@/components/ui/Field';
import { Checkbox, Input, Textarea } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface PermissionOption {
    id: number;
    name: string;
    label: string | null;
}

interface PermissionGroup {
    key: string;
    label: string;
    permissions: PermissionOption[];
}

interface Props {
    /** null when creating. */
    role: { id: number; name: string; description: string; permissionIds: number[]; usersCount: number } | null;
    groups: PermissionGroup[];
}

/** Create and edit a role and the permissions it grants. */
export default function RoleForm({ role, groups }: Props) {
    const editing = role !== null;
    const scope = editing ? 'edit' : 'create';
    const form = useForm({ name: role?.name ?? '', description: role?.description ?? '', permissions: role?.permissionIds ?? [] });
    const allIds = groups.flatMap((group) => group.permissions.map((permission) => permission.id));
    const allSelected = allIds.length > 0 && allIds.every((id) => form.data.permissions.includes(id));

    const toggle = (ids: number[], checked: boolean) =>
        form.setData(
            'permissions',
            checked ? Array.from(new Set([...form.data.permissions, ...ids])) : form.data.permissions.filter((id) => !ids.includes(id)),
        );

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (editing) {
            form.patch(route('roles.update', role.id));
        } else {
            form.post(route('roles.store'));
        }
    };

    return (
        <>
            <Head title={t(`roles.${scope}.title`)} />
            <PageHeader
                title={t(`roles.${scope}.title`)}
                description={editing ? t('roles.edit.subtitle', { name: role.name }) : t('roles.create.subtitle')}
                actions={
                    <LinkButton href={route('roles.index')} variant="secondary">
                        {t(`roles.${scope}.back`)}
                    </LinkButton>
                }
                className="mb-6"
            />

            <form onSubmit={submit} noValidate className="grid gap-6 lg:grid-cols-[20rem_minmax(0,1fr)]">
                <div className="space-y-6">
                    <Card className="space-y-4">
                        <Field label={t('roles.fields.name')} error={form.errors.name} hint={t(`roles.${scope}.name_hint`)} required>
                            <Input value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} maxLength={255} autoFocus={!editing} />
                        </Field>
                        <Field label={t('roles.fields.description')} error={form.errors.description} hint={t(`roles.${scope}.description_hint`)}>
                            <Textarea
                                rows={3}
                                value={form.data.description}
                                onChange={(event) => form.setData('description', event.target.value)}
                                maxLength={255}
                            />
                        </Field>
                    </Card>

                    {editing && (
                        <Card>
                            <h2 className="mb-3 text-sm font-semibold text-(--text)">{t('roles.edit.summary_title')}</h2>
                            <ul className="space-y-2 text-sm text-(--text-muted)">
                                <li className="flex items-center gap-2">
                                    <span className="h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true" />
                                    {t('roles.edit.summary_permissions', { count: allIds.length })}
                                </li>
                                <li className="flex items-center gap-2">
                                    <span className="h-2 w-2 rounded-full bg-blue-500" aria-hidden="true" />
                                    {t('roles.edit.summary_assigned', { count: role.usersCount })}
                                </li>
                            </ul>
                        </Card>
                    )}
                </div>

                <Card className="space-y-4">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 className="text-sm font-semibold text-(--text)">{t('roles.fields.permissions')}</h2>
                            <p className="text-xs text-(--text-subtle)">{t(`roles.${scope}.permissions_hint`)}</p>
                        </div>
                        <Button variant="secondary" size="sm" onClick={() => toggle(allIds, !allSelected)}>
                            {allSelected ? t(`roles.${scope}.deselect_all`) : t(`roles.${scope}.select_all`)}
                        </Button>
                    </div>
                    {form.errors.permissions && <p className="text-xs font-medium text-rose-600">{form.errors.permissions}</p>}

                    <div className="space-y-4">
                        {groups.map((group) => {
                            const ids = group.permissions.map((permission) => permission.id);
                            const selected = ids.filter((id) => form.data.permissions.includes(id)).length;

                            return (
                                <fieldset key={group.key} className="rounded-xl border border-(--border) bg-(--surface-soft) p-4">
                                    <legend className="sr-only">{group.label}</legend>
                                    <div className="mb-3 flex items-center justify-between gap-3">
                                        <Checkbox
                                            checked={selected === ids.length}
                                            onChange={(event) => toggle(ids, event.target.checked)}
                                            label={<span className="font-semibold">{group.label}</span>}
                                        />
                                        <span className="ui-badge ui-badge-emerald">
                                            {selected}/{ids.length}
                                        </span>
                                    </div>
                                    <div className="grid gap-2 md:grid-cols-2">
                                        {group.permissions.map((permission) => (
                                            <label
                                                key={permission.id}
                                                className="flex cursor-pointer items-start gap-2.5 rounded-lg border border-(--border) bg-(--surface-strong) px-3 py-2"
                                            >
                                                <Checkbox
                                                    checked={form.data.permissions.includes(permission.id)}
                                                    onChange={(event) => toggle([permission.id], event.target.checked)}
                                                    className="mt-0.5"
                                                />
                                                <span className="min-w-0">
                                                    <span className="block font-mono text-xs font-medium text-(--text)">{permission.name}</span>
                                                    {permission.label && <span className="block text-xs text-(--text-subtle)">{permission.label}</span>}
                                                </span>
                                            </label>
                                        ))}
                                    </div>
                                </fieldset>
                            );
                        })}
                    </div>

                    <div className="flex justify-end gap-3 pt-2">
                        <LinkButton href={route('roles.index')} variant="secondary">
                            {t(`roles.${scope}.cancel`)}
                        </LinkButton>
                        <Button type="submit" loading={form.processing}>
                            {editing ? t('roles.edit.save') : t('roles.create.submit')}
                        </Button>
                    </div>
                </Card>
            </form>
        </>
    );
}
