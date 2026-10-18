import type { ReactNode } from 'react';
import { cn } from '@/lib/cn';

interface CaseSectionProps {
    id: string;
    title: ReactNode;
    /** Shown as "N entries" on the right of the title. */
    count?: number;
    countLabel?: string;
    actions?: ReactNode;
    className?: string;
    children: ReactNode;
}

/** One panel of the case page; the id doubles as the URL hash target. */
export function CaseSection({ id, title, count, countLabel, actions, className, children }: CaseSectionProps) {
    return (
        <section id={id} aria-labelledby={`${id}-title`} className={cn('ui-card scroll-mt-24 space-y-4 p-5 sm:p-6', className)}>
            <div className="flex flex-wrap items-center justify-between gap-3 border-b border-(--border) pb-3">
                <h2 id={`${id}-title`} className="text-lg font-semibold text-(--text)">
                    {title}
                </h2>
                <div className="flex flex-wrap items-center gap-2">
                    {actions}
                    {count !== undefined && (
                        <span className="rounded-full bg-(--surface-soft) px-2.5 py-1 text-xs font-medium text-(--text-muted)">
                            {count}
                            {countLabel ? ` ${countLabel}` : ''}
                        </span>
                    )}
                </div>
            </div>
            {children}
        </section>
    );
}

export function EmptyBox({ children }: { children: ReactNode }) {
    return (
        <div className="rounded-lg border border-dashed border-(--border-strong) bg-(--surface-soft) p-6 text-center text-sm text-(--text-subtle)">
            {children}
        </div>
    );
}

/** Compact bordered table used inside case sections. */
export function SectionTable({ head, children }: { head: ReactNode; children: ReactNode }) {
    return (
        <div className="overflow-x-auto rounded-lg border border-(--border)">
            <table className="min-w-full text-sm">
                <thead className="bg-(--surface-soft) text-left text-(--text-muted)">{head}</thead>
                <tbody className="divide-y divide-(--border)">{children}</tbody>
            </table>
        </div>
    );
}

export const th = 'px-3 py-2 font-medium border-b border-(--border)';
export const td = 'px-3 py-2 align-top text-(--text-muted)';
