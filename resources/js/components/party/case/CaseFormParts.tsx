import { ExclamationCircleIcon, PlusIcon, XMarkIcon } from '@heroicons/react/24/outline';
import type { ReactNode } from 'react';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';

export const inputClass =
    'w-full rounded-lg border border-(--border) bg-(--surface-strong) px-3 py-2.5 text-(--text) focus:border-blue-600 focus:ring-2 focus:ring-blue-600 focus:outline-hidden disabled:bg-slate-100 disabled:text-slate-500';

/** All validation errors, listed at the top of a long form. */
export function ErrorSummary({ errors, title }: { errors: Record<string, string | undefined>; title: string }) {
    const messages = [...new Set(Object.values(errors).filter((message): message is string => Boolean(message)))];
    if (messages.length === 0) return null;

    return (
        <div role="alert" className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <div className="mb-1 flex items-center gap-2 font-semibold">
                <ExclamationCircleIcon className="h-4 w-4" />
                {title}
            </div>
            <ul className="mt-1 list-inside list-disc space-y-0.5">
                {messages.map((message) => (
                    <li key={message}>{message}</li>
                ))}
            </ul>
        </div>
    );
}

export function FieldLabel({ htmlFor, required, children }: { htmlFor?: string; required?: boolean; children: ReactNode }) {
    return (
        <label htmlFor={htmlFor} className="block font-medium text-(--text-muted)">
            {children} {required && <span className="text-red-600">*</span>}
        </label>
    );
}

export function FieldError({ message }: { message?: string }) {
    return message ? <p className="mt-1 text-xs text-red-600">{message}</p> : null;
}

export function Certify({ checked, onChange, error, children }: { checked: boolean; onChange: (value: boolean) => void; error?: string; children: ReactNode }) {
    return (
        <div>
            <label className="flex items-start gap-2 text-(--text-muted)">
                <input type="checkbox" className="mt-1 rounded-sm border-slate-300 text-orange-600 focus:ring-orange-500" checked={checked} onChange={(event) => onChange(event.target.checked)} required />
                <span>{children}</span>
            </label>
            <FieldError message={error} />
        </div>
    );
}

/** Case number preview for the chosen type: first 4 letters/digits of its prefix, e.g. CIVI/00001/26. */
export function caseNumberPreview(prefix: string | undefined, year: string): string {
    const cleaned = (prefix?.match(/[A-Za-z0-9]+/g) ?? []).join('').slice(0, 4).toUpperCase();
    return `${cleaned || 'CASE'}/00001/${year}`;
}

export interface DocumentRow {
    title: string;
    file: File | null;
}

/** Repeating "title + PDF" rows for evidence uploads. */
export function DocumentRows({
    rows,
    onChange,
    errors,
    required,
    disabled = false,
    titlePlaceholder,
}: {
    rows: DocumentRow[];
    onChange: (rows: DocumentRow[]) => void;
    errors: Record<string, string | undefined>;
    required: boolean;
    disabled?: boolean;
    titlePlaceholder: string;
}) {
    const update = (index: number, patch: Partial<DocumentRow>) => onChange(rows.map((row, i) => (i === index ? { ...row, ...patch } : row)));

    return (
        <div className="space-y-2">
            {rows.map((row, index) => (
                <div key={index} className="grid items-start gap-3 md:grid-cols-[1fr_1fr_auto]">
                    <div>
                        <input
                            type="text"
                            className={inputClass}
                            value={row.title}
                            onChange={(event) => update(index, { title: event.target.value })}
                            placeholder={titlePlaceholder}
                            aria-label={titlePlaceholder}
                            required={required}
                            disabled={disabled}
                            maxLength={255}
                        />
                        <FieldError message={errors[`evidence_titles.${index}`]} />
                    </div>
                    <div>
                        <input
                            type="file"
                            accept="application/pdf"
                            className={inputClass}
                            onChange={(event) => update(index, { file: event.target.files?.[0] ?? null })}
                            required={required}
                            disabled={disabled}
                            aria-label={t('cases.file')}
                        />
                        <FieldError message={errors[`evidence_files.${index}`]} />
                    </div>
                    <RemoveRow hidden={rows.length <= 1 || disabled} onClick={() => onChange(rows.filter((_, i) => i !== index))} />
                </div>
            ))}
        </div>
    );
}

export interface WitnessRow {
    full_name: string;
    phone: string;
    address: string;
    email?: string;
}

/** Repeating witness rows; the edit form also asks for an email. */
export function WitnessRows({
    rows,
    onChange,
    errors,
    required,
    withEmail = false,
    disabled = false,
    placeholders,
}: {
    rows: WitnessRow[];
    onChange: (rows: WitnessRow[]) => void;
    errors: Record<string, string | undefined>;
    required: boolean;
    withEmail?: boolean;
    disabled?: boolean;
    placeholders: { full_name: string; phone: string; address: string; email?: string };
}) {
    const update = (index: number, patch: Partial<WitnessRow>) => onChange(rows.map((row, i) => (i === index ? { ...row, ...patch } : row)));
    const fields: Array<{ name: keyof WitnessRow; wide?: boolean; type?: string }> = [
        { name: 'full_name', wide: true },
        { name: 'phone', type: 'tel' },
        ...(withEmail ? [{ name: 'email' as const, type: 'email' }] : []),
        { name: 'address', wide: !withEmail },
    ];

    return (
        <div className="space-y-2">
            {rows.map((row, index) => (
                <div key={index} className={cn('grid items-start gap-3', withEmail ? 'md:grid-cols-[2fr_1fr_1fr_2fr_auto]' : 'md:grid-cols-[2fr_1fr_2fr_auto]')}>
                    {fields.map(({ name, type }) => (
                        <div key={name}>
                            <input
                                type={type ?? 'text'}
                                className={inputClass}
                                value={row[name] ?? ''}
                                onChange={(event) => update(index, { [name]: event.target.value })}
                                placeholder={placeholders[name]}
                                aria-label={placeholders[name]}
                                required={required && name !== 'email'}
                                disabled={disabled}
                            />
                            <FieldError message={errors[`witnesses.${index}.${name}`]} />
                        </div>
                    ))}
                    <RemoveRow hidden={rows.length <= 1 || disabled} onClick={() => onChange(rows.filter((_, i) => i !== index))} />
                </div>
            ))}
        </div>
    );
}

export function AddRowButton({ onClick, disabled, children }: { onClick: () => void; disabled?: boolean; children: ReactNode }) {
    return (
        <button
            type="button"
            onClick={onClick}
            disabled={disabled}
            className="inline-flex items-center gap-1.5 rounded-md bg-orange-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-orange-600 focus:ring-2 focus:ring-orange-400 focus:ring-offset-1 focus:outline-hidden disabled:opacity-50"
        >
            <PlusIcon className="h-3.5 w-3.5" />
            {children}
        </button>
    );
}

function RemoveRow({ hidden, onClick }: { hidden: boolean; onClick: () => void }) {
    if (hidden) return <span className="hidden w-9 md:block" />;

    return (
        <button type="button" onClick={onClick} className="grid h-10 w-9 place-items-center rounded-lg text-(--text-subtle) hover:bg-red-50 hover:text-red-600" aria-label={t('ui.delete')}>
            <XMarkIcon className="h-4 w-4" />
        </button>
    );
}
