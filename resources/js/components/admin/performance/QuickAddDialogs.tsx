import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/Button';
import { Field } from '@/components/ui/Field';
import { Checkbox, Input, Select, Textarea } from '@/components/ui/Input';
import { Modal } from '@/components/ui/Modal';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface DialogProps {
    open: boolean;
    onClose: () => void;
}

/** The settings endpoints redirect back to `redirect_to` when it is on this site. */
const here = () => window.location.href;

export function AddCriterionDialog({ open, onClose, categories }: DialogProps & { categories: Array<{ slug: string; name: string }> }) {
    const form = useForm({
        name: '',
        name_am: '',
        category: categories[0]?.slug ?? '',
        weight: '10',
        sort_order: '0',
        active: true,
        description: '',
    });

    const close = () => {
        form.reset();
        form.clearErrors();
        onClose();
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.transform((data) => ({ ...data, redirect_to: here() }));
        form.post(route('settings.performance-evaluation.criteria.store'), { preserveScroll: true, onSuccess: close });
    };

    return (
        <Modal
            open={open}
            onClose={close}
            size="lg"
            title={t('performance.settings.create_criterion_title')}
            footer={
                <>
                    <Button variant="secondary" onClick={close}>
                        {t('performance.actions.cancel')}
                    </Button>
                    <Button type="submit" form="add-criterion-form" loading={form.processing}>
                        {t('performance.settings.actions.add_criterion')}
                    </Button>
                </>
            }
        >
            <p className="mb-4 text-sm text-(--text-muted)">{t('performance.settings.create_criterion_subtitle')}</p>
            <form id="add-criterion-form" onSubmit={submit} className="grid gap-4 sm:grid-cols-2" noValidate>
                <Field label={t('performance.settings.fields.name')} error={form.errors.name} required>
                    <Input value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} maxLength={255} required />
                </Field>
                <Field label={t('performance.settings.fields.name_am')} error={form.errors.name_am}>
                    <Input value={form.data.name_am} onChange={(event) => form.setData('name_am', event.target.value)} maxLength={255} />
                </Field>
                <Field label={t('performance.settings.fields.category')} error={form.errors.category} required>
                    <Select value={form.data.category} onChange={(event) => form.setData('category', event.target.value)} required>
                        {categories.map((category) => (
                            <option key={category.slug} value={category.slug}>
                                {category.name}
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
                    <Textarea rows={3} value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} maxLength={1000} />
                </Field>
            </form>
        </Modal>
    );
}

export function AddCategoryDialog({ open, onClose }: DialogProps) {
    const form = useForm({ name: '', name_am: '', slug: '', sort_order: '0', active: true });

    const close = () => {
        form.reset();
        form.clearErrors();
        onClose();
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.transform((data) => ({ ...data, redirect_to: here() }));
        form.post(route('settings.performance-evaluation.categories.store'), { preserveScroll: true, onSuccess: close });
    };

    return (
        <Modal
            open={open}
            onClose={close}
            title={t('performance.settings.create_category_title')}
            footer={
                <>
                    <Button variant="secondary" onClick={close}>
                        {t('performance.actions.cancel')}
                    </Button>
                    <Button type="submit" form="add-category-form" loading={form.processing}>
                        {t('performance.settings.actions.add_category')}
                    </Button>
                </>
            }
        >
            <p className="mb-4 text-sm text-(--text-muted)">{t('performance.settings.create_category_subtitle')}</p>
            <form id="add-category-form" onSubmit={submit} className="space-y-4" noValidate>
                <Field label={t('performance.settings.fields.name')} error={form.errors.name} required>
                    <Input value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} maxLength={255} required />
                </Field>
                <Field label={t('performance.settings.fields.name_am')} error={form.errors.name_am}>
                    <Input value={form.data.name_am} onChange={(event) => form.setData('name_am', event.target.value)} maxLength={255} />
                </Field>
                <Field label={t('performance.settings.fields.slug')} error={form.errors.slug} hint={t('performance.settings.slug_hint')}>
                    <Input value={form.data.slug} onChange={(event) => form.setData('slug', event.target.value)} maxLength={50} placeholder="efficiency" />
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
                <Checkbox
                    label={t('performance.settings.active')}
                    checked={form.data.active}
                    onChange={(event) => form.setData('active', event.target.checked)}
                />
            </form>
        </Modal>
    );
}
