import type { ReactNode } from 'react';
import { t } from '@/lib/i18n';

export interface BreakdownRow {
    label: string;
    total: number;
}

const number = (value: number) => value.toLocaleString();

/** A labelled count list with a proportion bar per row. */
export function Breakdown({ title, hint, rows, aside }: { title: string; hint?: string; rows: BreakdownRow[]; aside?: ReactNode }) {
    const max = Math.max(1, ...rows.map((row) => row.total));

    return (
        <section className="ui-card p-5 break-inside-avoid">
            <div className="flex items-start justify-between gap-3">
                <div>
                    <h3 className="text-sm font-semibold text-(--text)">{title}</h3>
                    {hint && <p className="text-xs text-(--text-subtle)">{hint}</p>}
                </div>
                {aside && <span className="text-xs font-medium tracking-wide whitespace-nowrap text-(--text-subtle) uppercase">{aside}</span>}
            </div>
            {rows.length === 0 ? (
                <p className="mt-3 text-xs text-(--text-subtle)">{t('reports.no_data')}</p>
            ) : (
                <ul className="mt-4 space-y-2.5">
                    {rows.map((row, index) => (
                        <li key={`${row.label}-${index}`} className="text-sm">
                            <div className="flex items-center justify-between gap-3">
                                <span className="truncate text-(--text-muted)">{row.label}</span>
                                <span className="font-semibold text-(--text) tabular-nums">{number(row.total)}</span>
                            </div>
                            <div className="mt-1 h-1.5 overflow-hidden rounded-full bg-(--surface-soft)" aria-hidden="true">
                                <div className="h-full rounded-full bg-blue-500/70" style={{ width: `${(row.total / max) * 100}%` }} />
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </section>
    );
}

export { number as formatNumber };
