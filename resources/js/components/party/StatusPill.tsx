import { BoltIcon, CheckIcon, ClockIcon } from '@heroicons/react/24/outline';
import type { CSSProperties } from 'react';
import { t } from '@/lib/i18n';

/** Theme colour behind a case status. */
export function caseStatusColor(status: string): string {
    if (status === 'pending') return 'var(--warning)';
    if (status === 'active') return 'var(--success)';
    return 'var(--text-subtle)';
}

export function tintStyle(color: string, withBorder = true): CSSProperties {
    return {
        background: `color-mix(in srgb, ${color} 12%, transparent)`,
        color,
        ...(withBorder ? { borderColor: `color-mix(in srgb, ${color} 28%, transparent)` } : {}),
    };
}

/** Case status chip, e.g. "Pending" with a clock. */
export function CaseStatusPill({ status, withIcon = true }: { status: string; withIcon?: boolean }) {
    const Icon = status === 'pending' ? ClockIcon : status === 'active' ? BoltIcon : CheckIcon;

    return (
        <span className="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium" style={tintStyle(caseStatusColor(status))}>
            {withIcon && <Icon className="h-3 w-3" strokeWidth={2} />}
            {t(`cases.status.${status}`)}
        </span>
    );
}

export type ReviewStatus = 'awaiting_review' | 'accepted' | 'returned' | 'rejected';

const reviewColors: Record<ReviewStatus, string> = {
    accepted: 'var(--success)',
    returned: 'var(--warning)',
    rejected: 'var(--danger)',
    awaiting_review: 'var(--warning)',
};

/** Court review state of a submitted response or reply. */
export function ReviewStatusPill({ status }: { status: ReviewStatus }) {
    return (
        <span className="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold" style={tintStyle(reviewColors[status])}>
            {t(`cases.review_status.${status}`)}
        </span>
    );
}
