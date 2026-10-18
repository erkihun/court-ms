import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { pointTone } from '@/components/admin/performance/scoring';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card, CardTitle } from '@/components/ui/Card';
import { EthDateInput } from '@/components/ui/EthDateInput';
import { Field } from '@/components/ui/Field';
import { Select, Textarea } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Criterion {
    id: number;
    name: string;
    description: string | null;
    category: string | null;
    weight: number;
    score: number;
    comment: string;
}

interface EvaluationRecord {
    id: number;
    memberName: string | null;
    period: string;
    evaluated_user_id: string;
    period_type: string;
    period_start: string;
    period_end: string;
    notes: string;
}

interface Props {
    evaluation: EvaluationRecord | null;
    users: Array<{ id: number; name: string }>;
    periodTypes: string[];
    criteria: Criterion[];
}

interface ScoreInput {
    criterion_id: number;
    score: number;
    comment: string;
}

interface FormData {
    evaluated_user_id: string;
    period_type: string;
    period_start: string;
    period_end: string;
    notes: string;
    scores: ScoreInput[];
    action: 'draft' | 'submitted';
}

/** Create and edit share one form; `evaluation` is null when creating. */
export default function PerformanceEvaluationForm({ evaluation, users, periodTypes, criteria }: Props) {
    const editing = evaluation !== null;
    const form = useForm<FormData>({
        evaluated_user_id: evaluation?.evaluated_user_id ?? '',
        period_type: evaluation?.period_type ?? 'monthly',
        period_start: evaluation?.period_start ?? '',
        period_end: evaluation?.period_end ?? '',
        notes: evaluation?.notes ?? '',
        scores: criteria.map((criterion) => ({ criterion_id: criterion.id, score: criterion.score, comment: criterion.comment })),
        action: 'draft',
    });
    const errors = form.errors as Record<string, string | undefined>;

    const setScore = (index: number, patch: Partial<ScoreInput>) =>
        form.setData(
            'scores',
            form.data.scores.map((score, position) => (position === index ? { ...score, ...patch } : score)),
        );

    const submit = (action: FormData['action']) => (event: FormEvent) => {
        event.preventDefault();
        form.transform((data) => ({ ...data, action }));
        if (evaluation) {
            form.patch(route('performance-evaluations.update', evaluation.id));
        } else {
            form.post(route('performance-evaluations.store'));
        }
    };

    const title = editing ? t('performance.edit_evaluation') : t('performance.new_evaluation');
    const backHref = evaluation ? route('performance-evaluations.show', evaluation.id) : route('performance-evaluations.index');

    return (
        <>
            <Head title={editing ? t('performance.edit_title') : t('performance.new_title')} />
            <PageHeader
                title={title}
                description={evaluation ? `${evaluation.memberName ?? ''} · ${evaluation.period}` : t('performance.subtitle')}
                actions={
                    <LinkButton href={backHref} variant="ghost">
                        ← {editing ? t('performance.actions.back') : t('performance.actions.back_to_list')}
                    </LinkButton>
                }
                className="mb-6"
            />

            <form onSubmit={submit('submitted')} className="mx-auto max-w-3xl space-y-6" noValidate>
                <Card header={<CardTitle title={t('performance.details')} />}>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field label={t('performance.fields.staff_member')} error={form.errors.evaluated_user_id} required className="sm:col-span-2">
                            <Select value={form.data.evaluated_user_id} onChange={(event) => form.setData('evaluated_user_id', event.target.value)} required>
                                <option value="">{t('performance.placeholders.select_member')}</option>
                                {users.map((user) => (
                                    <option key={user.id} value={String(user.id)}>
                                        {user.name}
                                    </option>
                                ))}
                            </Select>
                        </Field>
                        <Field label={t('performance.fields.period_type')} error={form.errors.period_type} required>
                            <Select value={form.data.period_type} onChange={(event) => form.setData('period_type', event.target.value)} required>
                                {periodTypes.map((period) => (
                                    <option key={period} value={period}>
                                        {t(`performance.periods.${period}`)}
                                    </option>
                                ))}
                            </Select>
                        </Field>
                        <div className="hidden sm:block" />
                        <Field label={t('performance.fields.period_start')} error={form.errors.period_start} required>
                            <EthDateInput value={form.data.period_start} onChange={(value) => form.setData('period_start', value)} />
                        </Field>
                        <Field label={t('performance.fields.period_end')} error={form.errors.period_end} required>
                            <EthDateInput value={form.data.period_end} onChange={(value) => form.setData('period_end', value)} />
                        </Field>
                        <Field label={t('performance.fields.general_notes')} error={form.errors.notes} className="sm:col-span-2">
                            <Textarea
                                rows={3}
                                maxLength={2000}
                                value={form.data.notes}
                                onChange={(event) => form.setData('notes', event.target.value)}
                                placeholder={t('performance.placeholders.general_notes')}
                            />
                        </Field>
                    </div>
                </Card>

                <Card header={<CardTitle title={t('performance.criteria_scores')} description={t('performance.score_hint')} />}>
                    {form.errors.scores && <p className="mb-3 text-xs font-medium text-rose-600">{form.errors.scores}</p>}
                    <div className="space-y-4">
                        {criteria.map((criterion, index) => {
                            const value = form.data.scores[index]?.score ?? 5;
                            const tone = pointTone(value);
                            const sliderId = `score-${criterion.id}`;
                            const error = errors[`scores.${index}.score`] ?? errors[`scores.${index}.comment`] ?? errors[`scores.${index}.criterion_id`];

                            return (
                                <div key={criterion.id} className="space-y-3 rounded-lg border border-(--border) bg-(--surface-soft) p-4">
                                    <div className="flex items-start justify-between gap-3">
                                        <div>
                                            <div className="flex flex-wrap items-center gap-2">
                                                {criterion.category && (
                                                    <span className="rounded-full border border-(--border) bg-(--surface-strong) px-2 py-0.5 text-[10px] font-semibold text-(--text-subtle) uppercase">
                                                        {criterion.category}
                                                    </span>
                                                )}
                                                <span className="text-xs text-(--text-subtle)">
                                                    {t('performance.fields.weight')}: {criterion.weight}%
                                                </span>
                                            </div>
                                            <label htmlFor={sliderId} className="mt-1 block text-sm font-semibold text-(--text)">
                                                {criterion.name}
                                            </label>
                                            {criterion.description && <p className="mt-0.5 text-xs text-(--text-subtle)">{criterion.description}</p>}
                                        </div>
                                        <div className="shrink-0 text-center" aria-hidden="true">
                                            <div className={cn('w-12 text-3xl font-extrabold tabular-nums', tone.text)}>{value}</div>
                                            <div className="text-[10px] text-(--text-subtle)">/10</div>
                                        </div>
                                    </div>
                                    <div>
                                        <input
                                            id={sliderId}
                                            type="range"
                                            min={0}
                                            max={10}
                                            step={1}
                                            value={value}
                                            onChange={(event) => setScore(index, { score: Number(event.target.value) })}
                                            className="w-full cursor-pointer accent-blue-600"
                                            aria-valuetext={`${value}/10`}
                                        />
                                        <div className="mt-1 flex justify-between text-[10px] text-(--text-subtle)" aria-hidden="true">
                                            {Array.from({ length: 11 }, (_, n) => (
                                                <span key={n}>{n}</span>
                                            ))}
                                        </div>
                                    </div>
                                    <Textarea
                                        rows={1}
                                        maxLength={500}
                                        aria-label={`${criterion.name} — ${t('performance.placeholders.criterion_comment')}`}
                                        placeholder={t('performance.placeholders.criterion_comment')}
                                        value={form.data.scores[index]?.comment ?? ''}
                                        onChange={(event) => setScore(index, { comment: event.target.value })}
                                        className="resize-none text-xs"
                                    />
                                    {error && <p className="text-xs font-medium text-rose-600">{error}</p>}
                                </div>
                            );
                        })}
                    </div>
                </Card>

                <div className="ui-card flex flex-wrap items-center justify-between gap-3 p-4">
                    <LinkButton href={backHref} variant="secondary">
                        {t('performance.actions.cancel')}
                    </LinkButton>
                    <div className="flex gap-2">
                        <Button variant="secondary" onClick={submit('draft')} disabled={form.processing}>
                            {t('performance.actions.save_draft')}
                        </Button>
                        <Button type="submit" loading={form.processing}>
                            {t('performance.actions.submit_review')}
                        </Button>
                    </div>
                </div>
            </form>
        </>
    );
}
