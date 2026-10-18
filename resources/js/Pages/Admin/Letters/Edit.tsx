import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { LetterFields } from '@/components/admin/letters/LetterFields';
import { Alert } from '@/components/ui/Alert';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { DescriptionList } from '@/components/ui/DescriptionList';
import { PageHeader } from '@/components/ui/PageHeader';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { LetterFormData } from '@/types/admin/letters';

interface Props {
    letter: LetterFormData & {
        id: number;
        templateTitle: string;
        referenceNumber: string | null;
        previewUrl: string;
    };
}

export default function LetterEdit({ letter }: Props) {
    const form = useForm<LetterFormData>({
        recipient_name: letter.recipient_name,
        recipient_title: letter.recipient_title,
        recipient_company: letter.recipient_company,
        case_number: letter.case_number,
        cc: letter.cc,
        send_to_applicant: letter.send_to_applicant,
        send_to_respondent: letter.send_to_respondent,
        subject: letter.subject,
        body: letter.body,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.patch(route('letters.update', letter.id));
    };

    return (
        <>
            <Head title={t('letters.titles.edit')} />
            <PageHeader
                title={t('letters.titles.edit')}
                description={t('letters.description.edit')}
                className="mb-6"
                actions={
                    <LinkButton href={letter.previewUrl} variant="secondary" external target="_blank">
                        {t('letters.actions.view')}
                    </LinkButton>
                }
            />

            <Card className="max-w-5xl">
                <DescriptionList
                    className="mb-6 rounded-lg border border-(--border) bg-(--surface-soft) p-4"
                    items={[
                        { label: t('letters.form.template_label'), value: letter.templateTitle },
                        { label: t('letters.table.reference'), value: letter.referenceNumber ?? t('letters.cards.missing') },
                    ]}
                />

                <form onSubmit={submit} className="space-y-5" noValidate>
                    {Object.keys(form.errors).length > 0 && <Alert tone="error">{t('letters.form.validation_notice')}</Alert>}

                    <LetterFields
                        data={form.data}
                        errors={form.errors}
                        onChange={(key, value) => form.setData((data) => ({ ...data, [key]: value }))}
                        caseNumberLocked
                        bodyHint={t('letters.form.body_preview_hint')}
                    />

                    <div className="flex items-center justify-between gap-3">
                        <LinkButton href={route('letters.index')} variant="secondary">
                            {t('letters.actions.back')}
                        </LinkButton>
                        <Button type="submit" loading={form.processing}>
                            {t('letters.actions.update_letter')}
                        </Button>
                    </div>
                </form>
            </Card>
        </>
    );
}
