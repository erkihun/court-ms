import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import {
    AddRowButton,
    caseNumberPreview,
    Certify,
    type DocumentRow,
    DocumentRows,
    ErrorSummary,
    FieldError,
    FieldLabel,
    inputClass,
    type WitnessRow,
    WitnessRows,
} from '@/components/party/case/CaseFormParts';
import { RichText } from '@/components/ui/RichText';
import { RichTextEditor } from '@/components/ui/RichTextEditor';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    types: Array<{ id: number; name: string; prefix: string }>;
    termsHtml: string | null;
    applicant: { isLawyer: boolean; fullName: string; address: string };
    today: string;
    year: string;
}

const EVIDENCE_MAX_MB = 5;

/** File a new case: parties, claim, relief, evidence PDFs and witnesses. */
export default function CaseCreate({ types, termsHtml, applicant, today, year }: Props) {
    const form = useForm({
        title: applicant.isLawyer ? '' : applicant.fullName,
        applicant_address: applicant.isLawyer ? '' : applicant.address,
        respondent_name: '',
        respondent_address: '',
        case_type_id: '',
        accept_terms: false,
        description: '',
        relief_requested: '',
        certify_appeal: false,
        documents: [{ title: '', file: null }] as DocumentRow[],
        witnesses: [{ full_name: '', phone: '', address: '' }] as WitnessRow[],
        certify_evidence: false,
    });
    const errors = form.errors as Record<string, string | undefined>;
    const selectedType = types.find((type) => String(type.id) === form.data.case_type_id);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.transform(({ documents, ...data }) => ({
            ...data,
            filing_date: today,
            evidence_titles: documents.map((row) => row.title),
            evidence_files: documents.map((row) => row.file),
        }));
        form.post(route('applicant.cases.store'), { forceFormData: true, onError: () => window.scrollTo({ top: 0, behavior: 'smooth' }) });
    };

    return (
        <>
            <Head title={t('cases.submit_case')} />
            <h1 className="text-2xl font-semibold text-(--text) md:text-3xl">{t('cases.submit_case')}</h1>

            <ErrorSummary errors={errors} title={t('cases.please_fix_errors')} />

            <form onSubmit={submit} className="space-y-8 rounded-xl border border-(--border) bg-(--surface-strong) p-6 shadow-xs md:p-8">
                <section className="grid gap-4 md:grid-cols-2">
                    <div>
                        <FieldLabel htmlFor="title" required>
                            {t('cases.applicant_name')}
                        </FieldLabel>
                        <input
                            id="title"
                            className={cn(inputClass, 'mt-1', !applicant.isLawyer && 'bg-slate-100')}
                            value={form.data.title}
                            onChange={(event) => form.setData('title', event.target.value)}
                            placeholder={t('cases.applicant_name_placeholder')}
                            readOnly={!applicant.isLawyer}
                            required
                            maxLength={255}
                        />
                        <FieldError message={errors.title} />
                    </div>
                    <div>
                        <FieldLabel htmlFor="applicant_address" required>
                            {t('cases.applicant_address')}
                        </FieldLabel>
                        <input
                            id="applicant_address"
                            className={cn(inputClass, 'mt-1', !applicant.isLawyer && 'bg-slate-100')}
                            value={form.data.applicant_address}
                            onChange={(event) => form.setData('applicant_address', event.target.value)}
                            placeholder={t('cases.applicant_address_placeholder')}
                            readOnly={!applicant.isLawyer}
                            required
                        />
                    </div>
                </section>

                <section className="grid gap-4 md:grid-cols-2">
                    <div>
                        <FieldLabel htmlFor="respondent_name" required>
                            {t('cases.respondent_name')}
                        </FieldLabel>
                        <input
                            id="respondent_name"
                            className={cn(inputClass, 'mt-1')}
                            value={form.data.respondent_name}
                            onChange={(event) => form.setData('respondent_name', event.target.value)}
                            placeholder={t('cases.respondent_name_placeholder')}
                            required
                            maxLength={255}
                        />
                        <FieldError message={errors.respondent_name} />
                    </div>
                    <div>
                        <FieldLabel htmlFor="respondent_address" required>
                            {t('cases.respondent_address')}
                        </FieldLabel>
                        <input
                            id="respondent_address"
                            className={cn(inputClass, 'mt-1')}
                            value={form.data.respondent_address}
                            onChange={(event) => form.setData('respondent_address', event.target.value)}
                            placeholder={t('cases.respondent_address_placeholder')}
                            required
                            maxLength={500}
                        />
                        <FieldError message={errors.respondent_address} />
                    </div>
                </section>

                <section className="grid gap-4 md:grid-cols-3">
                    <div>
                        <FieldLabel htmlFor="case_type_id" required>
                            {t('cases.case_type')}
                        </FieldLabel>
                        <select id="case_type_id" className={cn(inputClass, 'mt-1')} value={form.data.case_type_id} onChange={(event) => form.setData('case_type_id', event.target.value)} required>
                            <option value="">-- {t('cases.select_option')} --</option>
                            {types.map((type) => (
                                <option key={type.id} value={type.id}>
                                    {type.name}
                                </option>
                            ))}
                        </select>
                        {selectedType && <p className="mt-1 font-mono text-xs text-(--text-subtle)">{caseNumberPreview(selectedType.prefix, year)}</p>}
                        <FieldError message={errors.case_type_id} />
                    </div>
                </section>

                {termsHtml && (
                    <section className="space-y-3 rounded-xl border border-(--border) bg-(--surface-soft) p-4 md:p-5">
                        <RichText html={termsHtml} className="rounded-lg border border-(--border) bg-(--surface-strong) p-4 leading-relaxed text-(--text)" />
                        <Certify checked={form.data.accept_terms} onChange={(value) => form.setData('accept_terms', value)} error={errors.accept_terms}>
                            {t('terms.applicant_checkbox')}
                        </Certify>
                    </section>
                )}

                <section className="space-y-2">
                    <FieldLabel required>{t('cases.case_details')}</FieldLabel>
                    <RichTextEditor id="editor-description" value={form.data.description} onChange={(html) => form.setData('description', html)} height={520} />
                    <FieldError message={errors.description} />
                </section>

                <section className="space-y-2">
                    <FieldLabel required>{t('cases.relief_requested')}</FieldLabel>
                    <RichTextEditor id="editor-relief" value={form.data.relief_requested} onChange={(html) => form.setData('relief_requested', html)} height={380} />
                    <FieldError message={errors.relief_requested} />
                    <div className="mt-3">
                        <Certify checked={form.data.certify_appeal} onChange={(value) => form.setData('certify_appeal', value)} error={errors.certify_appeal}>
                            {t('cases.certify_appeal')}
                        </Certify>
                    </div>
                </section>

                <section className="space-y-3 rounded-xl border border-(--border) bg-(--surface-soft) p-4 md:p-5">
                    <div className="flex items-center justify-between gap-3">
                        <div>
                            <h2 className="font-semibold text-(--text)">
                                {t('cases.evidence_documents')} <span className="text-red-600">*</span>
                            </h2>
                            <p className="mt-0.5 text-xs text-(--text-subtle)">{t('cases.file_requirements')}</p>
                            <p className="mt-0.5 text-xs text-red-600">{t('cases.evidence_size_limit', { size: EVIDENCE_MAX_MB })}</p>
                        </div>
                        <AddRowButton onClick={() => form.setData('documents', [...form.data.documents, { title: '', file: null }])}>{t('cases.add_document')}</AddRowButton>
                    </div>
                    <DocumentRows
                        rows={form.data.documents}
                        onChange={(rows) => form.setData('documents', rows)}
                        errors={errors}
                        required
                        titlePlaceholder={t('cases.document_title_placeholder')}
                    />
                    <FieldError message={errors.evidence_files ?? errors.evidence_titles} />
                </section>

                <section className="space-y-3 rounded-xl border border-(--border) bg-(--surface-soft) p-4 md:p-5">
                    <div className="flex items-center justify-between">
                        <h2 className="font-semibold text-(--text)">
                            {t('cases.witnesses_section.title')} <span className="text-red-600">*</span>
                        </h2>
                        <AddRowButton onClick={() => form.setData('witnesses', [...form.data.witnesses, { full_name: '', phone: '', address: '' }])}>{t('cases.add_witness')}</AddRowButton>
                    </div>
                    <WitnessRows
                        rows={form.data.witnesses}
                        onChange={(rows) => form.setData('witnesses', rows)}
                        errors={errors}
                        required
                        placeholders={{
                            full_name: t('cases.witnesses_section.full_name_placeholder'),
                            phone: t('cases.witnesses_section.phone_placeholder'),
                            address: t('cases.witnesses_section.address_placeholder'),
                        }}
                    />
                    <FieldError message={errors.witnesses_duplicate_phone ?? errors.witnesses} />
                </section>

                <Certify checked={form.data.certify_evidence} onChange={(value) => form.setData('certify_evidence', value)} error={errors.certify_evidence}>
                    {t('cases.certify_evidence')}
                </Certify>

                <div className="flex flex-wrap gap-3 pt-2">
                    <button
                        type="submit"
                        disabled={form.processing}
                        className="inline-flex items-center gap-1.5 rounded-lg bg-orange-500 px-4 py-2.5 font-semibold text-white hover:bg-orange-600 focus:ring-2 focus:ring-orange-400 focus:ring-offset-1 focus:outline-hidden disabled:opacity-60"
                    >
                        {t('cases.submit_case_button')}
                    </button>
                    <Link href={route('applicant.cases.index')} className="inline-flex items-center rounded-lg bg-(--surface-soft) px-4 py-2.5 font-medium text-(--text-muted) hover:bg-(--border)">
                        {t('cases.cancel')}
                    </Link>
                </div>
                {form.progress && <progress value={form.progress.percentage} max={100} className="w-full" />}
            </form>
        </>
    );
}
