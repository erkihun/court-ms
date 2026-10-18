import { UserGroupIcon } from '@heroicons/react/24/outline';
import { Head } from '@inertiajs/react';
import { Button, LinkButton } from '@/components/ui/Button';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { EmptyState } from '@/components/ui/EmptyState';
import { PageHeader } from '@/components/ui/PageHeader';
import { Table } from '@/components/ui/Table';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface TeamRow {
    id: number;
    name: string;
    parentName: string | null;
    leaderName: string | null;
    leaderEmail: string | null;
    description: string | null;
    membersCount: number;
}

export default function TeamsIndex({ teams }: { teams: TeamRow[] }) {
    return (
        <>
            <Head title={t('teams.title')} />
            <PageHeader
                title={t('teams.headings.current')}
                description={t('teams.descriptions.current')}
                actions={<LinkButton href={route('teams.create')}>{t('teams.buttons.add_new')}</LinkButton>}
                className="mb-6"
            />

            <Table
                head={
                    <tr>
                        <th scope="col">{t('teams.labels.team')}</th>
                        <th scope="col">{t('teams.labels.members')}</th>
                        <th scope="col">{t('teams.labels.leader')}</th>
                        <th scope="col">{t('teams.labels.description')}</th>
                        <th scope="col">
                            <span className="sr-only">{t('teams.labels.actions')}</span>
                        </th>
                    </tr>
                }
            >
                {teams.length === 0 ? (
                    <tr>
                        <td colSpan={5} className="py-12">
                            <EmptyState icon={<UserGroupIcon className="h-6 w-6" />} title={t('teams.empty')} />
                        </td>
                    </tr>
                ) : (
                    teams.map((team) => (
                        <tr key={team.id} className="align-top">
                            <td>
                                <div className="font-semibold text-(--text)">{team.name}</div>
                                <p className="text-xs text-(--text-subtle)">
                                    {team.parentName ? t('teams.labels.reports_to', { team: team.parentName }) : t('teams.labels.top_level')}
                                </p>
                            </td>
                            <td>
                                <div className="font-semibold text-(--text)">{team.membersCount}</div>
                                <p className="text-xs text-(--text-subtle)">{t('teams.labels.member_count')}</p>
                            </td>
                            <td>
                                <div className="font-semibold text-(--text)">{team.leaderName ?? t('teams.meta.unassigned')}</div>
                                {team.leaderEmail && <p className="text-xs text-(--text-subtle)">{team.leaderEmail}</p>}
                            </td>
                            <td className="max-w-sm text-(--text-muted)">{team.description ?? t('teams.descriptions.description_optional')}</td>
                            <td>
                                <div className="flex flex-wrap justify-end gap-2">
                                    <LinkButton href={route('teams.show', team.id)} variant="secondary" size="sm">
                                        {t('teams.buttons.view')}
                                    </LinkButton>
                                    <LinkButton href={route('teams.edit', team.id)} size="sm">
                                        {t('teams.buttons.edit')}
                                    </LinkButton>
                                    {team.membersCount > 0 ? (
                                        // The server refuses to delete a team that still has members.
                                        <Button variant="danger" size="sm" disabled title={t('messages.error.team_has_members')}>
                                            {t('teams.buttons.delete')}
                                        </Button>
                                    ) : (
                                        <ConfirmButton url={route('teams.destroy', team.id)} title={t('teams.confirmations.delete')} message={team.name}>
                                            {t('teams.buttons.delete')}
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
