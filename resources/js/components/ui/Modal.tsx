import { XMarkIcon } from '@heroicons/react/24/outline';
import { type ReactNode, useEffect, useRef } from 'react';
import { t } from '@/lib/i18n';
import { cn } from '@/lib/cn';

interface ModalProps {
    open: boolean;
    onClose: () => void;
    title: ReactNode;
    children: ReactNode;
    footer?: ReactNode;
    size?: 'sm' | 'md' | 'lg' | 'xl';
}

const widths = { sm: 'max-w-md', md: 'max-w-lg', lg: 'max-w-2xl', xl: 'max-w-4xl' } as const;

/** Native <dialog>: focus trapping, Escape and the inert background come from the browser. */
export function Modal({ open, onClose, title, children, footer, size = 'md' }: ModalProps) {
    const dialog = useRef<HTMLDialogElement>(null);

    useEffect(() => {
        const element = dialog.current;
        if (!element) return;
        if (open && !element.open) element.showModal();
        if (!open && element.open) element.close();
    }, [open]);

    return (
        <dialog
            ref={dialog}
            onClose={onClose}
            onClick={(event) => {
                if (event.target === dialog.current) onClose();
            }}
            className={cn(
                'm-auto w-[calc(100%-2rem)] rounded-2xl border border-(--border) bg-(--surface-strong) p-0 text-(--text) shadow-2xl backdrop:bg-black/45 backdrop:backdrop-blur-[2px]',
                widths[size],
            )}
        >
            {open && (
                <div className="flex max-h-[85vh] flex-col">
                    <div className="flex items-center justify-between gap-4 border-b border-(--border) px-5 py-4">
                        <h2 className="text-base font-semibold">{title}</h2>
                        <button
                            type="button"
                            onClick={onClose}
                            className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-(--text-subtle) hover:bg-(--surface-soft) hover:text-(--text)"
                            aria-label={t('ui.close')}
                        >
                            <XMarkIcon className="h-4 w-4" />
                        </button>
                    </div>
                    <div className="overflow-y-auto px-5 py-4">{children}</div>
                    {footer && <div className="flex flex-wrap justify-end gap-2 border-t border-(--border) px-5 py-3">{footer}</div>}
                </div>
            )}
        </dialog>
    );
}
