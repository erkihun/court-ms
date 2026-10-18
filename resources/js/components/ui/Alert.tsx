import type { HTMLAttributes } from 'react';
import { cn } from '@/lib/cn';

export type AlertTone = 'success' | 'error' | 'warning' | 'info';

const tones: Record<AlertTone, string> = {
    success: 'ui-alert ui-alert-success',
    error: 'ui-alert ui-alert-error',
    warning: 'ui-alert border border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200',
    info: 'ui-alert ui-alert-info',
};

export function Alert({ tone = 'info', className, ...rest }: HTMLAttributes<HTMLDivElement> & { tone?: AlertTone }) {
    return <div role={tone === 'error' ? 'alert' : 'status'} className={cn(tones[tone], className)} {...rest} />;
}
