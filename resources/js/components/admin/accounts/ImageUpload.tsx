import { useEffect, useState } from 'react';
import { Field } from '@/components/ui/Field';
import { Checkbox, Input } from '@/components/ui/Input';
import { t } from '@/lib/i18n';
import { cn } from '@/lib/cn';

interface ImageUploadProps {
    label: string;
    /** Image currently stored on the server, if any. */
    currentUrl: string | null;
    emptyText: string;
    file: File | null;
    onFile: (file: File | null) => void;
    /** When set, offers a "remove current image" checkbox (edit forms only). */
    remove?: { checked: boolean; onChange: (checked: boolean) => void };
    accept?: string;
    error?: string;
    round?: boolean;
}

/** File picker with a live preview of the chosen image next to the stored one. */
export function ImageUpload({
    label,
    currentUrl,
    emptyText,
    file,
    onFile,
    remove,
    accept = 'image/png,image/webp,image/jpeg',
    error,
    round = false,
}: ImageUploadProps) {
    const [preview, setPreview] = useState<string | null>(null);

    useEffect(() => {
        if (!file) {
            setPreview(null);
            return;
        }
        const url = URL.createObjectURL(file);
        setPreview(url);

        return () => URL.revokeObjectURL(url);
    }, [file]);

    const shown = preview ?? (remove?.checked ? null : currentUrl);

    return (
        <div className="rounded-xl border border-(--border) bg-(--surface-soft) p-4">
            <Field label={label} error={error} hint={t('users.image_hint')}>
                <div className="flex flex-col items-start gap-3">
                    <div
                        className={cn(
                            'grid shrink-0 place-items-center overflow-hidden border border-(--border) bg-(--surface-strong)',
                            round ? 'h-16 w-16 rounded-full' : 'h-16 w-28 rounded-lg',
                        )}
                    >
                        {shown ? (
                            <img src={shown} alt={label} className={cn('h-full w-full', round ? 'object-cover' : 'object-contain')} />
                        ) : (
                            <span className="px-2 text-center text-[11px] text-(--text-subtle)">{emptyText}</span>
                        )}
                    </div>
                    <Input
                        type="file"
                        accept={accept}
                        onChange={(event) => onFile(event.target.files?.[0] ?? null)}
                        className="w-full text-xs file:me-3 file:rounded-md file:border-0 file:bg-(--surface-strong) file:px-3 file:py-1.5 file:text-xs file:font-medium"
                    />
                </div>
            </Field>
            {remove && currentUrl && !file && (
                <div className="mt-3">
                    <Checkbox checked={remove.checked} onChange={(event) => remove.onChange(event.target.checked)} label={t('users.remove_image')} />
                </div>
            )}
        </div>
    );
}
