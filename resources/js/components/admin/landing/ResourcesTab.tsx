import { DocumentTextIcon, LinkIcon, NewspaperIcon } from '@heroicons/react/24/outline';
import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Field } from '@/components/ui/Field';
import { Checkbox, Input, Select, Textarea } from '@/components/ui/Input';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import { CollectionPanel, EditorCard, FormActions, HiddenPill, Optional, RowActions, useEditor } from './CollectionPanel';
import type { Resource } from './types';

const TYPES: Array<[Resource['type'], string]> = [
    ['post', 'admin_landing.common.post_update'],
    ['form', 'admin_landing.common.downloadable_form'],
    ['document', 'admin_landing.common.document'],
    ['link', 'admin_landing.common.external_link'],
];

function ResourceForm({ resource, onDone }: { resource: Resource | null; onDone: () => void }) {
    const form = useForm({
        title: resource?.title ?? '',
        type: resource?.type ?? 'post',
        published_at: resource?.publishedAt ?? '',
        description: resource?.description ?? '',
        external_url: resource?.externalUrl ?? '',
        file: null as File | null,
        remove_file: false,
        cover_image: null as File | null,
        is_featured: resource?.isFeatured ?? false,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const options = { forceFormData: true, preserveScroll: true, onSuccess: onDone };
        if (resource) {
            form.transform((data) => ({ ...data, _method: 'put' }));
            form.post(route('admin.landing.resources.update', resource.id), options);
        } else {
            form.post(route('admin.landing.resources.store'), options);
        }
    };

    return (
        <EditorCard title={resource ? t('admin_landing.common.edit_resource') : t('admin_landing.common.new_resource')}>
            <form onSubmit={submit} className="space-y-4" noValidate>
                <div className="grid gap-4 sm:grid-cols-4">
                    <Field label={t('admin_landing.common.title')} error={form.errors.title} required className="sm:col-span-2">
                        <Input value={form.data.title} onChange={(event) => form.setData('title', event.target.value)} maxLength={255} required />
                    </Field>
                    <Field label={t('admin_landing.common.type')} error={form.errors.type} required>
                        <Select value={form.data.type} onChange={(event) => form.setData('type', event.target.value as Resource['type'])}>
                            {TYPES.map(([value, label]) => (
                                <option key={value} value={value}>
                                    {t(label)}
                                </option>
                            ))}
                        </Select>
                    </Field>
                    <Field label={t('admin_landing.common.publish_date')} error={form.errors.published_at} hint={t('admin_landing.common.schedule_future')}>
                        <Input type="datetime-local" value={form.data.published_at} onChange={(event) => form.setData('published_at', event.target.value)} />
                    </Field>
                </div>
                <Field label={t('admin_landing.common.description_summary')} error={form.errors.description}>
                    <Textarea value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} rows={3} maxLength={2000} />
                </Field>
                <Field label={t('admin_landing.common.external_url')} error={form.errors.external_url}>
                    <Input
                        type="url"
                        value={form.data.external_url}
                        onChange={(event) => form.setData('external_url', event.target.value)}
                        maxLength={500}
                        placeholder="https://"
                    />
                </Field>
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="space-y-2">
                        <Field
                            label={
                                <>
                                    {t('admin_landing.common.file')}
                                    <Optional extra="PDF, DOCX, XLS, 20 MB" />
                                </>
                            }
                            error={form.errors.file}
                        >
                            <Input
                                type="file"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip"
                                onChange={(event) => form.setData('file', event.target.files?.[0] ?? null)}
                            />
                        </Field>
                        {resource?.fileUrl && (
                            <div className="flex flex-wrap items-center gap-3 text-xs">
                                <a href={resource.fileUrl} target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:underline">
                                    {t('admin_landing.common.current_file')}: {resource.fileName}
                                </a>
                                <Checkbox
                                    label={t('admin_landing.common.remove_file')}
                                    checked={form.data.remove_file}
                                    onChange={(event) => form.setData('remove_file', event.target.checked)}
                                />
                            </div>
                        )}
                    </div>
                    <div className="space-y-2">
                        <Field
                            label={
                                <>
                                    {t('admin_landing.common.cover_image')}
                                    <Optional extra="2 MB" />
                                </>
                            }
                            error={form.errors.cover_image}
                        >
                            <Input type="file" accept="image/*" onChange={(event) => form.setData('cover_image', event.target.files?.[0] ?? null)} />
                        </Field>
                        {resource?.coverUrl && <img src={resource.coverUrl} alt="" className="h-16 w-28 rounded-lg object-cover" />}
                    </div>
                </div>
                <Checkbox
                    label={t('admin_landing.common.featured')}
                    checked={form.data.is_featured}
                    onChange={(event) => form.setData('is_featured', event.target.checked)}
                />
                <FormActions saveLabel={t('admin_landing.common.save_resource')} processing={form.processing} onCancel={onDone} />
            </form>
        </EditorCard>
    );
}

function TypeIcon({ type }: { type: Resource['type'] }) {
    const Icon = type === 'post' ? NewspaperIcon : type === 'link' ? LinkIcon : DocumentTextIcon;

    return (
        <span className="flex h-12 w-16 shrink-0 items-center justify-center rounded-lg bg-(--surface-soft) text-(--text-subtle)">
            <Icon className="h-6 w-6" />
        </span>
    );
}

export function ResourcesTab({ resources }: { resources: Resource[] }) {
    const editor = useEditor<Resource>();
    const typeLabel = (type: Resource['type']) => t(TYPES.find(([value]) => value === type)?.[1] ?? type);

    return (
        <CollectionPanel
            title={t('admin_landing.resources.title')}
            description={t('admin_landing.resources.description')}
            addLabel={t('admin_landing.resources.add')}
            onAdd={editor.create}
            form={editor.isOpen && <ResourceForm key={editor.item?.id ?? 'new'} resource={editor.item} onDone={editor.close} />}
            empty={resources.length === 0}
            emptyText={t('admin_landing.common.no_resources')}
        >
            {resources.map((resource) => (
                <li
                    key={resource.id}
                    className={cn('flex items-center gap-4 rounded-xl border border-(--border) bg-(--surface-strong) p-3', !resource.isActive && 'opacity-60')}
                >
                    {resource.coverUrl ? (
                        <img src={resource.coverUrl} alt="" className="h-12 w-16 shrink-0 rounded-lg object-cover" />
                    ) : (
                        <TypeIcon type={resource.type} />
                    )}
                    <div className="min-w-0 flex-1">
                        <div className="flex flex-wrap items-center gap-1.5 text-[11px]">
                            <span className="rounded-full bg-(--surface-soft) px-2 py-0.5 font-semibold text-(--text-muted)">{typeLabel(resource.type)}</span>
                            {resource.isFeatured && (
                                <span className="rounded-full bg-amber-50 px-2 py-0.5 font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                                    {t('admin_landing.common.featured')}
                                </span>
                            )}
                            <HiddenPill hidden={!resource.isActive} />
                            {resource.scheduledFor && (
                                <span className="rounded-full bg-violet-50 px-2 py-0.5 font-semibold text-violet-700 dark:bg-violet-500/10 dark:text-violet-300">
                                    {t('admin_landing.common.scheduled', { date: resource.scheduledFor })}
                                </span>
                            )}
                        </div>
                        <p className="truncate font-semibold text-(--text)">{resource.title}</p>
                        {resource.description && <p className="truncate text-xs text-(--text-subtle)">{resource.description}</p>}
                    </div>
                    <RowActions
                        label={resource.title}
                        isActive={resource.isActive}
                        toggleUrl={route('admin.landing.resources.toggle', resource.id)}
                        deleteUrl={route('admin.landing.resources.destroy', resource.id)}
                        onEdit={() => editor.edit(resource)}
                    />
                </li>
            ))}
        </CollectionPanel>
    );
}
