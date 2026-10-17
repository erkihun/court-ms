import type { InputHTMLAttributes, Ref, SelectHTMLAttributes, TextareaHTMLAttributes } from 'react';
import { cn } from '@/lib/cn';
import { useControlProps } from './Field';

const invalidRing = 'border-rose-400 focus:border-rose-500 focus:ring-rose-200 dark:border-rose-500/60';

type InputProps = InputHTMLAttributes<HTMLInputElement> & { invalid?: boolean; ref?: Ref<HTMLInputElement> };

export function Input({ className, invalid, id, ...rest }: InputProps) {
    const control = useControlProps(id, invalid);

    return <input {...control} className={cn('ui-input', control['aria-invalid'] && invalidRing, className)} {...rest} />;
}

type SelectProps = SelectHTMLAttributes<HTMLSelectElement> & { invalid?: boolean; ref?: Ref<HTMLSelectElement> };

export function Select({ className, invalid, id, children, ...rest }: SelectProps) {
    const control = useControlProps(id, invalid);

    return (
        <select {...control} className={cn('ui-select', control['aria-invalid'] && invalidRing, className)} {...rest}>
            {children}
        </select>
    );
}

type TextareaProps = TextareaHTMLAttributes<HTMLTextAreaElement> & { invalid?: boolean; ref?: Ref<HTMLTextAreaElement> };

export function Textarea({ className, invalid, id, ...rest }: TextareaProps) {
    const control = useControlProps(id, invalid);

    return <textarea {...control} className={cn('ui-textarea', control['aria-invalid'] && invalidRing, className)} {...rest} />;
}

type CheckboxProps = Omit<InputHTMLAttributes<HTMLInputElement>, 'type'> & { label?: React.ReactNode };

export function Checkbox({ className, label, id, ...rest }: CheckboxProps) {
    const control = useControlProps(id);
    const input = <input type="checkbox" {...control} className={cn('ui-checkbox', className)} {...rest} />;

    return label ? (
        <label className="inline-flex cursor-pointer items-start gap-2.5 text-sm text-(--text)">
            {input}
            <span>{label}</span>
        </label>
    ) : (
        input
    );
}
