import { ChevronDownIcon } from '@heroicons/react/24/outline';
import { useForm } from '@inertiajs/react';
import { type FormEvent, type ReactNode, useState } from 'react';
import { Button } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { Field } from '@/components/ui/Field';
import { Checkbox, Input, Textarea } from '@/components/ui/Input';
import { RichTextEditor } from '@/components/ui/RichTextEditor';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { FooterValues, ManualValues, MetricCard, Sections, SectionValues } from './types';

function TabHeading({ title, description, aside }: { title: string; description: string; aside?: ReactNode }) {
    return (
        <div className="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 className="text-base font-semibold text-(--text)">{title}</h2>
                <p className="text-sm text-(--text-subtle)">{description}</p>
            </div>
            {aside}
        </div>
    );
}

function SaveBar({ label, processing }: { label: string; processing: boolean }) {
    return (
        <div className="flex justify-end pt-2">
            <Button type="submit" loading={processing}>
                {label}
            </Button>
        </div>
    );
}

export function MetricsTab({ metrics }: { metrics: MetricCard[] }) {
    const form = useForm<Record<string, { label: string; description: string }>>(
        Object.fromEntries(metrics.map((metric) => [metric.key, { label: metric.label, description: metric.description }])),
    );
    const update = (key: string, field: 'label' | 'description', value: string) =>
        form.setData((data) => ({ ...data, [key]: { ...(data[key] ?? { label: '', description: '' }), [field]: value } }));
    const errors = form.errors as Record<string, string | undefined>;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put(route('admin.landing.metrics.update'), { preserveScroll: true });
    };

    return (
        <form onSubmit={submit} noValidate>
            <TabHeading
                title={t('admin_landing.metrics.title')}
                description={t('admin_landing.metrics.description')}
                aside={
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                        <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden="true" />
                        {t('admin_landing.metrics.automatic')}
                    </span>
                }
            />
            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                {metrics.map((metric) => (
                    <Card key={metric.key} padding="sm">
                        <div className="mb-3 flex items-center justify-between gap-2">
                            <h3 className="text-sm font-semibold text-(--text)">{t(`home.metrics.cards.${metric.key}.label`)}</h3>
                            <span className="text-lg font-bold text-[rgb(var(--ac))]">{metric.value}</span>
                        </div>
                        <div className="space-y-3">
                            <Field label={t('admin_landing.metrics.display_label')} error={errors[`${metric.key}.label`]}>
                                <Input
                                    value={form.data[metric.key]?.label ?? ''}
                                    onChange={(event) => update(metric.key, 'label', event.target.value)}
                                    maxLength={120}
                                    placeholder={metric.placeholderLabel}
                                />
                            </Field>
                            <Field label={t('admin_landing.metrics.description_label')} error={errors[`${metric.key}.description`]}>
                                <Textarea
                                    value={form.data[metric.key]?.description ?? ''}
                                    onChange={(event) => update(metric.key, 'description', event.target.value)}
                                    rows={2}
                                    maxLength={255}
                                    placeholder={metric.placeholderDescription}
                                />
                            </Field>
                        </div>
                    </Card>
                ))}
            </div>
            <SaveBar label={t('admin_landing.metrics.save')} processing={form.processing} />
        </form>
    );
}

export function ManualTab({ manual }: { manual: ManualValues }) {
    const form = useForm(manual);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put(route('admin.landing.user-manual.update'), { preserveScroll: true });
    };

    return (
        <form onSubmit={submit} noValidate>
            <TabHeading title={t('admin_landing.manual.title')} description={t('admin_landing.manual.description')} />
            <Card>
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label={t('admin_landing.manual.title_en')} error={form.errors.title_en} required>
                        <Input value={form.data.title_en} onChange={(event) => form.setData('title_en', event.target.value)} maxLength={255} required />
                    </Field>
                    <Field label={t('admin_landing.manual.title_am')} error={form.errors.title_am} required>
                        <Input value={form.data.title_am} onChange={(event) => form.setData('title_am', event.target.value)} maxLength={255} required />
                    </Field>
                </div>
                <div className="mt-4 grid gap-4 xl:grid-cols-2">
                    <Field label={t('admin_landing.manual.content_en')} error={form.errors.content_en} required>
                        <RichTextEditor value={form.data.content_en} onChange={(html) => form.setData('content_en', html)} height={480} />
                    </Field>
                    <Field label={t('admin_landing.manual.content_am')} error={form.errors.content_am} required>
                        <RichTextEditor value={form.data.content_am} onChange={(html) => form.setData('content_am', html)} height={480} />
                    </Field>
                </div>
                <div className="mt-4">
                    <Checkbox
                        label={t('admin_landing.manual.show_in_nav')}
                        checked={form.data.is_active}
                        onChange={(event) => form.setData('is_active', event.target.checked)}
                    />
                </div>
                <SaveBar label={t('admin_landing.manual.save')} processing={form.processing} />
            </Card>
        </form>
    );
}

const SOCIAL: Array<[keyof FooterValues, string]> = [
    ['social_facebook', 'Facebook'],
    ['social_twitter', 'Twitter / X'],
    ['social_linkedin', 'LinkedIn'],
    ['social_youtube', 'YouTube'],
    ['social_telegram', 'Telegram'],
    ['social_instagram', 'Instagram'],
];

export function FooterTab({ footer }: { footer: FooterValues }) {
    const form = useForm<FooterValues>(footer);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put(route('admin.landing.footer.update'), { preserveScroll: true });
    };

    return (
        <form onSubmit={submit} noValidate className="space-y-4">
            <TabHeading title={t('admin_landing.footer.title')} description={t('admin_landing.footer.description')} />
            <Card>
                <h3 className="mb-3 text-sm font-semibold text-(--text)">{t('admin_landing.footer.about')}</h3>
                <Field label={t('admin_landing.common.short_description')} error={form.errors.description}>
                    <Textarea value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} rows={3} maxLength={500} />
                </Field>
            </Card>
            <Card>
                <h3 className="mb-3 text-sm font-semibold text-(--text)">{t('admin_landing.footer.contact')}</h3>
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label={t('admin_landing.common.phone')} error={form.errors.contact_phone}>
                        <Input
                            value={form.data.contact_phone}
                            onChange={(event) => form.setData('contact_phone', event.target.value)}
                            maxLength={60}
                            placeholder="+251 11 123 4567"
                        />
                    </Field>
                    <Field label={t('admin_landing.common.email')} error={form.errors.contact_email}>
                        <Input
                            type="email"
                            value={form.data.contact_email}
                            onChange={(event) => form.setData('contact_email', event.target.value)}
                            maxLength={120}
                            placeholder="info@court.gov.et"
                        />
                    </Field>
                    <Field label={t('admin_landing.common.physical_address')} error={form.errors.contact_address} className="sm:col-span-2">
                        <Input value={form.data.contact_address} onChange={(event) => form.setData('contact_address', event.target.value)} maxLength={255} />
                    </Field>
                </div>
            </Card>
            <Card>
                <h3 className="text-sm font-semibold text-(--text)">{t('admin_landing.footer.social')}</h3>
                <p className="mb-3 text-xs text-(--text-subtle)">{t('admin_landing.footer.social_help')}</p>
                <div className="grid gap-4 sm:grid-cols-2">
                    {SOCIAL.map(([field, label]) => (
                        <Field key={field} label={label} error={form.errors[field]}>
                            <Input
                                type="url"
                                value={form.data[field]}
                                onChange={(event) => form.setData(field, event.target.value)}
                                maxLength={300}
                                placeholder="https://"
                            />
                        </Field>
                    ))}
                </div>
            </Card>
            <SaveBar label={t('admin_landing.footer.save')} processing={form.processing} />
        </form>
    );
}

type SectionKey = keyof Sections;

const SECTION_DEFS: Array<{ key: SectionKey; titled: boolean; cta: boolean }> = [
    { key: 'metrics', titled: true, cta: false },
    { key: 'process', titled: true, cta: false },
    { key: 'services', titled: true, cta: false },
    { key: 'cases', titled: false, cta: false },
    { key: 'resources', titled: true, cta: false },
    { key: 'faq', titled: true, cta: false },
    { key: 'cta', titled: false, cta: true },
];

function SectionRow({
    label,
    visible,
    onVisible,
    expandable,
    children,
}: {
    label: string;
    visible: boolean;
    onVisible: (visible: boolean) => void;
    expandable: boolean;
    children: ReactNode;
}) {
    const [open, setOpen] = useState(false);

    return (
        <div className="rounded-xl border border-(--border) bg-(--surface-strong)">
            <div className="flex items-center gap-3 px-4 py-3">
                <Checkbox label={<span className="font-semibold">{label}</span>} checked={visible} onChange={(event) => onVisible(event.target.checked)} />
                {expandable && (
                    <button
                        type="button"
                        onClick={() => setOpen((current) => !current)}
                        className="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg text-(--text-subtle) hover:bg-(--surface-soft)"
                        aria-expanded={open}
                        aria-label={label}
                    >
                        <ChevronDownIcon className={cn('h-4 w-4 transition-transform', open && 'rotate-180')} />
                    </button>
                )}
            </div>
            {expandable && open && <div className="border-t border-(--border) px-4 py-4">{children}</div>}
        </div>
    );
}

export function SectionsTab({ sections }: { sections: Sections }) {
    const form = useForm<Sections>(sections);
    const errors = form.errors as Record<string, string | undefined>;
    const set = (key: SectionKey, field: keyof SectionValues, value: string | boolean) =>
        form.setData((data) => ({ ...data, [key]: { ...data[key], [field]: value } }));
    const text = (
        key: SectionKey,
        field: keyof SectionValues,
        label: string,
        max: number,
        options: { placeholder?: string; multiline?: boolean; wide?: boolean } = {},
    ) => (
        <Field label={label} error={errors[`${key}.${field}`]} className={options.wide ? 'sm:col-span-2' : undefined}>
            {options.multiline ? (
                <Textarea
                    value={String(form.data[key][field] ?? '')}
                    onChange={(event) => set(key, field, event.target.value)}
                    rows={2}
                    maxLength={max}
                    placeholder={options.placeholder}
                />
            ) : (
                <Input
                    value={String(form.data[key][field] ?? '')}
                    onChange={(event) => set(key, field, event.target.value)}
                    maxLength={max}
                    placeholder={options.placeholder}
                />
            )}
        </Field>
    );

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put(route('admin.landing.sections.update'), { preserveScroll: true });
    };

    return (
        <form onSubmit={submit} noValidate>
            <TabHeading title={t('admin_landing.sections.title')} description={t('admin_landing.sections.description')} />
            <div className="space-y-2">
                {SECTION_DEFS.map((def) => (
                    <SectionRow
                        key={def.key}
                        expandable={def.titled || def.cta}
                        label={t(`admin_landing.sections.${def.key}`)}
                        visible={form.data[def.key].visible}
                        onVisible={(visible) => set(def.key, 'visible', visible)}
                    >
                        {def.titled && (
                            <div className="grid gap-4 sm:grid-cols-2">
                                {text(def.key, 'title', t('admin_landing.sections.section_title'), 255, {
                                    placeholder: t('admin_landing.sections.default_hint'),
                                })}
                                {text(def.key, 'subtitle', t('admin_landing.sections.subtitle'), 500, {
                                    placeholder: t('admin_landing.sections.default_hint'),
                                })}
                            </div>
                        )}
                        {def.cta && (
                            <div className="grid gap-4 sm:grid-cols-2">
                                {text('cta', 'title', t('admin_landing.common.banner_title'), 255, { wide: true })}
                                {text('cta', 'description', t('admin_landing.common.banner_description'), 500, { multiline: true, wide: true })}
                                {text('cta', 'primary_label', t('admin_landing.common.primary_button_label'), 120)}
                                {text('cta', 'primary_href', t('admin_landing.common.primary_button_url'), 500, { placeholder: '/applicant/register' })}
                                {text('cta', 'secondary_label', t('admin_landing.common.secondary_button_label'), 120)}
                                {text('cta', 'secondary_href', t('admin_landing.common.secondary_button_url'), 500, { placeholder: '/applicant/login' })}
                            </div>
                        )}
                    </SectionRow>
                ))}
            </div>
            <SaveBar label={t('admin_landing.sections.save')} processing={form.processing} />
        </form>
    );
}
