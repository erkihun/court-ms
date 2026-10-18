import { CheckCircleIcon, ExclamationTriangleIcon } from '@heroicons/react/24/outline';
import { Head, useForm } from '@inertiajs/react';
import { type FormEvent, useEffect, useState } from 'react';
import { type BrandingAssets, BrandingTab } from '@/components/admin/system-settings/tabs/BrandingTab';
import { ApiTab } from '@/components/admin/system-settings/tabs/ApiTab';
import { AppearanceTab } from '@/components/admin/system-settings/tabs/AppearanceTab';
import { type DatabaseMetrics, DataTab } from '@/components/admin/system-settings/tabs/DataTab';
import { GeneralTab } from '@/components/admin/system-settings/tabs/GeneralTab';
import { LocalizationTab } from '@/components/admin/system-settings/tabs/LocalizationTab';
import { NotificationsTab, type SmtpInfo } from '@/components/admin/system-settings/tabs/NotificationsTab';
import { type RoleOption, type SecurityMetrics, SecurityTab } from '@/components/admin/system-settings/tabs/SecurityTab';
import { type SecretField, type SettingsForm, type SettingsTab, type SettingsValues, TABS, tabHasErrors } from '@/components/admin/system-settings/types';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { PageHeader } from '@/components/ui/PageHeader';
import { Tabs } from '@/components/ui/Tabs';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    tab: SettingsTab;
    settings: SettingsValues;
    storedSecrets: Record<SecretField, boolean>;
    assets: BrandingAssets;
    maintenanceMode: boolean;
    timezones: string[];
    locales: Array<{ code: string; name: string }>;
    roles: RoleOption[];
    databaseMetrics: DatabaseMetrics;
    securityMetrics: SecurityMetrics;
    systemInfo: Array<{ label: string; value: string }>;
    smtp: SmtpInfo;
    csrfToken: string;
}

const TAB_LABELS: Record<SettingsTab, string> = {
    general: 'settings.tab_general',
    branding: 'settings.tab_branding',
    localization: 'settings.tab_localization',
    security: 'settings.tab_security',
    appearance: 'settings.tab_appearance',
    notifications: 'settings.tab_notifications',
    data: 'settings.tab_data_management',
    api: 'settings.tab_api',
};

const BLANK_UPLOADS = { banner: null, logo: null, favicon: null, seal: null };
const BLANK_SECRETS = { mail_password: '', telegram_bot_token: '', sms_api_key: '', sms_api_secret: '' };

export default function SystemSettings(props: Props) {
    const [tab, setTab] = useState<SettingsTab>(props.tab);
    const form = useForm<SettingsForm>({ ...props.settings, ...BLANK_UPLOADS, ...BLANK_SECRETS });

    // Warn before leaving the page with unsaved edits (Inertia visits away are allowed, as before).
    useEffect(() => {
        if (!form.isDirty) return;
        const warn = (event: BeforeUnloadEvent) => event.preventDefault();
        window.addEventListener('beforeunload', warn);

        return () => window.removeEventListener('beforeunload', warn);
    }, [form.isDirty]);

    const switchTab = (next: SettingsTab) => {
        setTab(next);
        // Keep the URL shareable without a server round trip; Inertia's history state is kept.
        window.history.replaceState(window.history.state, '', route('settings.system.edit', { tab: next }));
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const saved = { ...form.data, ...BLANK_UPLOADS, ...BLANK_SECRETS };
        // The tab only steers the redirect back to the same tab.
        form.transform((data) => ({ ...data, tab }));
        form.post(route('settings.system.update'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                // Uploaded files and secrets are write-only; clear them so a later save does not resend them.
                form.setData(saved);
                form.setDefaults(saved);
            },
        });
    };

    const discard = () => form.reset();

    return (
        <>
            <Head title={t('settings.title')} />
            <PageHeader
                title={t('settings.title')}
                description={t('settings.subtitle')}
                className="mb-6"
                actions={
                    props.maintenanceMode ? (
                        <Badge tone="warning" className="inline-flex items-center gap-1">
                            <ExclamationTriangleIcon className="h-3.5 w-3.5" />
                            {t('settings.maintenance_on')}
                        </Badge>
                    ) : (
                        <Badge tone="success" className="inline-flex items-center gap-1">
                            <CheckCircleIcon className="h-3.5 w-3.5" />
                            {t('settings.system_online')}
                        </Badge>
                    )
                }
            />

            <form onSubmit={submit} noValidate className="pb-4">
                <Tabs
                    label={t('settings.title')}
                    active={tab}
                    onChange={switchTab}
                    tabs={TABS.map((key) => ({
                        key,
                        label: (
                            <>
                                {t(TAB_LABELS[key])}
                                {tabHasErrors(key, form.errors) && (
                                    <span
                                        className="h-2 w-2 rounded-full bg-rose-500"
                                        title={t('settings.tab_has_errors')}
                                        aria-label={t('settings.tab_has_errors')}
                                    />
                                )}
                            </>
                        ),
                    }))}
                >
                    {tab === 'general' && <GeneralTab form={form} />}
                    {tab === 'branding' && <BrandingTab form={form} assets={props.assets} />}
                    {tab === 'localization' && (
                        <LocalizationTab form={form} locales={props.locales} timezones={props.timezones} savedLocale={props.settings.default_locale} />
                    )}
                    {tab === 'security' && <SecurityTab form={form} metrics={props.securityMetrics} roles={props.roles} />}
                    {tab === 'appearance' && <AppearanceTab form={form} />}
                    {tab === 'notifications' && <NotificationsTab form={form} storedSecrets={props.storedSecrets} smtp={props.smtp} />}
                    {tab === 'data' && <DataTab form={form} metrics={props.databaseMetrics} csrfToken={props.csrfToken} />}
                    {tab === 'api' && <ApiTab form={form} systemInfo={props.systemInfo} />}
                </Tabs>

                <div className="sticky bottom-0 z-30 mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-(--border) bg-(--surface-strong)/95 px-1 py-3 backdrop-blur">
                    <p
                        className={form.isDirty ? 'text-xs font-semibold text-amber-700 dark:text-amber-300' : 'text-xs text-(--text-subtle)'}
                        aria-live="polite"
                    >
                        {form.isDirty ? t('settings.unsaved_changes') : t('settings.save_hint')}
                    </p>
                    <div className="flex gap-2">
                        <Button variant="secondary" onClick={discard} disabled={!form.isDirty || form.processing}>
                            {t('settings.discard')}
                        </Button>
                        <Button type="submit" loading={form.processing}>
                            {form.processing ? t('settings.saving') : t('settings.save_settings')}
                        </Button>
                    </div>
                </div>
            </form>
        </>
    );
}
