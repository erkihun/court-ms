import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { PlaceholderHelp, TemplateImageInput } from '@/components/admin/templates/TemplateFormParts';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { Field } from '@/components/ui/Field';
import { Input, Textarea } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { LetterTemplateData } from '@/types/admin/letters';

interface Props {
    /** null when creating. */
    template: LetterTemplateData | null;
    categories: string[];
}

export default function LetterTemplateForm({ template, categories }: Props) {
    const editing = template !== null;
    const form = useForm({
        title: template?.title ?? '',
        category: template?.category ?? '',
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
            form.post(route('letter-templates.update', template.id), { forceFormData: true });
        } else {
            form.post(route('letter-templates.store'), { forceFormData: true });
        }
    };

    return (
        <>
            <Head title={editing ? t('letters.templates.edit') : t('letters.templates.new')} />
            <PageHeader
                title={editing ? t('letters.templates.edit') : t('letters.templates.create')}
                description={editing ? t('letters.templates.edit_help') : t('letters.templates.create_help')}
                className="mb-6"
            />

            <Card className="max-w-4xl">
                <form onSubmit={submit} className="space-y-5" noValidate>
                    <Field label={t('letters.templates.form.title')} error={form.errors.title} required>
                        <Input value={form.data.title} onChange={(event) => form.setData('title', event.target.value)} required maxLength={255} autoFocus />
                    </Field>

                    <Field
                        label={t('letters.templates.form.category')}
                        error={form.errors.category}
                        hint={categories.length === 0 ? t('letters.templates.form.no_categories') : undefined}
                    >
                        <Input
                            list="letter-category-list"
                            value={form.data.category}
                            onChange={(event) => form.setData('category', event.target.value)}
                            maxLength={120}
                            placeholder={t('letters.templates.form.category_choose')}
                        />
                    </Field>
                    <datalist id="letter-category-list">
                        {categories.map((name) => (
                            <option key={name} value={name} />
                        ))}
                    </datalist>

                    <Field
                        label={t('letters.templates.form.placeholders')}
                        error={form.errors.placeholders}
                        hint={t('letters.templates.form.placeholders_example')}
                    >
                        <Textarea
                            rows={2}
                            value={form.data.placeholders}
                            onChange={(event) => form.setData('placeholders', event.target.value)}
                            placeholder={t('letters.templates.form.placeholders_placeholder')}
                        />
                    </Field>
                    <PlaceholderHelp text={t('letters.templates.form.placeholders_help')} />

                    <div className="grid gap-4 md:grid-cols-2">
                        <TemplateImageInput
                            label={t('letters.templates.form.header_image')}
                            current={template?.headerImage ?? null}
                            currentKey="letters.templates.form.header_current"
                            removeLabel={t('letters.templates.form.remove_header')}
                            remove={form.data.remove_header_image}
                            onFile={(file) => form.setData('header_image', file)}
                            onRemove={(remove) => form.setData('remove_header_image', remove)}
                            error={form.errors.header_image}
                        />
                        <TemplateImageInput
                            label={t('letters.templates.form.footer_image')}
                            current={template?.footerImage ?? null}
                            currentKey="letters.templates.form.footer_current"
                            removeLabel={t('letters.templates.form.remove_footer')}
                            remove={form.data.remove_footer_image}
                            onFile={(file) => form.setData('footer_image', file)}
                            onRemove={(remove) => form.setData('remove_footer_image', remove)}
                            error={form.errors.footer_image}
                        />
                    </div>

                    <Field label={t('letters.templates.form.body')} error={form.errors.body} hint={t('letters.templates.form.body_help')} required>
                        <Textarea
                            rows={10}
                            value={form.data.body}
                            onChange={(event) => form.setData('body', event.target.value)}
                            required
                            className="font-mono text-sm"
                            placeholder={t('letters.templates.form.body_placeholder')}
                        />
                    </Field>

                    <div className="flex gap-3 pt-2">
                        <LinkButton href={route('letter-templates.index')} variant="secondary">
                            {editing ? t('letters.templates.actions.back') : t('letters.templates.actions.cancel')}
                        </LinkButton>
                        <Button type="submit" loading={form.processing}>
                            {editing ? t('letters.templates.actions.update_template') : t('letters.templates.actions.save_template')}
                        </Button>
                    </div>
                </form>
            </Card>
        </>
    );
}
