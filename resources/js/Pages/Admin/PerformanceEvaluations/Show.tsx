import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { EvaluationStatusBadge, type EvaluationRow, MemberAvatar, percentBar, percentTone, pointTone } from '@/components/admin/performance/scoring';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card, CardTitle } from '@/components/ui/Card';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { Field } from '@/components/ui/Field';
import { Textarea } from '@/components/ui/Input';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Score {
    id: number;
    criterion: string | null;
    category: string | null;
    weight: number | null;
    score: number;
    comment: string | null;
}

interface Props {
    evaluation: EvaluationRow & {
        periodStart: string;
        scoreLabel: string;
        reviewer: string | null;
        reviewedAt: string | null;
        notes: string | null;
        reviewerNotes: string | null;
        scores: Score[];
    };
    can: { update: boolean; review: boolean; delete: boolean };
}

export default function PerformanceEvaluationShow({ evaluation, can }: Props) {
    const na = t('performance.not_available');
    const review = useForm({ reviewer_notes: '' });

    const submitReview = (event: FormEvent) => {
        event.preventDefault();
        review.post(route('performance-evaluations.review', evaluation.id), { preserveScroll: true });
    };

    return (
        <>
            <Head title={t('performance.detail_title')} />

            <div className="mx-auto max-w-3xl space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <LinkButton href={route('performance-evaluations.index')} variant="ghost">
                        ← {t('performance.actions.back_to_list')}
                    </LinkButton>
                    <div className="flex gap-2">
                        {can.update && (
                            <LinkButton href={route('performance-evaluations.edit', evaluation.id)} variant="secondary">
                                {t('performance.actions.edit')}
                            </LinkButton>
                        )}
                        {can.delete && (
                            <ConfirmButton url={route('performance-evaluations.destroy', evaluation.id)} title={t('performance.confirm.delete')} size="md">
                                {t('performance.actions.delete')}
                            </ConfirmButton>
                        )}
                    </div>
                </div>

                <Card>
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-4">
                            <MemberAvatar name={evaluation.member} url={evaluation.memberAvatarUrl} size="lg" />
                            <div>
                                <h1 className="text-xl font-bold text-(--text)">{evaluation.member ?? na}</h1>
                                <p className="text-sm text-(--text-muted)">
                                    {evaluation.periodStart} – {evaluation.periodEnd} · {t(`performance.periods.${evaluation.periodType}`)}
                                </p>
                                <div className="mt-1">
                                    <EvaluationStatusBadge status={evaluation.status} />
                                </div>
                            </div>
                        </div>
                        <div className="flex flex-col items-center">
                            <div className={cn('text-5xl font-black tabular-nums', percentTone(evaluation.score))}>
                                {evaluation.score.toFixed(1)}
                                <span className="text-2xl text-(--text-subtle)">%</span>
                            </div>
                            <div className="mt-1 text-xs font-semibold tracking-wide text-(--text-subtle) uppercase">{evaluation.scoreLabel}</div>
                        </div>
                    </div>

                    <div
                        className="mt-5 h-3 w-full overflow-hidden rounded-full bg-(--surface-soft)"
                        role="progressbar"
                        aria-valuemin={0}
                        aria-valuemax={100}
                        aria-valuenow={evaluation.score}
                        aria-label={t('performance.fields.score')}
                    >
                        <div className={cn('h-full rounded-full', percentBar(evaluation.score))} style={{ width: `${Math.min(100, evaluation.score)}%` }} />
                    </div>

                    <dl className="mt-4 grid grid-cols-2 gap-3 text-xs sm:grid-cols-4">
                        <div>
                            <dt className="font-medium text-(--text)">{t('performance.fields.evaluator')}</dt>
                            <dd className="text-(--text-muted)">{evaluation.evaluator ?? na}</dd>
                        </div>
                        <div>
                            <dt className="font-medium text-(--text)">{t('performance.fields.created')}</dt>
                            <dd className="text-(--text-muted)">{evaluation.created}</dd>
                        </div>
                        {evaluation.reviewer && (
                            <>
                                <div>
                                    <dt className="font-medium text-(--text)">{t('performance.fields.reviewed_by')}</dt>
                                    <dd className="text-(--text-muted)">{evaluation.reviewer}</dd>
                                </div>
                                <div>
                                    <dt className="font-medium text-(--text)">{t('performance.fields.reviewed_at')}</dt>
                                    <dd className="text-(--text-muted)">{evaluation.reviewedAt ?? na}</dd>
                                </div>
                            </>
                        )}
                    </dl>

                    {evaluation.notes && (
                        <div className="mt-4 rounded-lg border border-(--border) bg-(--surface-soft) p-3">
                            <p className="mb-1 text-xs font-semibold tracking-wide text-(--text-subtle) uppercase">{t('performance.fields.notes')}</p>
                            <p className="text-sm whitespace-pre-line text-(--text)">{evaluation.notes}</p>
                        </div>
                    )}
                </Card>

                <Card header={<CardTitle title={t('performance.criteria_breakdown')} />}>
                    <ul className="space-y-4">
                        {evaluation.scores.map((score) => {
                            const tone = pointTone(score.score);

                            return (
                                <li key={score.id}>
                                    <div className="mb-1 flex items-center justify-between gap-3">
                                        <div>
                                            <span className="text-sm font-semibold text-(--text)">{score.criterion ?? na}</span>
                                            <span className="ms-2 text-[10px] text-(--text-subtle) uppercase">
                                                {score.category ?? na} · {score.weight ?? 0}%
                                            </span>
                                        </div>
                                        <span className={cn('text-lg font-extrabold tabular-nums', tone.text)}>
                                            {score.score}
                                            <span className="text-xs text-(--text-subtle)">/10</span>
                                        </span>
                                    </div>
                                    <div className="h-2.5 w-full overflow-hidden rounded-full bg-(--surface-soft)">
                                        <div className={cn('h-full rounded-full', tone.bar)} style={{ width: `${score.score * 10}%` }} />
                                    </div>
                                    {score.comment && <p className="mt-1 text-xs text-(--text-muted) italic">{score.comment}</p>}
                                </li>
                            );
                        })}
                    </ul>
                </Card>

                {evaluation.reviewerNotes && (
                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                        <p className="mb-1 text-xs font-semibold tracking-wide text-emerald-700 uppercase dark:text-emerald-300">
                            {t('performance.fields.reviewer_notes')}
                        </p>
                        <p className="text-sm whitespace-pre-line text-emerald-900 dark:text-emerald-100">{evaluation.reviewerNotes}</p>
                    </div>
                )}

                {can.review && (
                    <Card header={<CardTitle title={t('performance.review.title')} />}>
                        <form onSubmit={submitReview} className="space-y-3">
                            <Field label={t('performance.fields.reviewer_notes')} error={review.errors.reviewer_notes}>
                                <Textarea
                                    rows={3}
                                    maxLength={2000}
                                    value={review.data.reviewer_notes}
                                    onChange={(event) => review.setData('reviewer_notes', event.target.value)}
                                    placeholder={t('performance.placeholders.reviewer_notes')}
                                />
                            </Field>
                            <div className="flex justify-end">
                                <Button type="submit" variant="success" loading={review.processing}>
                                    {t('performance.actions.approve_reviewed')}
                                </Button>
                            </div>
                        </form>
                    </Card>
                )}
            </div>
        </>
    );
}
