import { Badge, type BadgeTone } from '@/components/ui/Badge';
import { t } from '@/lib/i18n';

export const CASE_STATUSES = ['pending', 'active', 'adjourned', 'dismissed', 'closed'] as const;
export type CaseStatus = (typeof CASE_STATUSES)[number];

export const REVIEW_STATUSES = ['awaiting_review', 'accepted', 'returned', 'rejected'] as const;
export type CaseReviewStatus = (typeof REVIEW_STATUSES)[number];

function caseStatusTone(status: string): BadgeTone {
    if (status === 'pending') return 'warning';
    if (status === 'active') return 'info';
    if (status === 'closed' || status === 'dismissed') return 'success';
    return 'neutral';
}

function reviewStatusTone(status: string): BadgeTone {
    if (status === 'accepted') return 'success';
    if (status === 'rejected') return 'danger';
    if (status === 'awaiting_review' || status === 'returned') return 'warning';
    return 'neutral';
}

export function CaseStatusBadge({ status }: { status: string }) {
    return <Badge tone={caseStatusTone(status)}>{t(`cases.status.${status}`)}</Badge>;
}

export function ReviewStatusBadge({ status }: { status: string }) {
    return <Badge tone={reviewStatusTone(status)}>{t(`cases.review_status.${status}`)}</Badge>;
}
