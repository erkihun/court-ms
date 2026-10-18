import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Field } from '@/components/ui/Field';
import { Input, Select, Textarea } from '@/components/ui/Input';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import { CollectionPanel, EditorCard, FormActions, HiddenPill, RowActions, useEditor } from './CollectionPanel';
import type { Step } from './types';

export const ACCENT_DOTS: Record<string, string> = {
    blue: 'bg-blue-500',
    orange: 'bg-orange-500',
    emerald: 'bg-emerald-500',
    violet: 'bg-violet-500',
    amber: 'bg-amber-500',
    pink: 'bg-pink-500',
};

function StepForm({ step, onDone }: { step: Step | null; onDone: () => void }) {
    const form = useForm({
        title: step?.title ?? '',
        meta: step?.meta ?? '',
        duration: step?.duration ?? '',
        description: step?.description ?? '',
        color: step?.color ?? 'blue',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const options = { preserveScroll: true, onSuccess: onDone };
        if (step) {
            form.put(route('admin.landing.steps.update', step.id), options);
        } else {
            form.post(route('admin.landing.steps.store'), options);
        }
    };

    return (
        <EditorCard title={step ? t('admin_landing.common.edit_step') : t('admin_landing.common.new_step')}>
            <form onSubmit={submit} className="space-y-4" noValidate>
                <div className="grid gap-4 sm:grid-cols-4">
                    <Field label={t('admin_landing.common.title')} error={form.errors.title} required className="sm:col-span-2">
                        <Input value={form.data.title} onChange={(event) => form.setData('title', event.target.value)} maxLength={255} required />
                    </Field>
                    <Field label={t('admin_landing.common.meta_label')} error={form.errors.meta}>
                        <Input value={form.data.meta} onChange={(event) => form.setData('meta', event.target.value)} maxLength={120} />
                    </Field>
                    <Field label={t('admin_landing.common.duration')} error={form.errors.duration}>
                        <Input value={form.data.duration} onChange={(event) => form.setData('duration', event.target.value)} maxLength={60} />
                    </Field>
                </div>
                <Field label={t('admin_landing.common.description')} error={form.errors.description}>
                    <Textarea value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} rows={3} maxLength={1000} />
                </Field>
                <Field label={t('admin_landing.common.colour_accent')} error={form.errors.color} className="max-w-xs">
                    <Select value={form.data.color} onChange={(event) => form.setData('color', event.target.value)}>
                        {['blue', 'orange', 'emerald', 'violet', 'amber'].map((color) => (
                            <option key={color} value={color}>
                                {t(`admin_landing.common.${color}`)}
                            </option>
                        ))}
                    </Select>
                </Field>
                <FormActions saveLabel={t('admin_landing.common.save_step')} processing={form.processing} onCancel={onDone} />
            </form>
        </EditorCard>
    );
}

export function StepsTab({ steps }: { steps: Step[] }) {
    const editor = useEditor<Step>();

    return (
        <CollectionPanel
            title={t('admin_landing.timeline.title')}
            description={t('admin_landing.timeline.description')}
            addLabel={t('admin_landing.timeline.add')}
            onAdd={editor.create}
            form={editor.isOpen && <StepForm key={editor.item?.id ?? 'new'} step={editor.item} onDone={editor.close} />}
            empty={steps.length === 0}
            emptyText={t('admin_landing.common.no_steps')}
        >
            {steps.map((step, index) => (
                <li
                    key={step.id}
                    className={cn('flex items-center gap-4 rounded-xl border border-(--border) bg-(--surface-strong) p-3', !step.isActive && 'opacity-60')}
                >
                    <span
                        className={cn(
                            'flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white',
                            ACCENT_DOTS[step.color] ?? 'bg-blue-500',
                        )}
                        aria-hidden="true"
                    >
                        {index + 1}
                    </span>
                    <div className="min-w-0 flex-1">
                        <div className="flex flex-wrap items-center gap-1.5 text-[11px] text-(--text-subtle)">
                            <HiddenPill hidden={!step.isActive} />
                            {step.meta && <span className="font-semibold">{step.meta}</span>}
                            {step.duration && <span>· {step.duration}</span>}
                        </div>
                        <p className="truncate font-semibold text-(--text)">{step.title}</p>
                        {step.description && <p className="truncate text-xs text-(--text-subtle)">{step.description}</p>}
                    </div>
                    <RowActions
                        label={step.title}
                        isActive={step.isActive}
                        toggleUrl={route('admin.landing.steps.toggle', step.id)}
                        deleteUrl={route('admin.landing.steps.destroy', step.id)}
                        onEdit={() => editor.edit(step)}
                    />
                </li>
            ))}
        </CollectionPanel>
    );
}
