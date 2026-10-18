import { ArrowUpTrayIcon, EyeIcon, EyeSlashIcon } from '@heroicons/react/24/outline';
import { type ReactNode, useId, useState } from 'react';
import { Card } from '@/components/ui/Card';
import { useControlProps } from '@/components/ui/Field';
import { Input } from '@/components/ui/Input';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';

/** One settings group: an icon, a title, optional badge, then fields. */
export function SettingsCard({
    title,
    icon,
    badge,
    children,
    className,
}: {
    title: ReactNode;
    icon?: ReactNode;
    badge?: ReactNode;
    children: ReactNode;
    className?: string;
}) {
    return (
        <Card className={className}>
            <h2 className="mb-4 flex items-center gap-2 text-sm font-bold text-(--text)">
                {icon && (
                    <span className="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-[rgb(var(--ac)/0.1)] text-[rgb(var(--ac))] [&>svg]:h-4 [&>svg]:w-4">
                        {icon}
                    </span>
                )}
                <span className="min-w-0 flex-1">{title}</span>
                {badge}
            </h2>
            {children}
        </Card>
    );
}

/** Accessible on/off switch (a styled checkbox). */
export function Switch({ checked, onChange, label }: { checked: boolean; onChange: (checked: boolean) => void; label: string }) {
    return (
        <span className="relative inline-flex h-5 w-9 shrink-0 items-center">
            <input type="checkbox" className="peer sr-only" checked={checked} onChange={(event) => onChange(event.target.checked)} aria-label={label} />
            <span
                aria-hidden="true"
                className="absolute inset-0 cursor-pointer rounded-full bg-(--border-strong) transition-colors peer-checked:bg-[rgb(var(--ac))] peer-focus-visible:ring-2 peer-focus-visible:ring-[rgb(var(--ac))] peer-focus-visible:ring-offset-2"
            />
            <span
                aria-hidden="true"
                className="pointer-events-none absolute left-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"
            />
        </span>
    );
}

/** Label + description on the left, switch on the right; the whole row toggles. */
export function ToggleRow({
    label,
    description,
    checked,
    onChange,
}: {
    label: string;
    description?: ReactNode;
    checked: boolean;
    onChange: (checked: boolean) => void;
}) {
    return (
        <label className="flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-(--border) bg-(--surface-soft) px-4 py-3">
            <span className="min-w-0 flex-1">
                <span className="block text-sm font-semibold text-(--text)">{label}</span>
                {description && <span className="mt-0.5 block text-xs text-(--text-subtle)">{description}</span>}
            </span>
            <Switch checked={checked} onChange={onChange} label={label} />
        </label>
    );
}

/** Number input with a trailing unit ("min", "days"…). */
export function UnitInput({
    value,
    onChange,
    unit,
    min,
    max,
    className,
}: {
    value: string;
    onChange: (value: string) => void;
    unit: string;
    min?: number;
    max?: number;
    className?: string;
}) {
    const control = useControlProps();

    return (
        <div
            className={cn(
                'flex overflow-hidden rounded-lg border border-(--border) bg-(--surface-soft) focus-within:ring-2 focus-within:ring-[rgb(var(--ac)/0.3)]',
                className,
            )}
        >
            <input
                {...control}
                type="number"
                min={min}
                max={max}
                value={value}
                onChange={(event) => onChange(event.target.value)}
                className="min-w-0 flex-1 border-0 bg-transparent px-3 py-2 text-sm text-(--text) focus:ring-0 focus:outline-none"
            />
            <span className="flex items-center border-s border-(--border) bg-(--surface-strong) px-3 text-xs font-semibold whitespace-nowrap text-(--text-subtle)">
                {unit}
            </span>
        </div>
    );
}

/** Write-only secret: the stored value is never sent to the browser, so blank keeps it. */
export function SecretInput({
    value,
    onChange,
    placeholder,
    stored,
}: {
    value: string;
    onChange: (value: string) => void;
    placeholder?: string;
    stored: boolean;
}) {
    const [visible, setVisible] = useState(false);

    return (
        <div className="relative">
            <Input
                type={visible ? 'text' : 'password'}
                value={value}
                onChange={(event) => onChange(event.target.value)}
                placeholder={stored ? '••••••••••••' : placeholder}
                autoComplete="new-password"
                className="pe-10"
            />
            <button
                type="button"
                onClick={() => setVisible((current) => !current)}
                className="absolute inset-y-0 end-0 flex items-center px-3 text-(--text-subtle) hover:text-(--text)"
                aria-label={visible ? t('settings.hide_value') : t('settings.show_value')}
                aria-pressed={visible}
            >
                {visible ? <EyeSlashIcon className="h-4 w-4" /> : <EyeIcon className="h-4 w-4" />}
            </button>
        </div>
    );
}

/** Key/value rows in a monospace column (system information, database facts). */
export function InfoRows({ rows }: { rows: Array<{ label: string; value: ReactNode }> }) {
    return (
        <dl className="space-y-1.5">
            {rows.map((row) => (
                <div
                    key={row.label}
                    className="flex items-center justify-between gap-3 rounded-md border border-(--border) bg-(--surface-soft) px-3 py-1.5 text-xs"
                >
                    <dt className="text-(--text-subtle)">{row.label}</dt>
                    <dd className="truncate font-mono font-semibold text-(--text)">{row.value}</dd>
                </div>
            ))}
        </dl>
    );
}

interface UploadFieldProps {
    label: string;
    hint: string;
    accept: string;
    file: File | null;
    currentUrl: string | null;
    onChange: (file: File | null) => void;
    error?: string;
    previewClassName?: string;
}

/** Drop-zone style file input with a preview of the current image. */
export function UploadField({ label, hint, accept, file, currentUrl, onChange, error, previewClassName = 'h-12' }: UploadFieldProps) {
    const id = useId();

    return (
        <div>
            <label htmlFor={id} className="mb-1.5 block text-sm font-medium text-(--text)">
                {label}
            </label>
            <div className="relative rounded-xl border-2 border-dashed border-(--border) bg-(--surface-soft) p-4 text-center transition-colors focus-within:border-[rgb(var(--ac)/0.6)] hover:border-[rgb(var(--ac)/0.45)]">
                <input
                    id={id}
                    type="file"
                    accept={accept}
                    onChange={(event) => onChange(event.target.files?.[0] ?? null)}
                    className="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                    aria-describedby={`${id}-hint`}
                    aria-invalid={error ? true : undefined}
                />
                <ArrowUpTrayIcon className="mx-auto mb-1.5 h-6 w-6 text-(--text-subtle)" aria-hidden="true" />
                <p className="text-xs font-medium text-(--text-muted)">{file ? file.name : t('settings.upload_label', { name: label.toLowerCase() })}</p>
                <p id={`${id}-hint`} className="mt-0.5 text-[11px] text-(--text-subtle)">
                    {hint}
                </p>
            </div>
            {error && <p className="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{error}</p>}
            {currentUrl && !file && (
                <div className="mt-2 flex items-center justify-center rounded-lg border border-(--border) bg-(--surface-strong) p-2">
                    <img src={currentUrl} alt="" className={cn('object-contain', previewClassName)} />
                </div>
            )}
        </div>
    );
}
