import { KeyIcon } from '@heroicons/react/24/outline';
import { Head, Link } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { ChipList } from '@/components/admin/accounts/ChipList';
import { Badge } from '@/components/ui/Badge';
import { Button, LinkButton } from '@/components/ui/Button';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { EmptyState } from '@/components/ui/EmptyState';
import { Input } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { Pagination } from '@/components/ui/Pagination';
import { Table } from '@/components/ui/Table';
import { useFilters } from '@/hooks/useFilters';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Paginated } from '@/types';

interface PermissionRow {
    id: number;
    name: string;
    label: string | null;
    description: string | null;
    roles: string[];
    usersCount: number;
}

interface Props {
    permissions: Paginated<PermissionRow>;
    filters: { q: string };
}

export default function PermissionsIndex({ permissions, filters }: Props) {
    const { values, set, apply } = useFilters(route('permissions.index'), filters);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        apply();
    };

    return (
        <>
            <Head title={t('permissions.index.title')} />
            <PageHeader
                title={t('permissions.index.title')}
                description={`${permissions.total.toLocaleString()} ${t('permissions.index.total_permissions')}`}
                actions={<LinkButton href={route('permissions.create')}>{t('permissions.index.new_permission')}</LinkButton>}
                className="mb-6"
            />

            <form onSubmit={submit} role="search" className="ui-toolbar mb-6 flex gap-3">
                <label htmlFor="permissions-q" className="sr-only">
                    {t('permissions.index.search_placeholder')}
                </label>
                <Input
                    id="permissions-q"
                    value={values.q}
                    onChange={(event) => set('q', event.target.value)}
                    placeholder={t('permissions.index.search_placeholder')}
                    className="md:w-80"
                />
                <Button type="submit" variant="secondary">
                    {t('app.Search')}
                </Button>
            </form>

            <Table
                head={
                    <tr>
                        <th scope="col">{t('permissions.fields.name')}</th>
                        <th scope="col">{t('permissions.fields.label')}</th>
                        <th scope="col">{t('permissions.fields.description')}</th>
                        <th scope="col">{t('permissions.index.roles')}</th>
                        <th scope="col">{t('permissions.index.users')}</th>
                        <th scope="col">
                            <span className="sr-only">{t('permissions.index.actions')}</span>
                        </th>
                    </tr>
                }
                footer={permissions.last_page > 1 ? <Pagination meta={permissions} /> : undefined}
            >
                {permissions.data.length === 0 ? (
                    <tr>
                        <td colSpan={6} className="py-12">
                            <EmptyState icon={<KeyIcon className="h-6 w-6" />} title={t('permissions.index.empty')} />
                        </td>
                    </tr>
                ) : (
                    permissions.data.map((permission) => (
                        <tr key={permission.id} className="align-top">
                            <td>
                                <Link href={route('permissions.edit', permission.id)} className="font-mono text-sm font-semibold text-(--text) hover:underline">
                                    {permission.name}
                                </Link>
                            </td>
                            <td className="text-(--text-muted)">{permission.label || '—'}</td>
                            <td className="max-w-xs text-(--text-muted)">
                                <span className="line-clamp-2">{permission.description || '—'}</span>
                            </td>
                            <td className="max-w-sm">
                                <ChipList items={permission.roles} />
                                <div className="mt-1 text-xs text-(--text-subtle)">
                                    {permission.roles.length} {t('permissions.index.roles_count')}
                                </div>
                            </td>
                            <td>
                                <Badge tone="info">{permission.usersCount}</Badge>
                            </td>
                            <td>
                                <div className="flex justify-end gap-2">
                                    <LinkButton href={route('permissions.edit', permission.id)} size="sm">
                                        {t('permissions.index.edit')}
                                    </LinkButton>
                                    <ConfirmButton
                                        url={route('permissions.destroy', permission.id)}
                                        title={t('permissions.index.delete_title')}
                                        message={`${t('permissions.index.delete_confirm')} ${permission.name}?`}
                                    >
                                        {t('permissions.index.delete')}
                                    </ConfirmButton>
                                </div>
                            </td>
                        </tr>
                    ))
                )}
            </Table>
        </>
    );
}
