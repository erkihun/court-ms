import { ArrowLeftIcon, CheckBadgeIcon, LockClosedIcon } from '@heroicons/react/24/outline';
import { Head, useForm } from '@inertiajs/react';
import { type FormEvent, useState } from 'react';
import { ApprovalBadge, DecisionStatusBadge, ReviewOutcomeBadge, type ReviewOutcome } from '@/components/admin/decisions/DecisionBadges';
import { ReviewFields } from '@/components/admin/decisions/ReviewFields';
import { Alert } from '@/components/ui/Alert';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card, CardTitle } from '@/components/ui/Card';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { ConfirmDialog } from '@/components/ui/ConfirmDialog';
import { DescriptionList } from '@/components/ui/DescriptionList';
import { Select } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { RichText } from '@/components/ui/RichText';
import { useCan } from '@/hooks/useCan';
import { useShared } from '@/hooks/useShared';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Decision {
    id: number;
    name: string | null;
    caseId: number | null;
    caseNumber: string | null;
    caseFileNumber: string | null;
    decisionDate: string;
    applicant: string | null;
    respondent: string | null;
    status: string;
    isApproved: boolean;
    approvedBy: string | null;
    approvedAt: string | null;
    isPresidingJudge: boolean;
    /** Sanitised by the server (HTMLPurifier). */
    contentHtml: string;
    /** Three seats, the decision's author first. */
    panel: Array<{ name: string | null; vote: string | null }>;
}

interface Review {
    id: number;
    reviewer: string | null;
    outcome: string;
    note: string | null;
    when: string;
    isOwner: boolean;
}

interface Props {
    decision: Decision;
    reviews: Review[];
    templates: Array<{ id: number; title: string }>;
}

export default function DecisionShow({ decision, reviews, templates }: Props) {
    const can = useCan();
    const { errors } = useShared();
    const published = decision.status === 'published';
    const title = decision.name ?? t('decisions.show.title');

    return (
        <>
            <Head title={title} />
            <PageHeader
                title={title}
                description={`${decision.caseNumber ?? '—'} · ${decision.decisionDate}`}
                className="mb-6"
                actions={
                    <>
                        <LinkButton href={route('decisions.index')} variant="secondary">
                            <ArrowLeftIcon className="h-4 w-4" />
                            {t('decisions.create.back')}
                        </LinkButton>
                        {can('decision.update') && decision.isPresidingJudge && !published && (
                            <LinkButton href={route('decisions.edit', decision.id)}>{t('decisions.index.edit')}</LinkButton>
                        )}
                    </>
                }
            />

            {(errors.approve || errors.status) && (
                <Alert tone="error" className="mb-6">
                    {errors.approve ?? errors.status}
                </Alert>
            )}

            <div className="space-y-6">
                <Card>
                    <DescriptionList
                        columns={3}
                        items={[
                            { label: t('decisions.fields.case_file_number'), value: decision.caseFileNumber },
                            { label: t('decisions.fields.case'), value: decision.caseNumber },
                            { label: t('decisions.fields.decision_date'), value: decision.decisionDate },
                            { label: t('decisions.fields.applicant_label'), value: decision.applicant },
                            { label: t('decisions.fields.respondent_label'), value: decision.respondent },
                            {
                                label: t('app.Status'),
                                value: (
                                    <span className="inline-flex flex-wrap gap-2">
                                        <DecisionStatusBadge status={decision.status} />
                                        <ApprovalBadge approved={decision.isApproved} />
                                    </span>
                                ),
                            },
                        ]}
                    />
                </Card>

                <Card
                    header={
                        <CardTitle
                            title={t('decisions.judges.judicial_panel')}
                            actions={<span className="text-xs text-(--text-subtle)">{t('decisions.judges.panel')}</span>}
                        />
                    }
                >
                    <div className="grid gap-4 md:grid-cols-3">
                        {decision.panel.map((judge, index) => (
                            <div key={index} className="rounded-xl border border-(--border) bg-(--surface-soft) p-4">
                                <div className="text-xs font-medium tracking-wide text-(--text-subtle) uppercase">
                                    {t('decisions.judges.judge', { number: index + 1 })}
                                </div>
                                <div className="mt-1 font-semibold text-(--text)">{judge.name ?? '—'}</div>
                                <div className="mt-1 text-xs text-(--text-muted)">
                                    {t('decisions.judges.vote', { vote: judge.vote ? t(`decisions.reviews.${judge.vote}`) : '—' })}
                                </div>
                            </div>
                        ))}
                    </div>
                </Card>

                <Card header={<CardTitle title={t('decision_templates.output.heading')} description={t('decision_templates.output.choose_template')} />}>
                    <div className="space-y-4">
                        {decision.isApproved && (
                            <p className="inline-flex items-center gap-2 text-sm font-medium text-emerald-700 dark:text-emerald-300">
                                <CheckBadgeIcon className="h-5 w-5" />
                                {t('decisions.approved_badge', { user: decision.approvedBy ?? '—', date: decision.approvedAt ?? '—' })}
                            </p>
                        )}

                        {published ? (
                            <div className="flex flex-wrap items-center gap-3">
                                <p className="inline-flex items-center gap-2 text-sm text-(--text-muted)">
                                    <LockClosedIcon className="h-4 w-4" />
                                    {t('decisions.published_locked')}
                                </p>
                                {can('decision.approve') && !decision.isApproved && (
                                    <ConfirmButton
                                        url={route('decisions.approve', decision.id)}
                                        method="patch"
                                        variant="success"
                                        title={t('decisions.approve')}
                                        message={t('decisions.approve_confirm')}
                                    >
                                        {t('decisions.approve')}
                                    </ConfirmButton>
                                )}
                            </div>
                        ) : (
                            can('decision.update') && <StatusForm decisionId={decision.id} status={decision.status} />
                        )}

                        {/* A plain GET form: the PDF opens in a new tab. */}
                        <form method="get" action={route('decisions.output', decision.id)} target="_blank" className="flex flex-wrap items-center gap-2">
                            <label htmlFor="decision-template" className="sr-only">
                                {t('decision_templates.output.choose_template')}
                            </label>
                            <Select id="decision-template" name="template_id" defaultValue="" className="w-auto min-w-56">
                                <option value="">{t('decision_templates.output.default_layout')}</option>
                                {templates.map((template) => (
                                    <option key={template.id} value={template.id}>
                                        {template.title}
                                    </option>
                                ))}
                            </Select>
                            <Button type="submit" name="mode" value="stream" variant="secondary">
                                {t('decision_templates.output.generate')}
                            </Button>
                            <Button type="submit" name="mode" value="download">
                                {t('decision_templates.output.download')}
                            </Button>
                        </form>
                    </div>
                </Card>

                <div className="grid gap-6 xl:grid-cols-3">
                    <Card
                        className="xl:col-span-2"
                        header={<CardTitle title={t('decisions.fields.decision_content')} actions={<DecisionStatusBadge status={decision.status} />} />}
                    >
                        <div className="overflow-x-auto">
                            <RichText html={decision.contentHtml} className="text-justify leading-relaxed" />
                        </div>
                    </Card>

                    <Card header={<CardTitle title={t('decisions.reviews.heading')} description={t('decisions.reviews.legend')} />}>
                        <Reviews decisionId={decision.id} reviews={reviews} locked={published} canReview={can('decision.update')} />
                    </Card>
                </div>
            </div>
        </>
    );
}

function StatusForm({ decisionId, status }: { decisionId: number; status: string }) {
    const form = useForm({ status });
    const [confirming, setConfirming] = useState(false);
    const publishing = form.data.status === 'published';

    const send = () =>
        form.patch(route('decisions.status', decisionId), {
            preserveScroll: true,
            onFinish: () => setConfirming(false),
        });

    return (
        <div className="flex flex-wrap items-center gap-2">
            <label htmlFor="decision-status" className="text-sm font-medium text-(--text-muted)">
                {t('decisions.fields.status')}:
            </label>
            <Select id="decision-status" value={form.data.status} onChange={(event) => form.setData('status', event.target.value)} className="w-auto">
                <option value="draft">{t('decisions.status.draft')}</option>
                <option value="published">{t('decisions.status.published')}</option>
            </Select>
            <Button variant="secondary" onClick={() => setConfirming(true)} disabled={form.data.status === status}>
                {t('decisions.status_change')}
            </Button>
            <ConfirmDialog
                open={confirming}
                title={t('decisions.status_change')}
                message={publishing ? t('decisions.publish_confirm') : t('decisions.status_change_confirm')}
                confirmLabel={publishing ? t('decisions.publish') : t('decisions.status_change')}
                variant={publishing ? 'success' : 'primary'}
                processing={form.processing}
                onCancel={() => setConfirming(false)}
                onConfirm={send}
            />
        </div>
    );
}

function Reviews({ decisionId, reviews, locked, canReview }: { decisionId: number; reviews: Review[]; locked: boolean; canReview: boolean }) {
    const form = useForm<{ outcome: ReviewOutcome; review_note: string }>({ outcome: 'approve', review_note: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('decisions.reviews.store', decisionId), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    return (
        <div className="space-y-4">
            {reviews.length === 0 ? (
                <p className="text-sm text-(--text-subtle)">{t('decisions.reviews.none')}</p>
            ) : (
                <ul className="space-y-3">
                    {reviews.map((review) => (
                        <li key={review.id} className="rounded-xl border border-(--border) p-3">
                            <div className="flex items-start justify-between gap-3">
                                <div>
                                    <div className="text-[11px] tracking-wide text-(--text-subtle) uppercase">{t('decisions.reviews.reviewer')}</div>
                                    <div className="text-sm font-medium text-(--text)">{review.reviewer ?? '—'}</div>
                                </div>
                                <ReviewOutcomeBadge outcome={review.outcome} />
                            </div>
                            <p className="mt-2 text-sm whitespace-pre-line text-(--text-muted)">{review.note ?? '—'}</p>
                            <div className="mt-2 flex items-center justify-between gap-2">
                                <span className="text-xs text-(--text-subtle)">{review.when}</span>
                                {review.isOwner && !locked && (
                                    <span className="flex gap-2">
                                        <LinkButton href={route('decisions.reviews.edit', [decisionId, review.id])} variant="secondary" size="sm">
                                            {t('decisions.reviews.update')}
                                        </LinkButton>
                                        <ConfirmButton
                                            url={route('decisions.reviews.destroy', [decisionId, review.id])}
                                            title={t('decisions.reviews.delete_confirm')}
                                        >
                                            {t('decisions.reviews.delete')}
                                        </ConfirmButton>
                                    </span>
                                )}
                            </div>
                        </li>
                    ))}
                </ul>
            )}

            {canReview && !locked && (
                <form onSubmit={submit} className="space-y-3 border-t border-(--border) pt-4" noValidate>
                    <h3 className="text-sm font-semibold text-(--text)">{t('decisions.reviews.add')}</h3>
                    <ReviewFields
                        outcome={form.data.outcome}
                        note={form.data.review_note}
                        onOutcome={(outcome) => form.setData('outcome', outcome)}
                        onNote={(note) => form.setData('review_note', note)}
                        errors={form.errors}
                    />
                    <Button type="submit" loading={form.processing}>
                        {t('decisions.reviews.submit')}
                    </Button>
                </form>
            )}
        </div>
    );
}
