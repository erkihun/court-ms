import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import { Button, Card, FormField, Input, Select, Textarea, buttonClassName } from '@/Components/ui';
import { useLocale } from '@/hooks/useLocale';

interface Props {
    /** Null on the create page. */
    appeal: { id: number; appeal_number: string; title: string; grounds: string | null } | null;
    /** The recent cases an appeal can be filed against (create only). */
    cases: { id: number; case_number: string; title: string | null }[];
}

/** One form for a new appeal (case, title, grounds) and for editing a draft or submitted one (title, grounds). */
export default function AppealForm({ appeal, cases }: Props) {
    const { t } = useLocale();
    const editing = appeal !== null;
    const form = useForm({ court_case_id: '', title: appeal?.title ?? '', grounds: appeal?.grounds ?? '' });
    const title = editing ? `${t('court.appeals.editTitle')} · ${appeal.appeal_number}` : t('court.appeals.newAppeal');
    const back = editing ? route('appeals.show', appeal.id) : route('appeals.index');

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (editing) form.patch(route('appeals.update', appeal.id));
        else form.post(route('appeals.store'));
    };

    return (
        <AuthenticatedLayout header={<PageHeader title={title} backHref={back} />}>
            <Head title={title} />
            <form onSubmit={submit} className="max-w-3xl">
                <Card className="space-y-5">
                    {!editing && (
                        <FormField label={t('court.appeals.case')} required error={form.errors.court_case_id}>
                            {({ id }) => (
                                <Select id={id} value={form.data.court_case_id} onChange={(event) => form.setData('court_case_id', event.target.value)} required>
                                    <option value="">{t('court.appeals.chooseCase')}</option>
                                    {cases.map((item) => <option key={item.id} value={item.id}>{item.case_number}{item.title ? ` — ${item.title}` : ''}</option>)}
                                </Select>
                            )}
                        </FormField>
                    )}
                    <FormField label={t('court.appeals.title')} required error={form.errors.title}>
                        {({ id }) => <Input id={id} value={form.data.title} onChange={(event) => form.setData('title', event.target.value)} required maxLength={255} />}
                    </FormField>
                    <FormField label={t('court.appeals.grounds')} error={form.errors.grounds}>
                        {({ id }) => <Textarea id={id} rows={6} value={form.data.grounds} onChange={(event) => form.setData('grounds', event.target.value)} />}
                    </FormField>
                    <div className="flex flex-wrap justify-end gap-2 border-t border-(--app-border) pt-4">
                        <Link href={back} className={buttonClassName({ variant: 'outline' })}>{t('court.appeals.cancel')}</Link>
                        <Button type="submit" disabled={form.processing}>{t('court.appeals.save')}</Button>
                    </div>
                </Card>
            </form>
        </AuthenticatedLayout>
    );
}
