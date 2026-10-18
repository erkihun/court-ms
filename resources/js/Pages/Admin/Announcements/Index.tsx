import { MegaphoneIcon } from '@heroicons/react/24/outline';
import { Head, Link } from '@inertiajs/react';
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

interface AnnouncementRow {
    id: number;
    title: string;
    status: 'active' | 'inactive';
    created: string;
    createdFull: string;
    updated: string;
}

export default function AnnouncementsIndex({ announcements }: { announcements: Paginated<AnnouncementRow> }) {
    const createButton = <LinkButton href={route('announcements.create')}>{t('announcements.create_cta')}</LinkButton>;

    return (
        <>
            <Head title={t('announcements.title')} />
            <PageHeader title={t('announcements.title')} description={t('announcements.manage_description')} actions={createButton} className="mb-6" />

            {announcements.data.length === 0 ? (
                <EmptyState
                    icon={<MegaphoneIcon className="h-6 w-6" />}
                    title={t('announcements.empty_state')}
                    description={t('announcements.empty_state_help')}
                >
                    {createButton}
                </EmptyState>
            ) : (
                <Table
                    head={
                        <tr>
                            <th scope="col">{t('announcements.table_title')}</th>
                            <th scope="col">{t('announcements.table_created')}</th>
                            <th scope="col">{t('announcements.table_updated')}</th>
                            <th scope="col">{t('announcements.form_status_label')}</th>
                            <th scope="col">
                                <span className="sr-only">{t('announcements.table_actions')}</span>
                            </th>
                        </tr>
                    }
                    footer={announcements.last_page > 1 ? <Pagination meta={announcements} /> : undefined}
                >
                    {announcements.data.map((announcement) => (
                        <tr key={announcement.id}>
                            <td>
                                <Link href={route('announcements.show', announcement.id)} className="font-semibold text-(--text) hover:underline">
                                    {announcement.title}
                                </Link>
                                <div className="text-xs text-(--text-subtle)">{announcement.createdFull}</div>
                            </td>
                            <td className="whitespace-nowrap">{announcement.created}</td>
                            <td className="whitespace-nowrap">{announcement.updated}</td>
                            <td>
                                <Badge tone={announcement.status === 'active' ? 'success' : 'neutral'}>
                                    {t(`announcements.status_${announcement.status}`)}
                                </Badge>
                            </td>
                            <td className="whitespace-nowrap">
                                <div className="flex justify-end gap-2">
                                    <LinkButton href={route('announcements.show', announcement.id)} variant="ghost" size="sm">
                                        {t('announcements.action_view')}
                                    </LinkButton>
                                    <LinkButton href={route('announcements.edit', announcement.id)} variant="secondary" size="sm">
                                        {t('announcements.action_edit')}
                                    </LinkButton>
                                    <ConfirmButton
                                        url={route('announcements.destroy', announcement.id)}
                                        title={t('announcements.confirm_delete')}
                                        message={announcement.title}
                                    >
                                        {t('announcements.action_delete')}
                                    </ConfirmButton>
                                </div>
                            </td>
                        </tr>
                    ))}
                </Table>
            )}
        </>
    );
}
