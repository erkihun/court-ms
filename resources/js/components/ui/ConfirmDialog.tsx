import type { ReactNode } from 'react';
import { t } from '@/lib/i18n';
import { Button, type ButtonVariant } from './Button';
import { Modal } from './Modal';

interface ConfirmDialogProps {
    open: boolean;
    onCancel: () => void;
    onConfirm: () => void;
    title: ReactNode;
    message?: ReactNode;
    confirmLabel?: string;
    variant?: ButtonVariant;
    processing?: boolean;
}

/** "Are you sure?" before destructive actions. */
export function ConfirmDialog({ open, onCancel, onConfirm, title, message, confirmLabel, variant = 'danger', processing = false }: ConfirmDialogProps) {
    return (
        <Modal
            open={open}
            onClose={onCancel}
            title={title}
            size="sm"
            footer={
                <>
                    <Button variant="secondary" onClick={onCancel} disabled={processing}>
                        {t('ui.cancel')}
                    </Button>
                    <Button variant={variant} onClick={onConfirm} loading={processing}>
                        {confirmLabel ?? t('ui.confirm')}
                    </Button>
                </>
            }
        >
            {message && <p className="text-sm text-(--text-muted)">{message}</p>}
        </Modal>
    );
}
