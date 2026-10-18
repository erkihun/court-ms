import { Head } from '@inertiajs/react';
import { ReplyActions } from '@/components/party/ReplyActions';
import { ResponseFacts } from '@/components/party/ResponseFacts';
import { ReviewStatusPill } from '@/components/party/StatusPill';
import { t } from '@/lib/i18n';
import type { RespondentResponseData, ResponseReplyData } from '@/types/party';

interface Props {
    response: RespondentResponseData;
    applicantName: string | null;
    reply: ResponseReplyData;
}

/** One of the applicant's replies, with its review state and PDF. */
export default function ResponseReplyShow({ response, applicantName, reply }: Props) {
    return (
        <div className="mx-auto max-w-[1600px] space-y-6">
            <Head title={t('respondent.response_of_response')} />
            <div className="space-y-5 rounded-2xl border border-(--border) bg-(--surface-strong) p-6 shadow-xs">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p className="text-xs font-semibold tracking-wide text-(--text-subtle) uppercase">{t('respondent.response_of_response')}</p>
                        <div className="mt-1 flex items-center gap-2">
                            <h1 className="text-2xl font-semibold text-(--text)">{t('respondent.response_of_response')}</h1>
                            <ReviewStatusPill status={reply.reviewStatus} />
                        </div>
                    </div>
                    <ReplyActions reply={reply} showView={false} />
                </div>
                <ResponseFacts response={response} applicantName={applicantName} />
            </div>

            <div className="rounded-2xl border border-(--border) bg-(--surface-strong) p-6 shadow-xs">
                <h2 className="text-lg font-semibold text-(--text)">{t('respondent.response_of_response_description')}</h2>
                <div className="mt-3 text-sm whitespace-pre-line text-(--text-muted)">{reply.description}</div>
                {reply.reviewNote && (
                    <div className="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                        <span className="font-semibold">{t('cases.reviewer_note')}</span> {reply.reviewNote}
                    </div>
                )}
            </div>

            <div className="rounded-2xl border border-(--border) bg-(--surface-strong) p-6 shadow-xs">
                <div className="flex items-center justify-between gap-3">
                    <h2 className="text-lg font-semibold text-(--text)">{t('cases.attachment')}</h2>
                    <a href={reply.downloadUrl} className="text-sm font-semibold text-blue-700 hover:underline">
                        {t('respondent.download')}
                    </a>
                </div>
                <iframe src={reply.inlineUrl} className="mt-4 min-h-[80vh] w-full rounded-xl border border-(--border)" title={t('respondent.response_of_response')} />
            </div>
        </div>
    );
}
