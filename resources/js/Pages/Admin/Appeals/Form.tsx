import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { Field } from '@/components/ui/Field';
import { Input, Select, Textarea } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    /** null when creating. */
    appeal: { id: number; appealNumber: string; title: string; grounds: string } | null;
    /** Recent cases to appeal against; empty when editing (the case is fixed). */
    cases: Array<{ id: number; label: string }>;
}

export default function AppealForm({ appeal, cases }: Props) {
    const editing = appeal !== null;
    const title = editing ? t('appeals.edit.title') : t('appeals.create.title');
    const form = useForm({
        court_case_id: cases[0] ? String(cases[0].id) : '',
        title: appeal?.title ?? '',
        grounds: appeal?.grounds ?? '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (editing) {
            form.patch(route('appeals.update', appeal.id));
        } else {
            form.post(route('appeals.store'));
        }
    };

    return (
        <>
            <Head title={editing ? `${title} · ${appeal.appealNumber}` : title} />
            <PageHeader title={title} description={editing ? t('appeals.edit.subtitle') : t('appeals.create.subtitle')} className="mb-6" />

            <Card className="max-w-3xl">
                <form onSubmit={submit} className="space-y-5" noValidate>
                    {!editing && (
                        <Field label={t('appeals.fields.case')} error={form.errors.court_case_id} required>
                            <Select value={form.data.court_case_id} onChange={(event) => form.setData('court_case_id', event.target.value)} required>
                                <option value="">{t('appeals.fields.select_case')}</option>
                                {cases.map((option) => (
                                    <option key={option.id} value={option.id}>
                                        {option.label}
                                    </option>
                                ))}
                            </Select>
                        </Field>
                    )}
                    <Field label={t('appeals.fields.title')} error={form.errors.title} required>
                        <Input value={form.data.title} onChange={(event) => form.setData('title', event.target.value)} maxLength={255} required />
                    </Field>
                    <Field label={t('appeals.fields.grounds')} error={form.errors.grounds}>
                        <Textarea value={form.data.grounds} onChange={(event) => form.setData('grounds', event.target.value)} rows={8} maxLength={10000} />
                    </Field>
                    <div className="flex gap-3 pt-2">
                        <LinkButton href={editing ? route('appeals.show', appeal.id) : route('appeals.index')} variant="secondary">
                            {t('appeals.actions.cancel')}
                        </LinkButton>
                        <Button type="submit" loading={form.processing}>
                            {editing ? t('appeals.edit.submit') : t('appeals.create.submit')}
                        </Button>
                    </div>
                </form>
            </Card>
        </>
    );
}
