import { Field } from '@/components/ui/Field';
import { Select, Textarea } from '@/components/ui/Input';
import { t } from '@/lib/i18n';
import type { ReviewOutcome } from './DecisionBadges';

interface ReviewFieldsProps {
    outcome: string;
    note: string;
    onOutcome: (outcome: ReviewOutcome) => void;
    onNote: (note: string) => void;
    errors: { outcome?: string; review_note?: string };
}

/** Outcome + note. A note is required when the reviewer disagrees or asks for an improvement. */
export function ReviewFields({ outcome, note, onOutcome, onNote, errors }: ReviewFieldsProps) {
    const needsNote = outcome === 'reject' || outcome === 'improve';

    return (
        <>
            <Field label={t('decisions.reviews.outcome')} error={errors.outcome} required>
                <Select value={outcome} onChange={(event) => onOutcome(event.target.value as ReviewOutcome)}>
                    <option value="approve">{t('decisions.reviews.approve')}</option>
                    <option value="reject">{t('decisions.reviews.reject')}</option>
                    <option value="improve">{t('decisions.reviews.improve')}</option>
                </Select>
            </Field>
            {needsNote && (
                <Field label={t('decisions.reviews.review_note')} error={errors.review_note} required>
                    <Textarea
                        value={note}
                        onChange={(event) => onNote(event.target.value)}
                        rows={4}
                        maxLength={2000}
                        required
                        placeholder={outcome === 'improve' ? t('decisions.reviews.note_placeholder_improve') : t('decisions.reviews.note_placeholder_reject')}
                    />
                </Field>
            )}
        </>
    );
}
