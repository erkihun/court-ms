import { Link } from '@inertiajs/react';
import type { ButtonHTMLAttributes, ReactNode } from 'react';
import { cn } from '@/lib/cn';

export type ButtonVariant = 'primary' | 'secondary' | 'danger' | 'success' | 'ghost';
export type ButtonSize = 'sm' | 'md' | 'lg';

const base =
    'inline-flex items-center justify-center gap-2 rounded-lg font-medium transition-fast hover:scale-(--scale-hover) active:scale-(--scale-press) focus:outline-hidden focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:scale-100';

const sizes: Record<ButtonSize, string> = {
    sm: 'px-3 py-1.5 text-xs',
    md: 'px-4 py-2 text-sm',
    lg: 'px-5 py-2.5 text-sm',
};

const variants: Record<ButtonVariant, string> = {
    primary: 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
    secondary: 'border border-(--border-strong) bg-(--surface-strong) text-(--text-muted) hover:bg-(--surface-soft) focus:ring-blue-500',
    danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    success: 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
    ghost: 'text-(--text-muted) hover:bg-(--surface-soft) focus:ring-blue-500',
};

export function buttonClasses(variant: ButtonVariant = 'primary', size: ButtonSize = 'md', className?: string): string {
    return cn(base, sizes[size], variants[variant], className);
}

interface CommonProps {
    variant?: ButtonVariant;
    size?: ButtonSize;
    className?: string;
    children: ReactNode;
}

type ButtonProps = CommonProps & ButtonHTMLAttributes<HTMLButtonElement> & { loading?: boolean };

export function Button({ variant, size, className, loading = false, disabled, type = 'button', children, ...rest }: ButtonProps) {
    return (
        <button type={type} className={buttonClasses(variant, size, className)} disabled={disabled || loading} aria-busy={loading || undefined} {...rest}>
            {loading && <span className="h-3.5 w-3.5 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true" />}
            {children}
        </button>
    );
}

type LinkButtonProps = CommonProps & {
    href: string;
    /** Use a plain anchor: downloads, files, and pages outside the React app. */
    external?: boolean;
    target?: string;
    title?: string;
};

export function LinkButton({ variant, size, className, href, external = false, target, title, children }: LinkButtonProps) {
    const classes = buttonClasses(variant, size, className);

    return external ? (
        <a href={href} className={classes} target={target} title={title} rel={target === '_blank' ? 'noopener noreferrer' : undefined}>
            {children}
        </a>
    ) : (
        <Link href={href} className={classes} title={title}>
            {children}
        </Link>
    );
}
