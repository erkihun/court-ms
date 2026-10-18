import { Badge, type BadgeTone } from '@/components/ui/Badge';
import { t } from '@/lib/i18n';

export function DecisionStatusBadge({ status }: { status: string | null }) {
    if (!status) return <span className="text-(--text-subtle)">—</span>;

    return <Badge tone={status === 'published' ? 'success' : 'neutral'}>{t(`decisions.status.${status}`)}</Badge>;
}

export function ApprovalBadge({ approved }: { approved: boolean }) {
    return <Badge tone={approved ? 'success' : 'warning'}>{t(`decisions.approval_status.${approved ? 'approved' : 'pending'}`)}</Badge>;
}

export type ReviewOutcome = 'approve' | 'reject' | 'improve';

const outcomeTones: Record<string, BadgeTone> = { approve: 'success', reject: 'danger', improve: 'warning' };

export function ReviewOutcomeBadge({ outcome }: { outcome: string }) {
    return <Badge tone={outcomeTones[outcome] ?? 'neutral'}>{t(`decisions.reviews.${outcome}`)}</Badge>;
}
