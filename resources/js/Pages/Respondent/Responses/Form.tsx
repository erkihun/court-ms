import { InformationCircleIcon } from '@heroicons/react/24/outline';
import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { ErrorSummary, FieldError, inputClass } from '@/components/party/case/CaseFormParts';
import { RichTextEditor } from '@/components/ui/RichTextEditor';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    /** null when creating. */
    response: { id: number; title: string; description: string; hasPdf: boolean } | null;
    caseNumber: string;
}

/** Create or edit a response to a case: title, rich text body and the signed PDF. */
export default function ResponseForm({ response, caseNumber }: Props) {
    const editing = response !== null;
    const form = useForm({
        title: response?.title ?? '',
        case_number: caseNumber,
        description: response?.description ?? '',
        pdf: null as File | null,
    });
    const errors = form.errors as Record<string, string | undefined>;
    const heading = t(editing ? 'respondent.edit_response' : 'respondent.create_response');

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (editing) {
            form.transform((data) => ({ ...data, _method: 'patch' }));
            form.post(route('respondent.responses.update', response.id), { forceFormData: true });
        } else {
            form.post(route('respondent.responses.store'), { forceFormData: true });
        }
    };

    return (
        <div className="mx-auto max-w-3xl space-y-5">
            <Head title={heading} />
            <div>
                <h1 className="text-2xl font-semibold text-(--text)">{heading}</h1>
                <p className="mt-1 text-sm text-(--text-subtle)">{t(editing ? 'respondent.response_edit_description' : 'respondent.response_intro')}</p>
            </div>

            <ErrorSummary errors={errors} title={t('cases.please_fix_errors')} />

            <form onSubmit={submit} className="overflow-hidden rounded-2xl border border-(--border) bg-(--surface-strong) shadow-xs">
                {!editing && (
                    <div className="flex gap-3 border-b border-amber-200 bg-amber-50 px-6 py-4">
                        <InformationCircleIcon className="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />
                        <p className="text-sm leading-relaxed text-amber-800">{t('respondent.response_instruction')}</p>
                    </div>
                )}

                <div className="space-y-6 p-6 md:p-8">
                    <div>
                        <label htmlFor="resp_title" className="mb-1 block text-sm font-medium text-(--text-muted)">
                            {t('respondent.response_title_label')} <span className="text-red-500">*</span>
                        </label>
                        <input id="resp_title" className={inputClass} value={form.data.title} onChange={(event) => form.setData('title', event.target.value)} required maxLength={255} autoComplete="off" />
                        <FieldError message={errors.title} />
                    </div>

                    <div>
                        <label htmlFor="resp_case_number" className="mb-1 block text-sm font-medium text-(--text-muted)">
                            {t('respondent.case_number_label')} {!editing && <span className="text-red-500">*</span>}
                        </label>
                        <input id="resp_case_number" className={cn(inputClass, 'cursor-not-allowed bg-slate-100')} value={form.data.case_number} readOnly required={!editing} />
                        <FieldError message={errors.case_number} />
                    </div>

                    <div>
                        <span className="mb-1 block text-sm font-medium text-(--text-muted)">{t('respondent.description_label')}</span>
                        <RichTextEditor value={form.data.description} onChange={(html) => form.setData('description', html)} height={420} />
                        <FieldError message={errors.description} />
                    </div>

                    <div>
                        <label htmlFor="resp_pdf" className="mb-1 block text-sm font-medium text-(--text-muted)">
                            {t(editing ? 'respondent.replace_pdf' : 'respondent.response_pdf')} {!editing && <span className="text-red-500">*</span>}
                        </label>
                        <input
                            id="resp_pdf"
                            type="file"
                            accept="application/pdf"
                            required={!editing}
                            onChange={(event) => form.setData('pdf', event.target.files?.[0] ?? null)}
                            className="w-full rounded-xl border border-(--border) px-4 py-3 text-sm text-(--text-muted) file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-blue-700"
                        />
                        <FieldError message={errors.pdf} />
                    </div>

                    <div className="flex items-center justify-end gap-3 border-t border-(--border) pt-4">
                        <Link href={route('respondent.responses.index')} className="inline-flex items-center rounded-xl border border-(--border) px-4 py-2.5 text-sm font-medium text-(--text-muted) hover:bg-(--surface-soft)">
                            {t('app.cancel')}
                        </Link>
                        <button type="submit" disabled={form.processing} className="inline-flex items-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-blue-700 disabled:opacity-60">
                            {t('respondent.submit_response')}
                        </button>
                    </div>
                    {form.progress && <progress value={form.progress.percentage} max={100} className="w-full" />}
                </div>
            </form>
        </div>
    );
}
