import { ShieldCheckIcon } from '@heroicons/react/24/outline';
import { Head, Link } from '@inertiajs/react';
import { ChipList } from '@/components/admin/accounts/ChipList';
import { Badge } from '@/components/ui/Badge';
import { LinkButton } from '@/components/ui/Button';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { EmptyState } from '@/components/ui/EmptyState';
import { PageHeader } from '@/components/ui/PageHeader';
import { Pagination } from '@/components/ui/Pagination';
import { Table } from '@/components/ui/Table';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Paginated } from '@/types';

interface RoleRow {
    id: number;
    name: string;
    description: string | null;
    permissions: string[];
    usersCount: number;
}

export default function RolesIndex({ roles }: { roles: Paginated<RoleRow> }) {
    return (
        <>
            <Head title={t('roles.index.title')} />
            <PageHeader
                title={t('roles.index.title')}
                description={`${roles.total.toLocaleString()} ${t('roles.index.total_roles')}`}
                actions={<LinkButton href={route('roles.create')}>{t('roles.index.new_role')}</LinkButton>}
                className="mb-6"
            />

            <Table
                head={
                    <tr>
                        <th scope="col">{t('roles.fields.name')}</th>
                        <th scope="col">{t('roles.fields.description')}</th>
                        <th scope="col">{t('roles.fields.permissions')}</th>
                        <th scope="col">{t('roles.fields.users')}</th>
                        <th scope="col">
                            <span className="sr-only">{t('roles.index.actions')}</span>
                        </th>
                    </tr>
                }
                footer={roles.last_page > 1 ? <Pagination meta={roles} /> : undefined}
            >
                {roles.data.length === 0 ? (
                    <tr>
                        <td colSpan={5} className="py-12">
                            <EmptyState icon={<ShieldCheckIcon className="h-6 w-6" />} title={t('roles.index.empty')} />
                        </td>
                    </tr>
                ) : (
                    roles.data.map((role) => (
                        <tr key={role.id} className="align-top">
                            <td>
                                <Link href={route('roles.edit', role.id)} className="font-semibold text-(--text) hover:underline">
                                    {role.name}
                                </Link>
                            </td>
                            <td className="max-w-xs text-(--text-muted)">
                                <span className="line-clamp-2">{role.description || '—'}</span>
                            </td>
                            <td className="max-w-md">
                                <ChipList items={role.permissions} />
                            </td>
                            <td>
                                <Badge tone="info">{role.usersCount}</Badge>
                            </td>
                            <td>
                                <div className="flex justify-end gap-2">
                                    <LinkButton href={route('roles.edit', role.id)} size="sm">
                                        {t('roles.index.edit')}
                                    </LinkButton>
                                    <span title={deleteBlockedReason(role) ?? undefined}>
                                        <ConfirmButton
                                            disabled={deleteBlockedReason(role) !== null}
                                            title={t('roles.index.delete_title')}
                                            url={route('roles.destroy', role.id)}
                                            message={`${t('roles.index.delete_confirm')} ${role.name}?`}
                                        >
                                            {t('roles.index.delete')}
                                        </ConfirmButton>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    ))
                )}
            </Table>
        </>
    );
}

/** The server refuses these deletes too; the reason is shown as the button's tooltip. */
function deleteBlockedReason(role: { name: string; usersCount: number }): string | null {
    if (role.name === 'admin') return t('roles.index.cannot_delete_admin');
    if (role.usersCount > 0) return t('roles.index.cannot_delete_assigned', { count: role.usersCount });
    return null;
}
