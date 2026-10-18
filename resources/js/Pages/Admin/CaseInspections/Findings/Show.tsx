import { Head } from '@inertiajs/react';
import { SeverityBadge } from '@/components/admin/inspections/InspectionBadges';
import { Badge } from '@/components/ui/Badge';
import { LinkButton } from '@/components/ui/Button';
import { Card, CardTitle } from '@/components/ui/Card';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { DescriptionList } from '@/components/ui/DescriptionList';
import { PageHeader } from '@/components/ui/PageHeader';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { FindingRow } from './Index';

interface Props {
    finding: FindingRow & { details: string; recordedBy: string | null; acceptedBy: string | null };
    canAccept: boolean;
}

export default function InspectionFindingShow({ finding, canAccept }: Props) {
    const none = t('case_inspections.common.no_data');

    return (
        <>
            <Head title={t('case_inspections.findings.show_title')} />
            <PageHeader
                title={finding.title}
                description={[finding.caseNumber, finding.requestSubject].filter(Boolean).join(' - ')}
                className="mb-6"
                actions={
                    <>
                        {canAccept && (
                            <ConfirmButton
                                url={route('case-inspection-findings.accept', finding.id)}
                                method="post"
                                variant="success"
                                size="md"
                                title={t('case_inspections.findings.accept')}
                                message={finding.title}
                            >
                                {t('case_inspections.findings.accept')}
                            </ConfirmButton>
                        )}
                        {!finding.accepted && (
                            <>
                                <LinkButton href={route('case-inspection-findings.edit', finding.id)} variant="secondary">
                                    {t('case_inspections.common.edit')}
                                </LinkButton>
                                <ConfirmButton
                                    url={route('case-inspection-findings.destroy', finding.id)}
                                    title={t('case_inspections.findings.confirm_delete')}
                                    message={finding.title}
                                    size="md"
                                >
                                    {t('case_inspections.common.delete')}
                                </ConfirmButton>
                            </>
                        )}
                        <LinkButton href={route('case-inspection-findings.index')} variant="secondary">
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
                            { label: t('case_inspections.findings.labels.finding_date'), value: finding.date },
                            { label: t('case_inspections.findings.labels.severity'), value: <SeverityBadge severity={finding.severity} /> },
                            { label: t('case_inspections.findings.labels.recorded_by'), value: finding.recordedBy },
                            {
                                label: t('case_inspections.findings.labels.attachment_pdf'),
                                value: finding.attachmentUrl ? (
                                    <a
                                        href={finding.attachmentUrl}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-blue-700 underline dark:text-blue-300"
                                    >
                                        {finding.attachmentName ?? t('case_inspections.findings.labels.download_attachment')}
                                    </a>
                                ) : (
                                    none
                                ),
                            },
                            {
                                label: t('case_inspections.findings.labels.accepted'),
                                value: finding.accepted ? (
                                    <span className="inline-flex items-center gap-2">
                                        <Badge tone="success">{t('case_inspections.findings.accepted')}</Badge>
                                        {finding.acceptedBy ?? none}
                                    </span>
                                ) : (
                                    t('case_inspections.findings.not_accepted')
                                ),
                            },
                        ]}
                    />
                </Card>

                <Card header={<CardTitle title={t('case_inspections.findings.labels.details')} />}>
                    <p className="text-sm whitespace-pre-line text-(--text)">{finding.details}</p>
                </Card>
            </div>
        </>
    );
}
