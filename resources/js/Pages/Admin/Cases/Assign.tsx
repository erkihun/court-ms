import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { CaseStatusBadge } from '@/components/admin/cases/CaseBadges';
import { Alert } from '@/components/ui/Alert';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { DescriptionList } from '@/components/ui/DescriptionList';
import { Field } from '@/components/ui/Field';
import { Checkbox, Select } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Person {
    id: number;
    name: string;
    email: string;
}

interface Props {
    case: {
        id: number;
        caseNumber: string;
        title: string;
        status: string;
        assignedUserId: number | null;
        assigneeName: string | null;
        assigneeEmail: string | null;
        assignedAt: string | null;
    };
    mode: 'leader' | 'admin';
    leaderTeamName: string | null;
    groups: Array<{ label: string; users: Person[] }>;
    canAssignLeaders: boolean;
}

export default function CaseAssign({ case: courtCase, mode, leaderTeamName, groups, canAssignLeaders }: Props) {
    const form = useForm({
        assigned_user_id: courtCase.assignedUserId ? String(courtCase.assignedUserId) : '',
        unassign: false,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.patch(route('cases.assign.update', courtCase.id));
    };

    const [leaderBefore, leaderAfter] = t('cases.assign.leader_context').split(':team');

    return (
        <>
            <Head title={t('cases.assign.assign_case')} />
            <PageHeader title={t('cases.assign.assign_case')} className="mb-6" />

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <Card header={<h2 className="ui-section-title">{t('cases.assign.summary_title')}</h2>}>
                    <DescriptionList
                        columns={1}
                        items={[
                            { label: t('cases.assign.case_number'), value: <span className="font-mono">{courtCase.caseNumber}</span> },
                            { label: t('cases.assign.title'), value: courtCase.title },
                            { label: t('cases.assign.status'), value: <CaseStatusBadge status={courtCase.status} /> },
                            {
                                label: t('cases.assign.current_assignee'),
                                value: courtCase.assigneeName ? (
                                    <>
                                        {courtCase.assigneeName} <span className="text-xs text-(--text-subtle)">({courtCase.assigneeEmail})</span>
                                    </>
                                ) : (
                                    <span className="text-(--text-subtle)">{t('cases.assign.unassigned')}</span>
                                ),
                            },
                            ...(courtCase.assignedAt ? [{ label: t('cases.assign.assigned_at'), value: courtCase.assignedAt }] : []),
                        ]}
                    />
                    <LinkButton href={route('cases.index')} variant="secondary" className="mt-5">
                        {t('cases.assign.back')}
                    </LinkButton>
                </Card>

                <Card className="lg:col-span-2">
                    <form onSubmit={submit} className="space-y-5" noValidate>
                        {!canAssignLeaders && <Alert tone="warning">{t('cases.assign.permission_warning')}</Alert>}
                        {mode === 'leader' && leaderTeamName ? (
                            <Alert tone="info">
                                {leaderBefore}
                                <strong>{leaderTeamName}</strong>
                                {leaderAfter}
                            </Alert>
                        ) : (
                            <Alert tone="info">{t('cases.assign.admin_context')}</Alert>
                        )}

                        <Field label={t('cases.assign.assign_to')} error={form.errors.assigned_user_id}>
                            <Select
                                value={form.data.assigned_user_id}
                                onChange={(event) => form.setData('assigned_user_id', event.target.value)}
                                disabled={form.data.unassign}
                            >
                                <option value="">{t('cases.assign.select_user')}</option>
                                {groups.map((group) => (
                                    <optgroup key={group.label} label={group.label}>
                                        {group.users.map((user) => (
                                            <option key={user.id} value={String(user.id)}>
                                                {user.name} - {user.email}
                                            </option>
                                        ))}
                                    </optgroup>
                                ))}
                            </Select>
                        </Field>

                        {courtCase.assignedUserId !== null && (
                            <Checkbox
                                checked={form.data.unassign}
                                onChange={(event) => form.setData('unassign', event.target.checked)}
                                label={t('cases.assign.unassign')}
                            />
                        )}

                        <div className="flex gap-3 pt-2">
                            <Button type="submit" loading={form.processing}>
                                {t('cases.general.save')}
                            </Button>
                            <LinkButton href={route('cases.index')} variant="secondary">
                                {t('cases.general.cancel')}
                            </LinkButton>
                        </div>
                    </form>
                </Card>
            </div>
        </>
    );
}
