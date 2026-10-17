import type { HTMLAttributes } from 'react';
import { cn } from '@/lib/cn';

export type BadgeTone = 'success' | 'warning' | 'danger' | 'info' | 'neutral';

const tones: Record<BadgeTone, string> = {
    success: 'ui-badge ui-badge-emerald',
    warning: 'ui-badge ui-badge-amber',
    danger: 'ui-badge ui-badge-rose',
    info: 'ui-badge ui-badge-blue',
    neutral: 'ui-badge ui-badge-slate',
};

export function Badge({ tone = 'info', className, ...rest }: HTMLAttributes<HTMLSpanElement> & { tone?: BadgeTone }) {
    return <span className={cn(tones[tone], className)} {...rest} />;
}
