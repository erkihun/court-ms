import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import type { Option } from '@/components/admin/inspections/InspectionBadges';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { EthDateInput } from '@/components/ui/EthDateInput';
import { Field } from '@/components/ui/Field';
import { Input, Select, Textarea } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface FindingRecord {
    id: number;
    case_inspection_request_id: string;
    finding_date: string;
    title: string;
    details: string;
    severity: string;
    attachmentName: string | null;
    attachmentUrl: string | null;
}

interface Props {
    finding: FindingRecord | null;
    defaults: { case_inspection_request_id: string; finding_date: string };
    requests: Option[];
    severities: string[];
}

interface FormData {
    case_inspection_request_id: string;
    finding_date: string;
    severity: string;
    title: string;
    details: string;
    attachment_pdf: File | null;
}

/** Create and edit share one form; `finding` is null when creating. */
export default function InspectionFindingForm({ finding, defaults, requests, severities }: Props) {
    const editing = finding !== null;
    const title = editing ? t('case_inspections.findings.edit_title') : t('case_inspections.findings.create_title');
    const form = useForm<FormData>({
        case_inspection_request_id: finding?.case_inspection_request_id ?? defaults.case_inspection_request_id,
        finding_date: finding?.finding_date ?? defaults.finding_date,
        severity: finding?.severity ?? 'medium',
        title: finding?.title ?? '',
        details: finding?.details ?? '',
        attachment_pdf: null,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (finding) {
            form.transform((data) => ({ ...data, _method: 'patch' }));
            form.post(route('case-inspection-findings.update', finding.id), { forceFormData: true });
        } else {
            form.post(route('case-inspection-findings.store'), { forceFormData: true });
        }
    };

    return (
        <>
            <Head title={title} />
            <PageHeader title={title} className="mb-6" />

            <Card className="max-w-3xl">
                <form onSubmit={submit} className="space-y-5" noValidate>
                    <Field label={t('case_inspections.findings.form.request')} error={form.errors.case_inspection_request_id} required>
                        <Select
                            value={form.data.case_inspection_request_id}
                            onChange={(event) => form.setData('case_inspection_request_id', event.target.value)}
                            required
                        >
                            <option value="">{t('case_inspections.findings.form.select_request')}</option>
                            {requests.map((option) => (
                                <option key={option.id} value={String(option.id)}>
                                    {option.label}
                                </option>
                            ))}
                        </Select>
                    </Field>

                    <div className="grid gap-5 md:grid-cols-2">
                        <Field label={t('case_inspections.findings.form.finding_date')} error={form.errors.finding_date} required>
                            <EthDateInput value={form.data.finding_date} onChange={(value) => form.setData('finding_date', value)} />
                        </Field>
                        <Field label={t('case_inspections.findings.form.severity')} error={form.errors.severity} required>
                            <Select value={form.data.severity} onChange={(event) => form.setData('severity', event.target.value)} required>
                                {severities.map((severity) => (
                                    <option key={severity} value={severity}>
                                        {t(`case_inspections.severity.${severity}`)}
                                    </option>
                                ))}
                            </Select>
                        </Field>
                    </div>

                    <Field label={t('case_inspections.findings.form.title')} error={form.errors.title} required>
                        <Input value={form.data.title} onChange={(event) => form.setData('title', event.target.value)} maxLength={255} required />
                    </Field>

                    <Field label={t('case_inspections.findings.form.details')} error={form.errors.details} required>
                        <Textarea rows={7} value={form.data.details} onChange={(event) => form.setData('details', event.target.value)} required />
                    </Field>

                    <Field
                        label={t('case_inspections.findings.form.attachment_pdf')}
                        error={form.errors.attachment_pdf}
                        hint={
                            <>
                                {t('case_inspections.findings.form.attachment_hint')}
                                {finding?.attachmentUrl && (
                                    <>
                                        {' '}
                                        <a
                                            href={finding.attachmentUrl}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="text-blue-700 underline dark:text-blue-300"
                                        >
                                            {t('case_inspections.findings.form.current_attachment')}: {finding.attachmentName}
                                        </a>
                                    </>
                                )}
                            </>
                        }
                    >
                        <Input type="file" accept="application/pdf" onChange={(event) => form.setData('attachment_pdf', event.target.files?.[0] ?? null)} />
                    </Field>

                    <div className="flex gap-3 pt-2">
                        <LinkButton href={route('case-inspection-findings.index')} variant="secondary">
                            {t('case_inspections.common.cancel')}
                        </LinkButton>
                        <Button type="submit" loading={form.processing}>
                            {editing ? t('case_inspections.findings.update') : t('case_inspections.findings.save')}
                        </Button>
                    </div>
                </form>
            </Card>
        </>
    );
}
