import { BuildingLibraryIcon, EnvelopeIcon } from '@heroicons/react/24/outline';
import { Field } from '@/components/ui/Field';
import { Input, Textarea } from '@/components/ui/Input';
import { t } from '@/lib/i18n';
import { SettingsCard } from '../controls';
import type { SettingsFormHandle } from '../types';

export function GeneralTab({ form }: { form: SettingsFormHandle }) {
    const { data, setData, errors } = form;

    return (
        <>
            <SettingsCard title={t('settings.platform_identity')} icon={<BuildingLibraryIcon />} className="mb-4">
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label={t('settings.app_name')} error={errors.app_name} hint={t('settings.app_name_hint')} required>
                        <Input value={data.app_name} onChange={(event) => setData('app_name', event.target.value)} maxLength={255} required />
                    </Field>
                    <Field label={t('settings.short_name')} error={errors.short_name}>
                        <Input value={data.short_name} onChange={(event) => setData('short_name', event.target.value)} maxLength={50} placeholder="CMS" />
                    </Field>
                    <Field label={t('settings.welcome_message')} error={errors.welcome_message}>
                        <Input value={data.welcome_message} onChange={(event) => setData('welcome_message', event.target.value)} maxLength={500} />
                    </Field>
                    <Field label={t('settings.website_url')} error={errors.website_url}>
                        <Input
                            type="url"
                            value={data.website_url}
                            onChange={(event) => setData('website_url', event.target.value)}
                            placeholder="https://court.gov.et"
                        />
                    </Field>
                    <Field label={t('settings.address')} error={errors.address} className="sm:col-span-2">
                        <Input value={data.address} onChange={(event) => setData('address', event.target.value)} maxLength={255} />
                    </Field>
                    <Field label={t('settings.about')} error={errors.about} className="sm:col-span-2">
                        <Textarea value={data.about} onChange={(event) => setData('about', event.target.value)} rows={3} />
                    </Field>
                    <Field label={t('settings.footer_text')} error={errors.footer_text} className="sm:col-span-2">
                        <Input value={data.footer_text} onChange={(event) => setData('footer_text', event.target.value)} maxLength={500} />
                    </Field>
                </div>
            </SettingsCard>
            <SettingsCard title={t('settings.contact_information')} icon={<EnvelopeIcon />}>
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label={t('settings.contact_email')} error={errors.contact_email}>
                        <Input
                            type="email"
                            value={data.contact_email}
                            onChange={(event) => setData('contact_email', event.target.value)}
                            placeholder="info@court.gov.et"
                        />
                    </Field>
                    <Field label={t('settings.contact_phone')} error={errors.contact_phone}>
                        <Input
                            value={data.contact_phone}
                            onChange={(event) => setData('contact_phone', event.target.value)}
                            maxLength={50}
                            placeholder="+251 11 …"
                        />
                    </Field>
                </div>
            </SettingsCard>
        </>
    );
}
