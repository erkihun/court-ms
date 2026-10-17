import { Link } from '@inertiajs/react';
import { t } from '@/lib/i18n';
import { cn } from '@/lib/cn';
import type { Paginated } from '@/types';

/** Laravel paginator links, kept as Inertia visits that preserve scroll position. */
export function Pagination({ meta }: { meta: Pick<Paginated<unknown>, 'links' | 'from' | 'to' | 'total' | 'last_page'> }) {
    if (meta.last_page <= 1) {
        return null;
    }

    return (
        <nav className="flex flex-wrap items-center justify-between gap-3" aria-label={t('ui.pagination')}>
            <p className="text-xs text-(--text-subtle)">
                {meta.from ?? 0}–{meta.to ?? 0} / {meta.total}
            </p>
            <ul className="flex flex-wrap items-center gap-1">
                {meta.links.map((link, index) => {
                    const label = paginatorLabel(link.label);
                    const classes = cn(
                        'inline-flex min-w-8 items-center justify-center rounded-md px-2.5 py-1 text-xs font-medium transition-colors',
                        link.active ? 'bg-blue-600 text-white' : 'text-(--text-muted) hover:bg-(--surface-strong)',
                        !link.url && 'pointer-events-none opacity-40',
                    );

                    return (
                        <li key={`${index}-${link.label}`}>
                            {link.url ? (
                                <Link href={link.url} preserveScroll className={classes} aria-current={link.active ? 'page' : undefined}>
                                    {label}
                                </Link>
                            ) : (
                                <span className={classes}>{label}</span>
                            )}
                        </li>
                    );
                })}
            </ul>
        </nav>
    );
}

/** Laravel sends HTML entities and translated "Previous"/"Next" labels; show arrows instead. */
function paginatorLabel(label: string): string {
    if (label.includes('&laquo;')) return '‹';
    if (label.includes('&raquo;')) return '›';
    return label;
}
