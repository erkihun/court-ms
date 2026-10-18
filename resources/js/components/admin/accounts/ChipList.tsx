import { useState } from 'react';
import { t } from '@/lib/i18n';

/** Neutral chips with "+N more" that expands in place. */
export function ChipList({ items, limit = 6, empty = '—' }: { items: string[]; limit?: number; empty?: string }) {
    const [expanded, setExpanded] = useState(false);

    if (items.length === 0) {
        return <span className="text-(--text-subtle)">{empty}</span>;
    }

    const extra = items.length - limit;
    const shown = expanded || extra <= 0 ? items : items.slice(0, limit);

    return (
        <div className="flex flex-wrap gap-1.5">
            {shown.map((item) => (
                <span key={item} className="ui-badge ui-badge-slate">
                    {item}
                </span>
            ))}
            {extra > 0 && (
                <button
                    type="button"
                    onClick={() => setExpanded((value) => !value)}
                    aria-expanded={expanded}
                    className="rounded-full border border-(--border-strong) px-2 py-0.5 text-[11px] font-medium text-(--text-muted) hover:bg-(--surface-soft)"
                >
                    {expanded ? '−' : `+${extra} ${t('roles.index.more')}`}
                </button>
            )}
        </div>
    );
}
