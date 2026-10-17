import { useShared } from '@/hooks/useShared';
import type { FlashToast } from '@/types';

interface AuthAlertsProps {
    /** How to show validation errors: the first message, every message, or not at all (fields show their own). */
    errors?: 'first' | 'list' | 'none';
}

const tone: Record<FlashToast['type'], string> = {
    success: 'auth-alert-success',
    info: 'auth-alert-info',
    warning: 'auth-alert-warning',
    error: 'auth-alert-error',
};

/** Flash messages and validation errors rendered inline, as the auth screens have no toaster. */
export function AuthAlerts({ errors = 'first' }: AuthAlertsProps) {
    const { toasts } = useShared();

    return (
        <>
            {toasts.map((toast, index) => {
                const isValidationSummary = toast.details.length > 0;
                if (isValidationSummary && errors === 'none') return null;

                return (
                    <div
                        key={`${toast.type}-${index}`}
                        role={toast.type === 'error' ? 'alert' : 'status'}
                        className={`auth-alert ${tone[toast.type]} mb-4`}
                    >
                        {!isValidationSummary ? (
                            toast.message
                        ) : errors === 'list' ? (
                            <ul className="ml-5 list-disc">
                                {toast.details.map((detail) => (
                                    <li key={detail}>{detail}</li>
                                ))}
                            </ul>
                        ) : (
                            toast.details[0]
                        )}
                    </div>
                );
            })}
        </>
    );
}
