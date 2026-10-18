import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { Field } from '@/components/ui/Field';
import { Input } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    type: { id: number; name: string; prefix: string | null } | null;
}

/** Create and edit share one form; `type` is null when creating. */
export default function CaseTypeForm({ type }: Props) {
    const editing = type !== null;
    const title = editing ? t('Edit Case Type') : t('Add Case Type');
    const form = useForm({ name: type?.name ?? '', prefix: type?.prefix ?? '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (editing) {
            form.patch(route('case-types.update', type.id));
        } else {
            form.post(route('case-types.store'));
        }
    };

    return (
        <>
            <Head title={title} />
            <PageHeader title={title} className="mb-6" />

            <Card className="max-w-2xl">
                <form onSubmit={submit} className="space-y-5" noValidate>
                    <Field label={t('Name')} error={form.errors.name} required>
                        <Input value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} required maxLength={100} autoFocus />
                    </Field>
                    <Field label={t('Prefix')} error={form.errors.prefix} hint={t('cases.types.prefix_hint')}>
                        <Input
                            value={form.data.prefix}
                            onChange={(event) => form.setData('prefix', event.target.value)}
                            maxLength={16}
                            className="font-mono text-sm"
                        />
                    </Field>
                    <div className="flex gap-3 pt-2">
                        <LinkButton href={route('case-types.index')} variant="secondary">
                            {t('Cancel')}
                        </LinkButton>
                        <Button type="submit" loading={form.processing}>
                            {editing ? t('Update') : t('Save')}
                        </Button>
                    </div>
                </form>
            </Card>
        </>
    );
}
