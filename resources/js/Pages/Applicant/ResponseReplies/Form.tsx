import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { FieldError, inputClass } from '@/components/party/case/CaseFormParts';
import { ResponseFacts } from '@/components/party/ResponseFacts';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { RespondentResponseData, ResponseReplyData } from '@/types/party';

interface Props {
    response: RespondentResponseData;
    applicantName: string | null;
    /** null when replying for the first time. */
    reply: ResponseReplyData | null;
}

/** The applicant's reply to a respondent response: a statement and a signed PDF. */
export default function ResponseReplyForm({ response, applicantName, reply }: Props) {
    const editing = reply !== null;
    const form = useForm({ description: reply?.description ?? '', pdf: null as File | null });
    const backHref = route('applicant.cases.respondentResponses.show', [response.caseId, response.id]);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (editing) {
            form.transform((data) => ({ ...data, _method: 'patch' }));
            form.post(route('applicant.cases.respondentResponses.replies.update', [response.caseId, response.id, reply.id]), { forceFormData: true });
        } else {
            form.post(route('applicant.cases.respondentResponses.replies.store', [response.caseId, response.id]), { forceFormData: true });
        }
    };

    return (
        <div className="mx-auto max-w-4xl space-y-6">
            <Head title={t('respondent.give_response_of_response')} />
            <div className="space-y-5 rounded-2xl border border-(--border) bg-(--surface-strong) p-6 shadow-xs">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p className="text-xs font-semibold tracking-wide text-(--text-subtle) uppercase">{t('respondent.response_of_response')}</p>
                        <h1 className="mt-1 text-2xl font-semibold text-(--text)">{t('respondent.give_response_of_response')}</h1>
                    </div>
                    <Link href={backHref} className="inline-flex items-center rounded-lg border border-(--border) px-4 py-2 text-sm font-semibold text-(--text-muted) hover:bg-(--surface-soft)">
                        {t('cases.back')}
                    </Link>
                </div>
                <ResponseFacts response={response} applicantName={applicantName} />
            </div>

            <form onSubmit={submit} className="space-y-5 rounded-2xl border border-(--border) bg-(--surface-strong) p-6 shadow-xs">
                <div>
                    <label htmlFor="description" className="block text-sm font-semibold text-(--text-muted)">
                        {t('respondent.response_of_response_description')}
                    </label>
                    <textarea
                        id="description"
                        rows={8}
                        className={`${inputClass} mt-1`}
                        value={form.data.description}
                        onChange={(event) => form.setData('description', event.target.value)}
                        aria-invalid={form.errors.description ? true : undefined}
                        required
                    />
                    <FieldError message={form.errors.description} />
                </div>
                <div>
                    <label htmlFor="pdf" className="block text-sm font-semibold text-(--text-muted)">
                        {t('cases.attachment')}
                    </label>
                    <p className="mt-1 text-xs text-(--text-subtle)">{t('respondent.response_reply_attachment_hint')}</p>
                    <input
                        id="pdf"
                        type="file"
                        accept="application/pdf"
                        required={!editing}
                        onChange={(event) => form.setData('pdf', event.target.files?.[0] ?? null)}
                        className="mt-2 block w-full text-sm text-(--text-muted) file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-blue-700"
                    />
                    <FieldError message={form.errors.pdf} />
                </div>
                <div className="flex justify-end gap-2">
                    <Link href={backHref} className="inline-flex items-center rounded-lg border border-(--border) px-4 py-2 text-sm font-semibold text-(--text-muted) hover:bg-(--surface-soft)">
                        {t('cases.buttons.cancel')}
                    </Link>
                    <button type="submit" disabled={form.processing} className="inline-flex items-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 disabled:opacity-60">
                        {t(editing ? 'cases.buttons.update' : 'cases.buttons.submit')}
                    </button>
                </div>
            </form>
        </div>
    );
}
