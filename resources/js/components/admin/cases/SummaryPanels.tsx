import { CalendarDaysIcon, DocumentTextIcon, PrinterIcon, ScaleIcon, UserIcon } from '@heroicons/react/24/outline';
import { type ReactNode, useRef, useState } from 'react';
import { Button } from '@/components/ui/Button';
import { Modal } from '@/components/ui/Modal';
import { RichText } from '@/components/ui/RichText';
import { t } from '@/lib/i18n';
import type { AdminCase, CaseDocumentRow, CaseHearing, CaseWitness } from '@/types/admin/case';
import { CaseStatusBadge, ReviewStatusBadge } from './CaseBadges';
import { CaseSection, EmptyBox, SectionTable, td, th } from './CaseSection';
import { printDocument } from './printDocument';

function Fact({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div className="rounded-xl border border-(--border) bg-(--surface-soft) p-4">
            <div className="mb-1 text-xs font-medium tracking-wide text-(--text-subtle) uppercase">{label}</div>
            <div className="text-sm font-semibold text-(--text)">{children || '—'}</div>
        </div>
    );
}

function LawyerBanner({ lawyer }: { lawyer: NonNullable<AdminCase['lawyer']> }) {
    const [open, setOpen] = useState(false);

    return (
        <div className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-500/30 dark:bg-indigo-500/10">
            <p className="font-semibold text-indigo-900 dark:text-indigo-200">{t('dashboard.submitted_by_lawyer', { name: lawyer.name })}</p>
            {lawyer.documentUrl && (
                <>
                    <Button size="sm" variant="secondary" onClick={() => setOpen(true)}>
                        {t('cases.show.view_lawyer_document')}
                    </Button>
                    <Modal
                        open={open}
                        onClose={() => setOpen(false)}
                        title={t('cases.show.lawyer_document')}
                        size="xl"
                        footer={
                            <a
                                href={lawyer.documentUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="text-sm font-medium text-blue-700 hover:underline dark:text-blue-300"
                            >
                                {t('cases.show.open_new_tab')}
                            </a>
                        }
                    >
                        {open && <iframe src={lawyer.documentUrl} title={t('cases.show.lawyer_document')} className="h-[70vh] w-full rounded-lg border-0" />}
                    </Modal>
                </>
            )}
        </div>
    );
}

export function SummarySection({ courtCase, hearings }: { courtCase: AdminCase; hearings: CaseHearing[] }) {
    return (
        <CaseSection
            id="case-summary"
            title={t('cases.navigation.case_summary')}
            actions={
                <>
                    <CaseStatusBadge status={courtCase.status} />
                    <ReviewStatusBadge status={courtCase.reviewStatus} />
                </>
            }
        >
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Fact label={t('cases.summary.type')}>{courtCase.caseType}</Fact>
                <Fact label={t('cases.summary.filing_date')}>{courtCase.filingDate}</Fact>
                <Fact label={t('cases.case_number')}>{courtCase.caseNumber}</Fact>
            </div>

            {courtCase.lawyer && <LawyerBanner lawyer={courtCase.lawyer} />}

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <Party
                    icon={<UserIcon className="h-4 w-4" />}
                    label={t('cases.summary.applicant')}
                    name={courtCase.title}
                    address={courtCase.applicant.address}
                    tone="indigo"
                />
                <Party
                    icon={<ScaleIcon className="h-4 w-4" />}
                    label={t('cases.respondent_defendant')}
                    name={courtCase.respondent.name}
                    address={courtCase.respondent.address}
                    tone="rose"
                />
            </div>

            <div className="border-t border-(--border) pt-4">
                <div className="mb-2 text-xs font-medium tracking-wide text-(--text-subtle) uppercase">{t('cases.summary.hearings')}</div>
                {hearings.length === 0 ? (
                    <EmptyBox>—</EmptyBox>
                ) : (
                    <ul className="space-y-2">
                        {hearings.map((hearing) => (
                            <li key={hearing.id} className="flex items-start gap-3 rounded-lg border border-(--border) p-3">
                                <span className="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10">
                                    <CalendarDaysIcon className="h-4 w-4" />
                                </span>
                                <div>
                                    <div className="font-semibold text-(--text)">{hearing.summaryDisplay}</div>
                                    <div className="text-xs text-(--text-subtle)">
                                        {t('cases.case_number')} {courtCase.caseNumber}
                                        {hearing.notesExcerpt && <> · {hearing.notesExcerpt}</>}
                                    </div>
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </div>

            <div className="border-t border-(--border) pt-4">
                <div className="mb-2 text-xs font-medium tracking-wide text-(--text-subtle) uppercase">{t('cases.summary.assignee')}</div>
                {courtCase.assignee ? (
                    <div className="space-y-2 rounded-xl border border-(--border) bg-(--surface-soft) p-4 text-sm">
                        <div className="font-semibold text-(--text)">{courtCase.assignee.name}</div>
                        {courtCase.assignee.teamName && (
                            <div className="text-(--text-muted)">
                                {t('cases.summary.team')}: <span className="font-medium text-(--text)">{courtCase.assignee.teamName}</span>
                            </div>
                        )}
                        {courtCase.assignee.assignedAt && (
                            <div>
                                <div className="text-xs font-medium tracking-wide text-(--text-subtle) uppercase">{t('cases.summary.assigned_at')}</div>
                                <div className="font-medium text-(--text)">{courtCase.assignee.assignedAt}</div>
                            </div>
                        )}
                    </div>
                ) : (
                    <div className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                        {t('cases.summary.unassigned')}
                    </div>
                )}
            </div>

            <div className="grid grid-cols-1 gap-4 border-t border-(--border) pt-4 sm:grid-cols-2">
                <Fact label={t('cases.summary.created')}>{courtCase.created}</Fact>
                <Fact label={t('cases.summary.updated')}>{courtCase.updated}</Fact>
            </div>
        </CaseSection>
    );
}

function Party({ icon, label, name, address, tone }: { icon: ReactNode; label: string; name: string | null; address: string | null; tone: 'indigo' | 'rose' }) {
    const tint =
        tone === 'indigo'
            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300'
            : 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300';

    return (
        <div className="rounded-xl border border-(--border) p-4">
            <div className="mb-2 flex items-center gap-2">
                <span className={`inline-flex h-7 w-7 items-center justify-center rounded-full ${tint}`}>{icon}</span>
                <div className="text-xs font-semibold tracking-wide text-(--text-muted) uppercase">{label}</div>
            </div>
            <p className="font-semibold text-(--text)">{name || '—'}</p>
            <p className="mt-0.5 text-xs whitespace-pre-line text-(--text-subtle)">{address || '—'}</p>
        </div>
    );
}

interface DetailsProps {
    courtCase: AdminCase;
    documents: CaseDocumentRow[];
    witnesses: CaseWitness[];
    termsItems: string[];
    printedAt: string;
}

export function DetailsSection({ courtCase, documents, witnesses, termsItems, printedAt }: DetailsProps) {
    const printable = useRef<HTMLDivElement>(null);
    const print = () => {
        if (printable.current) printDocument(`${t('cases.show.case_details_overview')} - ${courtCase.caseNumber}`, printable.current.innerHTML);
    };

    return (
        <CaseSection
            id="case-details"
            title={t('cases.show.case_details_overview')}
            actions={
                <Button size="sm" variant="secondary" onClick={print} className="print:hidden">
                    <PrinterIcon className="h-4 w-4" />
                    {t('cases.print')}
                </Button>
            }
        >
            {courtCase.lawyer && (
                <div className="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-800 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-200">
                    {t('dashboard.submitted_by_lawyer', { name: courtCase.lawyer.name })}
                </div>
            )}

            <div className="grid gap-4 rounded-xl border border-(--border) bg-(--surface-soft) p-4 text-sm md:grid-cols-2">
                <dl className="space-y-1">
                    <p className="text-xs font-semibold tracking-wide text-(--text-subtle) uppercase">{t('cases.summary.applicant')}</p>
                    <PartyLine label={t('cases.name')} value={courtCase.title} strong />
                    <PartyLine label={t('cases.address')} value={courtCase.applicant.address} />
                    <PartyLine label={t('cases.applicant_email')} value={courtCase.applicant.email} />
                </dl>
                <dl className="space-y-1">
                    <p className="text-xs font-semibold tracking-wide text-(--text-subtle) uppercase">{t('cases.respondent_defendant')}</p>
                    <PartyLine label={t('cases.name')} value={courtCase.respondent.name} strong />
                    <PartyLine label={t('cases.address')} value={courtCase.respondent.address} />
                </dl>
            </div>

            <div className="space-y-4">
                <div>
                    <p className="text-xs font-semibold tracking-wide text-(--text-subtle) uppercase">{t('cases.details.case_details')}</p>
                    {courtCase.descriptionHtml ? (
                        <RichText html={courtCase.descriptionHtml} className="rich-text mt-2" />
                    ) : (
                        <p className="mt-2 text-sm text-(--text-subtle)">{t('cases.details.no_details')}</p>
                    )}
                </div>
                <div>
                    <p className="text-xs font-semibold tracking-wide text-(--text-subtle) uppercase">{t('cases.details.relief_requested')}</p>
                    {courtCase.reliefHtml ? (
                        <RichText html={courtCase.reliefHtml} className="rich-text mt-2" />
                    ) : (
                        <p className="mt-2 text-sm text-(--text-subtle)">{t('cases.details.no_relief_specified')}</p>
                    )}
                </div>
            </div>

            <div className="space-y-3">
                <SubTitle count={documents.length} countLabel={t('cases.documents.items')}>
                    {t('cases.documents.submitted_documents')}
                </SubTitle>
                {documents.length === 0 ? (
                    <EmptyBox>{t('cases.documents.no_documents')}</EmptyBox>
                ) : (
                    <SectionTable
                        head={
                            <tr>
                                <th className={th}>{t('cases.documents.document')}</th>
                                <th className={th}>{t('cases.summary.type')}</th>
                                <th className={th}>{t('cases.show.uploaded')}</th>
                                <th className={`${th} text-right`}>{t('cases.show.action')}</th>
                            </tr>
                        }
                    >
                        {documents.map((doc) => (
                            <tr key={doc.id}>
                                <td className={td}>
                                    <div className="font-medium text-(--text)">{doc.title}</div>
                                    {doc.descriptionHtml && <RichText html={doc.descriptionHtml} className="mt-1 text-xs" />}
                                </td>
                                <td className={td}>{doc.mime ?? t('cases.documents.document')}</td>
                                <td className={td}>
                                    {doc.uploaded || '—'}{' '}
                                    {doc.sizeKb !== null && <span className="text-xs text-(--text-subtle)">({doc.sizeKb.toFixed(1)} KB)</span>}
                                </td>
                                <td className={`${td} text-right`}>
                                    {doc.url ? (
                                        <a
                                            href={doc.url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="text-xs font-medium text-blue-700 hover:underline dark:text-blue-300"
                                        >
                                            {t('cases.documents.view')}
                                        </a>
                                    ) : (
                                        '—'
                                    )}
                                </td>
                            </tr>
                        ))}
                    </SectionTable>
                )}
            </div>

            <div className="space-y-3">
                <SubTitle count={witnesses.length} countLabel={t('cases.witnesses_section.total')}>
                    {t('cases.witnesses_section.title')}
                </SubTitle>
                {witnesses.length === 0 ? (
                    <EmptyBox>{t('cases.witnesses_section.no_witnesses')}</EmptyBox>
                ) : (
                    <SectionTable
                        head={
                            <tr>
                                <th className={th}>{t('cases.labels.name')}</th>
                                <th className={th}>{t('cases.labels.phone')}</th>
                                <th className={th}>{t('cases.labels.email')}</th>
                                <th className={th}>{t('cases.labels.address')}</th>
                            </tr>
                        }
                    >
                        {witnesses.map((witness) => (
                            <tr key={witness.id}>
                                <td className={`${td} font-medium text-(--text)`}>{witness.name}</td>
                                <td className={td}>{witness.phone ?? '—'}</td>
                                <td className={td}>
                                    {witness.email ? (
                                        <a href={`mailto:${witness.email}`} className="text-blue-700 hover:underline dark:text-blue-300">
                                            {witness.email}
                                        </a>
                                    ) : (
                                        '—'
                                    )}
                                </td>
                                <td className={td}>{witness.address ?? '—'}</td>
                            </tr>
                        ))}
                    </SectionTable>
                )}
            </div>

            {/* Source of the printed overview: Word-style content without the page chrome. */}
            <div ref={printable} hidden>
                <PrintableOverview courtCase={courtCase} documents={documents} witnesses={witnesses} termsItems={termsItems} printedAt={printedAt} />
            </div>
        </CaseSection>
    );
}

function PartyLine({ label, value, strong = false }: { label: string; value: string | null; strong?: boolean }) {
    return (
        <div>
            <dt className="text-xs text-(--text-subtle)">{label}</dt>
            <dd className={strong ? 'font-semibold whitespace-pre-line text-(--text)' : 'whitespace-pre-line text-(--text-muted)'}>{value || '—'}</dd>
        </div>
    );
}

function SubTitle({ children, count, countLabel }: { children: ReactNode; count: number; countLabel: string }) {
    return (
        <div className="flex items-center justify-between border-b border-(--border) pb-3">
            <h3 className="flex items-center gap-2 font-semibold text-(--text)">
                <DocumentTextIcon className="h-4 w-4 text-(--text-subtle)" />
                {children}
            </h3>
            <span className="rounded-full bg-(--surface-soft) px-2.5 py-1 text-xs font-medium text-(--text-muted)">
                {count} {countLabel}
            </span>
        </div>
    );
}

function PrintableOverview({ courtCase, documents, witnesses, termsItems, printedAt }: DetailsProps) {
    return (
        <>
            <h1>{t('cases.show.case_details_overview')}</h1>
            <div className="doc-meta-row">
                <p>
                    {t('cases.case_number')} <strong>{courtCase.caseNumber}</strong>
                </p>
                <p>
                    {t('cases.summary.filing_date')} <strong>{courtCase.filingDate ?? '—'}</strong>
                </p>
            </div>

            <h2>{t('cases.summary.applicant')}</h2>
            {courtCase.lawyer && (
                <p>
                    <strong>{t('dashboard.submitted_by_lawyer', { name: courtCase.lawyer.name })}</strong>
                </p>
            )}
            <table className="doc-fields">
                <tbody>
                    <tr>
                        <td>{t('cases.name')}</td>
                        <td>{courtCase.title || '—'}</td>
                    </tr>
                    <tr>
                        <td>{t('cases.address')}</td>
                        <td>{courtCase.applicant.address ?? '—'}</td>
                    </tr>
                    <tr>
                        <td>{t('cases.applicant_email')}</td>
                        <td>{courtCase.applicant.email ?? '—'}</td>
                    </tr>
                </tbody>
            </table>

            <h2>{t('cases.respondent_defendant')}</h2>
            <table className="doc-fields">
                <tbody>
                    <tr>
                        <td>{t('cases.name')}</td>
                        <td>{courtCase.respondent.name ?? '—'}</td>
                    </tr>
                    <tr>
                        <td>{t('cases.address')}</td>
                        <td>{courtCase.respondent.address ?? '—'}</td>
                    </tr>
                </tbody>
            </table>

            {termsItems.length > 0 && (
                <ol className="doc-terms-list">
                    {termsItems.map((item, index) => (
                        // Purified on the server; only inline formatting tags remain.
                        <li key={index} dangerouslySetInnerHTML={{ __html: item }} />
                    ))}
                </ol>
            )}

            <h2>{t('cases.details.case_details')}</h2>
            <div className="doc-body" dangerouslySetInnerHTML={{ __html: courtCase.descriptionHtml || t('cases.details.no_details') }} />

            <h2>{t('cases.details.relief_requested')}</h2>
            <div className="doc-body" dangerouslySetInnerHTML={{ __html: courtCase.reliefHtml || t('cases.details.no_relief_specified') }} />

            {documents.length > 0 && (
                <>
                    <h2>{t('cases.documents.submitted_documents')}</h2>
                    <ol className="doc-list">
                        {documents.map((doc) => (
                            <li key={doc.id}>
                                {doc.title}
                                {doc.uploaded && ` — ${doc.uploaded}`}
                            </li>
                        ))}
                    </ol>
                </>
            )}

            {witnesses.length > 0 && (
                <>
                    <h2>{t('cases.witnesses_section.title')}</h2>
                    <table className="doc-table">
                        <thead>
                            <tr>
                                <th>{t('cases.labels.name')}</th>
                                <th>{t('cases.labels.phone')}</th>
                                <th>{t('cases.labels.email')}</th>
                                <th>{t('cases.labels.address')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {witnesses.map((witness) => (
                                <tr key={witness.id}>
                                    <td>{witness.name}</td>
                                    <td>{witness.phone ?? '—'}</td>
                                    <td>{witness.email ?? '—'}</td>
                                    <td>{witness.address ?? '—'}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </>
            )}

            <p className="doc-printed">{printedAt}</p>
        </>
    );
}
