import { Badge, type BadgeTone } from '@/components/ui/Badge';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';

/** Text colour for an overall percentage score. */
export function percentTone(score: number): string {
    if (score >= 85) return 'text-emerald-600 dark:text-emerald-400';
    if (score >= 70) return 'text-blue-600 dark:text-blue-400';
    if (score >= 50) return 'text-amber-600 dark:text-amber-400';
    return 'text-red-600 dark:text-red-400';
}

export function percentBar(score: number): string {
    if (score >= 85) return 'bg-emerald-500';
    if (score >= 70) return 'bg-blue-500';
    if (score >= 50) return 'bg-amber-400';
    return 'bg-red-500';
}

/** Colours for a 0–10 criterion score. */
export function pointTone(score: number): { text: string; bar: string } {
    if (score >= 8) return { text: 'text-emerald-600 dark:text-emerald-400', bar: 'bg-emerald-500' };
    if (score >= 6) return { text: 'text-blue-600 dark:text-blue-400', bar: 'bg-blue-500' };
    if (score >= 4) return { text: 'text-amber-600 dark:text-amber-400', bar: 'bg-amber-400' };
    return { text: 'text-red-600 dark:text-red-400', bar: 'bg-red-500' };
}

const statusTones: Record<string, BadgeTone> = { draft: 'warning', submitted: 'info', reviewed: 'success' };

export function EvaluationStatusBadge({ status }: { status: string }) {
    return <Badge tone={statusTones[status] ?? 'neutral'}>{t(`performance.statuses.${status}`)}</Badge>;
}

export function MemberAvatar({ name, url, size = 'sm' }: { name: string | null; url: string | null; size?: 'sm' | 'lg' }) {
    const classes = size === 'lg' ? 'h-16 w-16 text-2xl' : 'h-8 w-8 text-xs';

    return url ? (
        <img src={url} alt="" className={cn('rounded-full object-cover', classes)} />
    ) : (
        <span
            aria-hidden="true"
            className={cn(
                'flex shrink-0 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-700 dark:bg-blue-500/20 dark:text-blue-200',
                classes,
            )}
        >
            {(name ?? '?').charAt(0).toUpperCase()}
        </span>
    );
}

export interface EvaluationRow {
    id: number;
    member: string | null;
    memberAvatarUrl: string | null;
    period: string;
    periodEnd: string;
    periodType: string;
    score: number;
    status: string;
    evaluator: string | null;
    created: string;
}
