import { createContext, type ReactNode, useContext, useId } from 'react';
import { cn } from '@/lib/cn';

interface FieldContextValue {
    id: string;
    describedBy: string | undefined;
    invalid: boolean;
}

const FieldContext = createContext<FieldContextValue | null>(null);

/** Lets a control inside <Field> pick up its id and ARIA wiring. */
export function useField(): FieldContextValue | null {
    return useContext(FieldContext);
}

interface FieldProps {
    label?: ReactNode;
    error?: string | null;
    hint?: ReactNode;
    required?: boolean;
    className?: string;
    children: ReactNode;
}

/** Label + control + hint + validation error, wired together for screen readers. */
export function Field({ label, error, hint, required = false, className, children }: FieldProps) {
    const id = useId();
    const hintId = hint ? `${id}-hint` : undefined;
    const errorId = error ? `${id}-error` : undefined;
    const describedBy = [hintId, errorId].filter(Boolean).join(' ') || undefined;

    return (
        <FieldContext.Provider value={{ id, describedBy, invalid: !!error }}>
            <div className={cn('space-y-1.5', className)}>
                {label && (
                    <label htmlFor={id} className="block text-sm font-medium text-(--text)">
                        {label}
                        {required && (
                            <span className="ms-0.5 text-rose-600" aria-hidden="true">
                                *
                            </span>
                        )}
                    </label>
                )}
                {children}
                {hint && !error && (
                    <p id={hintId} className="text-xs text-(--text-subtle)">
                        {hint}
                    </p>
                )}
                {error && (
                    <p id={errorId} className="text-xs font-medium text-rose-600 dark:text-rose-400">
                        {error}
                    </p>
                )}
            </div>
        </FieldContext.Provider>
    );
}

/** id / aria props for a control rendered inside <Field>. */
export function useControlProps(explicitId?: string, explicitInvalid?: boolean) {
    const field = useField();

    return {
        id: explicitId ?? field?.id,
        'aria-describedby': field?.describedBy,
        'aria-invalid': explicitInvalid ?? field?.invalid ?? undefined,
    };
}
