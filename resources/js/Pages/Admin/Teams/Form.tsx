import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card, CardTitle } from '@/components/ui/Card';
import { Field } from '@/components/ui/Field';
import { Checkbox, Input, Select, Textarea } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Option {
    id: number;
    name: string;
}

interface UserOption extends Option {
    email: string;
}

interface Props {
    /** null when creating. */
    team: { id: number; name: string; parentId: number | null; description: string; leaderId: number | null; memberIds: number[] } | null;
    /** Teams that may become the parent (never the team itself or its sub-teams). */
    parents: Option[];
    /** Users not already in another team. */
    users: UserOption[];
}

/** Create a team, or edit its details and members. */
export default function TeamForm({ team, parents, users }: Props) {
    const editing = team !== null;
    const details = useForm({
        name: team?.name ?? '',
        parent_id: team?.parentId ? String(team.parentId) : '',
        description: team?.description ?? '',
        team_leader_id: team?.leaderId ? String(team.leaderId) : '',
    });
    const members = useForm({ users: team?.memberIds ?? [] });

    const saveDetails = (event: FormEvent) => {
        event.preventDefault();
        if (editing) {
            details.patch(route('teams.update', team.id));
        } else {
            details.post(route('teams.store'));
        }
    };

    const saveMembers = (event: FormEvent) => {
        event.preventDefault();
        if (editing) members.patch(route('teams.users.update', team.id), { preserveScroll: true });
    };

    const toggleMember = (id: number, checked: boolean) =>
        members.setData('users', checked ? [...members.data.users, id] : members.data.users.filter((userId) => userId !== id));

    return (
        <>
            <Head title={editing ? t('teams.page_header.edit') : t('teams.page_header.create')} />
            <PageHeader
                title={editing ? t('teams.page_header.edit') : t('teams.page_header.create')}
                description={editing ? t('teams.descriptions.edit_notice') : t('teams.descriptions.details')}
                actions={
                    <LinkButton href={route('teams.index')} variant="secondary">
                        {t('teams.buttons.back_to_list')}
                    </LinkButton>
                }
                className="mb-6"
            />

            <div className={editing ? 'grid gap-6 lg:grid-cols-2' : 'max-w-2xl'}>
                <Card header={<CardTitle title={t('teams.headings.details')} />}>
                    <form onSubmit={saveDetails} noValidate className="space-y-4">
                        <Field label={t('teams.labels.name')} error={details.errors.name} required>
                            <Input value={details.data.name} onChange={(event) => details.setData('name', event.target.value)} maxLength={100} />
                        </Field>
                        <Field label={t('teams.labels.parent')} error={details.errors.parent_id}>
                            <Select value={details.data.parent_id} onChange={(event) => details.setData('parent_id', event.target.value)}>
                                <option value="">{t('teams.labels.none')}</option>
                                {parents.map((parent) => (
                                    <option key={parent.id} value={parent.id}>
                                        {parent.name}
                                    </option>
                                ))}
                            </Select>
                        </Field>
                        <Field label={t('teams.labels.team_leader')} error={details.errors.team_leader_id} hint={t('teams.descriptions.leader_optional')}>
                            <Select value={details.data.team_leader_id} onChange={(event) => details.setData('team_leader_id', event.target.value)}>
                                <option value="">{t('teams.labels.none')}</option>
                                {users.map((user) => (
                                    <option key={user.id} value={user.id}>
                                        {user.name} ({user.email})
                                    </option>
                                ))}
                            </Select>
                        </Field>
                        <Field label={t('teams.labels.description')} error={details.errors.description}>
                            <Textarea rows={4} value={details.data.description} onChange={(event) => details.setData('description', event.target.value)} />
                        </Field>
                        <Button type="submit" loading={details.processing} className="w-full">
                            {editing ? t('teams.buttons.save_changes') : t('teams.buttons.create')}
                        </Button>
                    </form>
                </Card>

                {editing && (
                    <Card header={<CardTitle title={t('teams.headings.members')} description={t('teams.descriptions.members_edit_notice')} />}>
                        <form onSubmit={saveMembers} className="space-y-4">
                            <p className="text-xs text-(--text-subtle)">{t('teams.descriptions.members_leader_notice')}</p>
                            <fieldset>
                                <legend className="sr-only">{t('teams.headings.members')}</legend>
                                <div className="grid max-h-80 gap-1 overflow-y-auto rounded-lg border border-(--border) p-2 sm:grid-cols-2">
                                    {users.map((user) => (
                                        <label
                                            key={user.id}
                                            className="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-(--surface-soft)"
                                        >
                                            <Checkbox
                                                checked={members.data.users.includes(user.id)}
                                                onChange={(event) => toggleMember(user.id, event.target.checked)}
                                            />
                                            <span className="min-w-0 truncate text-(--text)">
                                                {user.name} <span className="text-(--text-subtle)">({user.email})</span>
                                            </span>
                                        </label>
                                    ))}
                                </div>
                            </fieldset>
                            <Button type="submit" loading={members.processing} className="w-full">
                                {t('teams.buttons.save_members')}
                            </Button>
                        </form>
                    </Card>
                )}
            </div>
        </>
    );
}
