import { TrashIcon } from '@heroicons/react/24/outline';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { type FormEvent, useState } from 'react';
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
import { CaseStatusPill } from '@/components/party/StatusPill';
import { ConfirmDialog } from '@/components/ui/ConfirmDialog';
import { EthDateInput } from '@/components/ui/EthDateInput';
import { RichTextEditor } from '@/components/ui/RichTextEditor';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    case: {
        id: number;
        caseNumber: string;
        status: string;
        title: string;
        filingDate: string;
        caseTypeId: number;
        respondentName: string;
        respondentAddress: string;
        description: string;
        relief: string;
    };
    types: Array<{ id: number; name: string; prefix: string }>;
    year: string;
    documents: Array<{ id: number; title: string; downloadUrl: string | null }>;
    witnesses: Array<{ id: number; fullName: string; phone: string | null; email: string | null; address: string | null }>;
}

type Removal = { kind: 'document' | 'witness'; id: number; label: string };

/** Correct a pending (returned or awaiting) case and add more evidence or witnesses. */
export default function CaseEdit({ case: item, types, year, documents, witnesses }: Props) {
    const [removal, setRemoval] = useState<Removal | null>(null);
    const form = useForm({
        title: item.title,
        filing_date: item.filingDate,
        case_type_id: String(item.caseTypeId),
        respondent_name: item.respondentName,
        respondent_address: item.respondentAddress,
        description: item.description,
        relief_requested: item.relief,
        certify_appeal: false,
        documents: [{ title: '', file: null }] as DocumentRow[],
        witnesses: [{ full_name: '', phone: '', email: '', address: '' }] as WitnessRow[],
        certify_evidence: false,
    });
    const errors = form.errors as Record<string, string | undefined>;
    const selectedType = types.find((type) => String(type.id) === form.data.case_type_id);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.transform(({ documents: rows, ...data }) => {
            const filled = rows.filter((row) => row.file !== null);
            return {
                ...data,
                _method: 'patch',
                evidence_titles: filled.map((row) => row.title),
                evidence_files: filled.map((row) => row.file),
                witnesses: data.witnesses.filter((row) => row.full_name.trim() !== ''),
            };
        });
        form.post(route('applicant.cases.update', item.id), { forceFormData: true, onError: () => window.scrollTo({ top: 0, behavior: 'smooth' }) });
    };

    const confirmRemoval = () => {
        if (!removal) return;
        const url =
            removal.kind === 'document'
                ? route('applicant.cases.evidences.delete', { id: item.id, evidenceId: removal.id })
                : route('applicant.cases.witnesses.delete', { id: item.id, witnessId: removal.id });
        router.delete(url, { preserveScroll: true, preserveState: true, onFinish: () => setRemoval(null) });
    };

    return (
        <>
            <Head title={t('cases.edit_title', { no: item.caseNumber })} />
            <div>
                <h1 className="text-2xl font-semibold text-(--text) md:text-3xl">{t('cases.edit_title', { no: item.caseNumber })}</h1>
                <p className="mt-1 text-(--text-subtle)">{t('cases.hints.edit_page_subtitle')}</p>
            </div>

            <ErrorSummary errors={errors} title={t('cases.please_fix_errors')} />

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <form onSubmit={submit} className="space-y-6 lg:col-span-2">
                    <div className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-(--border) bg-(--surface-strong) p-5 shadow-xs">
                        <div className="flex items-center gap-3">
                            <div>
                                <div className="text-xs text-(--text-subtle)">{t('cases.case_number_short')}</div>
                                <div className="text-xl font-semibold tracking-tight text-(--text)">{item.caseNumber}</div>
                            </div>
                            <CaseStatusPill status={item.status} withIcon={false} />
                        </div>
                        <Link href={route('applicant.cases.show', item.id)} className="inline-flex items-center rounded-md border border-(--border) px-3 py-2 text-xs font-medium text-(--text-muted) hover:bg-(--surface-soft)">
                            {t('cases.back_to_case')}
                        </Link>
                    </div>

                    <div className="space-y-5 rounded-xl border border-(--border) bg-(--surface-strong) p-5 shadow-xs">
                        <h2 className="font-semibold text-(--text)">{t('cases.section.case_details')}</h2>

                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <FieldLabel htmlFor="title" required>
                                    {t('cases.labels.title')}
                                </FieldLabel>
                                <input id="title" className={cn(inputClass, 'mt-1')} value={form.data.title} onChange={(event) => form.setData('title', event.target.value)} required maxLength={255} />
                                <FieldError message={errors.title} />
                            </div>
                            <div>
                                <FieldLabel required>{t('cases.labels.filing_date')}</FieldLabel>
                                <div className="mt-1">
                                    <EthDateInput value={form.data.filing_date} onChange={(value) => form.setData('filing_date', value)} invalid={!!errors.filing_date} />
                                </div>
                                <FieldError message={errors.filing_date} />
                            </div>
                            <div>
                                <FieldLabel htmlFor="case_type_id" required>
                                    {t('cases.labels.case_type')}
                                </FieldLabel>
                                <select id="case_type_id" className={cn(inputClass, 'mt-1')} value={form.data.case_type_id} onChange={(event) => form.setData('case_type_id', event.target.value)} required>
                                    <option value="">{t('cases.placeholders.select')}</option>
                                    {types.map((type) => (
                                        <option key={type.id} value={type.id}>
                                            {type.name}
                                        </option>
                                    ))}
                                </select>
                                <FieldError message={errors.case_type_id} />
                                <p className="mt-2 text-xs text-(--text-subtle)">
                                    {t('cases.case_number_format')}: <span className="font-mono text-(--text-muted)">{caseNumberPreview(selectedType?.prefix, year)}</span>
                                </p>
                            </div>
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <FieldLabel htmlFor="respondent_name">{t('cases.labels.respondent_name')}</FieldLabel>
                                <input id="respondent_name" className={cn(inputClass, 'mt-1')} value={form.data.respondent_name} onChange={(event) => form.setData('respondent_name', event.target.value)} maxLength={255} />
                                <FieldError message={errors.respondent_name} />
                            </div>
                            <div>
                                <FieldLabel htmlFor="respondent_address">{t('cases.labels.respondent_address')}</FieldLabel>
                                <input id="respondent_address" className={cn(inputClass, 'mt-1')} value={form.data.respondent_address} onChange={(event) => form.setData('respondent_address', event.target.value)} maxLength={500} />
                                <FieldError message={errors.respondent_address} />
                            </div>
                        </div>

                        <div className="space-y-2">
                            <FieldLabel required>{t('cases.labels.description')}</FieldLabel>
                            <RichTextEditor value={form.data.description} onChange={(html) => form.setData('description', html)} height={480} />
                            <FieldError message={errors.description} />
                        </div>
                        <div className="space-y-2">
                            <FieldLabel required>{t('cases.labels.relief_requested')}</FieldLabel>
                            <RichTextEditor value={form.data.relief_requested} onChange={(html) => form.setData('relief_requested', html)} height={340} />
                            <FieldError message={errors.relief_requested} />
                        </div>
                        <Certify checked={form.data.certify_appeal} onChange={(value) => form.setData('certify_appeal', value)} error={errors.certify_appeal}>
                            {t('cases.certify_appeal')}
                        </Certify>
                    </div>

                    <div className="space-y-3 rounded-xl border border-(--border) bg-(--surface-strong) p-5 shadow-xs">
                        <div className="flex items-center justify-between">
                            <h2 className="font-semibold text-(--text)">{t('cases.section.add_documents')}</h2>
                            <AddRowButton onClick={() => form.setData('documents', [...form.data.documents, { title: '', file: null }])}>{t('cases.buttons.add_row')}</AddRowButton>
                        </div>
                        <DocumentRows rows={form.data.documents} onChange={(rows) => form.setData('documents', rows)} errors={errors} required={false} titlePlaceholder={t('cases.placeholders.document_title')} />
                    </div>

                    <div className="space-y-3 rounded-xl border border-(--border) bg-(--surface-strong) p-5 shadow-xs">
                        <div className="flex items-center justify-between">
                            <h2 className="font-semibold text-(--text)">{t('cases.section.witnesses')}</h2>
                            <AddRowButton onClick={() => form.setData('witnesses', [...form.data.witnesses, { full_name: '', phone: '', email: '', address: '' }])}>{t('cases.buttons.add_witness')}</AddRowButton>
                        </div>
                        <WitnessRows
                            rows={form.data.witnesses}
                            onChange={(rows) => form.setData('witnesses', rows)}
                            errors={errors}
                            required={false}
                            withEmail
                            placeholders={{
                                full_name: t('cases.placeholders.full_name'),
                                phone: t('cases.placeholders.phone'),
                                email: t('cases.placeholders.email'),
                                address: t('cases.placeholders.address'),
                            }}
                        />
                        <FieldError message={errors.witnesses_duplicate_phone ?? errors.witnesses_duplicate_email} />
                    </div>

                    <Certify checked={form.data.certify_evidence} onChange={(value) => form.setData('certify_evidence', value)} error={errors.certify_evidence}>
                        {t('cases.certify_evidence')}
                    </Certify>

                    <div className="flex items-center gap-3">
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="inline-flex items-center rounded-lg bg-orange-500 px-4 py-2.5 font-semibold text-white hover:bg-orange-600 focus:ring-2 focus:ring-orange-400 focus:ring-offset-1 focus:outline-hidden disabled:opacity-60"
                        >
                            {t('cases.buttons.save_changes')}
                        </button>
                        <span className="text-xs text-(--text-subtle)">{t('cases.hints.edit_only_pending')}</span>
                    </div>
                    {form.progress && <progress value={form.progress.percentage} max={100} className="w-full" />}
                </form>

                <div className="space-y-6">
                    <section className="rounded-xl border border-(--border) bg-(--surface-strong) p-5 shadow-xs">
                        <div className="mb-3 flex items-center justify-between">
                            <h2 className="font-semibold text-(--text)">{t('cases.section.existing_documents')}</h2>
                            <span className="text-xs text-(--text-subtle)">
                                {documents.length} {t('cases.total_suffix')}
                            </span>
                        </div>
                        {documents.length === 0 ? (
                            <p className="text-sm text-(--text-subtle)">{t('cases.empty.no_documents')}</p>
                        ) : (
                            <ul className="divide-y divide-(--border)">
                                {documents.map((doc) => (
                                    <li key={doc.id} className="flex items-center justify-between gap-3 py-2">
                                        <span className="min-w-0 truncate text-(--text)">
                                            {doc.title}
                                            {!doc.downloadUrl && <span className="text-(--text-subtle)"> ({t('cases.inline')})</span>}
                                        </span>
                                        <span className="flex shrink-0 items-center gap-2">
                                            {doc.downloadUrl && (
                                                <a href={doc.downloadUrl} className="text-xs font-medium text-blue-700 hover:underline">
                                                    {t('cases.table.view')}
                                                </a>
                                            )}
                                            <button
                                                type="button"
                                                onClick={() => setRemoval({ kind: 'document', id: doc.id, label: doc.title })}
                                                className="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs text-red-600 hover:bg-red-50"
                                            >
                                                <TrashIcon className="h-3.5 w-3.5" />
                                                {t('cases.general.delete')}
                                            </button>
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </section>

                    <section className="rounded-xl border border-(--border) bg-(--surface-strong) p-5 shadow-xs">
                        <div className="mb-3 flex items-center justify-between">
                            <h2 className="font-semibold text-(--text)">{t('cases.section.existing_witnesses')}</h2>
                            <span className="text-xs text-(--text-subtle)">
                                {witnesses.length} {t('cases.total_suffix')}
                            </span>
                        </div>
                        {witnesses.length === 0 ? (
                            <p className="text-sm text-(--text-subtle)">{t('cases.empty.no_witnesses')}</p>
                        ) : (
                            <ul className="divide-y divide-(--border)">
                                {witnesses.map((witness) => (
                                    <li key={witness.id} className="flex items-start justify-between gap-3 py-2">
                                        <div className="min-w-0 text-sm">
                                            <div className="font-medium text-(--text)">{witness.fullName}</div>
                                            <div className="text-xs text-(--text-subtle)">{[witness.phone, witness.email, witness.address].filter(Boolean).join(' · ') || '—'}</div>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => setRemoval({ kind: 'witness', id: witness.id, label: witness.fullName })}
                                            className="inline-flex shrink-0 items-center gap-1 rounded-md px-2 py-1 text-xs text-red-600 hover:bg-red-50"
                                        >
                                            <TrashIcon className="h-3.5 w-3.5" />
                                            {t('cases.general.delete')}
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </section>
                </div>
            </div>

            <ConfirmDialog
                open={removal !== null}
                title={t('cases.general.delete')}
                message={removal ? `${removal.label} — ${t(removal.kind === 'document' ? 'cases.confirm.remove_document' : 'cases.confirm.remove_witness')}` : null}
                confirmLabel={t('cases.general.delete')}
                onCancel={() => setRemoval(null)}
                onConfirm={confirmRemoval}
            />
        </>
    );
}
