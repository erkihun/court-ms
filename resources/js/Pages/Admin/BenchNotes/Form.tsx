import { ArrowLeftIcon } from '@heroicons/react/24/outline';
import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card, CardTitle } from '@/components/ui/Card';
import { DescriptionList } from '@/components/ui/DescriptionList';
import { EthDateInput } from '@/components/ui/EthDateInput';
import { Field } from '@/components/ui/Field';
import { Input, Select } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { RichTextEditor } from '@/components/ui/RichTextEditor';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface EditableNote {
    id: number;
    caseId: number;
    title: string;
    noteDate: string;
    /** Stored (already purified) HTML for the editor. */
    note: string;
    judgeIds: Array<number | null>;
    firstJudgeName: string | null;
    author: string | null;
    created: string;
    updated: string | null;
}

interface Option {
    id: number;
    name: string;
}

interface Props {
    /** null when creating. */
    note: EditableNote | null;
    defaultCaseId: number | null;
    cases: Array<{ id: number; label: string }>;
    judges: Option[];
    currentUser: Option;
}

type JudgeSeat = { admin_user_id: string };

/** Create and edit share one form. Seat 1 is the presiding judge and is not chosen here. */
export default function BenchNoteForm({ note, defaultCaseId, cases, judges, currentUser }: Props) {
    const editing = note !== null;
    const seat = (index: number): string => {
        const id = note?.judgeIds[index] ?? (index === 0 ? currentUser.id : null);
        return id ? String(id) : '';
    };

    const form = useForm<{ case_id: string; title: string; note_date: string; note: string; judges: JudgeSeat[] }>({
        case_id: String(note?.caseId ?? defaultCaseId ?? ''),
        title: note?.title ?? '',
        note_date: note?.noteDate ?? '',
        note: note?.note ?? '',
        judges: [0, 1, 2].map((index) => ({ admin_user_id: seat(index) })),
    });

    const presidingName = note?.firstJudgeName ?? judges.find((judge) => String(judge.id) === form.data.judges[0]?.admin_user_id)?.name ?? currentUser.name;
    const backHref = route('bench-notes.index', note?.caseId || defaultCaseId ? { case_id: note?.caseId ?? defaultCaseId } : {});
    const errors = form.errors as Record<string, string | undefined>;

    const setSeat = (index: number, value: string) =>
        form.setData(
            'judges',
            form.data.judges.map((current, position) => (position === index ? { admin_user_id: value } : current)),
        );

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (editing) {
            form.patch(route('bench-notes.update', note.id));
        } else {
            form.post(route('bench-notes.store'));
        }
    };

    return (
        <>
            <Head title={t('bench.title')} />
            <PageHeader
                title={editing ? t('bench.headings.edit') : t('bench.headings.create')}
                description={editing ? t('bench.descriptions.edit') : t('bench.descriptions.create')}
                className="mb-6"
                actions={
                    <LinkButton href={backHref} variant="secondary">
                        <ArrowLeftIcon className="h-4 w-4" />
                        {editing ? t('bench.buttons.back_list') : t('bench.buttons.back')}
                    </LinkButton>
                }
            />

            <form onSubmit={submit} className="space-y-6" noValidate>
                {editing && (
                    <Card>
                        <DescriptionList
                            columns={3}
                            items={[
                                { label: t('bench.labels.author'), value: note.author ?? t('bench.meta.unknown') },
                                { label: t('bench.labels.created'), value: note.created },
                                { label: t('bench.labels.last_updated'), value: note.updated },
                            ]}
                        />
                    </Card>
                )}

                <Card header={<CardTitle title={t('bench.sections.basic_info')} />}>
                    <div className="grid gap-5 md:grid-cols-2">
                        <Field label={t('bench.labels.case')} error={form.errors.case_id} hint={t('bench.helpers.select_case')} required>
                            <Select value={form.data.case_id} onChange={(event) => form.setData('case_id', event.target.value)} required>
                                <option value="">{t('bench.placeholders.select_case')}</option>
                                {cases.map((option) => (
                                    <option key={option.id} value={option.id}>
                                        {option.label}
                                    </option>
                                ))}
                            </Select>
                        </Field>
                        <Field label={t('bench.labels.note_date')} error={form.errors.note_date} hint={t('bench.helpers.note_date')}>
                            <EthDateInput value={form.data.note_date} onChange={(value) => form.setData('note_date', value)} align="end" />
                        </Field>
                        <Field label={t('bench.labels.title')} error={form.errors.title} hint={t('bench.helpers.title')} required className="md:col-span-2">
                            <Input
                                value={form.data.title}
                                onChange={(event) => form.setData('title', event.target.value)}
                                maxLength={255}
                                placeholder={t('bench.placeholders.title')}
                                required
                            />
                        </Field>
                    </div>
                </Card>

                <Card header={<CardTitle title={t('bench.sections.judges')} />}>
                    <div className="grid gap-5 md:grid-cols-3">
                        <Field label={t('bench.labels.judge', { number: 1 })} error={errors['judges.0.admin_user_id']}>
                            <Input value={presidingName} readOnly className="bg-(--surface-soft)" />
                        </Field>
                        {[1, 2].map((index) => (
                            <Field key={index} label={t('bench.labels.judge', { number: index + 1 })} error={errors[`judges.${index}.admin_user_id`]}>
                                <Select value={form.data.judges[index]?.admin_user_id ?? ''} onChange={(event) => setSeat(index, event.target.value)}>
                                    <option value="">{t('bench.placeholders.select_judge')}</option>
                                    {judges.map((judge) => (
                                        <option key={judge.id} value={judge.id}>
                                            {judge.name}
                                        </option>
                                    ))}
                                </Select>
                            </Field>
                        ))}
                    </div>
                </Card>

                <Card header={<CardTitle title={t('bench.sections.note_content')} description={t('bench.helpers.note_editor')} />}>
                    <Field error={form.errors.note}>
                        <RichTextEditor value={form.data.note} onChange={(html) => form.setData('note', html)} height={600} />
                    </Field>
                </Card>

                <div className="flex justify-end gap-3">
                    <LinkButton href={backHref} variant="secondary">
                        {t('bench.buttons.cancel')}
                    </LinkButton>
                    <Button type="submit" loading={form.processing}>
                        {editing ? t('bench.buttons.update') : t('bench.buttons.save')}
                    </Button>
                </div>
            </form>
        </>
    );
}
