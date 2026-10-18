import type { HTMLAttributes, ReactNode } from 'react';
import { cn } from '@/lib/cn';

const paddings = { none: '', sm: 'p-4', md: 'p-5 sm:p-6', lg: 'p-6 sm:p-7' } as const;

interface CardProps extends HTMLAttributes<HTMLDivElement> {
    header?: ReactNode;
    padding?: keyof typeof paddings;
}

export function Card({ header, padding = 'md', className, children, ...rest }: CardProps) {
    return (
        <div className={cn('ui-card', className)} {...rest}>
            {header !== undefined && <div className="ui-card-header">{header}</div>}
            <div className={paddings[padding]}>{children}</div>
        </div>
    );
}

interface CardTitleProps {
    title: ReactNode;
    description?: ReactNode;
    actions?: ReactNode;
    icon?: ReactNode;
}

/** Standard card header row: icon, title and description on the left, actions on the right. */
export function CardTitle({ title, description, actions, icon }: CardTitleProps) {
    return (
        <div className="flex flex-wrap items-center justify-between gap-3">
            <div className="flex min-w-0 items-center gap-3">
                {icon}
                <div className="min-w-0">
                    <h2 className="ui-section-title truncate">{title}</h2>
                    {description && <p className="ui-section-copy">{description}</p>}
                </div>
            </div>
            {actions && <div className="flex flex-wrap items-center gap-2">{actions}</div>}
        </div>
    );
}
