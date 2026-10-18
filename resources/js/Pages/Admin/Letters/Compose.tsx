import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { LetterFields } from '@/components/admin/letters/LetterFields';
import { Alert } from '@/components/ui/Alert';
import { Button } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { Field } from '@/components/ui/Field';
import { Input, Select } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { ComposeTemplate, LetterFormData } from '@/types/admin/letters';

interface Props {
    templates: ComposeTemplate[];
    /** From ?template_id=… */
    selectedTemplateId: number | null;
    /** Prefilled when starting from a case (?case_id=…). */
    caseNumber: string | null;
    nextReference: string | null;
}

type ComposeForm = LetterFormData & { template_id: number | null };

export default function LetterCompose({ templates, selectedTemplateId, caseNumber, nextReference }: Props) {
    const initialTemplate = templates.find((template) => template.id === selectedTemplateId) ?? null;
    const form = useForm<ComposeForm>({
        template_id: initialTemplate?.id ?? null,
        recipient_name: '',
        recipient_title: '',
        recipient_company: '',
        case_number: caseNumber ?? '',
        cc: '',
        send_to_applicant: true,
        send_to_respondent: true,
        subject: '',
        body: initialTemplate?.body ?? '',
    });
    const template = templates.find((item) => item.id === form.data.template_id) ?? null;
    const hasErrors = Object.keys(form.errors).length > 0;

    /** Picking a template loads its body, as the old "Load" button did; the other fields are kept. */
    const chooseTemplate = (id: number | null) => {
        const next = templates.find((item) => item.id === id) ?? null;
        form.setData((data) => ({ ...data, template_id: next?.id ?? null, body: next?.body ?? '' }));
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('letters.store'));
    };

    return (
        <>
            <Head title={t('letters.titles.compose')} />
            <PageHeader title={t('letters.titles.compose')} description={t('letters.description.compose')} className="mb-6" />

            <div className="max-w-5xl space-y-6">
                <Card>
                    <Field label={t('letters.form.select_template')} error={form.errors.template_id}>
                        <Select
                            value={form.data.template_id ?? ''}
                            onChange={(event) => chooseTemplate(event.target.value === '' ? null : Number(event.target.value))}
                        >
                            <option value="">{t('letters.form.select_placeholder')}</option>
                            {templates.map((item) => (
                                <option key={item.id} value={item.id}>
                                    {item.title}
                                </option>
                            ))}
                        </Select>
                    </Field>
                </Card>

                <Card>
                    <form onSubmit={submit} className="space-y-5" noValidate>
                        {!template && <Alert tone="warning">{t('letters.form.template_notice')}</Alert>}
                        {hasErrors && <Alert tone="error">{t('letters.form.validation_notice')}</Alert>}

                        <LetterFields
                            data={form.data}
                            errors={form.errors}
                            onChange={(key, value) => form.setData((data) => ({ ...data, [key]: value }))}
                            bodyHint={t('letters.form.body_hint')}
                            disabled={!template}
                        >
                            {nextReference && caseNumber && form.data.case_number === caseNumber && (
                                <Field label={t('letters.form.reference_number_auto')} hint={t('letters.form.reference_number_auto_help')}>
                                    <Input value={nextReference} readOnly className="bg-(--surface-soft)" />
                                </Field>
                            )}

                            {template && template.placeholders.length > 0 && (
                                <div className="rounded-lg border border-dashed border-blue-300 bg-blue-50 px-4 py-3 text-xs text-blue-800 dark:border-blue-500/40 dark:bg-blue-500/10 dark:text-blue-200">
                                    <p className="mb-1 font-semibold">{t('letters.form.placeholders_title')}</p>
                                    <p>{t('letters.form.placeholders_help')}</p>
                                    <p className="mt-1 font-mono">{template.placeholders.join(', ')}</p>
                                </div>
                            )}

                            {template && (
                                <div className="rounded-lg border border-(--border) bg-(--surface-soft) px-4 py-3 text-sm text-(--text-muted)">
                                    <p className="text-xs tracking-wide text-(--text-subtle) uppercase">{t('letters.form.selected_template')}</p>
                                    <p className="font-semibold text-(--text)">{template.title}</p>
                                    <p className="text-xs">{template.category ?? t('letters.form.category_fallback')}</p>
                                    {template.excerpt && <p className="mt-1 text-xs text-(--text-subtle)">{template.excerpt}</p>}
                                </div>
                            )}
                        </LetterFields>

                        <div className="flex justify-end">
                            <Button type="submit" variant="success" loading={form.processing} disabled={!template}>
                                {t('letters.actions.save_letter')}
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </>
    );
}
