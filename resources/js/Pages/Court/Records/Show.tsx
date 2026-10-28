import { Head, Link } from '@inertiajs/react';
import { useState, type ReactNode } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import RichTextViewer from '@/Components/forms/RichTextViewer';
import LocalizedDateDisplay from '@/Components/Calendar/LocalizedDateDisplay';
import { Card, buttonClassName, cx } from '@/Components/ui';
import { useInspectionPagesText } from '@/court/i18n/useInspectionPagesText';
import type { RecordSection, RecordShowProps } from '@/court/types/records';

const SECTIONS: RecordSection[] = ['case-summary', 'case-details', 'respondent-responses', 'letters', 'hearings', 'bench-notes', 'final-decision', 'other-documents'];

/** `ucfirst()`, as the Blade page showed a raw status it had no label for. */
const ucfirst = (value: string): string => value.charAt(0).toUpperCase() + value.slice(1);

/**
 * One case's consolidated record: a section list at the left and one section
 * open at a time, as on the Blade page — summary, details (documents and
 * witnesses), respondent responses, letters, hearings, bench notes, the final
 * decision and the other documents. The printable PDF stays a Blade document.
 */
export default function RecordShow(props: RecordShowProps) {
    const { case: kase } = props;
    const { t, tx, text } = useInspectionPagesText();
    const [active, setActive] = useState<RecordSection>('case-summary');
    const title = t('cm.recordes.titles.record');
    const na = t('cm.recordes.messages.not_available');

    const labels: Record<RecordSection, string> = {
        'case-summary': t('cm.recordes.labels.case_summary'),
        'case-details': t('cm.recordes.labels.case_details'),
        'respondent-responses': t('cm.recordes.labels.respondent_responses'),
        letters: t('cm.recordes.labels.letters_section'),
        hearings: t('cm.recordes.labels.hearings'),
        'bench-notes': t('cm.recordes.labels.bench_notes'),
        'final-decision': t('cm.recordes.labels.final_judgment'),
        'other-documents': t('cm.recordes.labels.other_documents'),
    };
    const statusLabel = kase.status ? tx(`cm.cases.status.${kase.status}`, kase.status) : text('status_unknown');

    return (
        <AuthenticatedLayout header={(
            <PageHeader title={title} description={[kase.case_number, kase.title ?? t('cm.recordes.messages.untitled_case')].filter(Boolean).join(' — ')} backHref={route('recordes.index')} actions={(
                <div className="flex flex-wrap items-center gap-2">
                    <Link href={route('cases.show', kase.id)} className={buttonClassName({ variant: 'outline' })}>{text('open_case')}</Link>
                    <a href={route('recordes.pdf', kase.id)} target="_blank" rel="noopener" className={buttonClassName({ variant: 'outline' })}>{t('cm.recordes.buttons.pdf')}</a>
                    {props.hasAppealRecord && <a href={route('recordes.appeal-pdf', kase.id)} className={buttonClassName({})}>{t('cm.recordes.buttons.appeal_pdf')}</a>}
                </div>
            )} />
        )}>
            <Head title={`${title} ${kase.case_number ?? ''}`.trim()} />

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-4">
                <aside className="space-y-4 lg:sticky lg:top-24 lg:self-start">
                    <Card className="space-y-4">
                        <div>
                            <h2 className="text-sm font-semibold text-(--app-foreground)">{title}</h2>
                            {t('cm.recordes.descriptions.index_intro') !== '' && <p className="text-xs text-(--app-muted-foreground)">{t('cm.recordes.descriptions.index_intro')}</p>}
                        </div>
                        <nav aria-label={text('record_sections')}>
                            <ul className="space-y-1">
                                {SECTIONS.map((section) => (
                                    <li key={section}>
                                        <button type="button" onClick={() => setActive(section)} aria-current={active === section ? 'true' : undefined}
                                            className={cx('flex w-full items-center justify-between rounded-control px-3 py-2 text-left text-sm transition-colors',
                                                active === section ? 'bg-primary/15 font-semibold text-(--app-foreground)' : 'text-(--app-muted-foreground) hover:bg-(--app-surface-muted) hover:text-(--app-foreground)')}>
                                            <span>{labels[section]}</span>
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        </nav>
                        <div className="rounded-control border border-(--app-border) bg-(--app-surface-muted) p-3 text-xs text-(--app-muted-foreground)">
                            <p className="font-mono font-semibold text-(--app-foreground)">{kase.case_number ?? na}</p>
                            <p className="mt-0.5 line-clamp-2">{kase.title ?? t('cm.recordes.messages.untitled_case')}</p>
                            <p className="mt-1">{t('cm.recordes.labels.status_label')} <span className="font-semibold text-(--app-foreground)">{statusLabel}</span></p>
                        </div>
                    </Card>
                </aside>

                <div className="space-y-6 lg:col-span-3">
                    {active === 'case-summary' && <SummarySection {...props} statusLabel={statusLabel} />}
                    {active === 'case-details' && <DetailsSection {...props} statusLabel={statusLabel} />}
                    {active === 'respondent-responses' && <ResponsesSection {...props} />}
                    {active === 'letters' && <LettersSection {...props} />}
                    {active === 'hearings' && <HearingsSection {...props} />}
                    {active === 'bench-notes' && <BenchNotesSection {...props} />}
                    {active === 'final-decision' && <DecisionSection {...props} />}
                    {active === 'other-documents' && <OtherDocumentsSection {...props} />}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

/** One section card: a heading, a note at the right, and the section's body. */
function Section({ id, title, aside, children }: { id: RecordSection; title: ReactNode; aside?: ReactNode; children: ReactNode }) {
    return (
        <Card id={`section-${id}`} className="space-y-5 scroll-mt-24">
            <header className="flex flex-wrap items-start justify-between gap-3">
                <h2 className="text-lg font-semibold text-(--app-foreground)">{title}</h2>
                {aside && <div className="text-right text-xs text-(--app-muted-foreground)">{aside}</div>}
            </header>
            {children}
        </Card>
    );
}

/** A dashed line in a list, as the Blade page framed each item. */
function Item({ children, className }: { children: ReactNode; className?: string }) {
    return <div className={cx('space-y-2 rounded-control border border-(--app-border) bg-(--app-surface-muted) p-4 text-sm text-(--app-foreground)', className)}>{children}</div>;
}

function Empty({ children }: { children: ReactNode }) {
    return <p className="text-sm text-(--app-muted-foreground)">{children}</p>;
}

function SummarySection({ case: kase, generatedAt, statusLabel }: RecordShowProps & { statusLabel: string }) {
    const { t } = useInspectionPagesText();
    const na = t('cm.recordes.messages.not_available');

    return (
        <Section id="case-summary" title={(
            <span>
                <span className="block text-xs font-normal uppercase tracking-wide text-(--app-muted-foreground)">{t('cm.recordes.titles.record')}</span>
                {kase.case_number ?? na} — {kase.title ?? t('cm.recordes.messages.untitled_case')}
                <span className="mt-1 block text-sm font-normal text-(--app-muted-foreground)">
                    {t('cm.recordes.labels.filed')}: <LocalizedDateDisplay value={kase.filing_date} />
                    {kase.first_hearing_date && <> · {t('cm.recordes.labels.hearing_at')} <LocalizedDateDisplay value={kase.first_hearing_date} /></>}
                    {' · '}{t('cm.recordes.labels.status_label')} {statusLabel}
                </span>
            </span>
        )} aside={(
            <>
                {t('cm.recordes.labels.generated')} <LocalizedDateDisplay value={generatedAt} withTime /><br />
                {t('cm.recordes.labels.applicant')} {kase.applicant_name ?? na}
            </>
        )}>
            <div className="grid gap-6 text-sm text-(--app-foreground) md:grid-cols-2">
                <div className="space-y-1">
                    <h3 className="text-base font-semibold">{t('cm.recordes.labels.applicant_heading')}</h3>
                    <p><span className="font-medium">{t('cm.recordes.labels.name')}:</span> {kase.applicant_name ?? na}</p>
                    <p><span className="font-medium">{t('cm.recordes.labels.email')}</span> {kase.applicant_email ?? '—'}</p>
                    <p><span className="font-medium">{t('cm.recordes.labels.phone')}</span> {kase.applicant_phone ?? '—'}</p>
                </div>
                <div className="space-y-1">
                    <h3 className="text-base font-semibold">{t('cm.recordes.labels.respondent_heading')}</h3>
                    <p><span className="font-medium">{t('cm.recordes.labels.name')}:</span> {kase.respondent_name ?? na}</p>
                    <p><span className="font-medium">{t('cm.recordes.labels.address')}</span> {kase.respondent_address ?? '—'}</p>
                </div>
            </div>
        </Section>
    );
}

function DetailsSection({ case: kase, submittedDocuments, witnesses, statusLabel }: RecordShowProps & { statusLabel: string }) {
    const { t } = useInspectionPagesText();
    const na = t('cm.recordes.messages.not_available');
    const field = (label: string, value: ReactNode) => (
        <div>
            <dt className="text-xs uppercase tracking-wide text-(--app-muted-foreground)">{label}</dt>
            <dd className="font-medium text-(--app-foreground)">{value}</dd>
        </div>
    );

    return (
        <Section id="case-details" title={t('cm.recordes.labels.case_details')} aside={t('cm.recordes.labels.case_submission')}>
            <div className="space-y-3">
                <h3 className="text-sm font-semibold text-(--app-foreground)">{t('cm.recordes.labels.case_details_overview')}</h3>
                <dl className="grid gap-4 rounded-control border border-(--app-border) bg-(--app-surface-muted) p-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
                    {field(t('cm.recordes.labels.case_type'), kase.case_type ?? na)}
                    {field(t('cm.recordes.labels.status_label'), statusLabel)}
                    {field(t('cm.recordes.labels.filed'), <LocalizedDateDisplay value={kase.filing_date} />)}
                    {field(t('cm.recordes.labels.hearing_at'), <LocalizedDateDisplay value={kase.first_hearing_date} />)}
                </dl>
            </div>

            <div className="space-y-2">
                <h3 className="text-sm font-semibold text-(--app-foreground)">{t('cm.recordes.labels.case_details')}</h3>
                <RichTextViewer html={kase.description} className="text-sm" empty={<Empty>{t('cm.recordes.messages.no_details_provided')}</Empty>} />
            </div>

            {kase.relief_requested && (
                <div className="space-y-2">
                    <h3 className="text-sm font-semibold text-(--app-foreground)">{t('cm.recordes.labels.relief_requested')}</h3>
                    <RichTextViewer html={kase.relief_requested} className="text-sm" />
                </div>
            )}

            <div className="space-y-2">
                <h3 className="text-sm font-semibold text-(--app-foreground)">{t('cm.recordes.labels.submitted_documents')}</h3>
                {submittedDocuments.length === 0 ? <Empty>{t('cm.recordes.messages.no_files')}</Empty> : submittedDocuments.map((document, index) => (
                    <Item key={document.id ?? index} className="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p className="font-semibold">{document.label ?? t('cm.recordes.labels.document')}</p>
                            <p className="text-xs text-(--app-muted-foreground)"><LocalizedDateDisplay value={document.created_at} withTime fallback="" /></p>
                        </div>
                        {document.path && <span className="break-all text-xs text-(--app-muted-foreground)">{document.path}</span>}
                    </Item>
                ))}
            </div>

            <div className="space-y-2">
                <h3 className="text-sm font-semibold text-(--app-foreground)">{t('cm.recordes.labels.witnesses')}</h3>
                {witnesses.length === 0 ? <Empty>{t('cm.recordes.messages.no_witnesses')}</Empty> : witnesses.map((witness) => (
                    <Item key={witness.id}>
                        <p className="font-semibold">{witness.full_name ?? t('cm.recordes.labels.witness')}</p>
                        <p className="text-xs text-(--app-muted-foreground)">
                            {t('cm.recordes.labels.phone')} {witness.phone ?? na} · {t('cm.recordes.labels.email')} {witness.email ?? na} · {t('cm.recordes.labels.address')} {witness.address ?? na}
                        </p>
                    </Item>
                ))}
            </div>
        </Section>
    );
}

function ResponsesSection({ case: kase, respondentResponses }: RecordShowProps) {
    const { t } = useInspectionPagesText();
    const na = t('cm.recordes.messages.not_available');

    return (
        <Section id="respondent-responses" title={t('cm.recordes.labels.respondent_responses')} aside={t('cm.recordes.labels.respondent_responses')}>
            {respondentResponses.length === 0 ? <Empty>{t('cm.recordes.messages.no_responses')}</Empty> : respondentResponses.map((response) => (
                <Item key={response.id}>
                    <div className="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p className="font-semibold">{response.title ?? t('cm.recordes.labels.response')}</p>
                            <p className="text-xs text-(--app-muted-foreground)">{t('cm.recordes.labels.case_number')} {response.case_number ?? kase.case_number ?? na}</p>
                        </div>
                        <span className="text-xs text-(--app-muted-foreground)"><LocalizedDateDisplay value={response.created_at} withTime fallback="" /></span>
                    </div>
                    {response.description && <p>{response.description}</p>}
                    {response.pdf_path && (
                        <p className="text-xs text-(--app-muted-foreground)">
                            {t('cm.recordes.labels.attachment')}{' '}
                            {response.download_url ? <a href={response.download_url} target="_blank" rel="noopener" className="text-primary underline">{response.pdf_path}</a> : response.pdf_path}
                        </p>
                    )}
                </Item>
            ))}
        </Section>
    );
}

function LettersSection({ letters }: RecordShowProps) {
    const { t, tx } = useInspectionPagesText();

    return (
        <Section id="letters" title={t('cm.recordes.labels.letters_section')} aside={`${letters.length} ${t('cm.recordes.labels.total')}`}>
            {letters.length === 0 ? <Empty>{t('cm.recordes.messages.no_letters')}</Empty> : letters.map((letter) => (
                <Item key={letter.id}>
                    <div className="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p className="font-semibold">{letter.subject ?? t('cm.recordes.labels.letter')}</p>
                            <p className="text-xs text-(--app-muted-foreground)">
                                {t('cm.recordes.labels.reference_number')} {letter.reference_number ?? t('cm.recordes.messages.not_available')} · {tx(`cm.letters.table.status_${letter.approval_status ?? 'draft'}`, ucfirst(letter.approval_status ?? 'draft'))}
                            </p>
                            <p className="text-xs text-(--app-muted-foreground)">{t('cm.recordes.labels.author')} {letter.author_name ?? t('cm.recordes.messages.not_available')}</p>
                        </div>
                        <span className="text-xs text-(--app-muted-foreground)"><LocalizedDateDisplay value={letter.created_at} withTime fallback="" /></span>
                    </div>
                    {letter.body && <RichTextViewer html={letter.body} className="mt-2 text-sm" />}
                </Item>
            ))}
        </Section>
    );
}

function HearingsSection({ hearings }: RecordShowProps) {
    const { t } = useInspectionPagesText();
    const na = t('cm.recordes.messages.not_available');

    return (
        <Section id="hearings" title={t('cm.recordes.labels.hearings')} aside={`${hearings.length} ${t('cm.recordes.labels.total')}`}>
            {hearings.length === 0 ? <Empty>{t('cm.recordes.messages.no_hearings')}</Empty> : hearings.map((hearing) => (
                <Item key={hearing.id}>
                    <div className="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p className="font-semibold">{hearing.hearing_at ? <LocalizedDateDisplay value={hearing.hearing_at} withTime /> : t('cm.recordes.labels.hearing_unknown')}</p>
                            <p className="text-xs text-(--app-muted-foreground)">{t('cm.recordes.labels.location')} {hearing.location ?? na}</p>
                        </div>
                        <span className="text-xs text-(--app-muted-foreground)">{hearing.type ?? ''}</span>
                    </div>
                    {hearing.judge_notes && <p><span className="font-semibold">{t('cm.recordes.labels.judge_notes')}</span> {hearing.judge_notes}</p>}
                    {hearing.notes && <p className="text-(--app-muted-foreground)"><span className="font-semibold">{t('cm.recordes.labels.hearing_notes')}</span> {hearing.notes}</p>}
                </Item>
            ))}
        </Section>
    );
}

function BenchNotesSection({ benchNotes }: RecordShowProps) {
    const { t } = useInspectionPagesText();

    return (
        <Section id="bench-notes" title={t('cm.recordes.labels.bench_notes')} aside={`${benchNotes.length} ${t('cm.recordes.labels.total')}`}>
            {benchNotes.length === 0 ? <Empty>{t('cm.recordes.messages.no_bench_notes')}</Empty> : benchNotes.map((note) => (
                <Item key={note.id}>
                    <div className="flex flex-wrap items-start justify-between gap-3">
                        <p className="font-semibold">{note.title ?? t('cm.recordes.labels.note')}</p>
                        <span className="text-xs text-(--app-muted-foreground)"><LocalizedDateDisplay value={note.created_at} withTime fallback="" /></span>
                    </div>
                    <RichTextViewer html={note.note} className="text-sm" />
                    {note.author && <p className="text-xs text-(--app-muted-foreground)">{t('cm.recordes.labels.assigned_to')} {note.author}</p>}
                </Item>
            ))}
        </Section>
    );
}

function DecisionSection({ decision }: RecordShowProps) {
    const { t } = useInspectionPagesText();

    return (
        <Section id="final-decision" title={t('cm.recordes.labels.final_judgment')}
            aside={decision ? <>{t('cm.recordes.labels.created')} <LocalizedDateDisplay value={decision.created_at} withTime fallback="" /></> : t('cm.recordes.messages.no_decision')}>
            {decision ? (
                <Item>
                    <p className="font-semibold">{decision.title ?? t('cm.recordes.labels.decision')}</p>
                    {decision.content && <RichTextViewer html={decision.content} className="text-sm" />}
                </Item>
            ) : <Empty>{t('cm.recordes.messages.no_decision')}</Empty>}
        </Section>
    );
}

function OtherDocumentsSection({ otherDocuments, evidences, filesCount }: RecordShowProps) {
    const { t } = useInspectionPagesText();

    return (
        <Section id="other-documents" title={t('cm.recordes.labels.other_documents')} aside={`${filesCount} ${t('cm.recordes.labels.items')}`}>
            <div className="space-y-3">
                {otherDocuments.length === 0 ? <Empty>{t('cm.recordes.messages.no_uploaded_files')}</Empty> : otherDocuments.map((file, index) => (
                    <Item key={file.id ?? index} className="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p className="font-semibold">{file.label ?? t('cm.recordes.labels.document')}</p>
                            <p className="text-xs text-(--app-muted-foreground)"><LocalizedDateDisplay value={file.created_at} withTime fallback="" /></p>
                        </div>
                        {file.path && <span className="break-all text-xs text-(--app-muted-foreground)">{file.path}</span>}
                    </Item>
                ))}
            </div>
            <div className="space-y-3 border-t border-(--app-border) pt-4">
                <h3 className="text-sm font-semibold text-(--app-foreground)">{t('cm.recordes.labels.applicant_evidence')}</h3>
                {evidences.length === 0 ? <Empty>{t('cm.recordes.messages.no_evidence')}</Empty> : evidences.map((evidence) => (
                    <Item key={evidence.id}>
                        <p className="font-semibold">{evidence.title ?? t('cm.recordes.labels.document')}</p>
                        <p className="text-xs text-(--app-muted-foreground)"><LocalizedDateDisplay value={evidence.created_at} withTime fallback="" /></p>
                        {evidence.description && <p className="mt-1">{evidence.description}</p>}
                    </Item>
                ))}
            </div>
        </Section>
    );
}
