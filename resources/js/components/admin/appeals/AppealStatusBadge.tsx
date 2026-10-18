import { Badge, type BadgeTone } from '@/components/ui/Badge';
import { t } from '@/lib/i18n';

const tones: Record<string, BadgeTone> = {
    draft: 'neutral',
    submitted: 'warning',
    under_review: 'info',
    approved: 'success',
    rejected: 'danger',
    closed: 'neutral',
};

export function AppealStatusBadge({ status }: { status: string }) {
    return <Badge tone={tones[status] ?? 'neutral'}>{t(`appeals.statuses.${status}`)}</Badge>;
}
