import { Badge, type BadgeTone } from '@/components/ui/Badge';
import { t } from '@/lib/i18n';

const statusTones: Record<string, BadgeTone> = {
    pending: 'warning',
    in_progress: 'info',
    completed: 'success',
    cancelled: 'neutral',
};

const severityTones: Record<string, BadgeTone> = {
    low: 'neutral',
    medium: 'info',
    high: 'warning',
    critical: 'danger',
};

export function InspectionStatusBadge({ status }: { status: string }) {
    return <Badge tone={statusTones[status] ?? 'neutral'}>{t(`case_inspections.status.${status}`)}</Badge>;
}

export function SeverityBadge({ severity }: { severity: string }) {
    return <Badge tone={severityTones[severity] ?? 'neutral'}>{t(`case_inspections.severity.${severity}`)}</Badge>;
}

export interface Option {
    id: number;
    label: string;
}
