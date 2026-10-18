import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { Field } from '@/components/ui/Field';
import { Input, Textarea } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { LetterCategory } from '@/types/admin/letters';

interface Props {
    /** null when creating. */
    category: LetterCategory | null;
}

export default function LetterCategoryForm({ category }: Props) {
    const editing = category !== null;
    const form = useForm({ name: category?.name ?? '', description: category?.description ?? '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (editing) {
            form.patch(route('letter-categories.update', category.id));
        } else {
            form.post(route('letter-categories.store'));
        }
    };

    return (
        <>
            <Head title={editing ? t('letters.categories.edit') : t('letters.categories.create')} />
            <PageHeader
                title={editing ? t('letters.categories.edit') : t('letters.categories.create')}
                description={editing ? t('letters.categories.edit_help') : t('letters.categories.create_help')}
                className="mb-6"
            />

            <Card className="max-w-3xl">
                <form onSubmit={submit} className="space-y-5" noValidate>
                    <Field label={t('letters.categories.form.name')} error={form.errors.name} required>
                        <Input value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} required maxLength={120} autoFocus />
                    </Field>
                    <Field label={t('letters.categories.form.description')} error={form.errors.description}>
                        <Textarea
                            rows={2}
                            value={form.data.description}
                            onChange={(event) => form.setData('description', event.target.value)}
                            maxLength={255}
                            placeholder={t('letters.categories.form.description_placeholder')}
                        />
                    </Field>
                    <div className="flex gap-3 pt-2">
                        <LinkButton href={route('letter-categories.index')} variant="secondary">
                            {editing ? t('letters.actions.back') : t('letters.templates.actions.cancel')}
                        </LinkButton>
                        <Button type="submit" loading={form.processing}>
                            {editing ? t('letters.categories.update') : t('letters.categories.save')}
                        </Button>
                    </div>
                </form>
            </Card>
        </>
    );
}
