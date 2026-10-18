import { useState } from 'react';
import { t } from '@/lib/i18n';

/** Small "Copy" pill that briefly confirms success. */
export function CopyButton({ value }: { value: string }) {
    const [copied, setCopied] = useState(false);

    const copy = async () => {
        try {
            await navigator.clipboard.writeText(value);
            setCopied(true);
            window.setTimeout(() => setCopied(false), 1400);
        } catch {
            // Clipboard blocked (insecure context or permission): nothing to confirm.
        }
    };

    return (
        <button type="button" onClick={copy} className="rounded-full border border-(--border) px-3 py-1 text-[11px] text-(--text-muted) hover:bg-(--surface-soft)" aria-live="polite">
            {copied ? t('cases.copied') : t('cases.copy')}
        </button>
    );
}
