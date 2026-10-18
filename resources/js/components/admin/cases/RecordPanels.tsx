import { Link } from '@inertiajs/react';
import { Fragment, type ReactNode, useState } from 'react';
import { Badge, type BadgeTone } from '@/components/ui/Badge';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { CaseAudit, CaseLetterRow, InspectionFindingRow, InspectionRequestRow } from '@/types/admin/case';
import { CaseSection, EmptyBox, SectionTable, td, th } from './CaseSection';

export function AuditsSection({ audits }: { audits: CaseAudit[] }) {
    return (
        <CaseSection id="audits" title={t('cases.case_audit_trail')} count={audits.length} countLabel={t('cases.show.entries')}>
            {audits.length === 0 ? (
                <EmptyBox>{t('cases.no_audit_records')}</EmptyBox>
            ) : (
                <SectionTable
                    head={
                        <tr>
                            <th className={th}>{t('cases.show.when')}</th>
                            <th className={th}>{t('cases.show.action')}</th>
                            <th className={th}>{t('cases.show.actor')}</th>
                            <th className={th}>{t('cases.show.details')}</th>
                        </tr>
                    }
                >
                    {audits.map((audit) => (
                        <tr key={audit.id}>
                            <td className={`${td} whitespace-nowrap`}>{audit.when}</td>
                            <td className={`${td} font-medium text-(--text)`}>{audit.action}</td>
                            <td className={`${td} text-xs`}>{audit.actor}</td>
                            <td className={`${td} text-xs`}>
                                {audit.meta ? (
                                    <pre className="rounded-sm border border-(--border) bg-(--surface-soft) px-2 py-1 text-[11px] whitespace-pre-wrap">
                                        {audit.meta}
                                    </pre>
                                ) : (
                                    '—'
                                )}
                            </td>
                        </tr>
                    ))}
                </SectionTable>
            )}
        </CaseSection>
    );
}

export function InspectionRequestsSection({ requests }: { requests: InspectionRequestRow[] }) {
    const none = t('case_inspections.common.no_data');

    return (
        <CaseSection id="inspection-requests" title={t('case_inspections.requests.index_title')} count={requests.length} countLabel={t('cases.show.entries')}>
            {requests.length === 0 ? (
                <EmptyBox>{t('case_inspections.requests.empty')}</EmptyBox>
            ) : (
                <SectionTable
                    head={
                        <tr>
                            <th className={th}>{t('case_inspections.requests.table_date')}</th>
                            <th className={th}>{t('case_inspections.requests.table_subject')}</th>
                            <th className={th}>{t('case_inspections.requests.table_status')}</th>
                            <th className={th}>{t('case_inspections.requests.table_inspector')}</th>
                            <th className={th}>{t('case_inspections.requests.table_created_by')}</th>
                            <th className={`${th} text-right`}>{t('cases.labels.actions')}</th>
                        </tr>
                    }
                >
                    {requests.map((request) => (
                        <tr key={request.id}>
                            <td className={td}>{request.date}</td>
                            <td className={`${td} text-(--text)`}>{request.subject}</td>
                            <td className={td}>{t(`case_inspections.status.${request.status}`)}</td>
                            <td className={td}>{request.inspector ?? none}</td>
                            <td className={td}>{request.createdBy ?? none}</td>
                            <td className={`${td} space-x-3 text-right whitespace-nowrap`}>
                                <Link href={route('case-inspection-requests.show', request.id)} className="text-blue-700 hover:underline dark:text-blue-300">
                                    {t('case_inspections.common.view')}
                                </Link>
                                {request.status !== 'completed' && (
                                    <Link
                                        href={route('case-inspection-requests.edit', request.id)}
                                        className="text-amber-700 hover:underline dark:text-amber-300"
                                    >
                                        {t('case_inspections.common.edit')}
                                    </Link>
                                )}
                            </td>
                        </tr>
                    ))}
                </SectionTable>
            )}
        </CaseSection>
    );
}

export function InspectionFindingsSection({ findings, canAccept }: { findings: InspectionFindingRow[]; canAccept: boolean }) {
    const [expanded, setExpanded] = useState<number | null>(null);
    const none = t('case_inspections.common.no_data');

    return (
        <CaseSection id="inspection-findings" title={t('case_inspections.findings.index_title')} count={findings.length} countLabel={t('cases.show.entries')}>
            {findings.length === 0 ? (
                <EmptyBox>{t('case_inspections.findings.empty')}</EmptyBox>
            ) : (
                <SectionTable
                    head={
                        <tr>
                            <th className={th}>{t('case_inspections.findings.table_date')}</th>
                            <th className={th}>{t('case_inspections.findings.table_title')}</th>
                            <th className={th}>{t('case_inspections.findings.table_severity')}</th>
                            <th className={th}>{t('case_inspections.findings.table_request')}</th>
                            <th className={th}>{t('case_inspections.requests.table_inspector')}</th>
                            <th className={`${th} text-right`}>{t('cases.labels.actions')}</th>
                        </tr>
                    }
                >
                    {findings.map((finding) => (
                        <Fragment key={finding.id}>
                            <tr>
                                <td className={td}>{finding.date}</td>
                                <td className={`${td} text-(--text)`}>
                                    {finding.title}
                                    {finding.accepted && (
                                        <Badge tone="success" className="ms-2">
                                            {t('case_inspections.findings.accepted')}
                                        </Badge>
                                    )}
                                </td>
                                <td className={td}>{t(`case_inspections.severity.${finding.severity}`)}</td>
                                <td className={td}>{finding.requestSubject ?? none}</td>
                                <td className={td}>{finding.inspector ?? none}</td>
                                <td className={`${td} text-right whitespace-nowrap`}>
                                    <div className="flex items-center justify-end gap-2">
                                        {canAccept && !finding.accepted && (
                                            <ConfirmButton
                                                url={route('case-inspection-findings.accept', finding.id)}
                                                method="post"
                                                variant="success"
                                                title={t('case_inspections.findings.accept')}
                                                message={finding.title}
                                            >
                                                {t('case_inspections.findings.accept')}
                                            </ConfirmButton>
                                        )}
                                        <button
                                            type="button"
                                            onClick={() => setExpanded((current) => (current === finding.id ? null : finding.id))}
                                            aria-expanded={expanded === finding.id}
                                            className="text-sm text-blue-700 hover:underline dark:text-blue-300"
                                        >
                                            {t('case_inspections.common.view')}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            {expanded === finding.id && (
                                <tr className="bg-(--surface-soft)">
                                    <td colSpan={6} className="px-4 py-3">
                                        <div className="space-y-3 rounded-lg border border-(--border) bg-(--surface-strong) p-4 text-sm">
                                            <div className="grid gap-3 md:grid-cols-2">
                                                <Pair label={t('case_inspections.findings.labels.finding_date')}>{finding.date}</Pair>
                                                <Pair label={t('case_inspections.findings.labels.severity')}>
                                                    {t(`case_inspections.severity.${finding.severity}`)}
                                                </Pair>
                                                <Pair label={t('case_inspections.findings.labels.recorded_by')}>{finding.recordedBy ?? none}</Pair>
                                                <Pair label={t('case_inspections.findings.labels.attachment_pdf')}>
                                                    {finding.attachment ? (
                                                        <a
                                                            href={finding.attachment.url}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="text-blue-700 underline dark:text-blue-300"
                                                        >
                                                            {finding.attachment.name ?? t('case_inspections.findings.labels.download_attachment')}
                                                        </a>
                                                    ) : (
                                                        none
                                                    )}
                                                </Pair>
                                            </div>
                                            <div>
                                                <p className="mb-1 text-xs font-semibold tracking-wide text-(--text-subtle) uppercase">
                                                    {t('case_inspections.findings.labels.details')}
                                                </p>
                                                <div className="whitespace-pre-wrap text-(--text)">{finding.details}</div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            )}
                        </Fragment>
                    ))}
                </SectionTable>
            )}
        </CaseSection>
    );
}

function Pair({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div>
            <span className="text-(--text-subtle)">{label}:</span> <span className="text-(--text)">{children}</span>
        </div>
    );
}

const letterTones: Record<string, BadgeTone> = { pending: 'warning', approved: 'success', rejected: 'danger', returned: 'warning' };

export function LettersSection({ letters, composeUrl }: { letters: CaseLetterRow[]; composeUrl: string | null }) {
    return (
        <CaseSection
            id="letters"
            title={t('cases.navigation.letters')}
            count={letters.length}
            countLabel={t('cases.show.entries')}
            actions={
                composeUrl && (
                    <Link href={composeUrl} className="text-sm font-medium text-emerald-700 hover:underline dark:text-emerald-300">
                        {t('cases.show.write_letter')}
                    </Link>
                )
            }
        >
            {letters.length === 0 ? (
                <EmptyBox>{t('cases.show.no_letters_logged')}</EmptyBox>
            ) : (
                <SectionTable
                    head={
                        <tr>
                            <th className={th}>{t('cases.show.letters_table.reference')}</th>
                            <th className={th}>{t('cases.show.letters_table.subject')}</th>
                            <th className={th}>{t('cases.show.letters_table.template')}</th>
                            <th className={th}>{t('cases.show.letters_table.status')}</th>
                            <th className={th}>{t('cases.show.letters_table.date')}</th>
                            <th className={th}>{t('cases.show.letters_table.author')}</th>
                            <th className={`${th} text-right`}>{t('cases.show.letters_table.action')}</th>
                        </tr>
                    }
                >
                    {letters.map((letter) => (
                        <tr key={letter.id}>
                            <td className={`${td} font-medium text-(--text)`}>{letter.reference ?? '—'}</td>
                            <td className={td}>{letter.subject ?? '—'}</td>
                            <td className={td}>{letter.template ?? '—'}</td>
                            <td className={td}>
                                <Badge tone={letterTones[letter.status] ?? 'neutral'}>{t(`letters.table.status_${letter.status}`)}</Badge>
                            </td>
                            <td className={td}>{letter.date || '—'}</td>
                            <td className={td}>{letter.author ?? '—'}</td>
                            <td className={`${td} text-right`}>
                                {letter.url ? (
                                    <a
                                        href={letter.url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-xs font-medium text-blue-700 hover:underline dark:text-blue-300"
                                    >
                                        {t('cases.documents.view_letter')}
                                    </a>
                                ) : (
                                    '—'
                                )}
                            </td>
                        </tr>
                    ))}
                </SectionTable>
            )}
        </CaseSection>
    );
}
