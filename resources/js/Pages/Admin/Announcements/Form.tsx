import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { Field } from '@/components/ui/Field';
import { Input, Select } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { RichTextEditor } from '@/components/ui/RichTextEditor';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    announcement: { id: number; title: string; content: string; status: 'active' | 'inactive' } | null;
}

/** Create and edit share one form; `announcement` is null when creating. */
export default function AnnouncementForm({ announcement }: Props) {
    const editing = announcement !== null;
    const title = editing ? t('announcements.edit_heading') : t('announcements.create_heading');
    const form = useForm({
        title: announcement?.title ?? '',
        content: announcement?.content ?? '',
        status: announcement?.status ?? 'active',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (editing) {
            form.patch(route('announcements.update', announcement.id));
        } else {
            form.post(route('announcements.store'));
        }
    };

    return (
        <>
            <Head title={title} />
            <PageHeader title={title} description={editing ? t('announcements.edit_description') : t('announcements.create_description')} className="mb-6" />

            <Card className="max-w-4xl">
                <form onSubmit={submit} className="space-y-5" noValidate>
                    <Field label={t('announcements.form_title_label')} error={form.errors.title} required>
                        <Input value={form.data.title} onChange={(event) => form.setData('title', event.target.value)} maxLength={255} required autoFocus />
                    </Field>
                    <Field label={t('announcements.form_content_label')} error={form.errors.content} required>
                        <RichTextEditor value={form.data.content} onChange={(html) => form.setData('content', html)} height={360} />
                    </Field>
                    <Field label={t('announcements.form_status_label')} error={form.errors.status} required>
                        <Select value={form.data.status} onChange={(event) => form.setData('status', event.target.value as 'active' | 'inactive')}>
                            <option value="active">{t('announcements.status_active')}</option>
                            <option value="inactive">{t('announcements.status_inactive')}</option>
                        </Select>
                    </Field>
                    <div className="flex justify-end gap-3 pt-2">
                        <LinkButton href={route('announcements.index')} variant="secondary">
                            {t('announcements.cancel')}
                        </LinkButton>
                        <Button type="submit" loading={form.processing}>
                            {editing ? t('announcements.form_update') : t('announcements.form_submit')}
                        </Button>
                    </div>
                </form>
            </Card>
        </>
    );
}
