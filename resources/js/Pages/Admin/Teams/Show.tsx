import { Head } from '@inertiajs/react';
import { LinkButton } from '@/components/ui/Button';
import { Card, CardTitle } from '@/components/ui/Card';
import { PageHeader } from '@/components/ui/PageHeader';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    team: {
        id: number;
        name: string;
        parentName: string | null;
        leaderName: string | null;
        description: string | null;
        members: Array<{ id: number; name: string; email: string }>;
    };
}

export default function TeamShow({ team }: Props) {
    return (
        <>
            <Head title={t('teams.headings.overview')} />
            <PageHeader
                title={team.name}
                description={team.parentName ? t('teams.labels.reports_to', { team: team.parentName }) : t('teams.labels.top_level')}
                actions={
                    <LinkButton href={route('teams.index')} variant="secondary">
                        {t('teams.buttons.back_to_list')}
                    </LinkButton>
                }
                className="mb-6"
            >
                <p className="mt-3 text-sm text-(--text-muted)">
                    {t('teams.labels.leader_prefix')} <span className="font-semibold text-(--text)">{team.leaderName ?? t('teams.meta.unassigned')}</span>
                </p>
                <p className="mt-2 text-sm whitespace-pre-line text-(--text-muted)">{team.description || t('teams.descriptions.description_missing')}</p>
            </PageHeader>

            <Card
                header={
                    <CardTitle
                        title={t('teams.headings.members')}
                        description={`${team.members.length} ${t('teams.labels.assigned')}`}
                        actions={
                            <LinkButton href={route('teams.edit', team.id)} variant="secondary" size="sm">
                                {t('teams.buttons.manage_members')}
                            </LinkButton>
                        }
                    />
                }
            >
                {team.members.length === 0 ? (
                    <p className="text-sm text-(--text-subtle)">{t('teams.descriptions.members_empty')}</p>
                ) : (
                    <ul className="divide-y divide-(--border) text-sm">
                        {team.members.map((member) => (
                            <li key={member.id} className="py-2">
                                <div className="font-semibold text-(--text)">{member.name}</div>
                                <div className="text-xs text-(--text-subtle)">{member.email}</div>
                            </li>
                        ))}
                    </ul>
                )}
            </Card>
        </>
    );
}
