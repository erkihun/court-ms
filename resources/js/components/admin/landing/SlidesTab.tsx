import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Field } from '@/components/ui/Field';
import { Checkbox, Input, Select, Textarea } from '@/components/ui/Input';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import { CollectionPanel, EditorCard, FormActions, HiddenPill, Optional, RowActions, useEditor } from './CollectionPanel';
import type { Slide } from './types';

const SWATCHES: Record<Slide['bgStyle'], string> = {
    blue: 'bg-linear-to-br from-blue-700 to-blue-500',
    orange: 'bg-linear-to-br from-orange-600 to-amber-400',
    emerald: 'bg-linear-to-br from-emerald-700 to-teal-400',
};

function SlideForm({ slide, onDone }: { slide: Slide | null; onDone: () => void }) {
    const form = useForm({
        badge: slide?.badge ?? '',
        title: slide?.title ?? '',
        description: slide?.description ?? '',
        primary_label: slide?.primaryLabel ?? '',
        primary_href: slide?.primaryHref ?? '',
        secondary_label: slide?.secondaryLabel ?? '',
        secondary_href: slide?.secondaryHref ?? '',
        bg_style: slide?.bgStyle ?? 'blue',
        bg_image: null as File | null,
        remove_bg_image: false,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const options = { forceFormData: true, preserveScroll: true, onSuccess: onDone };
        if (slide) {
            form.transform((data) => ({ ...data, _method: 'put' }));
            form.post(route('admin.landing.slides.update', slide.id), options);
        } else {
            form.post(route('admin.landing.slides.store'), options);
        }
    };

    return (
        <EditorCard title={slide ? t('admin_landing.common.edit_slide') : t('admin_landing.common.new_slide')}>
            <form onSubmit={submit} className="space-y-4" noValidate>
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field
                        label={
                            <>
                                {t('admin_landing.common.badge_text')}
                                <Optional />
                            </>
                        }
                        error={form.errors.badge}
                    >
                        <Input value={form.data.badge} onChange={(event) => form.setData('badge', event.target.value)} maxLength={120} />
                    </Field>
                    <Field label={t('admin_landing.common.background_style')} error={form.errors.bg_style} required>
                        <Select value={form.data.bg_style} onChange={(event) => form.setData('bg_style', event.target.value as Slide['bgStyle'])}>
                            <option value="blue">{t('admin_landing.common.blue')}</option>
                            <option value="orange">{t('admin_landing.common.orange')}</option>
                            <option value="emerald">{t('admin_landing.common.emerald')}</option>
                        </Select>
                    </Field>
                </div>
                <Field label={t('admin_landing.common.title')} error={form.errors.title} required>
                    <Input value={form.data.title} onChange={(event) => form.setData('title', event.target.value)} maxLength={255} required />
                </Field>
                <Field label={t('admin_landing.common.description')} error={form.errors.description}>
                    <Textarea value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} rows={3} maxLength={1000} />
                </Field>
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label={t('admin_landing.common.primary_button_label')} error={form.errors.primary_label} required>
                        <Input
                            value={form.data.primary_label}
                            onChange={(event) => form.setData('primary_label', event.target.value)}
                            maxLength={120}
                            required
                        />
                    </Field>
                    <Field label={t('admin_landing.common.primary_button_url')} error={form.errors.primary_href} required>
                        <Input
                            value={form.data.primary_href}
                            onChange={(event) => form.setData('primary_href', event.target.value)}
                            maxLength={500}
                            placeholder="/applicant/register"
                            required
                        />
                    </Field>
                    <Field label={t('admin_landing.common.secondary_button_label')} error={form.errors.secondary_label}>
                        <Input value={form.data.secondary_label} onChange={(event) => form.setData('secondary_label', event.target.value)} maxLength={120} />
                    </Field>
                    <Field label={t('admin_landing.common.secondary_button_url')} error={form.errors.secondary_href}>
                        <Input
                            value={form.data.secondary_href}
                            onChange={(event) => form.setData('secondary_href', event.target.value)}
                            maxLength={500}
                            placeholder="/applicant/login"
                        />
                    </Field>
                </div>
                <Field
                    label={
                        <>
                            {t('admin_landing.common.background_image')}
                            <Optional extra="3 MB" />
                        </>
                    }
                    error={form.errors.bg_image}
                    hint={t('admin_landing.common.image_help')}
                >
                    <Input type="file" accept="image/*" onChange={(event) => form.setData('bg_image', event.target.files?.[0] ?? null)} />
                </Field>
                {slide?.bgImageUrl && (
                    <div className="flex items-center gap-3">
                        <img src={slide.bgImageUrl} alt="" className="h-16 w-28 rounded-lg object-cover" />
                        <Checkbox
                            label={t('admin_landing.common.remove_current_image')}
                            checked={form.data.remove_bg_image}
                            onChange={(event) => form.setData('remove_bg_image', event.target.checked)}
                        />
                    </div>
                )}
                <FormActions saveLabel={t('admin_landing.common.save_slide')} processing={form.processing} onCancel={onDone} />
            </form>
        </EditorCard>
    );
}

export function SlidesTab({ slides }: { slides: Slide[] }) {
    const editor = useEditor<Slide>();

    return (
        <CollectionPanel
            title={t('admin_landing.slides.title')}
            description={t('admin_landing.slides.description')}
            addLabel={t('admin_landing.slides.add')}
            onAdd={editor.create}
            form={editor.isOpen && <SlideForm key={editor.item?.id ?? 'new'} slide={editor.item} onDone={editor.close} />}
            empty={slides.length === 0}
            emptyText={t('admin_landing.common.no_slides')}
        >
            {slides.map((slide) => (
                <li
                    key={slide.id}
                    className={cn('flex items-center gap-4 rounded-xl border border-(--border) bg-(--surface-strong) p-3', !slide.isActive && 'opacity-60')}
                >
                    {slide.bgImageUrl ? (
                        <img src={slide.bgImageUrl} alt="" className="h-12 w-20 shrink-0 rounded-lg object-cover" />
                    ) : (
                        <span className={cn('h-12 w-20 shrink-0 rounded-lg', SWATCHES[slide.bgStyle])} aria-hidden="true" />
                    )}
                    <div className="min-w-0 flex-1">
                        <div className="flex flex-wrap items-center gap-1.5">
                            {slide.badge && (
                                <span className="rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">
                                    {slide.badge}
                                </span>
                            )}
                            <HiddenPill hidden={!slide.isActive} />
                        </div>
                        <p className="truncate font-semibold text-(--text)">{slide.title}</p>
                        {slide.description && <p className="truncate text-xs text-(--text-subtle)">{slide.description}</p>}
                        <p className="truncate font-mono text-[11px] text-(--text-subtle)">
                            {slide.primaryLabel} → {slide.primaryHref}
                        </p>
                    </div>
                    <RowActions
                        label={slide.title}
                        isActive={slide.isActive}
                        toggleUrl={route('admin.landing.slides.toggle', slide.id)}
                        deleteUrl={route('admin.landing.slides.destroy', slide.id)}
                        onEdit={() => editor.edit(slide)}
                    />
                </li>
            ))}
        </CollectionPanel>
    );
}
