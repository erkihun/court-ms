import { router } from '@inertiajs/react';
import { type ReactNode, useState } from 'react';
import { Button, type ButtonSize, type ButtonVariant } from './Button';
import { ConfirmDialog } from './ConfirmDialog';

type Method = 'delete' | 'post' | 'patch' | 'put';

interface ConfirmButtonProps {
    /** Endpoint the confirmed action is sent to. */
    url: string;
    method?: Method;
    data?: Record<string, string | number | boolean | null>;
    title: ReactNode;
    message?: ReactNode;
    /** Button label; also the dialog's confirm label unless confirmLabel is given. */
    children: ReactNode;
    confirmLabel?: string;
    variant?: ButtonVariant;
    size?: ButtonSize;
    className?: string;
    disabled?: boolean;
    preserveScroll?: boolean;
}

/** A button that asks before sending a destructive or irreversible request (delete, approve, close…). */
export function ConfirmButton({
    url,
    method = 'delete',
    data,
    title,
    message,
    children,
    confirmLabel,
    variant = 'danger',
    size = 'sm',
    className,
    disabled = false,
    preserveScroll = true,
}: ConfirmButtonProps) {
    const [open, setOpen] = useState(false);
    const [processing, setProcessing] = useState(false);

    const confirm = () => {
        router.visit(url, {
            method,
            data,
            preserveScroll,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setOpen(false);
            },
        });
    };

    return (
        <>
            <Button variant={variant} size={size} className={className} disabled={disabled} onClick={() => setOpen(true)}>
                {children}
            </Button>
            <ConfirmDialog
                open={open}
                title={title}
                message={message}
                confirmLabel={confirmLabel ?? (typeof children === 'string' ? children : undefined)}
                variant={variant === 'danger' || variant === 'success' || variant === 'primary' ? variant : 'primary'}
                processing={processing}
                onCancel={() => setOpen(false)}
                onConfirm={confirm}
            />
        </>
    );
}
