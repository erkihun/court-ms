import { RichText } from '@/components/ui/RichText';
import { t } from '@/lib/i18n';

export interface BenchNote {
    id: number;
    title: string;
    caseId: number | null;
    caseNumber: string | null;
    caseTitle: string | null;
    author: string | null;
    created: string;
    createdTime: string;
    updated: string | null;
    noteDate: string | null;
    /** Panel names in seat order; seat 1 is the presiding judge. */
    judges: Array<string | null>;
    signatureUrl: string | null;
    /** Sanitised by the server (HTMLPurifier). */
    noteHtml: string;
}

/** The three panel judges as a header row, as on the printed note. */
export function JudgeRow({ judges }: { judges: Array<string | null> }) {
    return (
        <div className="grid grid-cols-3 gap-3 text-center text-sm font-semibold text-(--text)">
            {judges.map((name, index) => (
                <div key={index}>
                    <div className="text-[11px] font-medium tracking-wide text-(--text-subtle) uppercase">{t('bench.labels.judge', { number: index + 1 })}</div>
                    <div className="mt-0.5">{name ?? t('bench.meta.unknown')}</div>
                </div>
            ))}
        </div>
    );
}

export function NoteBody({ html }: { html: string }) {
    return html ? (
        <RichText html={html} className="rounded-xl border border-(--border) bg-(--surface-soft) p-4 text-sm leading-relaxed" />
    ) : (
        <p className="text-sm text-(--text-subtle) italic">{t('bench.helpers.empty_content')}</p>
    );
}

/** Presiding judge's name and signature image, closing the note. */
export function SignatureBlock({ name, signatureUrl }: { name: string | null; signatureUrl: string | null }) {
    return (
        <div className="flex flex-col items-start gap-1">
            <div className="text-sm font-semibold text-(--text)">{name ?? t('bench.meta.unknown')}</div>
            {signatureUrl ? (
                <img src={signatureUrl} alt={name ?? ''} className="h-14 w-auto object-contain" />
            ) : (
                <div className="text-xs text-(--text-subtle) italic">{t('bench.labels.signature', { number: 1 })}</div>
            )}
        </div>
    );
}
