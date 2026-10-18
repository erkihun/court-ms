import { Head, Link } from '@inertiajs/react';
import { InspectionStatusBadge, SeverityBadge } from '@/components/admin/inspections/InspectionBadges';
import { LinkButton } from '@/components/ui/Button';
import { Card, CardTitle } from '@/components/ui/Card';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { DescriptionList } from '@/components/ui/DescriptionList';
import { PageHeader } from '@/components/ui/PageHeader';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { InspectionRequestRow } from './Index';

interface Props {
    request: InspectionRequestRow & {
        caseTitle: string | null;
        note: string | null;
        findings: Array<{ id: number; title: string; date: string; severity: string }>;
    };
}

export default function InspectionRequestShow({ request }: Props) {
    const none = t('case_inspections.common.no_data');

    return (
        <>
            <Head title={t('case_inspections.requests.show_title')} />
            <PageHeader
                title={request.subject}
                description={[request.caseNumber, request.caseTitle].filter(Boolean).join(' - ')}
                className="mb-6"
                actions={
                    <>
                        {!request.locked && (
                            <>
                                <LinkButton href={route('case-inspection-requests.edit', request.id)} variant="secondary">
                                    {t('case_inspections.common.edit')}
                                </LinkButton>
                                <ConfirmButton
                                    url={route('case-inspection-requests.destroy', request.id)}
                                    title={t('case_inspections.requests.confirm_delete')}
                                    message={request.subject}
                                    size="md"
                                >
                                    {t('case_inspections.common.delete')}
                                </ConfirmButton>
                            </>
                        )}
                        <LinkButton href={route('case-inspection-requests.index')} variant="secondary">
                            {t('case_inspections.common.back')}
                        </LinkButton>
                    </>
                }
            />

            <div className="space-y-6">
                <Card>
                    <DescriptionList
                        columns={3}
                        items={[
                            { label: t('case_inspections.requests.labels.request_date'), value: request.date },
                            { label: t('case_inspections.requests.labels.status'), value: <InspectionStatusBadge status={request.status} /> },
                            { label: t('case_inspections.requests.labels.inspector'), value: request.inspector },
                            { label: t('case_inspections.requests.labels.created_by'), value: request.createdBy },
                            {
                                label: t('case_inspections.requests.form.request_note'),
                                value: request.note ? <p className="whitespace-pre-line">{request.note}</p> : none,
                                wide: true,
                            },
                        ]}
                    />
                </Card>

                <Card header={<CardTitle title={t('case_inspections.requests.labels.findings')} />}>
                    {request.findings.length === 0 ? (
                        <p className="text-sm text-(--text-subtle)">{t('case_inspections.requests.labels.no_findings')}</p>
                    ) : (
                        <ul className="divide-y divide-(--border)">
                            {request.findings.map((finding) => (
                                <li key={finding.id} className="flex items-center justify-between gap-4 py-3">
                                    <div>
                                        <div className="font-medium text-(--text)">{finding.title}</div>
                                        <div className="mt-1 flex items-center gap-2 text-xs text-(--text-subtle)">
                                            {finding.date}
                                            <SeverityBadge severity={finding.severity} />
                                        </div>
                                    </div>
                                    <Link
                                        href={route('case-inspection-findings.show', finding.id)}
                                        className="text-sm font-medium text-blue-700 hover:underline dark:text-blue-300"
                                    >
                                        {t('case_inspections.common.view')}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </Card>
            </div>
        </>
    );
}
