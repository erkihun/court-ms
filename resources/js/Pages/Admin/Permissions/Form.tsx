import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { Field } from '@/components/ui/Field';
import { Input } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

type LocaleValues = Record<string, string>;

interface Props {
    /** null when creating. */
    permission: { id: number; name: string } | null;
    locales: string[];
    primaryLocale: string;
    labels: LocaleValues;
    descriptions: LocaleValues;
}

/** Create and edit a permission with its label and description per locale. */
export default function PermissionForm({ permission, locales, primaryLocale, labels, descriptions }: Props) {
    const editing = permission !== null;
    const form = useForm({ name: permission?.name ?? '', label_translations: labels, description_translations: descriptions });
    const errors = form.errors as Record<string, string | undefined>;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (editing) {
            form.patch(route('permissions.update', permission.id));
        } else {
            form.post(route('permissions.store'));
        }
    };

    const translated = (field: 'label_translations' | 'description_translations', title: string) => (
        <fieldset className="space-y-3">
            <legend className="text-sm font-medium text-(--text)">{title}</legend>
            {locales.map((locale) => (
                <Field
                    key={locale}
                    label={
                        <span className="text-xs tracking-wide text-(--text-subtle) uppercase">
                            {locale}
                            {locale === primaryLocale && <span className="ms-2 normal-case">({t('permissions.fields.primary_locale_notice')})</span>}
                        </span>
                    }
                    error={errors[`${field}.${locale}`]}
                >
                    <Input
                        value={form.data[field][locale] ?? ''}
                        onChange={(event) => form.setData(field, { ...form.data[field], [locale]: event.target.value })}
                        placeholder={`${title} (${locale.toUpperCase()})`}
                        maxLength={255}
                        lang={locale}
                    />
                </Field>
            ))}
        </fieldset>
    );

    const title = editing ? t('permissions.edit.title') : t('permissions.create.title');

    return (
        <>
            <Head title={title} />
            <PageHeader
                title={title}
                description={t('permissions.create.name_hint')}
                actions={
                    <LinkButton href={route('permissions.index')} variant="secondary">
                        {editing ? t('permissions.edit.back_button') : t('permissions.create.cancel_button')}
                    </LinkButton>
                }
                className="mb-6"
            />

            <Card className="max-w-3xl">
                <form onSubmit={submit} noValidate className="space-y-6">
                    <Field label={t('permissions.fields.name')} error={form.errors.name} hint={t('permissions.create.name_hint')} required>
                        <Input
                            value={form.data.name}
                            onChange={(event) => form.setData('name', event.target.value)}
                            maxLength={255}
                            pattern="[A-Za-z0-9_.\-]+"
                            className="font-mono text-sm"
                            autoFocus={!editing}
                        />
                    </Field>

                    {translated('label_translations', t('permissions.fields.label'))}
                    {translated('description_translations', t('permissions.fields.description'))}

                    <div className="flex gap-3 pt-2">
                        <Button type="submit" loading={form.processing}>
                            {editing ? t('permissions.edit.save_button') : t('permissions.create.create_button')}
                        </Button>
                        <LinkButton href={route('permissions.index')} variant="secondary">
                            {t('permissions.create.cancel_button')}
                        </LinkButton>
                    </div>
                </form>
            </Card>
        </>
    );
}
