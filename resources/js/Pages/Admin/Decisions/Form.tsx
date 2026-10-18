import { ArrowLeftIcon } from '@heroicons/react/24/outline';
import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card, CardTitle } from '@/components/ui/Card';
import { EthDateInput } from '@/components/ui/EthDateInput';
import { Field } from '@/components/ui/Field';
import { Input, Select } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { RichTextEditor } from '@/components/ui/RichTextEditor';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface CaseOption {
    id: number;
    label: string;
    caseNumber: string;
    applicant: string;
    respondent: string;
}

interface Option {
    id: number;
    name: string;
}

interface EditableDecision {
    id: number;
    name: string | null;
    caseId: number | null;
    caseFileNumber: string;
    decisionDate: string;
    /** Stored (already purified) HTML. */
    content: string;
    /** Panel by storage seat: [0] first judge, [1] presiding (session) judge, [2] third judge. */
    judgeIds: Array<number | null>;
}

interface Props {
    /** null when creating. */
    decision: EditableDecision | null;
    defaultCaseId: number | null;
    today: string;
    cases: CaseOption[];
    judges: Option[];
    currentUser: Option;
}

type JudgeSeat = { admin_user_id: string };

/**
 * The presiding judge (storage seat 1) is always the person writing the decision
 * and is shown first; the server rejects any other value for that seat.
 */
const DISPLAY_SEATS = [1, 0, 2] as const;

export default function DecisionForm({ decision, defaultCaseId, today, cases, judges, currentUser }: Props) {
    const editing = decision !== null;
    const initialCase = cases.find((option) => option.id === (decision?.caseId ?? defaultCaseId));

    const form = useForm<{
        case_id: string;
        case_file_number: string;
        decision_date: string;
        decision_content: string;
        judges: JudgeSeat[];
    }>({
        case_id: initialCase ? String(initialCase.id) : '',
        case_file_number: decision?.caseFileNumber ?? initialCase?.caseNumber ?? '',
        decision_date: decision?.decisionDate || today,
        decision_content: decision?.content ?? '',
        judges: [0, 1, 2].map((seat) => {
            const id = seat === 1 ? currentUser.id : (decision?.judgeIds[seat] ?? null);
            return { admin_user_id: id ? String(id) : '' };
        }),
    });

    const selectedCase = cases.find((option) => String(option.id) === form.data.case_id);
    const errors = form.errors as Record<string, string | undefined>;
    const panelError = errors.judges ?? errors['judges.0'] ?? errors['judges.1'] ?? errors['judges.2'];

    const chooseCase = (value: string) => {
        const option = cases.find((candidate) => String(candidate.id) === value);
        form.setData((data) => ({ ...data, case_id: value, case_file_number: option?.caseNumber ?? '' }));
    };

    const setSeat = (seat: number, value: string) =>
        form.setData(
            'judges',
            form.data.judges.map((current, index) => (index === seat ? { admin_user_id: value } : current)),
        );

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (editing) {
            form.patch(route('decisions.update', decision.id));
        } else {
            form.post(route('decisions.store'));
        }
    };

    const title = editing ? t('decisions.edit.title') : t('decisions.create.title');

    return (
        <>
            <Head title={title} />
            <PageHeader
                title={title}
                description={editing ? t('decisions.edit.subtitle', { name: decision.name ?? '' }) : t('decisions.create.subtitle')}
                className="mb-6"
                actions={
                    <LinkButton href={route('decisions.index')} variant="secondary">
                        <ArrowLeftIcon className="h-4 w-4" />
                        {t('decisions.create.back')}
                    </LinkButton>
                }
            />

            <form onSubmit={submit} className="space-y-6" noValidate>
                <Card>
                    <div className="grid gap-5 md:grid-cols-2">
                        <Field label={t('decisions.fields.case')} error={form.errors.case_id} required>
                            <Select value={form.data.case_id} onChange={(event) => chooseCase(event.target.value)} required>
                                <option value="">{t('decisions.fields.select_case')}</option>
                                {cases.map((option) => (
                                    <option key={option.id} value={option.id}>
                                        {option.label}
                                    </option>
                                ))}
                            </Select>
                        </Field>
                        <Field label={t('decisions.fields.decision_date')} error={form.errors.decision_date} required>
                            <EthDateInput value={form.data.decision_date} onChange={(value) => form.setData('decision_date', value)} align="end" />
                        </Field>
                        <Field label={t('decisions.fields.case_file_number')} error={form.errors.case_file_number}>
                            <Input
                                value={form.data.case_file_number}
                                onChange={(event) => form.setData('case_file_number', event.target.value)}
                                maxLength={255}
                                placeholder={t('decisions.fields.case_file_number_placeholder')}
                            />
                        </Field>
                        <div className="hidden md:block" />
                        <Field label={t('decisions.fields.applicant_label')}>
                            <Input value={selectedCase?.applicant ?? ''} readOnly className="bg-(--surface-soft)" />
                        </Field>
                        <Field label={t('decisions.fields.respondent_label')}>
                            <Input value={selectedCase?.respondent ?? ''} readOnly className="bg-(--surface-soft)" />
                        </Field>
                    </div>
                </Card>

                <Card
                    header={
                        <CardTitle
                            title={t('decisions.judges.heading')}
                            actions={<span className="text-xs text-(--text-subtle)">{t('decisions.judges.order_hint')}</span>}
                        />
                    }
                >
                    {panelError && <p className="mb-4 text-sm font-medium text-rose-600 dark:text-rose-400">{panelError}</p>}
                    <div className="grid gap-5 md:grid-cols-3">
                        {DISPLAY_SEATS.map((seat, position) => (
                            <Field key={seat} label={t('decisions.judges.judge', { number: position + 1 })} error={errors[`judges.${seat}.admin_user_id`]}>
                                {seat === 1 ? (
                                    <Input value={currentUser.name} readOnly className="bg-(--surface-soft)" />
                                ) : (
                                    <Select value={form.data.judges[seat]?.admin_user_id ?? ''} onChange={(event) => setSeat(seat, event.target.value)}>
                                        <option value="">{t('decisions.judges.select_judge')}</option>
                                        {judges.map((judge) => (
                                            <option key={judge.id} value={judge.id}>
                                                {judge.name}
                                            </option>
                                        ))}
                                    </Select>
                                )}
                            </Field>
                        ))}
                    </div>
                </Card>

                <Card header={<CardTitle title={t('decisions.fields.final_decision')} />}>
                    <Field error={form.errors.decision_content}>
                        <RichTextEditor value={form.data.decision_content} onChange={(html) => form.setData('decision_content', html)} height={480} />
                    </Field>
                </Card>

                <div className="flex justify-end gap-3">
                    <LinkButton href={route('decisions.index')} variant="secondary">
                        {editing ? t('decisions.edit.cancel') : t('decisions.create.cancel')}
                    </LinkButton>
                    <Button type="submit" variant={editing ? 'primary' : 'success'} loading={form.processing}>
                        {editing ? t('decisions.edit.save') : t('decisions.create.save')}
                    </Button>
                </div>
            </form>
        </>
    );
}
