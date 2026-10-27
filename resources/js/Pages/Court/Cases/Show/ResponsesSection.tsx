import { useForm } from '@inertiajs/react';
import { StatusBadge as UiStatusBadge } from '@euisis/ui';
import LocalizedDateDisplay from '@/Components/Calendar/LocalizedDateDisplay';
import { Button, FieldError, FormLabel, Textarea } from '@/Components/ui';
import type { ApplicantResponseReplyRow, RespondentResponseRow } from '@/court/types/caseDetail';
import SectionCard, { EmptyBox } from './SectionCard';
import { REVIEW_TONES, useCaseText } from './useCaseText';
import { RepliesIcon, ResponsesIcon } from './icons';

type ReviewForm = { decision: 'accept' | 'return'; review_note: string };

/** The accept / return form under a response or a reply; posts to the given route. */
function ReviewForm({ url, initialNote, returnLabel }: { url: string; initialNote: string | null; returnLabel: string }) {
    const { t } = useCaseText();
    const form = useForm<ReviewForm>({ decision: 'accept', review_note: initialNote ?? '' });

    const decide = (decision: 'accept' | 'return') => {
        form.transform((data) => ({ ...data, decision }));
        form.patch(url, { preserveScroll: true });
    };

    return (
        <form onSubmit={(event) => { event.preventDefault(); decide('accept'); }} className="space-y-2">
            <FormLabel className="text-xs">{t('cm.cases.reviewer_note')}</FormLabel>
            <Textarea rows={2} value={form.data.review_note} onChange={(event) => form.setData('review_note', event.target.value)} placeholder={t('cm.cases.show.add_reason_note')} className="w-full" aria-invalid={form.errors.review_note ? true : undefined} />
            <FieldError>{form.errors.review_note}</FieldError>
            <div className="flex flex-wrap items-center gap-2">
                <Button size="sm" variant="primary" className="bg-emerald-600 hover:bg-emerald-700" loading={form.processing && form.data.decision === 'accept'} disabled={form.processing} onClick={() => decide('accept')}>{t('cm.cases.show.accept')}</Button>
                <Button size="sm" variant="primary" className="bg-amber-600 hover:bg-amber-700" loading={form.processing && form.data.decision === 'return'} disabled={form.processing} onClick={() => decide('return')}>{returnLabel}</Button>
            </div>
        </form>
    );
}

function PdfFrame({ url, title }: { url: string; title: string }) {
    return (
        <div className="overflow-hidden rounded-control border border-(--app-border) bg-(--app-surface)">
            <iframe src={`${url}#toolbar=0&navpanes=0&scrollbar=0`} loading="lazy" className="w-full" style={{ height: 'min(80vh, 980px)', minHeight: 520 }} title={title} />
        </div>
    );
}

/** The respondent's responses to the case, each with its PDF and the reviewer's accept / return. */
export function RespondentResponsesSection({ caseNumber, responses, canReview }: { caseNumber: string; responses: RespondentResponseRow[]; canReview: boolean }) {
    const { t, reviewLabel } = useCaseText();

    return (
        <SectionCard id="respondent-responses" title={t('cm.cases.navigation.respondent_responses')} icon={<ResponsesIcon />} count={`${responses.length} ${t('cm.cases.respondent_responses')}`}>
            <div className="max-h-[85vh] space-y-4 overflow-auto pr-2">
                {responses.length === 0 ? <EmptyBox>{t('cm.recordes.messages.no_responses')}</EmptyBox> : responses.map((response) => {
                    const title = response.title ?? t('cm.cases.respondent_response');
                    return (
                        <article key={response.id} className="space-y-3 rounded-card border border-(--app-border) bg-(--app-surface-muted) p-4">
                            <div className="flex flex-wrap items-center justify-between gap-2 text-sm text-(--app-muted-foreground)">
                                <div className="flex flex-wrap items-center gap-2">
                                    <span className="font-semibold text-(--app-foreground)">{title}</span>
                                    <UiStatusBadge tone={REVIEW_TONES[response.review_status] ?? 'success'}>{reviewLabel(response.review_status)}</UiStatusBadge>
                                </div>
                                <LocalizedDateDisplay value={response.created_at} withTime fallback="" />
                            </div>
                            <div className="text-xs text-(--app-muted-foreground)">{t('cm.cases.case_number')}: {response.case_number ?? caseNumber}</div>
                            {response.description_html !== ''
                                ? <div className="rich-text rounded-control border border-(--app-border) bg-(--app-surface) px-4 py-3 text-sm" dangerouslySetInnerHTML={{ __html: response.description_html }} />
                                : <div className="text-xs italic text-(--app-muted-foreground)">{t('cm.cases.no_description')}</div>}
                            {response.review_note && (
                                <div className="text-xs text-(--app-muted-foreground)"><span className="font-semibold text-(--app-foreground)">{t('cm.cases.reviewer_note')}</span> {response.review_note}</div>
                            )}
                            {response.pdf_url && <PdfFrame url={response.pdf_url} title={title} />}
                            {canReview && ['awaiting_review', 'returned'].includes(response.review_status) && (
                                <ReviewForm url={route('respondent-responses.review', response.id)} initialNote={response.review_note} returnLabel={t('cm.cases.show.return')} />
                            )}
                        </article>
                    );
                })}
            </div>
        </SectionCard>
    );
}

/** The applicant's replies to respondent responses (people with cases.response-replies.manage). */
export function ResponseRepliesSection({ caseNumber, replies }: { caseNumber: string; replies: ApplicantResponseReplyRow[] }) {
    const { t, text, reviewLabel } = useCaseText();
    const notAvailable = t('cm.cases.labels.not_available');

    return (
        <SectionCard id="response-of-response" title={t('cm.cases.navigation.response_of_response')} icon={<RepliesIcon />} count={`${replies.length} ${text('replies_count')}`}>
            <div className="max-h-[85vh] space-y-4 overflow-auto pr-2">
                {replies.length === 0 ? <EmptyBox>{t('cm.respondent.no_applicant_response_replies')}</EmptyBox> : replies.map((reply) => (
                    <article key={reply.id} className="space-y-3 rounded-card border border-(--app-border) bg-(--app-surface-muted) p-4">
                        <div className="flex flex-wrap items-center justify-between gap-2 text-sm text-(--app-muted-foreground)">
                            <div className="flex flex-wrap items-center gap-2">
                                <span className="font-semibold text-(--app-foreground)">{t('cm.respondent.response_of_response')}</span>
                                <UiStatusBadge tone={REVIEW_TONES[reply.review_status] ?? 'success'}>{reviewLabel(reply.review_status)}</UiStatusBadge>
                            </div>
                            <LocalizedDateDisplay value={reply.created_at} withTime fallback="" />
                        </div>
                        <div className="grid gap-1 text-xs text-(--app-muted-foreground) sm:grid-cols-2">
                            <span>{t('cm.cases.case_number')}: {caseNumber}</span>
                            <span>{t('cm.respondent.response_number_label')}: {reply.response_number || notAvailable}</span>
                            <span>{t('cm.cases.applicant_full_name')}: {reply.applicant_name || notAvailable}</span>
                        </div>
                        <div className="whitespace-pre-line rounded-control border border-(--app-border) bg-(--app-surface) px-4 py-3 text-sm text-(--app-foreground)">{reply.description}</div>
                        {reply.review_note && (
                            <div className="text-xs text-(--app-muted-foreground)"><span className="font-semibold text-(--app-foreground)">{t('cm.cases.reviewer_note')}</span> {reply.review_note}</div>
                        )}
                        {reply.pdf_url && <PdfFrame url={reply.pdf_url} title={t('cm.respondent.response_of_response')} />}
                        {['awaiting_review', 'returned'].includes(reply.review_status) && (
                            <ReviewForm url={route('applicant-response-replies.review', reply.id)} initialNote={reply.review_note} returnLabel={t('cm.cases.show.give_correction')} />
                        )}
                    </article>
                ))}
            </div>
        </SectionCard>
    );
}
