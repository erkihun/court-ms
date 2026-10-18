import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { PlaceholderHelp, TemplateImageInput } from '@/components/admin/templates/TemplateFormParts';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { Field } from '@/components/ui/Field';
import { Checkbox, Input, Textarea } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { RichTextEditor } from '@/components/ui/RichTextEditor';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { DecisionTemplateData } from '@/types/admin/letters';

interface Props {
    /** null when creating. */
    template: DecisionTemplateData | null;
}

export default function DecisionTemplateForm({ template }: Props) {
    const editing = template !== null;
    const form = useForm({
        title: template?.title ?? '',
        category: template?.category ?? '',
        is_default: template?.isDefault ?? false,
        placeholders: template?.placeholders ?? '',
        body: template?.body ?? '',
        header_image: null as File | null,
        footer_image: null as File | null,
        remove_header_image: false,
        remove_footer_image: false,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (editing) {
            form.transform((data) => ({ ...data, _method: 'patch' }));
            form.post(route('decision-templates.update', template.id), { forceFormData: true });
        } else {
            form.post(route('decision-templates.store'), { forceFormData: true });
        }
    };

    return (
        <>
            <Head title={editing ? t('decision_templates.edit') : t('decision_templates.new')} />
            <PageHeader
                title={editing ? t('decision_templates.edit') : t('decision_templates.create')}
                description={editing ? t('decision_templates.edit_help') : t('decision_templates.create_help')}
                className="mb-6"
            />

            <Card className="max-w-4xl">
                <form onSubmit={submit} className="space-y-5" noValidate>
                    <Field label={t('decision_templates.form.title')} error={form.errors.title} required>
                        <Input value={form.data.title} onChange={(event) => form.setData('title', event.target.value)} required maxLength={255} autoFocus />
                    </Field>

                    <Field label={t('decision_templates.form.category')} error={form.errors.category}>
                        <Input
                            value={form.data.category}
                            onChange={(event) => form.setData('category', event.target.value)}
                            maxLength={120}
                            placeholder={t('decision_templates.form.category_placeholder')}
                        />
                    </Field>

                    <div>
                        <Checkbox
                            label={t('decision_templates.form.is_default')}
                            checked={form.data.is_default}
                            onChange={(event) => form.setData('is_default', event.target.checked)}
                        />
                        <p className="mt-1 text-xs text-(--text-subtle)">{t('decision_templates.form.is_default_help')}</p>
                    </div>

                    <Field
                        label={t('decision_templates.form.placeholders')}
                        error={form.errors.placeholders}
                        hint={t('decision_templates.form.placeholders_example')}
                    >
                        <Textarea
                            rows={2}
                            value={form.data.placeholders}
                            onChange={(event) => form.setData('placeholders', event.target.value)}
                            placeholder={t('decision_templates.form.placeholders_placeholder')}
                        />
                    </Field>
                    <PlaceholderHelp text={t('decision_templates.form.placeholders_help')} />

                    <div className="grid gap-4 md:grid-cols-2">
                        <TemplateImageInput
                            label={t('decision_templates.form.header_image')}
                            current={template?.headerImage ?? null}
                            currentKey="decision_templates.form.header_current"
                            removeLabel={t('decision_templates.form.remove_header')}
                            remove={form.data.remove_header_image}
                            onFile={(file) => form.setData('header_image', file)}
                            onRemove={(remove) => form.setData('remove_header_image', remove)}
                            error={form.errors.header_image}
                        />
                        <TemplateImageInput
                            label={t('decision_templates.form.footer_image')}
                            current={template?.footerImage ?? null}
                            currentKey="decision_templates.form.footer_current"
                            removeLabel={t('decision_templates.form.remove_footer')}
                            remove={form.data.remove_footer_image}
                            onFile={(file) => form.setData('footer_image', file)}
                            onRemove={(remove) => form.setData('remove_footer_image', remove)}
                            error={form.errors.footer_image}
                        />
                    </div>

                    <Field label={t('decision_templates.form.body')} error={form.errors.body} hint={t('decision_templates.form.body_help')} required>
                        <RichTextEditor value={form.data.body} onChange={(html) => form.setData('body', html)} height={400} />
                    </Field>

                    <div className="flex gap-3 pt-2">
                        <LinkButton href={route('decision-templates.index')} variant="secondary">
                            {t('decision_templates.actions.cancel')}
                        </LinkButton>
                        <Button type="submit" loading={form.processing}>
                            {t('decision_templates.actions.save_template')}
                        </Button>
                    </div>
                </form>
            </Card>
        </>
    );
}
