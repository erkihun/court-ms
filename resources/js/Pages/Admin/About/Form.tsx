import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { Field } from '@/components/ui/Field';
import { Checkbox, Input } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { RichTextEditor } from '@/components/ui/RichTextEditor';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    about: { id: number; title: string; slug: string; body: string; isPublished: boolean } | null;
}

/** Create and edit share one form; `about` is null when creating. */
export default function AboutForm({ about }: Props) {
    const editing = about !== null;
    const title = editing ? t('about.edit_heading') : t('about.create_heading');
    const form = useForm({
        title: about?.title ?? '',
        slug: about?.slug ?? '',
        body: about?.body ?? '',
        is_published: about?.isPublished ?? false,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (editing) {
            form.patch(route('about.update', about.id));
        } else {
            form.post(route('about.store'));
        }
    };

    return (
        <>
            <Head title={title} />
            <PageHeader title={title} className="mb-6" />

            <Card className="max-w-4xl">
                <form onSubmit={submit} className="space-y-5" noValidate>
                    <Field label={t('about.form_title_label')} error={form.errors.title} required>
                        <Input value={form.data.title} onChange={(event) => form.setData('title', event.target.value)} maxLength={200} required autoFocus />
                    </Field>
                    <Field label={t('about.form_slug_label')} error={form.errors.slug}>
                        <Input
                            value={form.data.slug}
                            onChange={(event) => form.setData('slug', event.target.value)}
                            maxLength={200}
                            className="font-mono text-sm"
                        />
                    </Field>
                    <Field label={t('about.form_body_label')} error={form.errors.body} required>
                        <RichTextEditor value={form.data.body} onChange={(html) => form.setData('body', html)} height={520} />
                    </Field>
                    <Checkbox
                        label={t('about.form_published_label')}
                        checked={form.data.is_published}
                        onChange={(event) => form.setData('is_published', event.target.checked)}
                    />
                    <div className="flex justify-end gap-3 pt-2">
                        <LinkButton href={route('about.index')} variant="secondary">
                            {t('about.form_cancel')}
                        </LinkButton>
                        <Button type="submit" loading={form.processing}>
                            {editing ? t('about.form_update') : t('about.form_save')}
                        </Button>
                    </div>
                </form>
            </Card>
        </>
    );
}
