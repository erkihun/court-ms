import type { ReactNode } from 'react';
import { cn } from '@/lib/cn';

export interface DescriptionItem {
    label: ReactNode;
    value: ReactNode;
    /** Span the full row on wide layouts (long text, rich text). */
    wide?: boolean;
}

/** Label/value pairs for detail pages; empty values show a dash. */
export function DescriptionList({ items, columns = 2, className }: { items: DescriptionItem[]; columns?: 1 | 2 | 3; className?: string }) {
    return (
        <dl className={cn('grid gap-x-6 gap-y-4 text-sm', columns === 2 && 'sm:grid-cols-2', columns === 3 && 'sm:grid-cols-2 lg:grid-cols-3', className)}>
            {items.map((item, index) => (
                <div key={index} className={cn(item.wide && 'sm:col-span-full')}>
                    <dt className="text-xs font-medium tracking-wide text-(--text-subtle) uppercase">{item.label}</dt>
                    <dd className="mt-1 text-(--text)">{item.value === null || item.value === undefined || item.value === '' ? '—' : item.value}</dd>
                </div>
            ))}
        </dl>
    );
}
