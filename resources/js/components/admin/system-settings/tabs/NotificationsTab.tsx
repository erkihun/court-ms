import { ChatBubbleLeftRightIcon, DevicePhoneMobileIcon, EnvelopeIcon, PaperAirplaneIcon } from '@heroicons/react/24/outline';
import { useForm } from '@inertiajs/react';
import type { KeyboardEvent, ReactNode } from 'react';
import { Button } from '@/components/ui/Button';
import { Field } from '@/components/ui/Field';
import { Input, Select } from '@/components/ui/Input';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import { SecretInput, SettingsCard, Switch } from '../controls';
import type { SecretField, SettingsFormHandle } from '../types';

export interface SmtpInfo {
    host: string;
    port: string;
    scheme: string;
    recipient: string;
}

interface Props {
    form: SettingsFormHandle;
    storedSecrets: Record<SecretField, boolean>;
    smtp: SmtpInfo;
}

function ChannelSwitch({ checked, onChange, label }: { checked: boolean; onChange: (checked: boolean) => void; label: string }) {
    return (
        <span className="flex items-center gap-2">
            <span
                className={cn(
                    'rounded-full px-2 py-0.5 text-[11px] font-bold',
                    checked ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-(--surface-soft) text-(--text-subtle)',
                )}
            >
                {checked ? t('settings.on') : t('settings.off')}
            </span>
            <Switch checked={checked} onChange={onChange} label={label} />
        </span>
    );
}

function SecretHint({ stored, children }: { stored: boolean; children?: ReactNode }) {
    return <>{stored ? t('settings.secret_saved_hint') : children}</>;
}

export function NotificationsTab({ form, storedSecrets, smtp }: Props) {
    const { data, setData, errors } = form;
    const test = useForm({ recipient: smtp.recipient });

    // Lives inside the main settings form, so it is a button group rather than a nested <form>.
    const sendTest = () => test.post(route('settings.system.testSmtp'), { preserveScroll: true });
    const sendOnEnter = (event: KeyboardEvent<HTMLInputElement>) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            sendTest();
        }
    };

    return (
        <div className="space-y-4">
            <SettingsCard
                title={t('settings.email_smtp')}
                icon={<EnvelopeIcon />}
                badge={<ChannelSwitch checked={data.mail_enabled} onChange={(checked) => setData('mail_enabled', checked)} label={t('settings.email_smtp')} />}
            >
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Field label={t('settings.smtp_host')} error={errors.mail_host} className="lg:col-span-2">
                        <Input value={data.mail_host} onChange={(event) => setData('mail_host', event.target.value)} placeholder="smtp.gmail.com" />
                    </Field>
                    <Field label={t('settings.smtp_port')} error={errors.mail_port}>
                        <Input type="number" min={1} max={65535} value={data.mail_port} onChange={(event) => setData('mail_port', event.target.value)} />
                    </Field>
                    <Field label={t('settings.encryption')} error={errors.mail_encryption}>
                        <Select value={data.mail_encryption} onChange={(event) => setData('mail_encryption', event.target.value)}>
                            <option value="tls">TLS (587)</option>
                            <option value="ssl">SSL (465)</option>
                            <option value="starttls">STARTTLS</option>
                            <option value="none">None (plain)</option>
                        </Select>
                    </Field>
                    <Field label={t('settings.mailer_driver')} error={errors.mail_mailer}>
                        <Select value={data.mail_mailer} onChange={(event) => setData('mail_mailer', event.target.value)}>
                            <option value="smtp">SMTP</option>
                            <option value="sendmail">Sendmail</option>
                            <option value="log">{t('settings.log_debug')}</option>
                        </Select>
                    </Field>
                    <Field label={t('settings.mail_username')} error={errors.mail_username}>
                        <Input value={data.mail_username} onChange={(event) => setData('mail_username', event.target.value)} autoComplete="off" />
                    </Field>
                    <Field label={t('settings.mail_password')} error={errors.mail_password} hint={t('settings.mail_password_hint')} className="lg:col-span-2">
                        <SecretInput value={data.mail_password} onChange={(value) => setData('mail_password', value)} stored={storedSecrets.mail_password} />
                    </Field>
                    <Field label={t('settings.from_address')} error={errors.mail_from_address} className="lg:col-span-2">
                        <Input
                            type="email"
                            value={data.mail_from_address}
                            onChange={(event) => setData('mail_from_address', event.target.value)}
                            placeholder="noreply@court.gov.et"
                        />
                    </Field>
                    <Field label={t('settings.from_name')} error={errors.mail_from_name} className="lg:col-span-2">
                        <Input value={data.mail_from_name} onChange={(event) => setData('mail_from_name', event.target.value)} />
                    </Field>
                </div>

                <div
                    role="group"
                    aria-label={t('settings.smtp_test_send')}
                    className="mt-5 flex flex-wrap items-end gap-3 rounded-xl border border-(--border) bg-(--surface-soft) p-4"
                >
                    <Field
                        label={t('settings.smtp_test_recipient')}
                        error={test.errors.recipient ?? (test.errors as Record<string, string | undefined>).smtp_test}
                        hint={t('settings.smtp_test_hint', { host: smtp.host, port: smtp.port, scheme: smtp.scheme })}
                        className="min-w-64 flex-1"
                    >
                        <Input
                            type="email"
                            value={test.data.recipient}
                            onChange={(event) => test.setData('recipient', event.target.value)}
                            onKeyDown={sendOnEnter}
                            autoComplete="email"
                        />
                    </Field>
                    <Button variant="secondary" loading={test.processing} onClick={sendTest}>
                        <PaperAirplaneIcon className="h-4 w-4" />
                        {t('settings.smtp_test_send')}
                    </Button>
                </div>
            </SettingsCard>

            <SettingsCard
                title={t('settings.telegram_bot')}
                icon={<ChatBubbleLeftRightIcon />}
                badge={
                    <ChannelSwitch
                        checked={data.telegram_enabled}
                        onChange={(checked) => setData('telegram_enabled', checked)}
                        label={t('settings.enable_telegram')}
                    />
                }
            >
                <p className="mb-4 text-xs text-(--text-subtle)">
                    {t('settings.telegram_hint')} {t('settings.enable_telegram_desc')}
                </p>
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field
                        label={t('settings.bot_token')}
                        error={errors.telegram_bot_token}
                        hint={<SecretHint stored={storedSecrets.telegram_bot_token}>{t('settings.bot_token_hint')}</SecretHint>}
                    >
                        <SecretInput
                            value={data.telegram_bot_token}
                            onChange={(value) => setData('telegram_bot_token', value)}
                            stored={storedSecrets.telegram_bot_token}
                            placeholder="123456789:AABBccDDee…"
                        />
                    </Field>
                    <Field label={t('settings.default_chat_id')} error={errors.telegram_default_chat_id} hint={t('settings.default_chat_id_hint')}>
                        <Input
                            value={data.telegram_default_chat_id}
                            onChange={(event) => setData('telegram_default_chat_id', event.target.value)}
                            placeholder="-100xxxxxxxxxx / @channelname"
                        />
                    </Field>
                </div>
            </SettingsCard>

            <SettingsCard
                title={t('settings.sms_gateway')}
                icon={<DevicePhoneMobileIcon />}
                badge={<ChannelSwitch checked={data.sms_enabled} onChange={(checked) => setData('sms_enabled', checked)} label={t('settings.sms_gateway')} />}
            >
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label={t('settings.sms_provider')} error={errors.sms_provider} hint={t('settings.sms_provider_hint')}>
                        <Input
                            value={data.sms_provider}
                            onChange={(event) => setData('sms_provider', event.target.value)}
                            placeholder="Infobip, Twilio, EthioTelecom…"
                        />
                    </Field>
                    <Field label={t('settings.sms_base_url')} error={errors.sms_base_url} hint={t('settings.sms_base_url_hint')}>
                        <Input
                            type="url"
                            value={data.sms_base_url}
                            onChange={(event) => setData('sms_base_url', event.target.value)}
                            placeholder="https://api.yourprovider.com/v1"
                        />
                    </Field>
                    <Field label={t('settings.sms_api_key')} error={errors.sms_api_key} hint={<SecretHint stored={storedSecrets.sms_api_key} />}>
                        <SecretInput value={data.sms_api_key} onChange={(value) => setData('sms_api_key', value)} stored={storedSecrets.sms_api_key} />
                    </Field>
                    <Field label={t('settings.sms_api_secret')} error={errors.sms_api_secret} hint={<SecretHint stored={storedSecrets.sms_api_secret} />}>
                        <SecretInput value={data.sms_api_secret} onChange={(value) => setData('sms_api_secret', value)} stored={storedSecrets.sms_api_secret} />
                    </Field>
                    <Field label={t('settings.sms_sender_id')} error={errors.sms_sender_id} hint={t('settings.sms_sender_id_hint')}>
                        <Input
                            value={data.sms_sender_id}
                            onChange={(event) => setData('sms_sender_id', event.target.value)}
                            maxLength={20}
                            placeholder="CourtMS"
                        />
                    </Field>
                </div>
            </SettingsCard>
        </div>
    );
}
