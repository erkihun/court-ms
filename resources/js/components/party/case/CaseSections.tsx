import { CalendarDaysIcon, MapPinIcon } from '@heroicons/react/24/outline';
import type { ReactNode } from 'react';
import { RichText } from '@/components/ui/RichText';
import { t } from '@/lib/i18n';

/** Building blocks shared by the applicant and respondent case pages. */

export function Labelled({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div>
            <div className="text-[11px] tracking-wide text-(--text-subtle) uppercase">{label}</div>
            <div className="mt-0.5 flex flex-wrap items-center gap-2">{children}</div>
        </div>
    );
}

export function SectionTitle({ children, count }: { children: ReactNode; count?: number }) {
    return (
        <div className="mb-3 flex items-center justify-between">
            <h3 className="font-semibold text-(--text)">{children}</h3>
            {count !== undefined && <span className="text-[11px] text-(--text-subtle)">{count}</span>}
        </div>
    );
}

export function Detail({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <dt className="text-(--text-subtle)">{label}</dt>
            <dd className="font-medium text-(--text)">{value}</dd>
        </div>
    );
}

export function Empty({ children }: { children: ReactNode }) {
    return <div className="rounded-lg border border-dashed border-(--border) bg-(--surface-soft) px-4 py-8 text-center text-(--text-subtle)">{children}</div>;
}

export function Panel({ title, count, children }: { title: string; count: number; children: ReactNode }) {
    return (
        <section className="rounded-xl border border-(--border) bg-(--surface-strong) p-5 shadow-lg">
            <SectionTitle count={count}>{title}</SectionTitle>
            {children}
        </section>
    );
}

export interface CaseDocument {
    id: number;
    title: string;
    mime: string | null;
    sizeKb?: number | null;
    created: string;
    descriptionHtml: string | null;
    downloadUrl: string | null;
}

export function DocumentList({ documents }: { documents: CaseDocument[] }) {
    return (
        <div>
            <SectionTitle count={documents.length}>{t('cases.submitted_documents')}</SectionTitle>
            {documents.length === 0 ? (
                <Empty>{t('cases.no_submitted_documents')}</Empty>
            ) : (
                <ul className="divide-y divide-(--border)">
                    {documents.map((doc) => (
                        <li key={doc.id} className="flex items-center justify-between gap-4 py-2">
                            <div className="flex-1">
                                <div className="font-medium text-(--text)">{doc.title}</div>
                                <div className="flex flex-wrap gap-1 text-xs text-(--text-subtle)">
                                    {doc.mime ?? t('cases.file')}
                                    {doc.sizeKb != null && <> · {doc.sizeKb.toFixed(1)} KB</>}
                                    {doc.created && <> · {doc.created}</>}
                                </div>
                                {doc.descriptionHtml && <RichText html={doc.descriptionHtml} className="mt-1 text-xs text-(--text-muted)" />}
                            </div>
                            {doc.downloadUrl && (
                                <a href={doc.downloadUrl} className="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100">
                                    {t('cases.view')}
                                </a>
                            )}
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}

export interface CaseHearing {
    id: number;
    at: string;
    type: string | null;
    location: string | null;
    notes: string | null;
    icsUrl?: string;
}

export function HearingList({ hearings }: { hearings: CaseHearing[] }) {
    return (
        <Panel title={t('cases.hearings.title')} count={hearings.length}>
            {hearings.length === 0 ? (
                <Empty>{t('cases.no_hearings_scheduled')}</Empty>
            ) : (
                <ul className="max-h-64 space-y-3 overflow-y-auto pr-1">
                    {hearings.map((hearing) => (
                        <li key={hearing.id} className="flex items-start justify-between gap-4 rounded-lg border border-(--border) p-3 transition-colors hover:border-blue-300">
                            <div className="flex items-start gap-3">
                                <div className="mt-0.5 grid h-9 w-9 place-items-center rounded-full border border-(--border) bg-(--surface-soft) text-(--text-muted)">
                                    <CalendarDaysIcon className="h-4 w-4" />
                                </div>
                                <div>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <div className="font-medium text-(--text)">{hearing.at}</div>
                                        {hearing.type && <span className="rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700">{hearing.type}</span>}
                                    </div>
                                    <div className="mt-1 flex items-center gap-1.5 text-xs text-(--text-subtle)">
                                        <MapPinIcon className="h-3.5 w-3.5" />
                                        {hearing.location ?? '—'}
                                    </div>
                                    {hearing.notes && <p className="mt-2 text-xs text-(--text-muted)">{hearing.notes}</p>}
                                </div>
                            </div>
                            {hearing.icsUrl && (
                                <a href={hearing.icsUrl} className="shrink-0 rounded-md border border-(--border) px-3 py-1.5 text-xs font-medium text-(--text-muted) hover:bg-(--surface-soft)">
                                    {t('cases.add_to_calendar')}
                                </a>
                            )}
                        </li>
                    ))}
                </ul>
            )}
        </Panel>
    );
}

export interface CaseLetter {
    id: number;
    title: string;
    reference: string | null;
    template: string | null;
    author: string | null;
    created: string;
    previewUrl: string;
    /** Badge shown next to the title, e.g. the approval status. */
    badge?: ReactNode;
}

export function LetterList({ letters }: { letters: CaseLetter[] }) {
    return (
        <Panel title={t('cases.letters_section.title')} count={letters.length}>
            {letters.length === 0 ? (
                <Empty>{t('cases.letters_section.empty')}</Empty>
            ) : (
                <div className="max-h-64 space-y-3 overflow-y-auto pr-1">
                    {letters.map((letter) => (
                        <div key={letter.id} className="rounded-lg border border-(--border) bg-(--surface-soft) p-3">
                            <div className="flex items-start justify-between gap-2">
                                <div className="space-y-1">
                                    <div className="flex items-center gap-2">
                                        <div className="font-semibold text-(--text)">{letter.title}</div>
                                        {letter.badge}
                                    </div>
                                    <div className="flex flex-wrap gap-3 text-xs text-(--text-muted)">
                                        <span>
                                            {t('cases.letters_section.reference')}: {letter.reference ?? '—'}
                                        </span>
                                        <span>
                                            {t('cases.letters_section.template')}: {letter.template ?? '—'}
                                        </span>
                                        <span>
                                            {t('cases.letters_section.author')}: {letter.author ?? '—'}
                                        </span>
                                        <span>
                                            {t('cases.letters_section.created')}: {letter.created}
                                        </span>
                                    </div>
                                </div>
                                <a href={letter.previewUrl} target="_blank" rel="noopener" className="text-xs font-semibold text-blue-600 underline hover:text-blue-800">
                                    {t('cases.view')}
                                </a>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </Panel>
    );
}
