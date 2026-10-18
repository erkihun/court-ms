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

interface RequestRecord {
    id: number;
    court_case_id: string;
    request_date: string;
    subject: string;
    request_note: string;
    status: string;
    assigned_inspector_user_id: string;
}

interface Props {
    record: RequestRecord | null;
    defaults: { court_case_id: string; request_date: string; assigned_inspector_user_id: string };
    cases: Option[];
    inspectors: Array<{ id: number; name: string }>;
    canAssign: boolean;
    statuses: string[];
}

/** Create and edit share one form; `record` is null when creating. */
export default function InspectionRequestForm({ record, defaults, cases, inspectors, canAssign, statuses }: Props) {
    const editing = record !== null;
    const title = editing ? t('case_inspections.requests.edit_title') : t('case_inspections.requests.create_title');
    const form = useForm({
        court_case_id: record?.court_case_id ?? defaults.court_case_id,
        request_date: record?.request_date ?? defaults.request_date,
        status: record?.status ?? 'pending',
        subject: record?.subject ?? '',
        assigned_inspector_user_id: record?.assigned_inspector_user_id ?? defaults.assigned_inspector_user_id,
        request_note: record?.request_note ?? '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (record) {
            form.patch(route('case-inspection-requests.update', record.id));
        } else {
            form.post(route('case-inspection-requests.store'));
        }
    };

    return (
        <>
            <Head title={title} />
            <PageHeader title={title} className="mb-6" />

            <Card className="max-w-3xl">
                <form onSubmit={submit} className="space-y-5" noValidate>
                    <Field label={t('case_inspections.requests.form.case')} error={form.errors.court_case_id} required>
                        <Select value={form.data.court_case_id} onChange={(event) => form.setData('court_case_id', event.target.value)} required>
                            <option value="">{t('case_inspections.requests.form.select_case')}</option>
                            {cases.map((option) => (
                                <option key={option.id} value={String(option.id)}>
                                    {option.label}
                                </option>
                            ))}
                        </Select>
                    </Field>

                    <div className="grid gap-5 md:grid-cols-2">
                        <Field label={t('case_inspections.requests.form.request_date')} error={form.errors.request_date} required>
                            <EthDateInput value={form.data.request_date} onChange={(value) => form.setData('request_date', value)} />
                        </Field>
                        <Field label={t('case_inspections.requests.form.status')} error={form.errors.status} required>
                            <Select value={form.data.status} onChange={(event) => form.setData('status', event.target.value)} required>
                                {statuses.map((status) => (
                                    <option key={status} value={status}>
                                        {t(`case_inspections.status.${status}`)}
                                    </option>
                                ))}
                            </Select>
                        </Field>
                    </div>

                    <Field label={t('case_inspections.requests.form.subject')} error={form.errors.subject} required>
                        <Input value={form.data.subject} onChange={(event) => form.setData('subject', event.target.value)} maxLength={255} required />
                    </Field>

                    {canAssign && (
                        <Field label={t('case_inspections.requests.form.assigned_inspector')} error={form.errors.assigned_inspector_user_id}>
                            <Select
                                value={form.data.assigned_inspector_user_id}
                                onChange={(event) => form.setData('assigned_inspector_user_id', event.target.value)}
                            >
                                <option value="">{t('case_inspections.requests.form.unassigned')}</option>
                                {inspectors.map((inspector) => (
                                    <option key={inspector.id} value={String(inspector.id)}>
                                        {inspector.name}
                                    </option>
                                ))}
                            </Select>
                        </Field>
                    )}

                    <Field label={t('case_inspections.requests.form.request_note')} error={form.errors.request_note}>
                        <Textarea rows={6} value={form.data.request_note} onChange={(event) => form.setData('request_note', event.target.value)} />
                    </Field>

                    <div className="flex gap-3 pt-2">
                        <LinkButton href={route('case-inspection-requests.index')} variant="secondary">
                            {t('case_inspections.common.cancel')}
                        </LinkButton>
                        <Button type="submit" loading={form.processing}>
                            {editing ? t('case_inspections.requests.update') : t('case_inspections.requests.save')}
                        </Button>
                    </div>
                </form>
            </Card>
        </>
    );
}
