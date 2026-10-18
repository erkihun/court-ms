import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { Field } from '@/components/ui/Field';
import { Checkbox, Input, Textarea } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    term: { id: number; title: string; body: string; isPublished: boolean } | null;
}

/** Create and edit share one form; `term` is null when creating. The body is plain text. */
export default function TermsForm({ term }: Props) {
    const editing = term !== null;
    const title = editing ? t('terms.edit_heading') : t('terms.create_heading');
    const form = useForm({ title: term?.title ?? '', body: term?.body ?? '', is_published: term?.isPublished ?? false });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (editing) {
            form.patch(route('terms.update', term.id));
        } else {
            form.post(route('terms.store'));
        }
    };

    return (
        <>
            <Head title={title} />
            <PageHeader title={title} className="mb-6" />

            <Card className="max-w-4xl">
                <form onSubmit={submit} className="space-y-5" noValidate>
                    <Field label={t('terms.form_title_label')} error={form.errors.title} required>
                        <Input value={form.data.title} onChange={(event) => form.setData('title', event.target.value)} maxLength={255} required autoFocus />
                    </Field>
                    <Field label={t('terms.form_body_label')} error={form.errors.body} required>
                        <Textarea value={form.data.body} onChange={(event) => form.setData('body', event.target.value)} rows={14} required />
                    </Field>
                    <Checkbox
                        label={t('terms.form_published_label')}
                        checked={form.data.is_published}
                        onChange={(event) => form.setData('is_published', event.target.checked)}
                    />
                    <div className="flex justify-end gap-3 pt-2">
                        <LinkButton href={route('terms.index')} variant="secondary">
                            {t('terms.form_cancel')}
                        </LinkButton>
                        <Button type="submit" loading={form.processing}>
                            {editing ? t('terms.form_update') : t('terms.form_save')}
                        </Button>
                    </div>
                </form>
            </Card>
        </>
    );
}
