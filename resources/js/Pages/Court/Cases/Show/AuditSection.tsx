import LocalizedDateDisplay from '@/Components/Calendar/LocalizedDateDisplay';
import { cx, tableBodyClassName, tableCellClassName, tableClassName, tableContainerClassName, tableHeadClassName, tableHeaderCellClassName, tableRowClassName } from '@/Components/ui';
import type { CaseAudit, CaseTimelineEntry } from '@/court/types/caseDetail';
import SectionCard, { EmptyBox } from './SectionCard';
import { useCaseText } from './useCaseText';
import { AuditIcon, ClockIcon } from './icons';

const humanize = (action: string): string => {
    const text = action.replace(/_/g, ' ');
    return text.charAt(0).toUpperCase() + text.slice(1);
};

/** The case audit trail: when, action, actor and the recorded details. */
export function AuditSection({ audits }: { audits: CaseAudit[] }) {
    const { t } = useCaseText();

    return (
        <SectionCard id="audits" title={t('cm.cases.case_audit_trail')} icon={<AuditIcon />} count={`${audits.length} ${t('cm.cases.show.entries')}`}>
            {audits.length === 0 ? <EmptyBox>{t('cm.cases.no_audit_records')}</EmptyBox> : (
                <div className={tableContainerClassName}>
                    <div className="overflow-x-auto">
                        <table className={cx(tableClassName, 'min-w-full text-left')}>
                            <thead className={tableHeadClassName}>
                                <tr>
                                    {[t('cm.cases.show.when'), t('cm.cases.show.action'), t('cm.cases.show.actor'), t('cm.cases.show.details')].map((heading) => (
                                        <th key={heading} className={cx(tableHeaderCellClassName, 'text-left')}>{heading}</th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className={tableBodyClassName}>
                                {audits.map((audit) => (
                                    <tr key={audit.id} className={tableRowClassName}>
                                        <td className={cx(tableCellClassName, 'whitespace-nowrap')}><LocalizedDateDisplay value={audit.created_at} withTime /></td>
                                        <td className={cx(tableCellClassName, 'font-medium text-(--app-foreground)')}>{humanize(audit.action)}</td>
                                        <td className={cx(tableCellClassName, 'text-xs')}>
                                            {audit.actor_name
                                                ? <>{audit.actor_name}{audit.actor_id ? ` (#${audit.actor_id})` : ''}</>
                                                : audit.actor_id
                                                    ? `${audit.actor_type ?? 'system'} (#${audit.actor_id})`
                                                    : (audit.actor_type ?? 'system')}
                                        </td>
                                        <td className={cx(tableCellClassName, 'text-xs')}>
                                            {audit.meta
                                                ? <pre className="whitespace-pre-wrap rounded-control border border-(--app-border) bg-(--app-surface-muted) px-2 py-1 text-[11px]">{audit.meta}</pre>
                                                : <span className="text-(--app-muted-foreground)">-</span>}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}
        </SectionCard>
    );
}

/** The status timeline (case_status_logs): each change, its note and when it happened. */
export function TimelineSection({ timeline }: { timeline: CaseTimelineEntry[] }) {
    const { t, text, statusLabel } = useCaseText();

    return (
        <SectionCard id="timeline" title={text('timeline_title')} icon={<ClockIcon />} count={`${timeline.length} ${t('cm.cases.show.entries')}`}>
            {timeline.length === 0 ? <EmptyBox>{text('timeline_empty')}</EmptyBox> : (
                <ol className="space-y-3">
                    {timeline.map((entry) => (
                        <li key={entry.id} className="flex flex-wrap items-start justify-between gap-3 rounded-control border border-(--app-border) bg-(--app-surface-muted) px-4 py-3 text-sm">
                            <div>
                                <span className="text-(--app-muted-foreground)">{text('timeline_from')} </span>
                                <span className="font-medium text-(--app-foreground)">{entry.from_status ? statusLabel(entry.from_status) : '—'}</span>
                                <span className="mx-2 text-(--app-muted-foreground)">→</span>
                                <span className="text-(--app-muted-foreground)">{text('timeline_to')} </span>
                                <span className="font-medium text-(--app-foreground)">{entry.to_status ? statusLabel(entry.to_status) : '—'}</span>
                                {entry.note && <p className="mt-1 text-xs text-(--app-muted-foreground)">{text('timeline_note')}: {entry.note}</p>}
                            </div>
                            <span className="text-xs text-(--app-muted-foreground)"><LocalizedDateDisplay value={entry.created_at} withTime /></span>
                        </li>
                    ))}
                </ol>
            )}
        </SectionCard>
    );
}
