import { UsersIcon } from '@heroicons/react/24/outline';
import { Head } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Badge } from '@/components/ui/Badge';
import { Button, LinkButton } from '@/components/ui/Button';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { EmptyState } from '@/components/ui/EmptyState';
import { Input, Select } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { Pagination } from '@/components/ui/Pagination';
import { Table } from '@/components/ui/Table';
import { useFilters } from '@/hooks/useFilters';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import { initials } from '@/lib/str';
import type { Paginated } from '@/types';

interface UserRow {
    id: number;
    name: string;
    email: string;
    avatarUrl: string | null;
    hasSignature: boolean;
    roles: string[];
    active: boolean;
    verified: boolean;
    created: string;
    canEdit: boolean;
    canDelete: boolean;
}

interface Props {
    users: Paginated<UserRow>;
    filters: { q: string; status: '' | 'active' | 'inactive' };
}

export default function UsersIndex({ users, filters }: Props) {
    const { values, set, change, apply, reset, active } = useFilters(route('users.index'), filters);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        apply();
    };

    return (
        <>
            <Head title={t('users.administration')} />
            <PageHeader
                title={t('users.administration')}
                description={t('users.management_subtitle')}
                actions={<LinkButton href={route('users.create')}>{t('users.new_user')}</LinkButton>}
                className="mb-6"
            />

            <form onSubmit={submit} role="search" className="ui-toolbar mb-6 flex flex-col gap-3 md:flex-row md:items-center">
                <label htmlFor="users-q" className="sr-only">
                    {t('app.Search')}
                </label>
                <Input
                    id="users-q"
                    value={values.q}
                    onChange={(event) => set('q', event.target.value)}
                    placeholder={t('users.search_placeholder')}
                    className="md:w-80"
                />
                <label htmlFor="users-status" className="sr-only">
                    {t('users.status')}
                </label>
                <Select
                    id="users-status"
                    value={values.status}
                    onChange={(event) => change('status', event.target.value as Props['filters']['status'])}
                    className="md:w-52"
                >
                    <option value="">{t('users.all_statuses')}</option>
                    <option value="active">{t('users.active')}</option>
                    <option value="inactive">{t('users.inactive')}</option>
                </Select>
                <div className="flex gap-2">
                    <Button type="submit">{t('users.filter')}</Button>
                    {active && (
                        <Button variant="secondary" onClick={reset}>
                            {t('users.reset')}
                        </Button>
                    )}
                </div>
            </form>

            <Table
                head={
                    <tr>
                        <th scope="col">{t('users.user')}</th>
                        <th scope="col">{t('users.email')}</th>
                        <th scope="col">{t('users.roles')}</th>
                        <th scope="col">{t('users.status')}</th>
                        <th scope="col">{t('users.verified_column')}</th>
                        <th scope="col">{t('users.created')}</th>
                        <th scope="col">
                            <span className="sr-only">{t('users.actions')}</span>
                        </th>
                    </tr>
                }
                footer={users.last_page > 1 ? <Pagination meta={users} /> : undefined}
            >
                {users.data.length === 0 ? (
                    <tr>
                        <td colSpan={7} className="py-12">
                            <EmptyState icon={<UsersIcon className="h-6 w-6" />} title={t('users.no_users_found')} />
                        </td>
                    </tr>
                ) : (
                    users.data.map((user) => (
                        <tr key={user.id}>
                            <td>
                                <div className="flex items-center gap-3">
                                    {user.avatarUrl ? (
                                        <img src={user.avatarUrl} alt="" className="h-9 w-9 rounded-full border border-(--border) object-cover" />
                                    ) : (
                                        <span
                                            className="grid h-9 w-9 place-items-center rounded-full border border-blue-200 bg-blue-100 text-xs font-semibold text-blue-700"
                                            aria-hidden="true"
                                        >
                                            {initials(user.name)}
                                        </span>
                                    )}
                                    <div>
                                        <div className="font-semibold text-(--text)">{user.name}</div>
                                        {user.hasSignature && (
                                            <Badge tone="success" className="mt-1">
                                                {t('app.signature')}
                                            </Badge>
                                        )}
                                    </div>
                                </div>
                            </td>
                            <td className="text-(--text-muted)">{user.email}</td>
                            <td className="text-(--text-muted)">{user.roles.length > 0 ? user.roles.join(', ') : '—'}</td>
                            <td>
                                <Badge tone={user.active ? 'success' : 'neutral'}>{user.active ? t('users.active') : t('users.inactive')}</Badge>
                            </td>
                            <td>
                                <Badge tone={user.verified ? 'success' : 'warning'}>{user.verified ? t('users.verified') : t('users.unverified')}</Badge>
                            </td>
                            <td className="whitespace-nowrap text-(--text-muted)">{user.created}</td>
                            <td>
                                <div className="flex flex-wrap justify-end gap-2">
                                    <LinkButton href={route('users.show', user.id)} variant="secondary" size="sm">
                                        {t('users.view_profile')}
                                    </LinkButton>
                                    {user.canEdit && (
                                        <LinkButton href={route('users.edit', user.id)} size="sm">
                                            {t('users.edit_profile')}
                                        </LinkButton>
                                    )}
                                    {user.canDelete && (
                                        <ConfirmButton url={route('users.destroy', user.id)} title={t('users.delete_user_confirm')} message={user.name}>
                                            {t('permissions.index.delete')}
                                        </ConfirmButton>
                                    )}
                                </div>
                            </td>
                        </tr>
                    ))
                )}
            </Table>
        </>
    );
}
