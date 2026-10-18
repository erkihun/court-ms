import { ArrowDownTrayIcon, DocumentTextIcon } from '@heroicons/react/24/outline';
import { Head } from '@inertiajs/react';
import { type ReactNode, useState } from 'react';
import { CaseStatusPill } from '@/components/party/StatusPill';
import { LinkButton } from '@/components/ui/Button';
import { DescriptionList } from '@/components/ui/DescriptionList';
import { RichText } from '@/components/ui/RichText';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';

interface CaseRecord {
    id: number;
    caseNumber: string | null;
    title: string | null;
    status: string;
    caseType: string | null;
    filed: string;
    firstHearing: string | null;
    generated: string;
    descriptionHtml: string | null;
    reliefHtml: string | null;
    applicant: { name: string | null; email: string | null; phone: string | null };
    respondent: { name: string | null; address: string | null };
    pdfUrl: string;
    appealPdfUrl: string | null;
    files: Array<{ id: number; label: string | null; created: string; url: string | null }>;
    evidences: Array<{ id: number; title: string | null; description: string | null; created: string }>;
    witnesses: Array<{ id: number; name: string | null; phone: string | null; email: string | null; address: string | null }>;
    responses: Array<{ id: number; title: string | null; number: string | null; excerpt: string | null; created: string; downloadUrl: string | null }>;
    letters: Array<{ id: number; subject: string | null; reference: string | null; author: string | null; created: string; bodyHtml: string | null }>;
    hearings: Array<{ id: number; when: string | null; location: string | null; type: string | null; notes: string | null }>;
    benchNotes: Array<{ id: number; created: string; author: string | null; html: string | null }>;
    decision: { name: string | null; created: string; html: string | null } | null;
}

type SectionKey = 'case-summary' | 'case-details' | 'respondent-responses' | 'letters' | 'hearings' | 'bench-notes' | 'final-decision' | 'other-documents';

const sections: Array<{ key: SectionKey; label: string }> = [
    { key: 'case-summary', label: 'recordes.labels.case_summary' },
    { key: 'case-details', label: 'recordes.labels.case_details' },
    { key: 'respondent-responses', label: 'recordes.labels.respondent_responses' },
    { key: 'letters', label: 'recordes.labels.letters_section' },
    { key: 'hearings', label: 'recordes.labels.hearings' },
    { key: 'bench-notes', label: 'recordes.labels.bench_notes' },
    { key: 'final-decision', label: 'recordes.labels.final_judgment' },
    { key: 'other-documents', label: 'recordes.labels.other_documents' },
];

export default function RecordShow({ record }: { record: CaseRecord }) {
    const [active, setActive] = useState<SectionKey>('case-summary');
    const na = t('recordes.messages.not_available');

    return (
        <>
            <Head title={t('recordes.titles.record')} />

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-4">
                <aside className="ui-card space-y-4 self-start p-5">
                    <h2 className="text-sm font-semibold text-(--text)">{t('recordes.titles.record')}</h2>
                    <nav aria-label={t('recordes.titles.record')} className="space-y-2">
                        {sections.map((section) => (
                            <button
                                key={section.key}
                                type="button"
                                onClick={() => setActive(section.key)}
                                aria-current={active === section.key ? 'true' : undefined}
                                className={cn(
                                    'flex w-full items-center justify-between rounded-lg border px-3 py-2 text-start text-sm font-semibold transition-colors',
                                    active === section.key
                                        ? 'border-blue-500 bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300'
                                        : 'border-(--border) text-(--text-muted) hover:bg-(--surface-soft)',
                                )}
                            >
                                {t(section.label)}
                            </button>
                        ))}
                    </nav>
                    <div className="rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-xs text-blue-800 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200">
                        <p className="font-semibold">{record.caseNumber ?? na}</p>
                        <p>{record.title ?? t('recordes.messages.untitled_case')}</p>
                        <div className="mt-2">
                            <CaseStatusPill status={record.status} withIcon={false} />
                        </div>
                    </div>
                    <div className="flex flex-col gap-2">
                        <LinkButton href={record.pdfUrl} external target="_blank" variant="secondary" size="sm">
                            <DocumentTextIcon className="h-4 w-4" />
                            {t('recordes.buttons.download_pdf')}
                        </LinkButton>
                        {record.appealPdfUrl && (
                            <LinkButton href={record.appealPdfUrl} external size="sm">
                                <ArrowDownTrayIcon className="h-4 w-4" />
                                {t('recordes.buttons.appeal_pdf')}
                            </LinkButton>
                        )}
                    </div>
                </aside>

                <div className="lg:col-span-3">
                    {active === 'case-summary' && (
                        <Section
                            title={`${record.caseNumber ?? na} — ${record.title ?? t('recordes.messages.untitled_case')}`}
                            eyebrow={t('recordes.titles.record')}
                        >
                            <p className="text-sm text-(--text-muted)">
                                {t('recordes.labels.filed')}: {record.filed}
                                {record.firstHearing && (
                                    <>
                                        {' · '}
                                        {t('recordes.labels.hearing_at')} {record.firstHearing}
                                    </>
                                )}
                                {' · '}
                                {t('recordes.labels.generated')} {record.generated}
                            </p>
                            <div className="grid gap-6 md:grid-cols-2">
                                <div>
                                    <h3 className="mb-2 text-base font-semibold text-(--text)">{t('recordes.labels.applicant_heading')}</h3>
                                    <DescriptionList
                                        columns={1}
                                        items={[
                                            { label: t('recordes.labels.name'), value: record.applicant.name },
                                            { label: t('recordes.labels.email'), value: record.applicant.email },
                                            { label: t('recordes.labels.phone'), value: record.applicant.phone },
                                        ]}
                                    />
                                </div>
                                <div>
                                    <h3 className="mb-2 text-base font-semibold text-(--text)">{t('recordes.labels.respondent_heading')}</h3>
                                    <DescriptionList
                                        columns={1}
                                        items={[
                                            { label: t('recordes.labels.name'), value: record.respondent.name },
                                            { label: t('recordes.labels.address'), value: record.respondent.address },
                                        ]}
                                    />
                                </div>
                            </div>
                        </Section>
                    )}

                    {active === 'case-details' && (
                        <Section title={t('recordes.labels.case_details')} aside={t('recordes.labels.case_submission')}>
                            <DescriptionList
                                items={[
                                    { label: t('recordes.labels.case_type'), value: record.caseType },
                                    { label: t('recordes.labels.status'), value: <CaseStatusPill status={record.status} withIcon={false} /> },
                                    { label: t('recordes.labels.filed'), value: record.filed },
                                    { label: t('recordes.labels.hearing_at'), value: record.firstHearing },
                                ]}
                            />
                            <SubHeading>{t('recordes.labels.case_details')}</SubHeading>
                            {record.descriptionHtml ? (
                                <RichText html={record.descriptionHtml} className="text-sm" />
                            ) : (
                                <Muted>{t('recordes.messages.no_details_provided')}</Muted>
                            )}
                            {record.reliefHtml && (
                                <>
                                    <SubHeading>{t('recordes.labels.relief_requested')}</SubHeading>
                                    <RichText html={record.reliefHtml} className="text-sm" />
                                </>
                            )}
                            <SubHeading>{t('recordes.labels.submitted_documents')}</SubHeading>
                            {record.files.length === 0 ? (
                                <Muted>{t('recordes.messages.no_files')}</Muted>
                            ) : (
                                <ItemList>
                                    {record.files.map((file) => (
                                        <Item key={file.id} title={file.label ?? t('recordes.labels.document')} meta={file.created} downloadUrl={file.url} />
                                    ))}
                                </ItemList>
                            )}
                            <SubHeading>{t('recordes.labels.witnesses')}</SubHeading>
                            {record.witnesses.length === 0 ? (
                                <Muted>{t('recordes.messages.no_witnesses')}</Muted>
                            ) : (
                                <ItemList>
                                    {record.witnesses.map((witness) => (
                                        <Item
                                            key={witness.id}
                                            title={witness.name ?? t('recordes.labels.witness')}
                                            meta={[
                                                `${t('recordes.labels.phone')} ${witness.phone ?? na}`,
                                                `${t('recordes.labels.email')} ${witness.email ?? na}`,
                                                `${t('recordes.labels.address')} ${witness.address ?? na}`,
                                            ].join(' · ')}
                                        />
                                    ))}
                                </ItemList>
                            )}
                        </Section>
                    )}

                    {active === 'respondent-responses' && (
                        <Section title={t('recordes.labels.respondent_responses')} aside={`${record.responses.length} ${t('recordes.labels.total')}`}>
                            {record.responses.length === 0 ? (
                                <Muted>{t('recordes.messages.no_responses')}</Muted>
                            ) : (
                                <ItemList>
                                    {record.responses.map((response) => (
                                        <Item
                                            key={response.id}
                                            title={response.title ?? t('recordes.labels.response')}
                                            meta={[response.number, response.created].filter(Boolean).join(' · ')}
                                            downloadUrl={response.downloadUrl}
                                        >
                                            {response.excerpt && <p className="mt-1 text-sm text-(--text-muted)">{response.excerpt}</p>}
                                        </Item>
                                    ))}
                                </ItemList>
                            )}
                        </Section>
                    )}

                    {active === 'letters' && (
                        <Section title={t('recordes.labels.letters_section')} aside={`${record.letters.length} ${t('recordes.labels.total')}`}>
                            {record.letters.length === 0 ? (
                                <Muted>{t('recordes.messages.no_letters')}</Muted>
                            ) : (
                                <ItemList>
                                    {record.letters.map((letter) => (
                                        <Item
                                            key={letter.id}
                                            title={letter.subject ?? t('recordes.labels.letter')}
                                            meta={[
                                                `${t('recordes.labels.reference_number')} ${letter.reference ?? na}`,
                                                `${t('recordes.labels.author')} ${letter.author ?? na}`,
                                                letter.created,
                                            ].join(' · ')}
                                        >
                                            {letter.bodyHtml && <RichText html={letter.bodyHtml} className="mt-2 text-sm" />}
                                        </Item>
                                    ))}
                                </ItemList>
                            )}
                        </Section>
                    )}

                    {active === 'hearings' && (
                        <Section title={t('recordes.labels.hearings')} aside={`${record.hearings.length} ${t('recordes.labels.total')}`}>
                            {record.hearings.length === 0 ? (
                                <Muted>{t('recordes.messages.no_hearings')}</Muted>
                            ) : (
                                <ItemList>
                                    {record.hearings.map((hearing) => (
                                        <Item
                                            key={hearing.id}
                                            title={hearing.when ?? t('recordes.labels.hearing_unknown')}
                                            meta={[`${t('recordes.labels.location')} ${hearing.location ?? na}`, hearing.type].filter(Boolean).join(' · ')}
                                        >
                                            {hearing.notes && (
                                                <p className="mt-1 text-sm text-(--text-muted)">
                                                    <span className="font-semibold">{t('recordes.labels.hearing_notes')}</span> {hearing.notes}
                                                </p>
                                            )}
                                        </Item>
                                    ))}
                                </ItemList>
                            )}
                        </Section>
                    )}

                    {active === 'bench-notes' && (
                        <Section title={t('recordes.labels.bench_notes')} aside={`${record.benchNotes.length} ${t('recordes.labels.total')}`}>
                            {record.benchNotes.length === 0 ? (
                                <Muted>{t('recordes.messages.no_bench_notes')}</Muted>
                            ) : (
                                <ItemList>
                                    {record.benchNotes.map((note) => (
                                        <Item
                                            key={note.id}
                                            title={t('recordes.labels.note')}
                                            meta={[note.created, note.author && `${t('recordes.labels.assigned_to')} ${note.author}`]
                                                .filter(Boolean)
                                                .join(' · ')}
                                        >
                                            {note.html && <RichText html={note.html} className="mt-2 text-sm" />}
                                        </Item>
                                    ))}
                                </ItemList>
                            )}
                        </Section>
                    )}

                    {active === 'final-decision' && (
                        <Section
                            title={t('recordes.labels.final_judgment')}
                            aside={record.decision ? `${t('recordes.labels.created')} ${record.decision.created}` : t('recordes.messages.no_decision')}
                        >
                            {record.decision ? (
                                <div className="rounded-lg border border-(--border) p-4">
                                    <p className="font-semibold text-(--text)">{record.decision.name ?? t('recordes.labels.decision')}</p>
                                    {record.decision.html && <RichText html={record.decision.html} className="mt-2 text-sm" />}
                                </div>
                            ) : (
                                <Muted>{t('recordes.messages.no_decision')}</Muted>
                            )}
                        </Section>
                    )}

                    {active === 'other-documents' && (
                        <Section title={t('recordes.labels.other_documents')} aside={`${record.evidences.length} ${t('recordes.labels.items')}`}>
                            <SubHeading>{t('recordes.labels.applicant_evidence')}</SubHeading>
                            {record.evidences.length === 0 ? (
                                <Muted>{t('recordes.messages.no_evidence')}</Muted>
                            ) : (
                                <ItemList>
                                    {record.evidences.map((evidence) => (
                                        <Item key={evidence.id} title={evidence.title ?? t('recordes.labels.document')} meta={evidence.created}>
                                            {evidence.description && <p className="mt-1 text-sm text-(--text-muted)">{evidence.description}</p>}
                                        </Item>
                                    ))}
                                </ItemList>
                            )}
                        </Section>
                    )}
                </div>
            </div>
        </>
    );
}

function Section({ title, eyebrow, aside, children }: { title: string; eyebrow?: string; aside?: string; children: ReactNode }) {
    return (
        <section className="ui-card space-y-4 p-6">
            <header className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    {eyebrow && <p className="text-xs tracking-wide text-(--text-subtle) uppercase">{eyebrow}</p>}
                    <h1 className="text-lg font-semibold text-(--text)">{title}</h1>
                </div>
                {aside && <span className="text-xs text-(--text-subtle)">{aside}</span>}
            </header>
            {children}
        </section>
    );
}

function SubHeading({ children }: { children: ReactNode }) {
    return <h3 className="pt-2 text-sm font-semibold text-(--text)">{children}</h3>;
}

function Muted({ children }: { children: ReactNode }) {
    return <p className="text-sm text-(--text-subtle)">{children}</p>;
}

function ItemList({ children }: { children: ReactNode }) {
    return <ul className="space-y-3">{children}</ul>;
}

function Item({ title, meta, downloadUrl, children }: { title: string; meta?: string; downloadUrl?: string | null; children?: ReactNode }) {
    return (
        <li className="rounded-lg border border-(--border) p-4 text-sm">
            <div className="flex items-start justify-between gap-3">
                <div className="min-w-0">
                    <p className="font-semibold text-(--text)">{title}</p>
                    {meta && <p className="text-xs text-(--text-subtle)">{meta}</p>}
                </div>
                {downloadUrl && (
                    <a
                        href={downloadUrl}
                        className="inline-flex shrink-0 items-center gap-1 text-xs font-medium text-blue-700 hover:underline dark:text-blue-300"
                    >
                        <ArrowDownTrayIcon className="h-3.5 w-3.5" />
                        {t('recordes.buttons.view')}
                    </a>
                )}
            </div>
            {children}
        </li>
    );
}
