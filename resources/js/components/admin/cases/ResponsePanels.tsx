import { useForm } from '@inertiajs/react';
import type { SyntheticEvent } from 'react';
import { Button } from '@/components/ui/Button';
import { Field } from '@/components/ui/Field';
import { Textarea } from '@/components/ui/Input';
import { RichText } from '@/components/ui/RichText';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { RespondentResponseRow, ResponseReplyRow, ReviewStatus } from '@/types/admin/case';
import { ReviewStatusBadge } from './CaseBadges';
import { CaseSection, EmptyBox } from './CaseSection';

const reviewable = (status: ReviewStatus) => status === 'awaiting_review' || status === 'returned';

function PdfFrame({ url, title }: { url: string; title: string }) {
    return (
        <div className="overflow-hidden rounded-xl border border-(--border) bg-white">
            <iframe
                src={`${url}#toolbar=0&navpanes=0&scrollbar=0`}
                loading="lazy"
                title={title}
                className="w-full"
                style={{ height: 'min(80vh, 980px)', minHeight: 520 }}
            />
        </div>
    );
}

/** Accept, or return with a note; both review endpoints take the same fields. */
function ReviewForm({ url, initialNote, returnLabel }: { url: string; initialNote: string; returnLabel: string }) {
    const form = useForm({ decision: '', review_note: initialNote });

    const send = (decision: 'accept' | 'return') => (event: SyntheticEvent) => {
        event.preventDefault();
        form.transform((data) => ({ ...data, decision }));
        form.patch(url, { preserveScroll: true });
    };

    return (
        <form onSubmit={send('accept')} className="space-y-2">
            <Field label={t('cases.reviewer_note')} error={form.errors.review_note}>
                <Textarea
                    rows={2}
                    value={form.data.review_note}
                    onChange={(event) => form.setData('review_note', event.target.value)}
                    placeholder={t('cases.show.add_reason_note')}
                    maxLength={2000}
                />
            </Field>
            <div className="flex flex-wrap gap-2">
                <Button type="submit" size="sm" variant="success" disabled={form.processing}>
                    {t('cases.show.accept')}
                </Button>
                <Button size="sm" variant="secondary" onClick={send('return')} disabled={form.processing}>
                    {returnLabel}
                </Button>
            </div>
        </form>
    );
}

function ReviewNote({ note }: { note: string | null }) {
    if (!note) return null;

    return (
        <div className="text-xs text-(--text-muted)">
            <span className="font-semibold text-(--text)">{t('cases.reviewer_note')}</span> {note}
        </div>
    );
}

export function RespondentResponsesSection({ responses, canReview }: { responses: RespondentResponseRow[]; canReview: boolean }) {
    return (
        <CaseSection
            id="respondent-responses"
            title={t('cases.navigation.respondent_responses')}
            count={responses.length}
            countLabel={t('cases.respondent_responses')}
        >
            {responses.length === 0 ? (
                <EmptyBox>{t('recordes.messages.no_responses')}</EmptyBox>
            ) : (
                <div className="space-y-4">
                    {responses.map((response) => {
                        const title = response.title ?? t('cases.respondent_response');

                        return (
                            <article key={response.id} className="space-y-3 rounded-2xl border border-(--border) bg-(--surface-soft) p-4">
                                <div className="flex flex-wrap items-center justify-between gap-2 text-sm text-(--text-subtle)">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <span className="font-semibold text-(--text)">{title}</span>
                                        <ReviewStatusBadge status={response.status} />
                                    </div>
                                    <span>{response.created}</span>
                                </div>
                                <div className="text-xs text-(--text-muted)">
                                    {t('cases.case_number')}: {response.caseNumber}
                                </div>
                                {response.bodyHtml ? (
                                    <RichText html={response.bodyHtml} className="rich-text text-sm" />
                                ) : (
                                    <div className="text-xs text-(--text-subtle) italic">{t('cases.no_description')}</div>
                                )}
                                <ReviewNote note={response.reviewNote} />
                                {response.pdfUrl && <PdfFrame url={response.pdfUrl} title={title} />}
                                {canReview && reviewable(response.status) && (
                                    <ReviewForm
                                        url={route('respondent-responses.review', response.id)}
                                        initialNote={response.reviewNote ?? ''}
                                        returnLabel={t('cases.show.return')}
                                    />
                                )}
                            </article>
                        );
                    })}
                </div>
            )}
        </CaseSection>
    );
}

export function ResponseRepliesSection({ caseNumber, replies }: { caseNumber: string; replies: ResponseReplyRow[] }) {
    const missing = t('cases.labels.not_available');

    return (
        <CaseSection id="response-of-response" title={t('cases.navigation.response_of_response')} count={replies.length}>
            {replies.length === 0 ? (
                <EmptyBox>{t('respondent.no_applicant_response_replies')}</EmptyBox>
            ) : (
                <div className="space-y-4">
                    {replies.map((reply) => (
                        <article key={reply.id} className="space-y-3 rounded-2xl border border-(--border) bg-(--surface-soft) p-4">
                            <div className="flex flex-wrap items-center justify-between gap-2 text-sm text-(--text-subtle)">
                                <div className="flex flex-wrap items-center gap-2">
                                    <span className="font-semibold text-(--text)">{t('respondent.response_of_response')}</span>
                                    <ReviewStatusBadge status={reply.status} />
                                </div>
                                <span>{reply.created}</span>
                            </div>
                            <div className="grid gap-1 text-xs text-(--text-muted) sm:grid-cols-2">
                                <span>
                                    {t('cases.case_number')}: {caseNumber}
                                </span>
                                <span>
                                    {t('respondent.response_number_label')}: {reply.responseNumber ?? missing}
                                </span>
                                <span>
                                    {t('cases.applicant_full_name')}: {reply.applicantName ?? missing}
                                </span>
                            </div>
                            <div className="text-sm whitespace-pre-line text-(--text)">{reply.description}</div>
                            <ReviewNote note={reply.reviewNote} />
                            {reply.pdfUrl && <PdfFrame url={reply.pdfUrl} title={t('respondent.response_of_response')} />}
                            {reviewable(reply.status) && (
                                <ReviewForm
                                    url={route('applicant-response-replies.review', reply.id)}
                                    initialNote={reply.reviewNote ?? ''}
                                    returnLabel={t('cases.show.give_correction')}
                                />
                            )}
                        </article>
                    ))}
                </div>
            )}
        </CaseSection>
    );
}
