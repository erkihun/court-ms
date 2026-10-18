import { Head, Link } from '@inertiajs/react';
import { ReplyActions } from '@/components/party/ReplyActions';
import { ReviewStatusPill } from '@/components/party/StatusPill';
import { RichText } from '@/components/ui/RichText';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { RespondentResponseData, ResponseReplyData } from '@/types/party';

interface Props {
    response: RespondentResponseData;
    replies: ResponseReplyData[];
}

/** A respondent's accepted response to the applicant's case, and the applicant's replies to it. */
export default function RespondentResponseShow({ response, replies }: Props) {
    const contact: Array<[string, string | null]> = [
        [t('cases.respondent_defendant'), response.respondentName],
        [t('cases.applicant_email'), response.respondentEmail],
        [t('cases.labels.phone'), response.respondentPhone],
        [t('cases.respondent_name'), response.respondentOrganization],
    ];

    return (
        <div className="case-typography space-y-6">
            <Head title={t('cases.respondent_response')} />

            <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p className="text-xs font-semibold tracking-wide text-(--text-subtle) uppercase">
                        {t('cases.case_number')}: {response.caseNumber}
                    </p>
                    <h1 className="text-2xl font-semibold text-(--text)">{t('cases.respondent_response')}</h1>
                </div>
                <div className="flex flex-wrap gap-2">
                    <Link
                        href={route('applicant.cases.respondentResponses.replies.create', [response.caseId, response.id])}
                        className="inline-flex items-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800"
                    >
                        {t('respondent.give_response_of_response')}
                    </Link>
                    <Link href={route('applicant.cases.show', response.caseId)} className="inline-flex items-center rounded-lg border border-(--border) px-4 py-2 text-sm font-semibold text-(--text-muted) hover:bg-(--surface-soft)">
                        {t('cases.back_to_case')}
                    </Link>
                </div>
            </div>

            <section className="space-y-4 rounded-2xl border border-(--border) bg-(--surface-strong) p-6 shadow-xs">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 className="text-lg font-semibold text-(--text)">{response.title}</h2>
                        <p className="text-sm text-(--text-muted)">
                            {t('cases.case_number')}: {response.caseNumber}
                        </p>
                        {response.responseNumber && (
                            <p className="text-sm font-semibold text-(--text-muted)">
                                {t('respondent.response_number_label')}: {response.responseNumber}
                            </p>
                        )}
                    </div>
                    <div className="text-(--text-muted)">{response.created}</div>
                </div>

                {contact.some(([, value]) => value) && (
                    <dl className="grid gap-3 text-xs sm:grid-cols-2 lg:grid-cols-4">
                        {contact
                            .filter(([, value]) => value)
                            .map(([label, value]) => (
                                <div key={label}>
                                    <dt className="tracking-wide text-(--text-subtle) uppercase">{label}</dt>
                                    <dd className="font-medium text-(--text)">{value}</dd>
                                </div>
                            ))}
                    </dl>
                )}

                {response.summary && <div className="rounded-md border border-blue-100 bg-blue-50 p-3 text-blue-900">{response.summary}</div>}

                {response.descriptionHtml ? <RichText html={response.descriptionHtml} className="text-(--text)" /> : <p className="text-(--text-subtle)">{t('cases.no_description')}</p>}

                {response.pdfInlineUrl && (
                    <div className="border-t border-(--border) pt-4">
                        <div className="mb-2 text-(--text-muted)">{t('cases.attachment')}:</div>
                        <iframe src={response.pdfInlineUrl} className="min-h-[70vh] w-full rounded-xl border border-(--border)" title={response.title} />
                    </div>
                )}
            </section>

            <section className="rounded-2xl border border-(--border) bg-(--surface-strong) p-6 shadow-xs">
                <h2 className="text-lg font-semibold text-(--text)">{t('respondent.applicant_response_replies')}</h2>
                <p className="mb-4 text-sm text-(--text-subtle)">{t('respondent.response_of_response')}</p>
                {replies.length === 0 ? (
                    <div className="rounded-lg border border-dashed border-(--border) p-6 text-center text-sm text-(--text-subtle)">{t('respondent.no_applicant_response_replies')}</div>
                ) : (
                    <ul className="space-y-3">
                        {replies.map((reply) => (
                            <li key={reply.id} className="rounded-lg border border-(--border) bg-(--surface-soft) p-4">
                                <div className="flex flex-wrap items-center justify-between gap-2">
                                    <div className="flex items-center gap-2">
                                        <span className="font-semibold text-(--text)">{t('respondent.response_of_response')}</span>
                                        <ReviewStatusPill status={reply.reviewStatus} />
                                    </div>
                                    <span className="text-xs text-(--text-subtle)">{reply.created}</span>
                                </div>
                                <p className="mt-2 line-clamp-3 text-sm text-(--text-muted)">{reply.description}</p>
                                {reply.reviewNote && (
                                    <div className="mt-2 text-xs text-(--text-muted)">
                                        <span className="font-semibold text-(--text)">{t('cases.reviewer_note')}</span> {reply.reviewNote}
                                    </div>
                                )}
                                <div className="mt-3">
                                    <ReplyActions reply={reply} />
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </section>
        </div>
    );
}
