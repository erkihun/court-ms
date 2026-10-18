import { CheckIcon, ExclamationTriangleIcon, InformationCircleIcon, XMarkIcon } from '@heroicons/react/24/outline';
import { usePage } from '@inertiajs/react';
import { createContext, type ReactNode, useCallback, useContext, useEffect, useRef, useState } from 'react';
import { t } from '@/lib/i18n';
import { cn } from '@/lib/cn';
import type { FlashToast, SharedProps, ToastType } from '@/types';

interface Toast extends FlashToast {
    id: number;
    leaving: boolean;
}

type Push = (message: string, type?: ToastType, details?: string[]) => void;

const ToastContext = createContext<Push>(() => undefined);

/** Show a toast from anywhere inside a layout. */
export function useToast(): Push {
    return useContext(ToastContext);
}

const tones: Record<ToastType, string> = {
    success: 'border-emerald-200/80 bg-emerald-600 shadow-emerald-950/15 dark:border-emerald-400/25',
    error: 'border-rose-200/90 bg-rose-600 shadow-rose-950/15 dark:border-rose-400/25',
    warning: 'border-amber-200/90 bg-amber-500 shadow-amber-950/15 dark:border-amber-400/25',
    info: 'border-blue-200/90 bg-blue-600 shadow-blue-950/15 dark:border-blue-400/25',
};

const icons: Record<ToastType, typeof CheckIcon> = {
    success: CheckIcon,
    error: ExclamationTriangleIcon,
    warning: ExclamationTriangleIcon,
    info: InformationCircleIcon,
};

/**
 * Renders the server's flash messages (shared `toasts` prop) after every
 * visit, plus anything pushed with useToast().
 */
export function ToastProvider({ children }: { children: ReactNode }) {
    const page = usePage<SharedProps>();
    const [toasts, setToasts] = useState<Toast[]>([]);
    const nextId = useRef(0);

    const dismiss = useCallback((id: number) => {
        setToasts((current) => current.map((toast) => (toast.id === id ? { ...toast, leaving: true } : toast)));
        window.setTimeout(() => setToasts((current) => current.filter((toast) => toast.id !== id)), 250);
    }, []);

    const push = useCallback<Push>(
        (message, type = 'success', details = []) => {
            const id = ++nextId.current;
            setToasts((current) => [...current, { id, message, type, details, leaving: false }]);
            window.setTimeout(() => dismiss(id), type === 'error' ? 8000 : 4500);
        },
        [dismiss],
    );

    // A new page object arrives on every visit; flash data is consumed server-side, so it never repeats.
    useEffect(() => {
        page.props.toasts?.forEach((toast) => push(toast.message, toast.type, toast.details));
    }, [page, push]);

    return (
        <ToastContext.Provider value={push}>
            {children}
            <div
                className="pointer-events-none fixed left-1/2 top-4 z-1000 flex w-[min(94vw,34rem)] -translate-x-1/2 flex-col items-stretch gap-3"
                aria-live="polite"
            >
                {toasts.map((toast) => {
                    const Icon = icons[toast.type];

                    return (
                        <div
                            key={toast.id}
                            role={toast.type === 'error' ? 'alert' : 'status'}
                            className={cn(
                                'pointer-events-auto relative overflow-hidden rounded-2xl border px-4 py-3 text-white shadow-2xl backdrop-blur-xl transition duration-250',
                                tones[toast.type],
                                toast.leaving ? '-translate-y-3 scale-95 opacity-0' : 'animate-[toast-in_300ms_ease-out]',
                            )}
                        >
                            <div className="flex items-start gap-3 pe-8">
                                <span className="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/18 ring-1 ring-white/25">
                                    <Icon className="h-4 w-4" strokeWidth={2.4} aria-hidden="true" />
                                </span>
                                <div className="min-w-0 flex-1">
                                    <p className="text-sm font-semibold leading-6">{toast.message}</p>
                                    {toast.details.length > 0 && (
                                        <ul className="mt-1 max-h-28 list-disc space-y-0.5 overflow-y-auto ps-4 text-xs leading-5 text-white/85">
                                            {toast.details.slice(0, 4).map((detail) => (
                                                <li key={detail}>{detail}</li>
                                            ))}
                                        </ul>
                                    )}
                                </div>
                            </div>
                            <button
                                type="button"
                                onClick={() => dismiss(toast.id)}
                                className="absolute end-3 top-3 inline-flex h-7 w-7 items-center justify-center rounded-full text-white/75 transition hover:bg-white/15 hover:text-white focus:outline-hidden focus:ring-2 focus:ring-white/40"
                                aria-label={t('ui.close')}
                            >
                                <XMarkIcon className="h-4 w-4" strokeWidth={2.2} />
                            </button>
                        </div>
                    );
                })}
            </div>
        </ToastContext.Provider>
    );
}
