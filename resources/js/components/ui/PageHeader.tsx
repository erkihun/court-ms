import type { ReactNode } from 'react';
import { cn } from '@/lib/cn';

interface PageHeaderProps {
    title: ReactNode;
    description?: ReactNode;
    actions?: ReactNode;
    accent?: boolean;
    className?: string;
    children?: ReactNode;
}

export function PageHeader({ title, description, actions, accent = false, className, children }: PageHeaderProps) {
    return (
        <div className={cn('ui-page-header', accent && 'ui-page-header-accent', className)}>
            <div className="flex flex-wrap items-start justify-between gap-4">
                <div className="min-w-0">
                    <h1 className="text-xl font-semibold text-(--text)">{title}</h1>
                    {description && <p className="mt-1 text-sm text-(--text-muted)">{description}</p>}
                </div>
                {actions && <div className="flex flex-wrap items-center gap-2">{actions}</div>}
            </div>
            {children}
        </div>
    );
}
