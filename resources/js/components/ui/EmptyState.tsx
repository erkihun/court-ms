import type { ReactNode } from 'react';
import { cn } from '@/lib/cn';

interface EmptyStateProps {
    title?: ReactNode;
    description?: ReactNode;
    icon?: ReactNode;
    children?: ReactNode;
    className?: string;
}

export function EmptyState({ title, description, icon, children, className }: EmptyStateProps) {
    return (
        <div className={cn('ui-empty', className)}>
            {icon && <div className="ui-empty-icon">{icon}</div>}
            {title && <h3 className="mt-4 text-sm font-semibold text-(--text)">{title}</h3>}
            {description && <p className="mt-2 text-sm text-(--text-subtle)">{description}</p>}
            {children && <div className="mt-4">{children}</div>}
        </div>
    );
}
