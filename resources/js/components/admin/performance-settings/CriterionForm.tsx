import { useForm } from '@inertiajs/react';
import type { FormEvent, ReactNode } from 'react';
import { Button } from '@/components/ui/Button';
import { Field } from '@/components/ui/Field';
import { Checkbox, Input, Select, Textarea } from '@/components/ui/Input';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { CategoryOption, Criterion } from './types';

interface CriterionFormProps {
    categories: CategoryOption[];
    /** The criterion being edited; null creates a new one. */
    criterion?: Criterion | null;
    onSuccess?: () => void;
    /** Rendered before the submit button (cancel / back). */
    cancel?: ReactNode;
}

/** Create/edit form for one scoring criterion, used on the settings page and the standalone create page. */
export function CriterionForm({ categories, criterion = null, onSuccess, cancel }: CriterionFormProps) {
    const form = useForm({
        name: criterion?.name ?? '',
        name_am: criterion?.nameAm ?? '',
        category: criterion?.category ?? categories[0]?.value ?? '',
        weight: String(criterion?.weight ?? 10),
        sort_order: String(criterion?.sortOrder ?? 0),
        active: criterion?.active ?? true,
        description: criterion?.description ?? '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const options = { preserveScroll: true, onSuccess: () => onSuccess?.() };
        if (criterion) {
            form.patch(route('settings.performance-evaluation.criteria.update', criterion.id), options);
        } else {
            form.post(route('settings.performance-evaluation.criteria.store'), options);
        }
    };

    return (
        <form onSubmit={submit} className="space-y-5" noValidate>
            <div className="grid gap-4 sm:grid-cols-2">
                <Field label={t('performance.settings.fields.name')} error={form.errors.name} required>
                    <Input value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} maxLength={255} required />
                </Field>
                <Field label={t('performance.settings.fields.name_am')} error={form.errors.name_am}>
                    <Input value={form.data.name_am} onChange={(event) => form.setData('name_am', event.target.value)} maxLength={255} />
                </Field>
                <Field label={t('performance.settings.fields.category')} error={form.errors.category} required>
                    <Select value={form.data.category} onChange={(event) => form.setData('category', event.target.value)} required>
                        {categories.map((category) => (
                            <option key={category.value} value={category.value}>
                                {category.label}
                            </option>
                        ))}
                    </Select>
                </Field>
                <Field label={t('performance.settings.fields.weight')} error={form.errors.weight} required>
                    <Input type="number" min={0} max={100} value={form.data.weight} onChange={(event) => form.setData('weight', event.target.value)} required />
                </Field>
                <Field label={t('performance.settings.fields.order')} error={form.errors.sort_order}>
                    <Input
                        type="number"
                        min={0}
                        max={65535}
                        value={form.data.sort_order}
                        onChange={(event) => form.setData('sort_order', event.target.value)}
                    />
                </Field>
                <div className="flex items-end pb-2">
                    <Checkbox
                        label={t('performance.settings.active')}
                        checked={form.data.active}
                        onChange={(event) => form.setData('active', event.target.checked)}
                    />
                </div>
                <Field label={t('performance.settings.fields.description')} error={form.errors.description} className="sm:col-span-2">
                    <Textarea value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} rows={3} maxLength={1000} />
                </Field>
            </div>
            <div className="flex justify-end gap-2">
                {cancel}
                <Button type="submit" loading={form.processing}>
                    {criterion ? t('performance.settings.actions.update') : t('performance.settings.actions.add_criterion')}
                </Button>
            </div>
        </form>
    );
}
