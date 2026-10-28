import { Head, router, useForm } from '@inertiajs/react';
import { useMemo, useState, type FormEvent, type ReactNode } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import DashboardTabs from '@/Components/dashboard/DashboardTabs';
import LocalizedDateDisplay from '@/Components/Calendar/LocalizedDateDisplay';
import { Alert, Button, Card, FileUpload, FormField, Input, Select, Textarea, badgeClassName, cx } from '@/Components/ui';
import { useConfirm } from '@/hooks/useConfirm';
import { useLocale } from '@/hooks/useLocale';
import type { SystemSettingsProps } from '@/court/types/settings';

type Tab = 'general' | 'branding' | 'localization' | 'security' | 'appearance' | 'notifications' | 'data' | 'api';
const TABS: { id: Tab; labelKey: string }[] = [
    { id: 'general', labelKey: 'tab_general' }, { id: 'branding', labelKey: 'tab_branding' }, { id: 'localization', labelKey: 'tab_localization' },
    { id: 'security', labelKey: 'tab_security' }, { id: 'appearance', labelKey: 'tab_appearance' }, { id: 'notifications', labelKey: 'tab_notifications' },
    { id: 'data', labelKey: 'tab_data_management' }, { id: 'api', labelKey: 'tab_api' },
];
/** Which tab holds each field, to mark a tab whose fields came back with errors. */
const FIELD_TABS: Record<string, Tab> = {
    app_name: 'general', short_name: 'general', welcome_message: 'general', about: 'general', footer_text: 'general', address: 'general', website_url: 'general', contact_email: 'general', contact_phone: 'general',
    logo: 'branding', banner: 'branding', favicon: 'branding', seal: 'branding',
    default_locale: 'localization', show_language_switcher: 'localization', timezone: 'localization', date_format: 'localization', time_format: 'localization', use_ethiopian_calendar: 'localization',
    maintenance_mode: 'security', registration_open: 'security', force_https: 'security', mfa_enabled: 'security', mfa_roles: 'security', session_lifetime: 'security', session_warning_minutes: 'security',
    session_extend_on_activity: 'security', password_min_length: 'security', password_require_uppercase: 'security', password_require_number: 'security', password_require_symbol: 'security', login_max_attempts: 'security', lockout_minutes: 'security',
    accent_palette: 'appearance', default_theme: 'appearance', show_banner_on_login: 'appearance', custom_css: 'appearance',
    mail_enabled: 'notifications', mail_mailer: 'notifications', mail_host: 'notifications', mail_port: 'notifications', mail_username: 'notifications', mail_password: 'notifications', mail_encryption: 'notifications',
    mail_from_address: 'notifications', mail_from_name: 'notifications', telegram_enabled: 'notifications', telegram_bot_token: 'notifications', telegram_default_chat_id: 'notifications',
    sms_enabled: 'notifications', sms_provider: 'notifications', sms_api_key: 'notifications', sms_api_secret: 'notifications', sms_sender_id: 'notifications', sms_base_url: 'notifications', smtp_test: 'notifications', recipient: 'notifications',
    audit_logging_enabled: 'data', audit_retention_days: 'data',
    api_enabled: 'api', api_rate_limit: 'api',
};
const DATE_FORMATS: [string, string][] = [['Y-m-d', '2026-06-11 (ISO)'], ['d/m/Y', '11/06/2026'], ['m/d/Y', '06/11/2026'], ['d-m-Y', '11-06-2026'], ['F j, Y', 'June 11, 2026']];
const SECURITY_CONTROLS: [keyof SystemSettingsProps['securityMetrics'], string][] = [
    ['https_enforced', 'https_protection'], ['debug_disabled', 'debug_protection'], ['secure_cookies', 'cookie_protection'],
    ['strong_password', 'password_strength'], ['strict_lockout', 'login_protection'], ['mfa_configured', 'mfa_protection'],
];
type Secret = 'mail_password' | 'telegram_bot_token' | 'sms_api_key' | 'sms_api_secret';

/**
 * System settings, tab by tab: identity, branding files, language and
 * formats, security policy (with the live protection score), appearance,
 * email/Telegram/SMS channels with an SMTP test, data and backup, and the
 * API with system information and cache clearing. Stored secrets never reach
 * the page: a blank field keeps one, the remove box clears it.
 */
export default function SystemSettings({ tab: initialTab, settings, secretsSet, assets, timezones, locales, roles, databaseMetrics, securityMetrics, systemInfo }: SystemSettingsProps) {
    const { t } = useLocale();
    const { confirm } = useConfirm();
    const s = (key: string) => t(`cm.settings.${key}`);
    const [tab, setTab] = useState<Tab>(initialTab as Tab);

    const form = useForm({
        ...settings,
        mfa_roles: roles.filter((role) => role.mfa_required).map((role) => role.id),
        mail_password: '', telegram_bot_token: '', sms_api_key: '', sms_api_secret: '',
        clear_secrets: [] as Secret[],
        logo: null as File | null, banner: null as File | null, favicon: null as File | null, seal: null as File | null,
        tab: initialTab,
    });
    const smtp = useForm({ recipient: '' });
    const errors = { ...form.errors, ...smtp.errors } as Record<string, string | undefined>;
    const tabsWithErrors = useMemo(() => new Set(Object.keys(errors).filter((key) => errors[key]).map((key) => FIELD_TABS[key.split('.')[0]]).filter(Boolean)), [errors]);

    const changeTab = (id: string) => {
        setTab(id as Tab);
        form.setData('tab', id);
        window.history.replaceState(window.history.state, '', route('settings.system.edit', { tab: id }));
    };
    const value = (key: keyof typeof settings) => (form.data[key] ?? '') as string | number;
    const text = (key: keyof typeof settings) => (event: { target: { value: string } }) => form.setData(key, event.target.value as never);
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('settings.system.update'), { forceFormData: true, preserveScroll: true, onSuccess: () => form.reset('mail_password', 'telegram_bot_token', 'sms_api_key', 'sms_api_secret', 'clear_secrets', 'logo', 'banner', 'favicon', 'seal') });
    };
    const clearCaches = async () => {
        const { confirmed } = await confirm({ title: s('clear_caches'), description: s('clear_caches_confirm'), confirmLabel: s('clear_caches'), cancelLabel: t('common.cancel'), variant: 'warning' });
        if (confirmed) router.post(route('settings.system.clearCache'), { tab }, { preserveScroll: true });
    };
    const downloadBackup = async () => {
        const { confirmed } = await confirm({ title: s('download_backup'), description: s('download_backup_confirm'), confirmLabel: s('download_backup'), cancelLabel: t('common.cancel'), variant: 'warning' });
        if (!confirmed) return;
        // A file download, not an Inertia visit: a plain form post carries the session and CSRF token.
        const post = document.createElement('form');
        post.method = 'post';
        post.action = route('settings.system.databaseBackup');
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
        post.appendChild(token);
        document.body.appendChild(post);
        post.submit();
        post.remove();
    };

    const field = (key: keyof typeof settings, label: string, options: { hint?: string; type?: string; required?: boolean; unit?: string; min?: number; max?: number; className?: string } = {}) => (
        <FormField label={label} description={options.hint} required={options.required} error={errors[key]} className={options.className}>
            {({ id, describedBy, invalid }) => (
                <div className="flex items-center gap-2">
                    <Input id={id} type={options.type ?? 'text'} value={value(key)} onChange={text(key)} required={options.required} min={options.min} max={options.max}
                        aria-describedby={describedBy} aria-invalid={invalid || undefined} />
                    {options.unit && <span className="shrink-0 text-xs text-(--app-muted-foreground)">{options.unit}</span>}
                </div>
            )}
        </FormField>
    );
    const toggle = (key: keyof typeof settings, label: string, description?: string) => (
        <label className="flex items-start gap-3 rounded-lg border border-(--app-border) p-3">
            <input type="checkbox" checked={Boolean(form.data[key])} onChange={(event) => form.setData(key, event.target.checked as never)} className="mt-0.5 h-4 w-4 rounded border-(--app-border)" />
            <span>
                <span className="block text-sm font-medium text-(--app-foreground)">{label}</span>
                {description && <span className="block text-xs text-(--app-muted-foreground)">{description}</span>}
            </span>
        </label>
    );
    const secret = (key: Secret, label: string, hint?: string) => (
        <FormField label={label} description={hint ?? (secretsSet[key] ? t('court.settings.secretKeep') : undefined)} error={errors[key]}>
            {({ id, describedBy }) => (
                <div className="space-y-2">
                    <Input id={id} type="password" autoComplete="new-password" value={form.data[key]} onChange={(event) => form.setData(key, event.target.value)}
                        placeholder={secretsSet[key] ? '••••••••' : ''} aria-describedby={describedBy} disabled={form.data.clear_secrets.includes(key)} />
                    <div className="flex flex-wrap items-center gap-3 text-xs">
                        <span className={badgeClassName(secretsSet[key] ? 'success' : 'neutral')}>{secretsSet[key] ? t('court.settings.secretSaved') : t('court.settings.secretNotSet')}</span>
                        {secretsSet[key] && (
                            <label className="flex items-center gap-1.5 text-(--app-muted-foreground)">
                                <input type="checkbox" checked={form.data.clear_secrets.includes(key)} className="h-3.5 w-3.5"
                                    onChange={(event) => form.setData('clear_secrets', event.target.checked ? [...form.data.clear_secrets, key] : form.data.clear_secrets.filter((item) => item !== key))} />
                                {t('court.settings.secretRemove')}
                            </label>
                        )}
                    </div>
                </div>
            )}
        </FormField>
    );
    const section = (title: string, children: ReactNode, hint?: string, action?: ReactNode) => (
        <Card className="space-y-4">
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 className="text-sm font-semibold text-(--app-foreground)">{title}</h2>
                    {hint && <p className="mt-1 text-xs text-(--app-muted-foreground)">{hint}</p>}
                </div>
                {action}
            </div>
            {children}
        </Card>
    );
    const asset = (key: 'banner' | 'logo' | 'favicon' | 'seal', label: string, hint: string, accept: string, maxSizeMb: number) => (
        <div className="space-y-2">
            {assets[key] && !form.data[key] && <img src={assets[key] ?? ''} alt="" className={cx('rounded-lg border border-(--app-border) bg-(--app-surface-muted) object-contain', key === 'banner' ? 'max-h-28 w-full' : 'h-20 w-20')} />}
            <FileUpload label={label} value={form.data[key]} accept={accept} maxSizeMb={maxSizeMb} error={errors[key]} onChange={(file) => form.setData(key, file)} onRemove={() => form.setData(key, null)} />
            <p className="text-xs text-(--app-muted-foreground)">{hint} {assets[key] ? `· ${s('keep_current_file')}` : ''}</p>
        </div>
    );
    const select = (key: keyof typeof settings, label: string, options: [string, string][]) => (
        <FormField label={label} error={errors[key]}>
            {({ id }) => <Select id={id} value={value(key)} onChange={text(key)}>{options.map(([option, name]) => <option key={option} value={option}>{name}</option>)}</Select>}
        </FormField>
    );

    return (
        <AuthenticatedLayout header={<PageHeader title={s('title')} description={s('save_hint')} />}>
            <Head title={s('title')} />

            <div className="space-y-4">
                <DashboardTabs tabs={TABS.map((item) => ({ id: item.id, label: `${s(item.labelKey)}${tabsWithErrors.has(item.id) ? ' •' : ''}` }))} activeId={tab} onChange={changeTab} label={s('title')} idPrefix="settings" />
                {tabsWithErrors.size > 0 && <Alert tone="danger">{s('tab_has_errors')}</Alert>}

                <form onSubmit={submit} className="space-y-4 pb-24" id="system-settings-form">
                    {tab === 'general' && (
                        <>
                            {section(s('platform_identity'), (
                                <div className="grid gap-4 md:grid-cols-2">
                                    {field('app_name', s('app_name'), { hint: s('app_name_hint'), required: true })}
                                    {field('short_name', s('short_name'))}
                                    {field('welcome_message', s('welcome_message'), { className: 'md:col-span-2' })}
                                    {field('website_url', s('website_url'), { type: 'url' })}
                                    {field('address', s('address'))}
                                    <FormField label={s('about')} error={errors.about} className="md:col-span-2">
                                        {({ id }) => <Textarea id={id} rows={4} value={value('about')} onChange={text('about')} />}
                                    </FormField>
                                    {field('footer_text', s('footer_text'), { className: 'md:col-span-2' })}
                                </div>
                            ))}
                            {section(s('contact_information'), (
                                <div className="grid gap-4 md:grid-cols-2">
                                    {field('contact_email', s('contact_email'), { type: 'email' })}
                                    {field('contact_phone', s('contact_phone'))}
                                </div>
                            ))}
                        </>
                    )}

                    {tab === 'branding' && section(s('visual_assets'), (
                        <div className="grid gap-6 md:grid-cols-2">
                            <div className="md:col-span-2">{asset('banner', s('header_banner'), s('banner_hint'), '.png,.jpg,.jpeg,.webp', 3)}</div>
                            {asset('logo', s('logo'), s('logo_hint'), '.png,.jpg,.jpeg,.svg,.webp', 2)}
                            {asset('favicon', s('favicon'), s('favicon_hint'), '.png,.ico', 0.5)}
                            {asset('seal', s('official_seal'), s('seal_hint'), '.png', 1)}
                        </div>
                    ))}

                    {tab === 'localization' && (
                        <>
                            {section(s('default_language'), (
                                <div className="grid gap-4 md:grid-cols-2">
                                    {select('default_locale', s('default_language'), locales.map((locale) => [locale.code, locale.name]))}
                                    {toggle('show_language_switcher', s('show_lang_switcher'), s('show_lang_switcher_desc'))}
                                </div>
                            ))}
                            {section(s('region_formats'), (
                                <div className="grid gap-4 md:grid-cols-3">
                                    {select('timezone', s('timezone'), timezones.map((zone) => [zone, zone]))}
                                    {select('date_format', s('date_format'), DATE_FORMATS)}
                                    {select('time_format', s('time_format'), [['H:i', '14:30 (24h)'], ['h:i A', '02:30 PM (12h)']])}
                                    <div className="md:col-span-3">{toggle('use_ethiopian_calendar', s('ethiopian_calendar'), s('ethiopian_calendar_desc'))}</div>
                                </div>
                            ))}
                        </>
                    )}

                    {tab === 'security' && (
                        <>
                            {section(s('security_posture'), (
                                <div className="space-y-4">
                                    <div className="flex flex-wrap items-center gap-4">
                                        <div className="text-4xl font-bold tabular-nums text-(--app-foreground)">{securityMetrics.score}%</div>
                                        <div>
                                            <div className="text-sm text-(--app-muted-foreground)">{s('controls_active')}</div>
                                            <span className={badgeClassName(securityMetrics.score === 100 ? 'success' : 'warning')}>{securityMetrics.score === 100 ? s('protected') : s('action_recommended')}</span>
                                        </div>
                                    </div>
                                    <ul className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                        {SECURITY_CONTROLS.map(([key, label]) => (
                                            <li key={key} className="flex items-center justify-between gap-2 rounded-lg border border-(--app-border) px-3 py-2 text-sm">
                                                <span>{s(label)}</span>
                                                <span className={badgeClassName(securityMetrics[key] ? 'success' : 'warning')}>{securityMetrics[key] ? s('active') : s('review_needed')}</span>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            ), s('security_posture_hint'))}
                            {section(s('access_controls'), (
                                <div className="grid gap-3 md:grid-cols-2">
                                    {toggle('maintenance_mode', s('maintenance_mode'), s('maintenance_mode_desc'))}
                                    {toggle('registration_open', s('registration_open'), s('registration_open_desc'))}
                                    {toggle('force_https', s('force_https'), s('force_https_desc'))}
                                    {toggle('mfa_enabled', s('mfa_enabled'), s('mfa_enabled_desc'))}
                                </div>
                            ))}
                            {section(s('mfa_role_policy'), roles.length === 0 ? <p className="text-sm text-(--app-muted-foreground)">{s('mfa_no_roles')}</p> : (
                                <div className="grid gap-2 md:grid-cols-2">
                                    {roles.map((role) => (
                                        <label key={role.id} className="flex items-start gap-3 rounded-lg border border-(--app-border) p-3">
                                            <input type="checkbox" className="mt-0.5 h-4 w-4" checked={form.data.mfa_roles.includes(role.id)}
                                                onChange={(event) => form.setData('mfa_roles', event.target.checked ? [...form.data.mfa_roles, role.id] : form.data.mfa_roles.filter((id) => id !== role.id))} />
                                            <span><span className="block text-sm font-medium">{role.name}</span><span className="block text-xs text-(--app-muted-foreground)">{role.description || s('mfa_role_default_desc')}</span></span>
                                        </label>
                                    ))}
                                </div>
                            ), s('mfa_role_policy_hint'))}
                            {section(s('password_policy'), (
                                <div className="grid gap-3 md:grid-cols-2">
                                    {field('password_min_length', s('password_min_length'), { type: 'number', min: 6, max: 128, unit: s('unit_characters') })}
                                    {toggle('password_require_uppercase', s('require_uppercase'), s('require_uppercase_desc'))}
                                    {toggle('password_require_number', s('require_number'), s('require_number_desc'))}
                                    {toggle('password_require_symbol', s('require_symbol'), s('require_symbol_desc'))}
                                </div>
                            ))}
                            {section(s('session_lockout'), (
                                <div className="grid gap-4 md:grid-cols-2">
                                    {field('session_lifetime', s('session_lifetime'), { type: 'number', min: 5, max: 43200, unit: s('unit_min'), hint: s('session_lifetime_hint') })}
                                    {field('login_max_attempts', s('max_login_attempts'), { type: 'number', min: 1, max: 100, unit: s('unit_tries') })}
                                    {field('lockout_minutes', s('lockout_duration'), { type: 'number', min: 1, max: 1440, unit: s('unit_min') })}
                                    {field('session_warning_minutes', s('session_warning_minutes'), { type: 'number', min: 1, max: 120, unit: s('unit_min'), hint: s('session_warning_hint') })}
                                    <div className="md:col-span-2">{toggle('session_extend_on_activity', s('session_extend_on_activity'), s('session_extend_on_activity_desc'))}</div>
                                </div>
                            ))}
                        </>
                    )}

                    {tab === 'appearance' && (
                        <>
                            {section(s('accent_palette'), (
                                <div className="grid gap-4 md:grid-cols-2">
                                    {select('accent_palette', s('accent_palette'), ['blue', 'teal', 'violet', 'emerald', 'rose'].map((palette) => [palette, s(`palette_${palette}`)]))}
                                    {select('default_theme', s('default_theme'), ['light', 'dark', 'system'].map((theme) => [theme, s(`theme_${theme}`)]))}
                                </div>
                            ), s('accent_palette_hint'))}
                            {section(s('display_options'), (
                                <div className="space-y-4">
                                    {toggle('show_banner_on_login', s('show_banner_login'), s('show_banner_login_desc'))}
                                    <FormField label={<span className="flex items-center gap-2">{s('custom_css')} <span className={badgeClassName('warning')}>{s('custom_css_badge')}</span></span>} description={s('custom_css_hint')} error={errors.custom_css}>
                                        {({ id }) => <Textarea id={id} rows={6} className="font-mono text-xs" value={value('custom_css')} onChange={text('custom_css')} />}
                                    </FormField>
                                </div>
                            ))}
                        </>
                    )}

                    {tab === 'notifications' && (
                        <>
                            {section(s('email_smtp'), (
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="md:col-span-2">{toggle('mail_enabled', s('email_smtp'))}</div>
                                    {field('mail_host', s('smtp_host'))}
                                    {field('mail_port', s('smtp_port'), { type: 'number', min: 1, max: 65535 })}
                                    {select('mail_encryption', s('encryption'), [['tls', 'TLS'], ['ssl', 'SSL'], ['starttls', 'STARTTLS'], ['none', t('court.settings.none')]])}
                                    {select('mail_mailer', s('mailer_driver'), [['smtp', 'SMTP'], ['sendmail', 'Sendmail'], ['log', s('log_debug')]])}
                                    {field('mail_username', s('mail_username'))}
                                    {secret('mail_password', s('mail_password'), s('mail_password_hint'))}
                                    {field('mail_from_address', s('from_address'), { type: 'email' })}
                                    {field('mail_from_name', s('from_name'))}
                                </div>
                            ), undefined, <span className={badgeClassName(form.data.mail_enabled ? 'success' : 'neutral')}>{form.data.mail_enabled ? s('on') : s('off')}</span>)}
                            {section(s('telegram_bot'), (
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="md:col-span-2">{toggle('telegram_enabled', s('enable_telegram'), s('enable_telegram_desc'))}</div>
                                    {secret('telegram_bot_token', s('bot_token'), s('bot_token_hint'))}
                                    {field('telegram_default_chat_id', s('default_chat_id'), { hint: s('default_chat_id_hint') })}
                                </div>
                            ), s('telegram_hint'), <span className={badgeClassName(form.data.telegram_enabled ? 'success' : 'neutral')}>{form.data.telegram_enabled ? s('on') : s('off')}</span>)}
                            {section(s('sms_gateway'), (
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="md:col-span-2">{toggle('sms_enabled', s('sms_gateway'))}</div>
                                    {field('sms_provider', s('sms_provider'), { hint: s('sms_provider_hint') })}
                                    {field('sms_base_url', s('sms_base_url'), { type: 'url', hint: s('sms_base_url_hint') })}
                                    {secret('sms_api_key', s('sms_api_key'))}
                                    {secret('sms_api_secret', s('sms_api_secret'))}
                                    {field('sms_sender_id', s('sms_sender_id'), { hint: s('sms_sender_id_hint') })}
                                </div>
                            ), undefined, <span className={badgeClassName(form.data.sms_enabled ? 'success' : 'neutral')}>{form.data.sms_enabled ? s('on') : s('off')}</span>)}
                        </>
                    )}

                    {tab === 'data' && (
                        <>
                            {section(s('data_overview'), (
                                <dl className="grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
                                    {([['data_driver', databaseMetrics.driver], ['data_connection', databaseMetrics.connection], ['data_database', databaseMetrics.database], ['data_tables', databaseMetrics.table_count], ['data_size', databaseMetrics.size ?? '—'], ['data_migration_batch', databaseMetrics.migration_batch ?? '—']] as [string, ReactNode][]).map(([label, item]) => (
                                        <div key={label} className="rounded-lg border border-(--app-border) p-3"><dt className="text-xs text-(--app-muted-foreground)">{s(label)}</dt><dd className="mt-1 font-semibold">{item}</dd></div>
                                    ))}
                                </dl>
                            ), s('data_overview_hint'))}
                            {section(s('backup_database'), (
                                <div className="space-y-3">
                                    <p className="text-sm text-(--app-muted-foreground)">{s('backup_database_hint')}</p>
                                    <Button type="button" variant="outline" disabled={!databaseMetrics.backup_supported} onClick={() => void downloadBackup()}>{s('download_backup')}</Button>
                                    {!databaseMetrics.backup_supported && <p className="text-xs text-(--app-muted-foreground)">{s('backup_unsupported')}</p>}
                                </div>
                            ), undefined, <span className={badgeClassName(databaseMetrics.backup_supported ? 'success' : 'warning')}>{databaseMetrics.backup_supported ? s('backup_supported') : s('backup_not_supported')}</span>)}
                            {section(s('audit_log_configuration'), (
                                <div className="grid gap-4 md:grid-cols-2">
                                    {toggle('audit_logging_enabled', s('audit_logging_enabled'), s('audit_logging_enabled_desc'))}
                                    {field('audit_retention_days', s('audit_retention_days'), { type: 'number', min: 1, max: 3650, unit: s('unit_days'), hint: s('audit_retention_hint') })}
                                </div>
                            ))}
                        </>
                    )}

                    {tab === 'api' && (
                        <>
                            {section(s('rest_api'), (
                                <div className="grid gap-4 md:grid-cols-2">
                                    {toggle('api_enabled', s('enable_api'), s('enable_api_desc'))}
                                    {field('api_rate_limit', s('rate_limit'), { type: 'number', min: 1, max: 10000, unit: s('unit_req_min'), hint: s('rate_limit_hint') })}
                                </div>
                            ))}
                            {section(s('system_information'), (
                                <dl className="grid gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                                    {Object.entries(systemInfo).map(([key, item]) => (
                                        <div key={key} className="flex justify-between gap-3 border-b border-(--app-border) py-1.5">
                                            <dt className="text-(--app-muted-foreground)">{s(key)}</dt>
                                            <dd className="font-mono text-xs">{key === 'info_server_time' ? <LocalizedDateDisplay value={String(item)} withTime /> : typeof item === 'boolean' ? (item ? s('info_enabled') : s('info_disabled')) : String(item)}</dd>
                                        </div>
                                    ))}
                                </dl>
                            ))}
                            {section(s('cache_management'), <Button type="button" variant="outline" onClick={() => void clearCaches()}>{s('clear_caches')}</Button>, s('cache_hint'))}
                        </>
                    )}

                    <div className="sticky bottom-0 z-10 -mx-1 flex flex-wrap items-center justify-between gap-3 rounded-t-lg border border-(--app-border) bg-(--app-surface) px-4 py-3 shadow-lg">
                        <span className="text-sm text-(--app-muted-foreground)">{form.isDirty ? s('unsaved_changes') : s('save_hint')}</span>
                        <div className="flex gap-2">
                            <Button type="button" variant="outline" disabled={!form.isDirty || form.processing} onClick={() => form.reset()}>{s('discard')}</Button>
                            <Button type="submit" disabled={form.processing}>{form.processing ? s('saving') : s('save_settings')}</Button>
                        </div>
                    </div>
                </form>

                {tab === 'notifications' && (
                    <Card>
                        <form onSubmit={(event) => { event.preventDefault(); smtp.post(route('settings.system.testSmtp'), { preserveScroll: true }); }} className="flex flex-wrap items-end gap-3">
                            <FormField label={s('smtp_test_recipient')} required error={smtp.errors.recipient ?? (smtp.errors as Record<string, string | undefined>).smtp_test} className="min-w-64 flex-1">
                                {({ id }) => <Input id={id} type="email" required value={smtp.data.recipient} onChange={(event) => smtp.setData('recipient', event.target.value)} />}
                            </FormField>
                            <Button type="submit" variant="outline" disabled={smtp.processing}>{s('smtp_test_send')}</Button>
                        </form>
                    </Card>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
