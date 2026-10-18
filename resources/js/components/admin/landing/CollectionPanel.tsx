import { EyeIcon, EyeSlashIcon, PencilSquareIcon, PlusIcon, TrashIcon } from '@heroicons/react/24/outline';
import { router } from '@inertiajs/react';
import { type ReactNode, useState } from 'react';
import { Button } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { ConfirmDialog } from '@/components/ui/ConfirmDialog';
import { t } from '@/lib/i18n';

/** Add/edit state for one list on the landing manager: null = closed, 'new' = creating, an item = editing it. */
export function useEditor<T>() {
    const [editing, setEditing] = useState<T | 'new' | null>(null);

    return {
        editing,
        item: editing !== null && editing !== 'new' ? editing : null,
        isOpen: editing !== null,
        create: () => setEditing('new'),
        edit: (item: T) => {
            setEditing(item);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        close: () => setEditing(null),
    };
}

interface CollectionPanelProps {
    title: string;
    description: string;
    addLabel: string;
    onAdd: () => void;
    /** The add/edit form card, shown above the list while open. */
    form: ReactNode;
    empty: boolean;
    emptyText: string;
    children: ReactNode;
}

export function CollectionPanel({ title, description, addLabel, onAdd, form, empty, emptyText, children }: CollectionPanelProps) {
    return (
        <section className="space-y-4">
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 className="text-base font-semibold text-(--text)">{title}</h2>
                    <p className="text-sm text-(--text-subtle)">{description}</p>
                </div>
                <Button onClick={onAdd}>
                    <PlusIcon className="h-4 w-4" />
                    {addLabel}
                </Button>
            </div>
            {form}
            {empty ? (
                <div className="rounded-xl border border-dashed border-(--border) bg-(--surface-soft) px-4 py-10 text-center text-sm text-(--text-subtle)">
                    {emptyText}
                </div>
            ) : (
                <ul className="space-y-2">{children}</ul>
            )}
        </section>
    );
}

/** The card wrapping an add/edit form. */
export function EditorCard({ title, children }: { title: string; children: ReactNode }) {
    return (
        <Card className="border-[rgb(var(--ac)/0.35)]">
            <h3 className="mb-4 text-sm font-semibold text-(--text)">{title}</h3>
            {children}
        </Card>
    );
}

interface RowActionsProps {
    isActive: boolean;
    toggleUrl: string;
    deleteUrl: string;
    onEdit: () => void;
    label: string;
}

/** Show/hide, edit and delete controls for one landing item. */
export function RowActions({ isActive, toggleUrl, deleteUrl, onEdit, label }: RowActionsProps) {
    const [confirming, setConfirming] = useState(false);
    const [processing, setProcessing] = useState(false);
    const iconButton = 'inline-flex h-8 w-8 items-center justify-center rounded-lg text-(--text-subtle) hover:bg-(--surface-soft) hover:text-(--text)';

    return (
        <div className="flex shrink-0 items-center gap-1">
            <button
                type="button"
                className={iconButton}
                title={isActive ? t('admin_landing.common.hide') : t('admin_landing.common.show')}
                aria-label={`${isActive ? t('admin_landing.common.hide') : t('admin_landing.common.show')}: ${label}`}
                aria-pressed={!isActive}
                onClick={() => router.patch(toggleUrl, {}, { preserveScroll: true })}
            >
                {isActive ? <EyeIcon className="h-4 w-4" /> : <EyeSlashIcon className="h-4 w-4" />}
            </button>
            <button
                type="button"
                className={iconButton}
                title={t('admin_landing.common.edit')}
                aria-label={`${t('admin_landing.common.edit')}: ${label}`}
                onClick={onEdit}
            >
                <PencilSquareIcon className="h-4 w-4" />
            </button>
            <button
                type="button"
                className={`${iconButton} hover:text-rose-600`}
                title={t('admin_landing.common.delete')}
                aria-label={`${t('admin_landing.common.delete')}: ${label}`}
                onClick={() => setConfirming(true)}
            >
                <TrashIcon className="h-4 w-4" />
            </button>
            <ConfirmDialog
                open={confirming}
                title={t('admin_landing.common.confirm_delete')}
                message={label}
                confirmLabel={t('admin_landing.common.delete')}
                processing={processing}
                onCancel={() => setConfirming(false)}
                onConfirm={() =>
                    router.delete(deleteUrl, {
                        preserveScroll: true,
                        onStart: () => setProcessing(true),
                        onFinish: () => {
                            setProcessing(false);
                            setConfirming(false);
                        },
                    })
                }
            />
        </div>
    );
}

/** Pill shown on hidden items. */
export function HiddenPill({ hidden }: { hidden: boolean }) {
    return hidden ? (
        <span className="rounded-full bg-(--surface-soft) px-2 py-0.5 text-[11px] font-semibold text-(--text-subtle)">{t('admin_landing.common.hidden')}</span>
    ) : null;
}

export function FormActions({ saveLabel, processing, onCancel }: { saveLabel: string; processing: boolean; onCancel: () => void }) {
    return (
        <div className="flex gap-2 pt-1">
            <Button type="submit" loading={processing}>
                {saveLabel}
            </Button>
            <Button variant="secondary" onClick={onCancel}>
                {t('admin_landing.common.cancel')}
            </Button>
        </div>
    );
}

/** Label suffix for optional fields: "(optional)". */
export function Optional({ extra }: { extra?: string }) {
    return (
        <span className="font-normal text-(--text-subtle)">
            {' '}
            ({t('admin_landing.common.optional')}
            {extra ? `, ${extra}` : ''})
        </span>
    );
}
