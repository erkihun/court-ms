import type { ReactNode } from 'react';

interface AuthFieldProps {
    id: string;
    label: string;
    error?: string;
    centerLabel?: boolean;
    children: ReactNode;
}

/** Label + control + error in the auth card style. The control must use `id` and `${id}-error`. */
export function AuthField({ id, label, error, centerLabel = false, children }: AuthFieldProps) {
    return (
        <div>
            <label htmlFor={id} className={centerLabel ? 'auth-label text-center' : 'auth-label'}>
                {label}
            </label>
            {children}
            {error && (
                <div id={`${id}-error`} className="mt-1 text-xs text-red-600">
                    {error}
                </div>
            )}
        </div>
    );
}

/** aria wiring for a control inside <AuthField>. */
export function fieldAria(id: string, error?: string) {
    return {
        id,
        'aria-invalid': error ? true : undefined,
        'aria-describedby': error ? `${id}-error` : undefined,
    } as const;
}
