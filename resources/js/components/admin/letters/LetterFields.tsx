import type { ReactNode } from 'react';
import { Field } from '@/components/ui/Field';
import { Checkbox, Input } from '@/components/ui/Input';
import { RichTextEditor } from '@/components/ui/RichTextEditor';
import { t } from '@/lib/i18n';
import type { LetterFormData } from '@/types/admin/letters';

interface LetterFieldsProps {
    data: LetterFormData;
    errors: Partial<Record<keyof LetterFormData, string>>;
    onChange: <K extends keyof LetterFormData>(key: K, value: LetterFormData[K]) => void;
    /** Editing keeps the letter's case: its reference number was derived from it. */
    caseNumberLocked?: boolean;
    /** Shown between the subject and the body (reference preview, template details…). */
    children?: ReactNode;
    bodyHint: string;
    disabled?: boolean;
}

/** Recipient, delivery, subject and body fields shared by composing and editing a letter. */
export function LetterFields({ data, errors, onChange, caseNumberLocked = false, children, bodyHint, disabled = false }: LetterFieldsProps) {
    return (
        <fieldset disabled={disabled} className="space-y-5 disabled:opacity-60">
            <div className="grid gap-4 md:grid-cols-2">
                <Field label={t('letters.form.recipient_name')} error={errors.recipient_name}>
                    <Input value={data.recipient_name} onChange={(event) => onChange('recipient_name', event.target.value)} maxLength={255} />
                </Field>
                <Field label={t('letters.form.recipient_title')} error={errors.recipient_title}>
                    <Input value={data.recipient_title} onChange={(event) => onChange('recipient_title', event.target.value)} maxLength={255} />
                </Field>
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <Field label={t('letters.form.recipient_company')} error={errors.recipient_company} hint={t('letters.form.recipient_company_hint')}>
                    <Input value={data.recipient_company} onChange={(event) => onChange('recipient_company', event.target.value)} maxLength={255} />
                </Field>
                <Field label={t('letters.form.case_number')} error={errors.case_number} hint={t('letters.form.case_number_help')}>
                    <Input
                        value={data.case_number}
                        onChange={(event) => onChange('case_number', event.target.value)}
                        readOnly={caseNumberLocked}
                        maxLength={60}
                        className="bg-(--surface-soft)"
                        placeholder={t('letters.form.case_number_placeholder')}
                    />
                </Field>
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <Field label={t('letters.form.cc')} error={errors.cc} hint={t('letters.form.cc_hint')}>
                    <Input
                        value={data.cc}
                        onChange={(event) => onChange('cc', event.target.value)}
                        maxLength={255}
                        placeholder={t('letters.form.cc_placeholder')}
                    />
                </Field>
                <fieldset className="space-y-1.5">
                    <legend className="text-sm font-medium text-(--text)">{t('letters.form.delivery_label')}</legend>
                    <div className="flex flex-wrap items-center gap-4 pt-1">
                        <Checkbox
                            label={t('letters.form.deliver_applicant')}
                            checked={data.send_to_applicant}
                            onChange={(event) => onChange('send_to_applicant', event.target.checked)}
                        />
                        <Checkbox
                            label={t('letters.form.deliver_respondent')}
                            checked={data.send_to_respondent}
                            onChange={(event) => onChange('send_to_respondent', event.target.checked)}
                        />
                    </div>
                    {errors.send_to_applicant ? (
                        <p className="text-xs font-medium text-rose-600 dark:text-rose-400">{errors.send_to_applicant}</p>
                    ) : (
                        <p className="text-xs text-(--text-subtle)">{t('letters.form.delivery_hint')}</p>
                    )}
                </fieldset>
            </div>

            <Field label={t('letters.form.subject')} error={errors.subject}>
                <Input value={data.subject} onChange={(event) => onChange('subject', event.target.value)} maxLength={255} />
            </Field>

            {children}

            <Field label={t('letters.form.body')} error={errors.body} hint={bodyHint} required>
                <RichTextEditor value={data.body} onChange={(html) => onChange('body', html)} height={700} disabled={disabled} />
            </Field>
        </fieldset>
    );
}
