import { ArrowLeftIcon } from '@heroicons/react/24/outline';
import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import type { ReviewOutcome } from '@/components/admin/decisions/DecisionBadges';
import { ReviewFields } from '@/components/admin/decisions/ReviewFields';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { Field } from '@/components/ui/Field';
import { Input } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    decision: { id: number; caseNumber: string | null };
    review: { id: number; outcome: string; note: string; reviewer: string | null };
}

export default function DecisionReviewEdit({ decision, review }: Props) {
    const form = useForm<{ outcome: ReviewOutcome; review_note: string }>({
        outcome: review.outcome as ReviewOutcome,
        review_note: review.note,
    });
    const backHref = route('decisions.show', decision.id);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.patch(route('decisions.reviews.update', [decision.id, review.id]));
    };

    return (
        <>
            <Head title={t('decisions.reviews.edit_title')} />
            <PageHeader
                title={t('decisions.reviews.edit_title')}
                description={t('decisions.reviews.edit_subtitle', { case: decision.caseNumber ?? '—', reviewer: review.reviewer ?? '—' })}
                className="mb-6"
                actions={
                    <LinkButton href={backHref} variant="secondary">
                        <ArrowLeftIcon className="h-4 w-4" />
                        {t('decisions.reviews.back')}
                    </LinkButton>
                }
            />

            <Card className="max-w-3xl">
                <form onSubmit={submit} className="space-y-5" noValidate>
                    <Field label={t('decisions.reviews.case_number')}>
                        <Input value={decision.caseNumber ?? ''} readOnly className="bg-(--surface-soft)" />
                    </Field>
                    <ReviewFields
                        outcome={form.data.outcome}
                        note={form.data.review_note}
                        onOutcome={(outcome) => form.setData('outcome', outcome)}
                        onNote={(note) => form.setData('review_note', note)}
                        errors={form.errors}
                    />
                    <div className="flex gap-3 pt-2">
                        <LinkButton href={backHref} variant="secondary">
                            {t('decisions.reviews.cancel')}
                        </LinkButton>
                        <Button type="submit" loading={form.processing}>
                            {t('decisions.reviews.update_review')}
                        </Button>
                    </div>
                </form>
            </Card>
        </>
    );
}
