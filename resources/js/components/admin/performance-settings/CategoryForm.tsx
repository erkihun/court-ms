import { useForm } from '@inertiajs/react';
import type { FormEvent, ReactNode } from 'react';
import { Button } from '@/components/ui/Button';
import { Field } from '@/components/ui/Field';
import { Checkbox, Input } from '@/components/ui/Input';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

/** Form for a new criterion category, used on the settings page and the standalone create page. */
export function CategoryForm({ onSuccess, cancel }: { onSuccess?: () => void; cancel?: ReactNode }) {
    const form = useForm({ name: '', name_am: '', slug: '', sort_order: '0', active: true });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('settings.performance-evaluation.categories.store'), { preserveScroll: true, onSuccess: () => onSuccess?.() });
    };

    return (
        <form onSubmit={submit} className="space-y-4" noValidate>
            <Field label={t('performance.settings.fields.name')} error={form.errors.name} required>
                <Input value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} maxLength={255} required />
            </Field>
            <Field label={t('performance.settings.fields.name_am')} error={form.errors.name_am}>
                <Input value={form.data.name_am} onChange={(event) => form.setData('name_am', event.target.value)} maxLength={255} />
            </Field>
            <Field label={t('performance.settings.fields.slug')} error={form.errors.slug} hint={t('performance.settings.slug_hint')}>
                <Input value={form.data.slug} onChange={(event) => form.setData('slug', event.target.value)} maxLength={50} className="font-mono text-sm" />
            </Field>
            <Field label={t('performance.settings.fields.order')} error={form.errors.sort_order}>
                <Input type="number" min={0} max={65535} value={form.data.sort_order} onChange={(event) => form.setData('sort_order', event.target.value)} />
            </Field>
            <Checkbox label={t('performance.settings.active')} checked={form.data.active} onChange={(event) => form.setData('active', event.target.checked)} />
            <div className="flex justify-end gap-2">
                {cancel}
                <Button type="submit" loading={form.processing}>
                    {t('performance.settings.actions.add_category')}
                </Button>
            </div>
        </form>
    );
}
