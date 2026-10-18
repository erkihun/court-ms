import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Field } from '@/components/ui/Field';
import { Input, Select, Textarea } from '@/components/ui/Input';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import { CollectionPanel, EditorCard, FormActions, HiddenPill, RowActions, useEditor } from './CollectionPanel';
import { ACCENT_DOTS } from './StepsTab';
import type { Service } from './types';

const ICONS: Array<[string, string]> = [
    ['document', 'admin_landing.common.document_upload'],
    ['calendar', 'admin_landing.common.calendar_hearing'],
    ['lock', 'admin_landing.common.lock_evidence'],
    ['chart', 'admin_landing.common.chart_tracking'],
    ['database', 'admin_landing.common.database'],
    ['chat', 'admin_landing.common.chat_dispute'],
];

function ServiceForm({ service, onDone }: { service: Service | null; onDone: () => void }) {
    const form = useForm({
        title: service?.title ?? '',
        meta: service?.meta ?? '',
        description: service?.description ?? '',
        features: service?.features.join('\n') ?? '',
        icon_type: service?.iconType ?? 'document',
        accent: service?.accent ?? 'blue',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const options = { preserveScroll: true, onSuccess: onDone };
        if (service) {
            form.put(route('admin.landing.services.update', service.id), options);
        } else {
            form.post(route('admin.landing.services.store'), options);
        }
    };

    return (
        <EditorCard title={service ? t('admin_landing.common.edit_service') : t('admin_landing.common.new_service')}>
            <form onSubmit={submit} className="space-y-4" noValidate>
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label={t('admin_landing.common.title')} error={form.errors.title} required>
                        <Input value={form.data.title} onChange={(event) => form.setData('title', event.target.value)} maxLength={255} required />
                    </Field>
                    <Field label={t('admin_landing.common.meta_label')} error={form.errors.meta}>
                        <Input value={form.data.meta} onChange={(event) => form.setData('meta', event.target.value)} maxLength={120} />
                    </Field>
                </div>
                <Field label={t('admin_landing.common.description')} error={form.errors.description}>
                    <Textarea value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} rows={2} maxLength={1000} />
                </Field>
                <Field label={`${t('admin_landing.common.features')} (${t('admin_landing.common.one_per_line')})`} error={form.errors.features}>
                    <Textarea value={form.data.features} onChange={(event) => form.setData('features', event.target.value)} rows={4} />
                </Field>
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label={t('admin_landing.common.icon')} error={form.errors.icon_type}>
                        <Select value={form.data.icon_type} onChange={(event) => form.setData('icon_type', event.target.value)}>
                            {ICONS.map(([value, label]) => (
                                <option key={value} value={value}>
                                    {t(label)}
                                </option>
                            ))}
                        </Select>
                    </Field>
                    <Field label={t('admin_landing.common.accent_colour')} error={form.errors.accent}>
                        <Select value={form.data.accent} onChange={(event) => form.setData('accent', event.target.value)}>
                            {['blue', 'orange', 'emerald', 'violet', 'amber', 'pink'].map((accent) => (
                                <option key={accent} value={accent}>
                                    {t(`admin_landing.common.${accent}`)}
                                </option>
                            ))}
                        </Select>
                    </Field>
                </div>
                <FormActions saveLabel={t('admin_landing.common.save_service')} processing={form.processing} onCancel={onDone} />
            </form>
        </EditorCard>
    );
}

export function ServicesTab({ services }: { services: Service[] }) {
    const editor = useEditor<Service>();
    const iconLabel = (type: string) => t(ICONS.find(([value]) => value === type)?.[1] ?? type);

    return (
        <CollectionPanel
            title={t('admin_landing.services.title')}
            description={t('admin_landing.services.description')}
            addLabel={t('admin_landing.services.add')}
            onAdd={editor.create}
            form={editor.isOpen && <ServiceForm key={editor.item?.id ?? 'new'} service={editor.item} onDone={editor.close} />}
            empty={services.length === 0}
            emptyText={t('admin_landing.common.no_services_defaults')}
        >
            {services.map((service) => (
                <li
                    key={service.id}
                    className={cn('flex items-center gap-4 rounded-xl border border-(--border) bg-(--surface-strong) p-3', !service.isActive && 'opacity-60')}
                >
                    <span className={cn('h-3 w-3 shrink-0 rounded-full', ACCENT_DOTS[service.accent] ?? 'bg-blue-500')} aria-hidden="true" />
                    <div className="min-w-0 flex-1">
                        <div className="flex items-center gap-1.5">
                            <p className="truncate font-semibold text-(--text)">{service.title}</p>
                            <HiddenPill hidden={!service.isActive} />
                        </div>
                        <p className="truncate text-xs text-(--text-subtle)">{[service.meta, iconLabel(service.iconType)].filter(Boolean).join(' · ')}</p>
                    </div>
                    <RowActions
                        label={service.title}
                        isActive={service.isActive}
                        toggleUrl={route('admin.landing.services.toggle', service.id)}
                        deleteUrl={route('admin.landing.services.destroy', service.id)}
                        onEdit={() => editor.edit(service)}
                    />
                </li>
            ))}
        </CollectionPanel>
    );
}
