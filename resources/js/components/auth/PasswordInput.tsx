import { EyeIcon, EyeSlashIcon } from '@heroicons/react/24/outline';
import { type InputHTMLAttributes, type ReactNode, useState } from 'react';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';

interface PasswordInputProps extends Omit<InputHTMLAttributes<HTMLInputElement>, 'type'> {
    /** Classes for the wrapper; the input itself takes `className`. */
    wrapperClassName?: string;
    toggleClassName?: string;
    /** Rendered before the input (e.g. a leading icon positioned by the page's CSS). */
    leading?: ReactNode;
}

/** Password field with a show/hide toggle. */
export function PasswordInput({ wrapperClassName, toggleClassName, leading, className, ...props }: PasswordInputProps) {
    const [visible, setVisible] = useState(false);
    const Icon = visible ? EyeSlashIcon : EyeIcon;

    return (
        <div className={cn('relative', wrapperClassName)}>
            {leading}
            <input {...props} type={visible ? 'text' : 'password'} className={className} />
            <button
                type="button"
                onClick={() => setVisible((value) => !value)}
                className={toggleClassName ?? 'absolute end-3 top-1/2 -translate-y-1/2 rounded-md p-1 text-slate-500 hover:text-slate-700'}
                aria-label={visible ? t('auth.hide_password') : t('auth.show_password')}
                aria-pressed={visible}
            >
                <Icon className="h-[1.05rem] w-[1.05rem]" strokeWidth={1.7} />
            </button>
        </div>
    );
}
