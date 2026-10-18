import { DocumentIcon } from '@heroicons/react/24/outline';
import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { AppealStatusBadge } from '@/components/admin/appeals/AppealStatusBadge';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card, CardTitle } from '@/components/ui/Card';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { DescriptionList } from '@/components/ui/DescriptionList';
import { EmptyState } from '@/components/ui/EmptyState';
import { Field } from '@/components/ui/Field';
import { Input, Select, Textarea } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { useCan } from '@/hooks/useCan';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Appeal {
    id: number;
    appealNumber: string;
    caseNumber: string;
    caseTitle: string;
    title: string;
    grounds: string | null;
    status: string;
    submitted: string | null;
    decidedBy: string | null;
    decided: string | null;
    decisionNotes: string | null;
    editable: boolean;
    decidable: boolean;
}

interface AppealDocument {
    id: number;
    label: string;
    uploaded: string;
    downloadUrl: string;
}

interface Props {
    appeal: Appeal;
    documents: AppealDocument[];
}

export default function AppealShow({ appeal, documents }: Props) {
    const can = useCan();

    return (
        <>
            <Head title={appeal.appealNumber} />
            <PageHeader
                title={appeal.title}
                description={`${appeal.appealNumber} · ${appeal.caseNumber} — ${appeal.caseTitle}`}
                className="mb-6"
                actions={
                    can('appeals.edit') &&
                    appeal.status === 'draft' && (
                        <>
                            <LinkButton href={route('appeals.edit', appeal.id)} variant="secondary">
                                {t('appeals.actions.edit')}
                            </LinkButton>
                            <ConfirmButton
                                url={route('appeals.submit', appeal.id)}
                                method="post"
                                variant="primary"
                                size="md"
                                title={t('appeals.confirm.submit_title')}
                                message={t('appeals.confirm.submit_message')}
                            >
                                {t('appeals.actions.submit')}
                            </ConfirmButton>
                        </>
                    )
                }
            />

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="space-y-6 lg:col-span-2">
                    <Card>
                        <DescriptionList
                            items={[
                                { label: t('appeals.fields.status'), value: <AppealStatusBadge status={appeal.status} /> },
                                { label: t('appeals.fields.case'), value: `${appeal.caseNumber} — ${appeal.caseTitle}` },
                                { label: t('appeals.fields.submitted_at'), value: appeal.submitted },
                                { label: t('appeals.fields.decided_by'), value: appeal.decidedBy },
                                { label: t('appeals.fields.decided_at'), value: appeal.decided },
                                {
                                    label: t('appeals.fields.grounds'),
                                    value: appeal.grounds ? <p className="leading-7 whitespace-pre-line">{appeal.grounds}</p> : null,
                                    wide: true,
                                },
                                ...(appeal.decisionNotes
                                    ? [
                                          {
                                              label: t('appeals.fields.decision_notes'),
                                              value: <p className="whitespace-pre-line">{appeal.decisionNotes}</p>,
                                              wide: true,
                                          },
                                      ]
                                    : []),
                            ]}
                        />
                    </Card>

                    {can('appeals.decide') && appeal.decidable && <DecisionForm appealId={appeal.id} />}
                </div>

                <Card header={<CardTitle title={t('appeals.documents.heading')} />}>
                    {can('appeals.edit') && <UploadForm appealId={appeal.id} />}

                    {documents.length === 0 ? (
                        <EmptyState icon={<DocumentIcon className="h-6 w-6" />} title={t('appeals.documents.empty')} className="mt-4" />
                    ) : (
                        <ul className="mt-4 divide-y divide-(--border)">
                            {documents.map((doc) => (
                                <li key={doc.id} className="flex items-center justify-between gap-3 py-3">
                                    <div className="min-w-0 text-sm">
                                        <div className="truncate font-medium text-(--text)">{doc.label}</div>
                                        <div className="text-xs text-(--text-subtle)">{doc.uploaded}</div>
                                    </div>
                                    <div className="flex shrink-0 items-center gap-2">
                                        <LinkButton href={doc.downloadUrl} external target="_blank" variant="secondary" size="sm">
                                            {t('appeals.actions.view')}
                                        </LinkButton>
                                        {can('appeals.edit') && (
                                            <ConfirmButton
                                                url={route('appeals.docs.delete', [appeal.id, doc.id])}
                                                title={t('appeals.confirm.delete_document')}
                                                message={doc.label}
                                            >
                                                {t('appeals.actions.delete')}
                                            </ConfirmButton>
                                        )}
                                    </div>
                                </li>
                            ))}
                        </ul>
                    )}
                </Card>
            </div>
        </>
    );
}

function UploadForm({ appealId }: { appealId: number }) {
    const form = useForm<{ label: string; file: File | null }>({ label: '', file: null });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const element = event.currentTarget;
        form.post(route('appeals.docs.upload', appealId), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                element.reset();
            },
        });
    };

    return (
        <form onSubmit={submit} className="space-y-3" noValidate>
            <Field label={t('appeals.fields.label')} error={form.errors.label}>
                <Input value={form.data.label} onChange={(event) => form.setData('label', event.target.value)} maxLength={255} />
            </Field>
            <Field label={t('appeals.fields.file')} error={form.errors.file} hint={t('appeals.documents.hint')} required>
                <Input
                    type="file"
                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp"
                    onChange={(event) => form.setData('file', event.target.files?.[0] ?? null)}
                    required
                />
            </Field>
            <Button type="submit" loading={form.processing} disabled={!form.data.file} className="w-full">
                {t('appeals.actions.upload')}
            </Button>
        </form>
    );
}

function DecisionForm({ appealId }: { appealId: number }) {
    const form = useForm({ decision: 'approved', decision_notes: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('appeals.decide', appealId), { preserveScroll: true });
    };

    return (
        <Card header={<CardTitle title={t('appeals.decision.heading')} />}>
            <form onSubmit={submit} className="space-y-4" noValidate>
                <Field label={t('appeals.decision.recorded')} error={form.errors.decision} required>
                    <Select value={form.data.decision} onChange={(event) => form.setData('decision', event.target.value)}>
                        <option value="approved">{t('appeals.decision.approve')}</option>
                        <option value="rejected">{t('appeals.decision.reject')}</option>
                        <option value="closed">{t('appeals.decision.close')}</option>
                    </Select>
                </Field>
                <Field label={t('appeals.fields.decision_notes')} error={form.errors.decision_notes}>
                    <Textarea
                        value={form.data.decision_notes}
                        onChange={(event) => form.setData('decision_notes', event.target.value)}
                        rows={3}
                        maxLength={5000}
                        placeholder={t('appeals.decision.notes_placeholder')}
                    />
                </Field>
                <Button type="submit" loading={form.processing}>
                    {t('appeals.actions.save_decision')}
                </Button>
            </form>
        </Card>
    );
}
