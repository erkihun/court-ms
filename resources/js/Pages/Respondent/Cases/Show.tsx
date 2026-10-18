import { ArrowDownTrayIcon } from '@heroicons/react/24/outline';
import { Head, Link } from '@inertiajs/react';
import { type CaseDocument, type CaseHearing, type CaseLetter, Detail, DocumentList, Empty, HearingList, Labelled, LetterList, Panel, SectionTitle } from '@/components/party/case/CaseSections';
import { CopyButton } from '@/components/ui/CopyButton';
import { RichText } from '@/components/ui/RichText';
import { t } from '@/lib/i18n';

interface Props {
    case: {
        id: number;
        caseNumber: string;
        status: string;
        reviewStatus: string;
        reviewNote: string | null;
        caseType: string | null;
        filed: string;
        applicantName: string | null;
        applicantEmail: string | null;
        respondentName: string | null;
        respondentAddress: string | null;
        descriptionHtml: string;
        reliefHtml: string | null;
        respondHref: string;
    };
    documents: CaseDocument[];
    hearings: CaseHearing[];
    letters: CaseLetter[];
    decisions: Array<{ id: number; name: string; date: string; downloadUrl: string }>;
}

/** A case as the respondent sees it, with the entry point for submitting a response. */
export default function RespondentCaseShow({ case: item, documents, hearings, letters, decisions }: Props) {
    return (
        <>
            <Head title={item.caseNumber} />
            <div className="case-typography grid grid-cols-1 gap-4 lg:grid-cols-3">
                <section className="overflow-hidden rounded-xl border border-(--border) bg-(--surface-strong) shadow-lg lg:col-span-3">
                    <div className="flex flex-wrap items-center justify-between gap-3 border-b border-(--border) px-5 py-4">
                        <div className="flex items-start gap-4 md:items-center">
                            <Labelled label={t('cases.case_number')}>
                                <span className="text-xl font-semibold tracking-tight text-(--text)">{item.caseNumber}</span>
                                <CopyButton value={item.caseNumber} />
                            </Labelled>
                            <div className="flex flex-col gap-1">
                                <Link
                                    href={item.respondHref}
                                    className="inline-flex items-center gap-2 rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-extrabold text-white shadow-xs hover:bg-blue-800 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-hidden"
                                >
                                    {t('respondent.give_response')}
                                </Link>
                                {item.reviewNote && (
                                    <div className="max-w-3xl text-xs leading-snug text-(--text-muted)">
                                        <span className="font-semibold text-(--text)">{t('cases.reviewer_note')}</span> {item.reviewNote}
                                    </div>
                                )}
                            </div>
                        </div>
                        <div className="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700">{t(`cases.status.${item.status}`)}</div>
                    </div>
                </section>

                <section className="space-y-5 rounded-xl border border-(--border) bg-(--surface-strong) p-5 shadow-xs lg:col-span-2">
                    {item.reviewStatus !== 'accepted' && <div className="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">{t('cases.review_notice')}</div>}

                    <div>
                        <SectionTitle>{t('cases.case_information')}</SectionTitle>
                        <dl className="grid gap-4 text-sm md:grid-cols-2">
                            <Detail label={t('cases.case_type')} value={item.caseType ?? '-'} />
                            <Detail label={t('cases.filed')} value={item.filed} />
                            <Detail label={t('cases.applicant_full_name')} value={item.applicantName ?? '-'} />
                            <Detail label={t('cases.applicant_email')} value={item.applicantEmail ?? '-'} />
                        </dl>
                    </div>

                    {(item.respondentName || item.respondentAddress) && (
                        <div>
                            <SectionTitle>{t('cases.respondent_defendant')}</SectionTitle>
                            <dl className="grid gap-4 text-sm md:grid-cols-2">
                                <Detail label={t('cases.name')} value={item.respondentName ?? t('cases.not_available')} />
                                <Detail label={t('cases.address')} value={item.respondentAddress ?? t('cases.not_available')} />
                            </dl>
                        </div>
                    )}

                    <div>
                        <SectionTitle>{t('cases.case_details')}</SectionTitle>
                        <RichText html={item.descriptionHtml} className="text-sm text-(--text)" />
                    </div>

                    {item.reliefHtml && (
                        <div>
                            <SectionTitle>{t('cases.relief_requested')}</SectionTitle>
                            <RichText html={item.reliefHtml} className="text-sm text-(--text)" />
                        </div>
                    )}

                    <DocumentList documents={documents} />
                </section>

                <div className="space-y-4">
                    <HearingList hearings={hearings} />
                    <LetterList letters={letters} />
                    <Panel title={t('dashboard.decisions')} count={decisions.length}>
                        {decisions.length === 0 ? (
                            <Empty>{t('dashboard.no_decisions')}</Empty>
                        ) : (
                            <ul className="space-y-2">
                                {decisions.map((decision) => (
                                    <li key={decision.id} className="flex items-center justify-between gap-3 rounded-lg border border-(--border) bg-(--surface-soft) p-3">
                                        <div className="min-w-0">
                                            <div className="truncate font-semibold text-(--text)">{decision.name}</div>
                                            <div className="text-xs text-(--text-subtle)">{decision.date}</div>
                                        </div>
                                        <a
                                            href={decision.downloadUrl}
                                            className="inline-flex shrink-0 items-center gap-1 rounded-full border border-emerald-300 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700"
                                        >
                                            <ArrowDownTrayIcon className="h-3.5 w-3.5" />
                                            {t('decisions.download')}
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </Panel>
                </div>
            </div>
        </>
    );
}
