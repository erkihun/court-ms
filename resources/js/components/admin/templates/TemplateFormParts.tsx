import { Fragment } from 'react';
import { Field } from '@/components/ui/Field';
import { Checkbox, Input } from '@/components/ui/Input';
import { t } from '@/lib/i18n';
import type { TemplateImage } from '@/types/admin/letters';

interface TemplateImageInputProps {
    label: string;
    current: TemplateImage | null;
    /** Translation key taking :file, e.g. 'letters.templates.form.header_current'. */
    currentKey: string;
    removeLabel: string;
    remove: boolean;
    onFile: (file: File | null) => void;
    onRemove: (remove: boolean) => void;
    error?: string;
}

/** Header/footer image upload with the current image and a "remove" option. */
export function TemplateImageInput({ label, current, currentKey, removeLabel, remove, onFile, onRemove, error }: TemplateImageInputProps) {
    return (
        <Field label={label} error={error} hint={current ? t(currentKey, { file: current.name }) : undefined}>
            <Input type="file" accept="image/*" onChange={(event) => onFile(event.target.files?.[0] ?? null)} />
            {current && (
                <div className="mt-2 space-y-2">
                    <img src={current.url} alt="" className="h-20 rounded-sm border border-(--border) object-contain" />
                    <Checkbox label={removeLabel} checked={remove} onChange={(event) => onRemove(event.target.checked)} />
                </div>
            )}
        </Field>
    );
}

/** Help text whose <code>…</code> tokens become monospace chips; any other markup is dropped. */
export function PlaceholderHelp({ text }: { text: string }) {
    const parts = text.split(/<code>(.*?)<\/code>/g);

    return (
        <p className="text-xs text-(--text-subtle) italic">
            {parts.map((part, index) =>
                index % 2 === 1 ? (
                    <code key={index} className="rounded-sm bg-(--surface-soft) px-1 font-mono not-italic">
                        {part}
                    </code>
                ) : (
                    <Fragment key={index}>{part.replace(/<[^>]*>/g, '')}</Fragment>
                ),
            )}
        </p>
    );
}

/** Placeholder list for a table cell, shortened like the old Str::limit(…, 60). */
export function PlaceholderSummary({ placeholders, none }: { placeholders: string[]; none: string }) {
    const full = placeholders.join(', ');
    const short = full.length > 60 ? `${full.slice(0, 60)}...` : full;

    return (
        <span className="text-xs text-(--text-subtle)" title={full || none}>
            {short || none}
        </span>
    );
}
