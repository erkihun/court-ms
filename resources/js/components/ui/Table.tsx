import type { ReactNode } from 'react';
import { cn } from '@/lib/cn';

interface TableProps {
    head: ReactNode;
    children: ReactNode;
    footer?: ReactNode;
    sticky?: boolean;
    className?: string;
}

/** ui-table with an optional sticky header and a footer strip (e.g. pagination). */
export function Table({ head, children, footer, sticky = true, className }: TableProps) {
    return (
        <div className={cn('ui-table-wrap', className)}>
            <div className="ui-table-scroll">
                <table className="ui-table">
                    <thead className={sticky ? 'sticky top-0 z-10' : undefined}>{head}</thead>
                    <tbody>{children}</tbody>
                </table>
            </div>
            {footer && <div className="border-t border-(--border) bg-(--surface-soft) px-4 py-3">{footer}</div>}
        </div>
    );
}
